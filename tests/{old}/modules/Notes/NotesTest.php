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

/**
 * @coversDefaultClass Note
 */
class NotesTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestCaseUtilities::removeAllCreatedCases();
        unset($GLOBALS['current_user']);
    }

    public function setContactProvider()
    {
        return [
            [
                'Cases',
                '',
                'parent_contact_id',
                'parent_contact_id',
            ],
            [
                'Cases',
                'note_contact_id',
                'parent_contact_id',
                'note_contact_id',
            ],
            [
                'Tasks',
                '',
                'parent_contact_id',
                '',
            ],
            [
                'Tasks',
                'note_contact_id',
                'parent_contact_id',
                'note_contact_id',
            ],
        ];
    }

    /**
     * @param $parentModule
     * @param $noteContactId
     * @param $parentContactId
     * @param $expectedContactId
     * @covers ::setContactId
     * @dataProvider setContactProvider
     */
    public function testSetContactId($parentModule, $noteContactId, $parentContactId, $expectedContactId)
    {
        if ($parentModule == 'Cases') {
            $parent = SugarTestCaseUtilities::createCase();
            $parent->primary_contact_id = $parentContactId;
        } elseif ($parentModule == 'Tasks') {
            $parent = SugarTestTaskUtilities::createTask();
            $parent->contact_id = $parentContactId;
        } else {
            $this->fail('No hanlder for module:' . $parentModule);
        }

        $note = SugarTestNoteUtilities::createNote(null, [
            'parent_type' => $parentModule,
            'parent_id' => $parent->id,
            'contact_id' => $noteContactId,
        ]);
        $this->assertSame($expectedContactId, $note->contact_id);
    }

    /**
     * @ticket 19499
     */
    public function testCreateProperNameFieldContainsFirstAndLastName()
    {
        $contact = new Contact();
        $contact->first_name = 'Josh';
        $contact->last_name = 'Chi';
        $contact->salutation = 'Mr';
        $contact->title = 'VP Operations';
        $contact->disable_row_level_security = true;
        $contact_id = $contact->save();

        $note = SugarTestNoteUtilities::createNote(null, [
            'contact_id' => $contact_id,
        ]);

        $note->disable_row_level_security = true;
        $note->retrieve();

        $this->assertStringContainsString($contact->first_name, $note->contact_name);
        $this->assertStringContainsString($contact->last_name, $note->contact_name);

        $GLOBALS['db']->query('DELETE FROM contacts WHERE id =\'' . $contact_id . '\'');
    }

    public function testSave_NoFile_FileMetadataIsDefaulted()
    {
        $note = SugarTestNoteUtilities::createNote();
        $this->assertEmpty($note->file_mime_type, 'Should not store a mime type when there is no file');
        $this->assertEmpty($note->file_ext, 'Should not store an extension when there is no file');
        $this->assertSame(0, $note->file_size);
    }

    public function testSave_FileFound_FileMetadataIsSaved()
    {
        $note = SugarTestNoteUtilities::createNote();

        $file = "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $filesize = filesize($file);

        $note->filename = 'quote.pdf';
        $note->save(false);

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');
    }

    public function testSave_FileFoundAtUploadId_FileMetadataIsSaved()
    {
        $note = SugarTestNoteUtilities::createNote();
        $note->upload_id = Uuid::uuid1();

        $file = "upload://{$note->upload_id}";
        file_put_contents($file, $note->upload_id);
        $filesize = filesize($file);

        $note->filename = 'quote.pdf';
        $note->save(false);

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');
    }

    public function testSave_FileFoundInTemporaryLocation_FileMetadataIsSaved()
    {
        $filename = Uuid::uuid1();
        $file = "upload://tmp/{$filename}";
        file_put_contents($file, $filename);
        $filesize = filesize($file);

        $uploadFile = $this->getMockBuilder('UploadFile')
            ->disableOriginalConstructor()
            ->setMethods(['get_temp_file_location'])
            ->getMock();
        $uploadFile->method('get_temp_file_location')->willReturn($file);

        $note = BeanFactory::newBean('Notes');
        $note->file = $uploadFile;
        $note->filename = 'quote.pdf';
        $note->save(false);
        SugarTestNoteUtilities::setCreatedNotes([$note->id]);

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');

        unlink($file);
    }

    public function markDeletedProvider()
    {
        return [
            [
                [
                    'upload_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ],
                true,
            ],
            [
                [],
                false,
            ],
        ];
    }

    /**
     * @covers ::mark_deleted
     * @dataProvider markDeletedProvider
     */
    public function testMarkDeleted($data, $expected)
    {
        $note = SugarTestNoteUtilities::createNote('', $data);

        $file = $note->upload_id ? "upload://{$note->upload_id}" : "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $this->assertFileExists($file);

        $note->mark_deleted($note->id);
        $this->assertSame($expected, file_exists($file));
    }

    public function deleteAttachmentProvider()
    {
        return [
            [
                [
                    'filename' => 'foo.jpg',
                    'file_mime_type' => 'image/jpg',
                    'file_ext' => 'jpg',
                    'file_size' => 111,
                    'email_type' => 'Emails',
                    'email_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                    'upload_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ],
                true,
            ],
            [
                [
                    'filename' => 'foo.jpg',
                    'file_mime_type' => 'image/jpg',
                    'file_ext' => 'jpg',
                    'file_size' => 111,
                    'email_type' => 'Emails',
                    'email_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::deleteAttachment
     * @dataProvider deleteAttachmentProvider
     */
    public function testDeleteAttachment($data, $expected)
    {
        $note = SugarTestNoteUtilities::createNote('', $data);

        $file = $note->upload_id ? "upload://{$note->upload_id}" : "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $this->assertFileExists($file);

        $note->deleteAttachment();
        $this->assertSame($expected, file_exists($file));

        $note = BeanFactory::retrieveBean('Notes', $note->id, ['use_cache' => false]);
        $this->assertEmpty($note->filename, 'The filename should be empty');
        $this->assertEmpty($note->file_mime_type, 'The file_mime_type should be empty');
        $this->assertEmpty($note->file_ext, 'The file_ext should be empty');
        $this->assertEmpty($note->file_size, 'The file_size should be empty');
        $this->assertEmpty($note->file_source, 'The file_source should be empty');
        $this->assertEmpty($note->email_type, 'The email_type should be empty');
        $this->assertEmpty($note->email_id, 'The email_id should be empty');
        $this->assertEmpty($note->upload_id, 'The upload_id should be empty');
        $this->assertEmpty($note->file, 'There should not be an UploadFile object');

        unlink($file);
    }

    public function testSaveNoteAndSyncTeamIds()
    {
        // initial Note
        $note = SugarTestNoteUtilities::createNote();

        // add attachment
        $note2 = SugarTestNoteUtilities::createNote();
        $note->load_relationship('attachments');
        $note->attachments->add($note2);

        // add 2nd level nested attachment
        $note3 = SugarTestNoteUtilities::createNote();
        $note2->load_relationship('attachments');
        $note2->attachments->add($note3);

        // add 3rd level nested attachment
        $note4 = SugarTestNoteUtilities::createNote();
        $note3->load_relationship('attachments');
        $note3->attachments->add($note4);

        // assign a new team instead of initial one
        $note->team_id = "new_test_team";
        $note->team_set_id = "new_test_team_set_id";
        $note->save();

        // attachment team should be in sync with parent note
        $notesToCheck = [
            $note,
            $note2,
            $note3,
            $note4,
        ];

        foreach ($notesToCheck as $noteIndex => $noteToCheck) {
            // check already fetched bean
            $this->assertEquals('new_test_team', $noteToCheck->team_id, 'Incorrect team for Note #' . $noteIndex);

            // check real DB value
            $noteToCheck = BeanFactory::getBean(
                $noteToCheck->getModuleName(),
                $noteToCheck->id,
                ['use_cache' => false, 'disable_row_level_security' => true]
            );
            $this->assertEquals('new_test_team', $noteToCheck->team_id, 'Incorrect team for Note #' . $noteIndex);
        }
    }
}
