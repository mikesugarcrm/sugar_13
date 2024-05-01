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

class Bug47069Test extends TestCase
{
    /**
     * @var mixed|\Note
     */
    public $note1;
    /**
     * @var mixed|\Note
     */
    public $note2;

    protected function setUp(): void
    {
        $GLOBALS['action'] = 'async';
        $GLOBALS['module'] = 'Administration';
        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['mod_strings'] = return_module_language('en_us', 'Administration');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['module']);
        unset($GLOBALS['action']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
        unset($_REQUEST);
        $GLOBALS['db']->query("DELETE FROM notes WHERE id IN ('" . $this->note1->id . "','" . $this->note2->id . "')");
        // Just in case there is a custom table here
        if ($GLOBALS['db']->tableExists('notes_cstm')) {
            $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN ('" . $this->note1->id . "','" . $this->note2->id . "')");
        }
    }

    public function testRepairXSSNotDuplicating()
    {
        $out = null;
        $this->note1 = new Note();
        $this->note1->id = create_guid();
        $this->note1->new_with_id = true;
        $this->note1->name = '[Bug47069] Not deleted Note';
        $this->note1->description = "This note shouldn't be deleted.";
        $this->note1->save();

        $this->note2 = new Note();
        $this->note2->id = create_guid();
        $this->note2->new_with_id = true;
        $this->note2->name = '[Bug47069] Deleted Note';
        $this->note2->description = 'This note should be deleted.';
        $this->note2->deleted = 1;
        $this->note2->save();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once 'modules/Administration/Async.php';
        $firstEstimate = $out;
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'repairXssExecute';
        $_REQUEST['bean'] = 'Notes';
        $_REQUEST['id'] = json_encode([$this->note1->id, $this->note2->id]);
        require_once 'modules/Administration/Async.php';
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once 'modules/Administration/Async.php';
        $secondEstimate = $out;
        ob_end_clean();

        $this->assertEquals($firstEstimate['count'], $secondEstimate['count'], 'The record count should not increase after a repair XSS');
    }
}
