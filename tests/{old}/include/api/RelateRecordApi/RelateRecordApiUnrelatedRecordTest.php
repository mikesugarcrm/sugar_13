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

use PHPUnit\Framework\TestCase;

/**
 * @covers RelateRecordApi
 */
class RelateRecordApiUnrelatedRecordTest extends TestCase
{
    /**#@+
     * @var Account
     */
    private static $account1;
    private static $account2;

    /**#@-*/

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        self::$account1 = SugarTestAccountUtilities::createAccount();
        self::$account2 = SugarTestAccountUtilities::createAccount();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testCreateUnrelatedRecord()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new RelateRecordApi();

        $result = $api->createRelatedRecord($service, [
            'module' => 'Accounts',
            'record' => self::$account1->id,
            'link_name' => 'contacts',
            'account_id' => self::$account2->id,
        ]);

        SugarTestContactUtilities::setCreatedContact([$result['related_record']['id']]);
        $this->assertEquals(self::$account2->id, $result['related_record']['account_id']);
    }

    public function testGetUnrelatedRecord()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new RelateRecordApi();
        $contact = SugarTestContactUtilities::createContact();

        $this->expectException(SugarApiExceptionNotFound::class);
        $api->getRelatedRecord($service, [
            'module' => 'Accounts',
            'record' => self::$account1->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
        ]);
    }

    public function testDeleteLinkToUnrelatedRecord()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new RelateRecordApi();
        $contact = SugarTestContactUtilities::createContact();

        $this->expectException(SugarApiExceptionNotFound::class);
        $api->deleteRelatedLink($service, [
            'module' => 'Accounts',
            'record' => self::$account1->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
        ]);
    }
}
