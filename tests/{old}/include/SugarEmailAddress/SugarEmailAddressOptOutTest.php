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

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @coversDefaultClass SugarEmailAddress
 */
class SugarEmailAddressOptOutTest extends TestCase
{
    private $configOptoutBackUp;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }
    }

    public function optoutDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::save
     * @dataProvider optoutDataProvider
     */
    public function testCreateContactWithNewEmailAddress_ConfigureOptoutDefault_OptoutPropertySet(bool $defaultOptout)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $defaultOptout;
        $contact = SugarTestContactUtilities::createContact();

        $contact = BeanFactory::retrieveBean('Contacts', $contact->id);
        $actualOptout = (bool)$contact->emailAddress->addresses[0]['opt_out'];
        $this->assertSame(
            $defaultOptout,
            $actualOptout,
            'Expected New Contact Email to Match Configured Optout Default: ' . intval($defaultOptout)
        );
    }
}
