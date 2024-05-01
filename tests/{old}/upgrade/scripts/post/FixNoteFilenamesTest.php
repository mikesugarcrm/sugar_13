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
 * Test for fixing Note filenames on upgrade
 */
class FixNoteFilenamesTest extends UpgradeTestCase
{
    protected $script;
    protected $testNote;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = $this->upgrader->getScript('post', '9_FixNoteFilenames');
        $this->script->upgrader->db = DBManagerFactory::getInstance();

        $filename = "There is a newline \r\nin this string.xls";
        $noteData = [
            'name' => $filename,
            'filename' => $filename,
        ];
        $this->testNote = SugarTestNoteUtilities::createNote(null, $noteData);
    }

    protected function tearDown(): void
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }

    public function testExecuteQuery()
    {
        $noteBean = BeanFactory::retrieveBean('Notes', $this->testNote->id, ['use_cache' => false]);
        $this->assertEquals("There is a newline \r\nin this string.xls", $noteBean->name);
        $this->assertEquals("There is a newline \r\nin this string.xls", $noteBean->filename);

        SugarTestReflection::callProtectedMethod($this->script, 'executeQuery');

        $noteBean = BeanFactory::retrieveBean('Notes', $this->testNote->id, ['use_cache' => false]);
        $this->assertEquals('There is a newline in this string.xls', $noteBean->name);
        $this->assertEquals('There is a newline in this string.xls', $noteBean->filename);
    }
}
