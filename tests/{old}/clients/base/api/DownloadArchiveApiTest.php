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

require_once 'include/download_file.php';

/**
 * Test FileApi::getArchive()
 *
 * @group ApiTests
 */
class DownloadArchiveApiTest extends TestCase
{
    /**
     * @var ServiceBase
     */
    public $service;

    /**
     * Notes.
     *
     * @var array
     */
    public $notes = [];

    /**
     * @var Account
     */
    public $account;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('ACLStatic');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->load_relationship('notes');

        $bean = BeanFactory::newBean('Notes');
        $sfh = new SugarFieldHandler();
        $def = $bean->field_defs['filename'];
        /* @var $sf SugarFieldFile */
        $sf = $sfh->getSugarField($def['type']);

        for ($i = 0; $i < 3; $i++) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'DownloadArchiveTest' . $i);
            file_put_contents($tmpFile, uniqid());

            $note = BeanFactory::newBean('Notes');
            $note->name = 'DownloadArchiveTest' . uniqid();

            $_FILES['uploadfile'] = [
                'name' => 'DownloadArchiveTest' . $i . '.txt',
                'tmp_name' => $tmpFile,
                'size' => filesize($tmpFile),
                'error' => 0,
                '_SUGAR_API_UPLOAD' => true,
            ];

            $sf->save($note, [], 'filename', $def, 'DownloadArchiveTest_');

            $this->account->notes->add($note);
            $this->notes[] = $note;
        }
    }

    protected function tearDown(): void
    {
        // Notes cleanup
        if (count($this->notes)) {
            $download = new DownloadFile();
            $noteIds = [];
            foreach ($this->notes as $note) {
                if (false !== $fileInfo = $download->getFileInfo($note, 'filename')) {
                    if (file_exists($fileInfo['path'])) {
                        @unlink($fileInfo['path']);
                    }
                }
                $noteIds[] = $note->id;
            }
            $noteIds = "('" . implode("','", $noteIds) . "')";
            $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
        }
        $this->notes = [];

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for get archive test.
     *
     * @return array
     */
    public function dataProviderGetArchive()
    {
        return [
            'force download' => [
                true,
            ],
            'not force download' => [
                false,
            ],
        ];
    }

    /**
     * Test get archived files.
     * Always should force download.
     *
     * @dataProvider dataProviderGetArchive
     */
    public function testGetArchive($forceDownload)
    {
        $unit = $this;
        $downloadMock = $this->getMockBuilder('DownloadFileApi')
            ->setMethods(['outputFile'])
            ->setConstructorArgs([$this->service])
            ->getMock();
        $downloadMock->expects($this->once())->method('outputFile')
            ->with(
                $this->logicalAnd($this->isType('bool'), $this->isTrue()),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->arrayHasKey('path'),
                    $this->arrayHasKey('content-type'),
                    $this->arrayHasKey('content-length'),
                    $this->arrayHasKey('name')
                )
            )
            ->will($this->returnCallback(function ($fd, $info) use ($unit) {
                $unit->assertNotEmpty($info['path'], 'File path is empty');
                $unit->assertFileExists($info['path'], 'Archive file not exists');

                $unit->assertEquals($unit->account->name . '.zip', $info['name']);

                $contentType = mime_is_detectable() ? 'application/zip' : 'application/octet-stream';

                $unit->assertEquals($contentType, $info['content-type'], 'Invalid content-type');
                $unit->assertEquals(filesize($info['path']), $info['content-length'], 'Invalid content-length');

                $zip = new ZipArchive();
                $zip->open($info['path']);
                $numFiles = $zip->numFiles;
                $zip->close();

                $unit->assertEquals(3, $numFiles, 'Invalid file counts in archive');
            }));

        $apiMock = $this->createPartialMock('FileApi', ['getDownloadFileApi']);
        $apiMock->expects($this->once())
            ->method('getDownloadFileApi')
            ->will($this->returnValue($downloadMock));

        $apiMock->getArchive($this->service, [
            'module' => 'Accounts',
            'record' => $this->account->id,
            'link_name' => 'notes',
            'field' => 'filename',
            'force_download' => $forceDownload,
        ]);
    }

    /**
     * Test get archived files when field not specified.
     */
    public function testGetArchiveFieldNotSpecified()
    {
        $api = new FileApi();
        $this->expectException(SugarApiExceptionMissingParameter::class);

        $api->getArchive($this->service, [
            'module' => 'Accounts',
            'record' => $this->account->id,
            'link_name' => 'notes',
        ]);
    }

    /**
     * Test get archived files when field not specified.
     */
    public function testGetArchiveInvalidLinkName()
    {
        $api = new FileApi();
        $this->expectException(SugarApiExceptionNotFound::class);

        $api->getArchive($this->service, [
            'module' => 'Accounts',
            'record' => $this->account->id,
            'field' => 'filename',
            'link_name' => 'invalid_link_notes',
        ]);
    }
}
