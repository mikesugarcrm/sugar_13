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
require_once 'include/utils/file_utils.php';

/**
 * Test DownloadFile:getArchive()
 */
class DownloadArchiveTest extends TestCase
{
    /**
     * Notes.
     *
     * @var array
     */
    public $notes = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
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
            '4 files, force download, name: testarchive' => [
                4,
                'testarchive',
                'testarchive.zip',
            ],
            '2 files, force download, name: someother.zip' => [
                2,
                'someother.zip',
                'someother.zip',
            ],
            '3 files, not force download, name: three.zip' => [
                3,
                'three',
                'three.zip',
            ],
            '4 files, force download, name:empty' => [
                4,
                '', // empty
                'archive.zip',
            ],
            '5 files, not force download, name:empty' => [
                5,
                '', // empty
                'archive.zip',
            ],
        ];
    }

    /**
     * Test get archive.
     *
     * @dataProvider dataProviderGetArchive
     */
    public function testGetArchive($fileCounts, $outputName, $expectedOutputName)
    {
        $bean = BeanFactory::newBean('Notes');
        $sfh = new SugarFieldHandler();
        $def = $bean->field_defs['filename'];
        /* @var $sf SugarFieldFile */
        $sf = $sfh->getSugarField($def['type']);

        $notes = [];

        for ($i = 0; $i < $fileCounts; $i++) {
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

            $this->notes[] = $note;
            $notes[] = $note;
        }

        $unit = $this;

        $downloadMock = $this->createPartialMock('DownloadFile', ['outputFile']);
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
            ->will($this->returnCallback(function ($forceDownload, $info) use ($unit, $expectedOutputName, $fileCounts) {
                $unit->assertNotEmpty($info['path'], 'File path is empty');
                $unit->assertFileExists($info['path'], 'Archive file not exists');

                $unit->assertEquals($expectedOutputName, $info['name']);

                $contentType = mime_is_detectable() ? 'application/zip' : 'application/octet-stream';

                $unit->assertEquals($contentType, $info['content-type'], 'Invalid content-type');
                $unit->assertEquals(filesize($info['path']), $info['content-length'], 'Invalid content-length');

                $zip = new ZipArchive();
                $zip->open($info['path']);
                $numFiles = $zip->numFiles;
                $zip->close();

                $unit->assertEquals($fileCounts, $numFiles, 'Invalid file counts in archive');
            }));

        // get archived files
        $downloadMock->getArchive($notes, 'filename', $outputName);
    }

    /**
     * Test get archive when given empty bean list
     */
    public function testGetArchiveEmptyBeanList()
    {
        $downloadMock = $this->createPartialMock('DownloadFile', ['outputFile']);
        $this->expectExceptionMessage('Files could not be retrieved for this record');
        $downloadMock->getArchive([], 'filename');
    }
}
