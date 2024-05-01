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
 * @ticket 23816
 *
 *      Original Bug Steps to reproduce:
 *      1) Start on the Contacts listview, click on a contact name to open the record in the detailview.
 *      2) Notice the VCR controls in the upper right of the layout - now click the edit button to go to the editview.
 *      3) Save an edit - which will return you back to the detailview
 */
class Bug23816Test extends TestCase
{
    public function testVcrAfterSave()
    {
        $return_id = '1bb73165-dcd7-21b2-b648-4ded8dce0bf8';
        $_REQUEST['return_action'] = 'DetailView';
        $_REQUEST['return_module'] = 'Accounts';
        $_REQUEST['return_id'] = $return_id;
        $_REQUEST['offset'] = 4;

        require_once 'include/formbase.php';
        $url = buildRedirectURL($return_id, 'Accounts');

        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_id']);
        unset($_REQUEST['offset']);

        $this->assertStringContainsString('offset=4', $url);
    }
}
