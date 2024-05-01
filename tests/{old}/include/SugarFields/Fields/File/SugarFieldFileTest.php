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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

class SugarFieldFileTest extends TestCase
{
    protected $origNote;
    protected $newNote;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->origNote = BeanFactory::newBean('Notes');
        $this->origNote->name = 'test note';
        $this->origNote->file_mime_type = 'plain/text';
        $this->origNote->filename = 'test.txt';
        $this->origNote->save();
        file_put_contents("upload://{$this->origNote->id}", 'test');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        if (!empty($this->origNote->id)) {
            $GLOBALS['db']->query("delete from notes where id = '{$this->origNote->id}'");
            if (file_exists("upload://{$this->origNote->id}")) {
                unlink("upload://{$this->origNote->id}");
            }
        }
        if (!empty($this->newNote->id)) {
            $GLOBALS['db']->query("delete from notes where id = '{$this->newNote->id}'");
            if (file_exists("upload://{$this->newNote->id}")) {
                unlink("upload://{$this->newNote->id}");
            }
        }
    }

    /**
     * Test duplicating files
     */
    public function testApiSave()
    {
        $this->newNote = BeanFactory::newBean('Notes');
        $this->newNote->id = create_guid();

        $submittedData = [
            'name' => 'new note',
            'filename' => 'test.txt',
            'filename_duplicateBeanId' => $this->origNote->id,
        ];

        $sfh = new SugarFieldHandler();
        $field = $sfh->getSugarField($this->newNote->field_defs['filename']['type']);
        $field->apiSave($this->newNote, $submittedData, 'filename', $this->newNote->field_defs['filename']);

        $this->assertFileExists("upload://{$this->newNote->id}");
    }

    public function testApiSave_ReusesExistingFile()
    {
        $this->newNote = BeanFactory::getBean('Notes');
        $this->newNote->id = Uuid::uuid1();
        $submittedData = [
            'name' => 'new note',
            'filename' => 'test.txt',
            'filename_duplicateBeanId' => $this->origNote->id,
            'email_type' => 'Emails',
            'email_id' => Uuid::uuid1(),
        ];

        $sfh = new SugarFieldHandler();
        $field = $sfh->getSugarField($this->newNote->field_defs['filename']['type']);
        $field->apiSave($this->newNote, $submittedData, 'filename', $this->newNote->field_defs['filename']);

        $this->assertEquals($this->origNote->id, $this->newNote->getUploadId());
        $this->assertFileDoesNotExist("upload://{$this->newNote->id}");
    }
}
