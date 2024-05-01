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

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiAttachmentsTest extends EmailsApiIntegrationTestCase
{
    public static function tearDownAfterClass(): void
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::createRecord
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers EmailsRelateRecordApi::createRelatedRecord
     * @covers ModuleApi::moveTemporaryFiles
     */
    public function testCreateRecord()
    {
        $uploadId = Uuid::uuid1();
        file_put_contents("upload://tmp/{$uploadId}", 'test');

        $docRevisionId = Uuid::uuid1();
        file_put_contents("upload://{$docRevisionId}", 'test');

        $args = [
            'state' => Email::STATE_DRAFT,
            'attachments' => [
                'create' => [
                    [
                        'filename_guid' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ],
                    [
                        'upload_id' => $docRevisionId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => 'DocumentRevisions',
                    ],
                ],
            ],
        ];
        $record = $this->createRecord($args);

        $this->assertFileDoesNotExist("upload://tmp/{$uploadId}", 'The file should have been moved');

        $attachments = $this->getRelatedRecords($record['id'], 'attachments');
        $this->assertCount(2, $attachments['records']);
        $this->assertFiles($attachments['records']);

        unlink("upload://{$docRevisionId}");
    }

    /**
     * @covers ::updateRecord
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::unlinkRelatedRecords
     * @covers EmailsRelateRecordApi::createRelatedRecord
     * @covers ModuleApi::moveTemporaryFiles
     */
    public function testUpdateRecord()
    {
        $uploadId = Uuid::uuid1();
        file_put_contents("upload://tmp/{$uploadId}", 'test');

        $templateId = Uuid::uuid1();
        file_put_contents("upload://{$templateId}", 'test');

        $attachment1 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment1->id}", 'test');

        $attachment2 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment2->id}", 'test');

        $email = SugarTestEmailUtilities::createEmail(
            '',
            [
                'state' => Email::STATE_DRAFT,
                'assigned_user_id' => $GLOBALS['current_user']->id,
            ]
        );
        $email->load_relationship('attachments');
        $email->attachments->add($attachment1);
        $email->attachments->add($attachment2);
        $this->assertCount(2, $email->attachments->get(), 'Should start with two attachments');

        $args = [
            'attachments' => [
                'create' => [
                    [
                        'filename_guid' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ],
                    [
                        'upload_id' => $templateId,
                        'name' => 'bbbbb',
                        'filename' => 'bbbbb.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => 'EmailTemplates',
                    ],
                ],
                'delete' => [
                    $attachment2->id,
                ],
            ],
        ];
        $record = $this->updateRecord($email->id, $args);

        $attachments = $this->getRelatedRecords($record['id'], 'attachments');
        $this->assertCount(3, $attachments['records'], 'Should have three attachments after updating');
        $this->assertFiles($attachments['records']);

        $found = array_filter($attachments['records'], function ($attachment) use ($attachment2) {
            return $attachment['id'] === $attachment2->id;
        });
        $this->assertCount(0, $found, "{$attachment2->id} should not have been returned");
        $this->assertFileDoesNotExist(
            "upload://{$attachment2->id}",
            "The file {$attachment2->id} should have been deleted"
        );

        unlink("upload://{$templateId}");
    }
}
