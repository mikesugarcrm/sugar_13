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
 * Bug #46411
 * Importing Calls will not populate Leads or Contacts Subpanel
 *
 * @author adetskin@sugarcrm.com
 * @ticket 46411
 */
class Bug46411Test extends TestCase
{
    /**
     * @var \stdClass|mixed
     */
    public $importSource;

    /**
     * Importing Calls will not populate Leads or Contacts Subpanel
     *
     * @group 46411
     */
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->importSource = new stdClass();
        $this->importSource->columncount = 2;
        $this->importSource->colnum_0 = 'date_entered';
        $this->importSource->colnum_1 = 'last_name';
        $this->importSource->import_module = 'Calls';
        $this->importSource->importlocale_charset = 'UTF-8';
        $this->importSource->importlocale_dateformat = 'm/d/Y';
        $this->importSource->importlocale_timeformat = 'h:i a';
        $this->importSource->importlocale_timezone = 'GMT';
        $this->importSource->importlocale_default_currency_significant_digits = '2';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_dec_sep = '.';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_default_locale_name_format = 's f l';
        $this->importSource->importlocale_num_grp_sep = ',';
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    public function testMissingFields()
    {
        $bean = $this->getMockBuilder('Call')
            ->setMethods([
                'get_importable_fields',
                'populateDefaultValues',
                'beforeImportSave',
                'save',
                'afterImportSave',
                'writeRowToLastImport',
            ])
            ->getMock();

        $bean->expects($this->any())
            ->method('get_importable_fields')
            ->will($this->returnValue([
                'account_id' => 'accounts',
                'opportunity_id' => 'opportunities',
                'contact_id' => 'contacts',
                'case_id' => 'cases',
                'user_id' => 'users',
                'assigned_user_id' => 'users',
                'note_id' => 'notes',
                'lead_id' => 'leads',
            ]));

        $bean->expects($this->any())
            ->method('populateDefaultValues')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('beforeImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('save')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('afterImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('writeRowToLastImport')
            ->will($this->returnValue('foo'));

        $bean->date_modified = 'true';
        $bean->fetched_row = ['date_modified' => ''];
        $bean->object_name = '';

        $lead = SugarTestLeadUtilities::createLead();
        $a = new bug46411_Importer_mock($this->importSource, $bean);
        //        $b = new Call();

        $bean->parent_type = 'leads';
        $bean->parent_id = $lead->id;
        //        $bean->relationship_fields = $b->relationship_fields;

        $a->saveImportBean($bean, false);
        $this->assertEquals($bean->parent_id, $bean->lead_id);
    }
}

class bug46411_Importer_mock extends Importer
{
    public function saveImportBean($focus, $newRecord)
    {
        return parent::saveImportBean($focus, $newRecord);
    }
}
