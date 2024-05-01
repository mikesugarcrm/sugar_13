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
 * RS-49
 * Prepare File Api
 */
class RS49Test extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var FileApi */
    protected $api = null;

    /** @var string */
    protected $file = '';

    /** @var Note */
    protected $note = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUP('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new FileApi();

        $this->file = tempnam(sys_get_temp_dir(), self::class);
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->file);
        file_put_contents($this->file, create_guid());

        $this->note = new Note();
        $this->note->title = 'Note ' . self::class;
        $this->note->save();

        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_FILES = [];

        if ($this->note instanceof Note) {
            $this->note->mark_deleted($this->note->id);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * On correct info saveFilePut should call saveFilePost and to return its result.
     */
    public function testSaveFilePut()
    {
        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];
        $_FILES[$parameters['field']] = [];
        $api = $this->createPartialMock('FileApi', ['saveFilePost']);
        $api->expects($this->once())->method('saveFilePost')->will($this->returnValue('saveFilePostReturnString'))->with($this->equalTo($this->service), $this->equalTo($parameters));
        $actual = $api->saveFilePut($this->service, $parameters, $this->file);
        $this->assertEquals('saveFilePostReturnString', $actual);
        $this->assertNotEmpty($_FILES[$parameters['field']]);
    }

    /**
     * On empty size of file we should get exception
     */
    public function testSaveFilePutFileSize()
    {
        file_put_contents($this->file, '');
        $api = $this->createPartialMock('FileApi', ['saveFilePost']);
        $api->expects($this->never())->method('saveFilePost');

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $api->saveFilePut($this->service, [], $this->file);
    }

    /**
     * We should get record & file info on success upload
     */
    public function testSaveFilePost()
    {
        $_FILES = [
            'filename' => [
                'name' => 'test.txt',
                'size' => filesize($this->file),
                'tmp_name' => $this->file,
                'error' => 0,
                '_SUGAR_API_UPLOAD' => true,
            ],
        ];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];
        $actual = $this->api->saveFilePost($this->service, $parameters);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey($parameters['field'], $actual);
        $this->assertArrayHasKey('record', $actual);
        $this->assertEquals($this->note->id, $actual['record']['id']);
        $this->assertArrayHasKey($parameters['field'] . '_file', $_FILES);
        $this->assertEquals($_FILES[$parameters['field'] . '_file']['name'], $actual['record'][$parameters['field']]);
    }

    /**
     * We should get exception if ACLAccess returns false
     */
    public function testSaveFilePostBeanACLAccessView()
    {
        $bean = $this->createPartialMock('Note', ['ACLAccess']);
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES isn't set
     */
    public function testSaveFilePostFilesAreNotSet()
    {
        $_FILES = null;

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     */
    public function testSaveFilePostFilesAreSetAndEmpty()
    {
        $_FILES = [];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is present but doesn't contain current file
     */
    public function testSaveFilePostFilesAreSetButWithoutCurrentFile()
    {
        $_FILES = [
            'name' => [
                'name' => 'name',
            ],
        ];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->saveFilePost($this->service, $parameters);
    }

    public function testSaveFilePostIncorrectFieldType()
    {
        $_FILES = [
            'name' => [
                'name' => 'name',
            ],
        ];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'name',
        ];

        $this->expectException(SugarApiExceptionError::class);
        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     * Also mark_deleted method should be called if delete_if_fails parameter is present
     */
    public function testDeleteIfFailsWithParameter()
    {
        $_FILES = [];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
            'delete_if_fails' => true,
        ];

        $bean = $this->createPartialMock('Note', ['mark_deleted']);
        $bean->id = $this->note->id;
        $bean->created_by = $GLOBALS['current_user']->id;
        $bean->expects($this->once())->method('mark_deleted')->with($this->equalTo($bean->id))->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->any())->method('loadBean')->will($this->returnValue($bean));

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     * Also mark_deleted method shouldn't be called if delete_if_fails parameter isn't present
     */
    public function testDeleteIfFailsWithoutParameter()
    {
        $_FILES = [];

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        ];

        $bean = $this->createPartialMock('Note', ['mark_deleted']);
        $bean->id = $this->note->id;
        $bean->created_by = $GLOBALS['current_user']->id;
        $bean->expects($this->never())->method('mark_deleted');

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->any())->method('loadBean')->will($this->returnValue($bean));

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get list of file/image fields
     */
    public function testGetFileList()
    {
        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
        ];
        $actual = $this->api->getFileList($this->service, $parameters);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('filename', $actual);
    }

    /**
     * We should get exception if ACLAccess returns false
     */
    public function testGetFileListACLAccessView()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['ACLAccess'])->getMock();
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $api->getFileList($this->service, $parameters);
    }

    /**
     * We should get exception about incorrect file, it means success of getFile method.
     */
    public function testGetFile()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['ACLAccess'])->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->expectExceptionMessage('File information could not be retrieved for this record');
        $api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if field is not present in $parameters
     */
    public function testGetFileWithoutField()
    {
        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if ACLAccess returns false
     */
    public function testGetFileACLAccessView()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['ACLAccess'])->getMock();
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if field is empty
     */
    public function testGetFileACLEmptyField()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['ACLAccess'])->getMock();
        $bean->id = $this->note->id;
        $bean->filename = '';
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $api->getFile($this->service, $parameters);
    }

    /**
     * getFileList method should be called in the end of removeFile method
     * If field is present then deleteAttachment method should be called on bean
     */
    public function testRemoveFile()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['deleteAttachment'])->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('deleteAttachment')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', ['loadBean', 'getFileList']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->once())->method('getFileList')->will($this->returnValue(['getFileListReturnString']));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];
        $actual = $api->removeFile($this->service, $parameters);
        $this->assertContains('getFileListReturnString', $actual);
    }

    /**
     * getFileList method should be called in the end of removeFile method
     * If field isn't present then deleteAttachment method shouldn't be called on bean
     */
    public function testRemoveFileEmptyField()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['deleteAttachment'])->getMock();
        $bean->id = $this->note->id;
        $bean->filename = '';

        $api = $this->createPartialMock('FileApi', ['loadBean', 'getFileList']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->once())->method('getFileList')->will($this->returnValue(['getFileListReturnString']));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];
        $actual = $api->removeFile($this->service, $parameters);
        $this->assertContains('getFileListReturnString', $actual);
    }

    /**
     * We should get exception if field isn't file or image
     */
    public function testRemoveFileIncorrectFieldType()
    {
        $api = $this->createPartialMock('FileApi', ['loadBean', 'getFileList']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($this->note));
        $api->expects($this->never())->method('getFileList')->will($this->returnValue('getFileListReturnString'));

        $parameters = [
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'id',
        ];

        $this->expectException(SugarApiExceptionError::class);
        $api->removeFile($this->service, $parameters);
    }

    /**
     * We should get exception if deleteAttachment fails
     */
    public function testRemoveFileDeleteAttachmentFails()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(['deleteAttachment'])->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('deleteAttachment')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', ['loadBean', 'getFileList']);
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->never())->method('getFileList')->will($this->returnValue('getFileListReturnString'));

        $parameters = [
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        ];

        $this->expectException(SugarApiExceptionRequestMethodFailure::class);
        $api->removeFile($this->service, $parameters);
    }
}
