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

class ContactFormBaseTest extends TestCase
{
    public $form;
    public $contact1;

    protected function setUp(): void
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Mike' AND last_name = 'TheSituationSorrentino'");
        $this->form = new ContactFormBase();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Contacts');

        //Create a test Contact
        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact1->first_name = 'Collin';
        $this->contact1->last_name = 'Lee';
        $this->contact1->save();
        $this->contact1->emailAddress->addAddress('clee@sugarcrm.com', true, false);
        $this->contact1->emailAddress->save($this->contact1->id, $this->contact1->module_dir);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($this->form);
        unset($this->contact1);
    }

    /**
     * contactsProvider
     */
    public function contactsProvider()
    {
        return [
            ['Collin', 'Lee', true],
            ['', 'Lee', true],
            ['Mike', 'TheSituationSorrentino', false],
        ];
    }


    /**
     * testCreatingDuplicateContact
     *
     * @dataProvider contactsProvider
     */
    public function testCreatingDuplicateContact($first_name, $last_name, $hasDuplicate)
    {
        $_POST['first_name'] = $first_name;
        $_POST['last_name'] = $last_name;
        $_POST['Contacts0emailAddresss0'] = 'clee@sugarcrm.com';

        $rows = $this->form->checkForDuplicates();

        if ($hasDuplicate) {
            $this->assertTrue(safeCount($rows) > 0, 'Assert that checkForDuplicates returned matches');
            $this->assertEquals($rows[0]['last_name'], $last_name, 'Assert duplicate row entry last_name is ' . $last_name);
            $output = $this->form->buildTableForm($rows);
            $this->assertMatchesRegularExpression('/\&action\=DetailView\&record/', $output);
        } else {
            $this->assertTrue(empty($rows), 'Assert that checkForDuplicates returned no matches');
        }
    }
}
