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

class DocApiUploadTest extends TestCase
{
    /**
     * @var string|bool|mixed
     */
    public $file;
    public $documents;


    protected function setUp(): void
    {
        SugarTestHelper::setup('current_user');
        SugarTestHelper::setUp('files');
        $document = BeanFactory::newBean('Documents');
        $document->name = 'Documents Upload Test' . random_int(0, 1000);
        $document->save();
        $this->documents[] = $document;
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_FILES = [];
        foreach ($this->documents as $document) {
            $document->mark_deleted($document->id);
        }
        SugarTestHelper::tearDown();
    }

    public function testDocUloadApi()
    {
        $api = new DocumentsFileApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();
        $this->file = tempnam(sys_get_temp_dir(), self::class);
        SugarTestHelper::saveFile($this->file);
        file_put_contents($this->file, create_guid());

        $_FILES = [
            'filename' => [
                'name' => 'test.txt',
                'size' => filesize($this->file),
                'tmp_name' => $this->file,
                'error' => 0,
                '_SUGAR_API_UPLOAD' => true,
            ],
        ];


        $result = $api->saveFilePost($rest, ['module' => 'Documents', 'record' => $this->documents[0]->id, 'field' => 'filename']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('record', $result);

        $this->assertEquals($this->documents[0]->id, $result['record']['id'], 'Wrong ID');
        $this->assertEquals('test.txt', $result['record']['filename'], 'Wrong filename');
        $this->assertNotEmpty($result['record']['document_revision_id'], 'Revision missing');
        $this->assertEquals('test.txt', $result['filename']['name'], 'Filename missing');
        $this->assertStringContainsString(
            $this->documents[0]->id . '/file/filename',
            $result['filename']['uri']
        );
        $this->assertEquals('1', $result['record']['revision'], 'Wrong revision');
        $this->assertNotEmpty($result['record']['last_rev_create_date'], 'Revision date not set');

        $rev1 = $result['record']['document_revision_id'];

        file_put_contents($this->file, create_guid());
        $_FILES = [
            'filename' => [
                'name' => 'test2.txt',
                'size' => filesize($this->file),
                'tmp_name' => $this->file,
                'error' => 0,
                '_SUGAR_API_UPLOAD' => true,
            ],
        ];

        $result = $api->saveFilePost($rest, ['module' => 'Documents', 'record' => $this->documents[0]->id, 'field' => 'filename']);

        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('record', $result);
        $this->assertEquals($this->documents[0]->id, $result['record']['id'], 'Wrong ID');
        $this->assertEquals('test2.txt', $result['record']['filename'], 'Wrong filename');
        $this->assertNotEmpty($result['record']['document_revision_id'], 'Revision missing');
        $this->assertEquals('test2.txt', $result['filename']['name'], 'Filename missing');
        $this->assertStringContainsString(
            $this->documents[0]->id . '/file/filename',
            $result['filename']['uri']
        );
        $this->assertEquals('2', $result['record']['revision'], 'Wrong revision');
        $this->assertNotEmpty($result['record']['last_rev_create_date'], 'Revision date not set');

        $rev2 = $result['record']['document_revision_id'];
        $this->assertNotEquals($rev1, $rev2, 'Revision not updated');

        $this->documents[0]->load_relationship('revisions');
        $this->assertNotEmpty($this->documents[0]->revisions, 'Failed to load revisions');
        $rels = $this->documents[0]->revisions->get();
        $this->assertEquals(2, safeCount($rels), 'Wrong revision count');
        $this->assertContains($rev1, $rels);
        $this->assertContains($rev2, $rels);
    }
}
