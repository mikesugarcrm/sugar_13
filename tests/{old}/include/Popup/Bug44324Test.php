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

class Bug44324Test extends TestCase
{
    public $contact;

    protected function setUp(): void
    {
        $GLOBALS['current_language'] = 'en_us';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Contacts']);
        SugarTestHelper::setUp('current_user');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->salutation = 'Ms.';
        $this->contact->first_name = 'Lady';
        $this->contact->last_name = 'Gaga';
        //Save contact with salutation
        $this->contact->save();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testSearchNamePopulatedCorrectly()
    {
        $popupSmarty = new PopupSmarty($this->contact, $this->contact->module_dir);
        $this->contact->_create_proper_name_field();
        $search_data = [];
        $search_data[] = ['ID' => $this->contact->id, 'NAME' => $this->contact->name, 'FIRST_NAME' => $this->contact->first_name, 'LAST_NAME' => $this->contact->last_name];

        $data = ['data' => $search_data];
        $data['pageData']['offsets']['lastOffsetOnPage'] = 0;
        $data['pageData']['offsets']['current'] = 0;
        $popupSmarty->data = $data;
        $popupSmarty->fieldDefs = [];
        $popupSmarty->view = 'popup';
        $popupSmarty->tpl = 'include/Popups/tpls/PopupGeneric.tpl';
        $this->assertMatchesRegularExpression('/\"NAME\":\"Ms. Lady Gaga\"/', $popupSmarty->display());
    }
}
