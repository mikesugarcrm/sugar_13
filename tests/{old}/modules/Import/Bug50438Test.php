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

/*
 * This tests whether a relationship with parent bean is saved during import.  We simulate a call being imported with
 * parent_id and parent_type columns filled out, which should save the relationship even during import
 * @ticket 50438
 */


class Bug50438Test extends TestCase
{
    public $contact;
    public $fileArr;
    public $call_id;

    protected function setUp(): void
    {
        global $currentModule;
        SugarTestHelper::setUp('app_list_strings');
        $this->call_id = create_guid();
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Contacts');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //create a contact
        $this->contact = new Contact();
        $this->contact->first_name = 'Joe UT ';
        $this->contact->last_name = 'Smith UT 50438';
        $this->contact->disable_custom_fields = true;
        $this->contact->save();

        //create array to output as import file using the new contact as the related parent
        $this->fileArr = [
            0 => "\"{$this->call_id}\",\"Call for Unit Test 50438\",\"Planned\", \"{$this->contact->module_dir}\",\"{$this->contact->id}\"",
        ];
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->call_id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id='{$this->contact->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->call_id);
        unset($this->contact);
        unset($this->fileArr);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
    }

    public function testParentsAreRelatedDuringImport()
    {
        // FIXME TY-1310: figure out why this test fails
        // suspect it has to do with the colnum_2 being 'status'
        $file = 'upload://test50438.csv';
        $ret = file_put_contents($file, $this->fileArr);
        $this->assertGreaterThan(0, $ret, 'Failed to write to ' . $file . ' for content ' . var_export($this->fileArr, true));

        $importSource = new ImportFile($file, ',', '"');

        $bean = BeanFactory::newBean('Calls');
        $bean->date_start = TimeDate::getInstance()->getNow()->asDb();

        $_REQUEST['columncount'] = 5;
        $_REQUEST['colnum_0'] = 'id';
        $_REQUEST['colnum_1'] = 'subject';
        $_REQUEST['colnum_2'] = 'status';
        $_REQUEST['colnum_3'] = 'parent_type';
        $_REQUEST['colnum_4'] = 'parent_id';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';
        $_REQUEST['importlocale_dateformat'] = 'm/d/y';
        $_REQUEST['importlocale_timeformat'] = 'h:i:s';

        $importer = new Importer($importSource, $bean);
        $importer->import();

        //fetch the bean using the passed in id and get related contacts
        $call = new Call();
        $call->retrieve($this->call_id);
        $call->load_relationship('contacts');
        $related_contacts = $call->contacts->get();

        //test that the contact id is in the array of related contacts.
        $this->assertContains($this->contact->id, $related_contacts, ' Contact was not related during simulated import despite being set in related parent id');
        unset($call);

        /*
        if (is_file($file)) {
            unlink($file);
        }
        */
    }
}
