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

namespace Popup;

use PHPUnit\Framework\TestCase;
use PopupSmarty;
use SugarTestContactUtilities;
use SugarTestHelper;

class PopupSmartyTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_language'] = 'en_us';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Contacts']);
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers PopupSmarty::_build_field_defs
     */
    public function testBuildFieldDefs()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'test';

        $popupSmarty = new PopupSmarty($contact, $contact->module_dir);
        // the following should not cause Warning or TypeError: Illegal offset type in isset or empty
        $contact->field_defs['first_name']['options'] = ['foo'];
        $popupSmarty->_build_field_defs();

        $this->assertTrue(true); // the assertion in case of no exception
    }

    /**
     * @covers PopupSmarty::setup
     */
    public function testSetup()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'test';

        $popupSmarty = new PopupSmarty($contact, $contact->module_dir);

        $popupSmarty->listviewdefs = ['Contacts' => ['id' => ['default' => ['foo'], 'label' => 'test']]];
        $popupSmarty->displayColumns = ['id' => ['width' => 1, 'label' => 'test']];
        $popupSmarty->fieldDefs = [];
        $popupSmarty->view = 'popup';
        $popupSmarty->tpl = 'include/Popups/tpls/PopupGeneric.tpl';

        // the following should not cause Warning or TypeError: get_object_vars() expects parameter 1 to be object, null given
        $_REQUEST['request_data'] = '1';
        $popupSmarty->setup('');

        $this->assertTrue(true); // the assertion in case of no exception
    }
}
