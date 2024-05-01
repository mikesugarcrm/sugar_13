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
 * @group ApiTests
 */
class FileApiTest extends TestCase
{
    public $documents;

    /** @var FileApi */
    private $fileApi;

    public $tempFileFrom = 'tests/{old}/clients/base/api/FileApiTempFileFrom.txt';
    public $tempFileTo;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('ACLStatic');
        // load up the unifiedSearchApi for good times ahead
        $this->fileApi = $this->createPartialMock('FileApi', ['getDownloadFileApi']);
        $this->fileApi
            ->expects($this->any())
            ->method('getDownloadFileApi')
            ->with($this->isInstanceOf('ServiceBase'))
            ->will($this->returnCallback(function ($service) {
                return new DownloadFileApi($service);
            }));

        $document = BeanFactory::newBean('Documents');
        $document->name = 'RelateApi setUp Documents';
        $document->save();
        $this->documents[] = $document;
    }

    protected function tearDown(): void
    {
        // Clean up temp file stuff
        if ($this->tempFileTo && file_exists($this->tempFileTo)) {
            @unlink($this->tempFileTo);
        }

        foreach ($this->documents as $document) {
            $document->mark_deleted($document->id);
        }

        SugarTestHelper::tearDown();
    }

    public function testSaveFilePost()
    {
        $this->denyDocumentView();
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->fileApi->saveFilePost(SugarTestRestUtilities::getRestServiceMock(), [
            'module' => 'Documents',
            'record' => $this->documents[0]->id,
            'field' => 'filename',
        ]);
    }

    public function testGetFileList()
    {
        $this->denyDocumentView();
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->fileApi->getFileList(SugarTestRestUtilities::getRestServiceMock(), [
            'module' => 'Documents',
            'record' => $this->documents[0]->id,
            'field' => 'filename',
        ]);
    }

    private function denyDocumentView()
    {
        global $current_user;

        ACLAction::setACLData($current_user->id, 'Documents', [
            'module' => [
                'view' => [
                    'aclaccess' => ACL_ALLOW_NONE,
                ],
            ],
        ]);
    }

    public function testCreateTempFileFromInput()
    {
        // Tests checking encoding requests
        $encoded = SugarTestReflection::callProtectedMethod($this->fileApi, 'isFileEncoded', [
            SugarTestRestUtilities::getRestServiceMock(),
            [
                'content_transfer_encoding' => 'base64',
            ],
        ]);
        $this->assertTrue($encoded, 'Encoded request check failed');

        // Handle our test of file encoding
        $this->tempFileTo = $this->fileApi->getTempFileName();
        SugarTestReflection::callProtectedMethod($this->fileApi, 'createTempFileFromInput', [
            $this->tempFileTo,
            $this->tempFileFrom,
            $encoded,
        ]);

        // Test that the temporary file was created
        $this->assertFileExists($this->tempFileTo, 'Temp file was not created');

        // Test that the contents of the new file are the base64_decoded contents of the test file
        $createdContents = file_get_contents($this->tempFileTo);
        $encodedContents = base64_decode(file_get_contents($this->tempFileFrom));
        $this->assertEquals($createdContents, $encodedContents, 'Creating temp file from encoded file failed');
    }

    public function testCreateTempFileFromInputNoEncoding()
    {
        // Tests checking encoding requests
        $encoded = SugarTestReflection::callProtectedMethod($this->fileApi, 'isFileEncoded', [
            SugarTestRestUtilities::getRestServiceMock(),
            [],
        ]);
        $this->assertFalse($encoded, 'Second encoded request check failed');

        // Handle our test of file encoding
        $this->tempFileTo = $this->fileApi->getTempFileName();
        SugarTestReflection::callProtectedMethod($this->fileApi, 'createTempFileFromInput', [
            $this->tempFileTo,
            $this->tempFileFrom,
            $encoded,
        ]);

        // Test that the temporary file was created
        $this->assertFileExists($this->tempFileTo, 'Temp file was not created');

        // Test that the contents of the new file are the same as the contents of the test file
        $createdContents = file_get_contents($this->tempFileTo);
        $encodedContents = file_get_contents($this->tempFileFrom);
        $this->assertEquals($createdContents, $encodedContents, 'Creating temp file from encoded file failed');
    }

    /**
     * Test protected method getDownloadFileApi
     */
    public function testGetDownloadFileApi()
    {
        $result = SugarTestReflection::callProtectedMethod(new FileApi(), 'getDownloadFileApi', [
            SugarTestRestUtilities::getRestServiceMock(),
        ]);

        $this->assertInstanceOf('DownloadFileApi', $result);
    }

    /**
     * @return void
     * @throws SugarApiExceptionMissingParameter
     */
    public function testSaveFilePut()
    {
        $fileApi = $this->createPartialMock('FileApi', ['getDownloadFileApi', 'saveFilePost', 'getContentLength']);
        $fileApi->expects($this->any())
            ->method('getDownloadFileApi')
            ->with($this->isInstanceOf('ServiceBase'))
            ->will($this->returnCallback(function ($service) {
                return new DownloadFileApi($service);
            }));
        $fileApi->expects($this->any())
            ->method('saveFilePost')
            ->willReturn(['OK']);
        $inputSize = filesize($this->tempFileFrom);
        $fileApi->expects($this->any())
            ->method('getContentLength')
            ->willReturn($inputSize, $inputSize, 1000000);

        $result = $fileApi->saveFilePut(
            SugarTestRestUtilities::getRestServiceMock(),
            [
                'module' => 'Notes',
                'record' => 'some_string_pretending_to_be_uuid',
                'field' => 'filename',
                'content_transfer_encoding' => 'base64',
            ],
            $this->tempFileFrom
        );
        $this->assertEquals($result, ['OK'], 'Failed saving encoded file');

        $result = $fileApi->saveFilePut(
            SugarTestRestUtilities::getRestServiceMock(),
            [
                'module' => 'Notes',
                'record' => 'some_string_pretending_to_be_uuid',
                'field' => 'filename',
            ],
            $this->tempFileFrom
        );
        $this->assertEquals($result, ['OK'], 'Failed saving encoded file');

        $this->expectException(SugarApiExceptionRequestTooLarge::class);
        $fileApi->saveFilePut(
            SugarTestRestUtilities::getRestServiceMock(),
            [
                'module' => 'Notes',
                'record' => 'some_string_pretending_to_be_uuid',
                'field' => 'filename',
            ],
            $this->tempFileFrom
        );
    }
}
