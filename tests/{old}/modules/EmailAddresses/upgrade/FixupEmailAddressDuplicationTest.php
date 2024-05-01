<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * @coversDefaultClass SugarUpgradeFixupEmailAddressDuplication
 */
class FixupEmailAddressDuplicationTest extends UpgradeTestCase
{
    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $emailAddress = 'foo_' . \Sugarcrm\Sugarcrm\Util\Uuid::uuid1() . '@bar.com';
        $emailAddressCaps = sugarStrToUpper($emailAddress);

        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $contact1 = SugarTestContactUtilities::createContact();
        $address1 = $this->getEmailAddressBean($contact1->email1);

        $contact2 = SugarTestContactUtilities::createContact();
        $address2 = $this->getEmailAddressBean($contact2->email1);

        $rel = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $rel->add($email, $this->createEmailParticipant($contact1, $address1));
        $rel->add($email, $this->createEmailParticipant($contact2, $address2));

        $oe1 = BeanFactory::newBean('OutboundEmail');
        $oe1->email_address_id = $address1->id;
        $oe1->reply_to_email_address_id = $address1->id;
        $oe1->save();

        $oe2 = BeanFactory::newBean('OutboundEmail');
        $oe2->email_address_id = $address2->id;
        $oe2->reply_to_email_address_id = $address2->id;
        $oe2->save();

        $this->duplicateEabrRecord($address1->id);
        $this->duplicateEabrRecord($address2->id);

        $expectedEaCount = 1;
        $expectedEabrRecords = 2;
        $expectedEearCount = 2;
        $expectedOeCount = 2;

        $sql = "UPDATE email_addresses set email_address='{$emailAddress}', email_address_caps='{$emailAddressCaps}'" .
            " WHERE id IN ('$address1->id','$address2->id')";
        DBManagerFactory::getConnection()->executeStatement($sql);

        // Run The Script
        $script = $this->upgrader->getScript('post', 'FixupEmailAddressDuplication');
        $script->db = $GLOBALS['db'];
        $script->from_version = '9.0.0';
        $script->run();

        $eaBean = $this->getEmailAddressBean($emailAddress);
        $eaRecords = $this->getRecords('email_addresses', 'id', $eaBean->id);
        $this->assertCount($expectedEaCount, $eaRecords, 'Expecting a single email_address record');
        $eaId = $eaBean->id;

        $eabrRecords = $this->getRecords('email_addr_bean_rel', 'email_address_id', $eaId, true);
        $this->assertCount($expectedEabrRecords, $eabrRecords, 'email_addr_bean_rel record count mismatch');

        $eearRecords = $this->getRecords('emails_email_addr_rel', 'email_address_id', $eaId, true);
        $this->assertCount($expectedEearCount, $eearRecords, 'emails_email_addr_rel record count mismatch');

        $oeEaIdRecords = $this->getRecords('outbound_email', 'email_address_id', $eaId, true);
        $this->assertCount($expectedOeCount, $oeEaIdRecords, 'outbound_email email_address_id record count mismatch');

        $oeRtIdRecords = $this->getRecords('outbound_email', 'reply_to_email_address_id', $eaId, true);
        $this->assertCount(
            $expectedOeCount,
            $oeRtIdRecords,
            'outbound_email reply_to_email_address_id record count mismatch'
        );
    }

    private function getRecords($tableName, $columnName = '', $columnValue = '', $match = true)
    {
        $sql = "SELECT * FROM {$tableName} WHERE deleted=0";
        if (!empty($columnName) && !empty($columnValue)) {
            $comparator = $match ? '=' : '<>';
            $sql .= " AND {$columnName} {$comparator} '{$columnValue}'";
        }
        $records = [];
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql);
        while ($row = $stmt->fetchAssociative()) {
            $records[] = $row;
        }
        return $records;
    }

    private function duplicateEabrRecord($emailAddressId)
    {
        $records = $this->getRecords('email_addr_bean_rel', 'email_address_id', $emailAddressId);
        foreach ($records as $rec) {
            $rec['id'] = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
            $fields = 'id,email_address_id,bean_id,bean_module';

            $sql = "INSERT INTO email_addr_bean_rel ({$fields}) VALUES (" .
                $GLOBALS['db']->quoted($rec['id']) . ', ' .
                $GLOBALS['db']->quoted($rec['email_address_id']) . ', ' .
                $GLOBALS['db']->quoted($rec['bean_id']) . ', ' .
                $GLOBALS['db']->quoted($rec['bean_module']) . ')';
            $GLOBALS['db']->query($sql);
        }
    }

    private function getEmailAddressBean($emailAddress)
    {
        $bean = BeanFactory::newBean('EmailAddresses');
        $q = new SugarQuery();
        $q->from($bean)->where()->equals('email_address_caps', sugarStrToUpper($emailAddress));
        $matches = $bean->fetchFromQuery($q);
        if (!empty($matches)) {
            return array_values($matches)[0];
        }
        return null;
    }

    private function createEmailParticipant($bean, $address = null)
    {
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        BeanFactory::registerBean($ep);
        if ($bean) {
            $ep->parent_type = $bean->getModuleName();
            $ep->parent_id = $bean->id;
        }

        if ($address) {
            $ep->email_address_id = $address->id;
        }
        return $ep;
    }
}
