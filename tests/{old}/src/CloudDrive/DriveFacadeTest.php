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
use Sugarcrm\Sugarcrm\CloudDrive\Constants\DriveType;
use Sugarcrm\Sugarcrm\CloudDrive\DriveFacade;
use Sugarcrm\Sugarcrm\CloudDrive\Drives\GoogleDrive;

class DriveFacadeTest extends TestCase
{
    /**
     * @var \Sugarcrm\Sugarcrm\CloudDrive\DriveFacade|mixed
     */
    public $facade;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\CloudDrive\Drives\GoogleDrive|mixed
     */
    public $googleDriveApi;

    /**
     * setUp function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->facade = new DriveFacade(DriveType::GOOGLE);
        $this->googleDriveApi = $this->getMockBuilder(GoogleDrive::class)
            ->onlyMethods(['listFolders', 'createFolder', 'getFile',
                'listFiles', 'downloadFile', 'uploadFile', 'deleteFile'])
            ->getMock();
        $this->facade->setDrive($this->googleDriveApi);
    }

    /**
     * tearDown function
     *
     * @return void
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for the listFolders test
     *
     * @return array
     */
    public function providerListFolders(): array
    {
        return [
            [
                'args' => [
                    'folderId' => 'x123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's listFolders
     *
     * @param array $args
     *
     * @dataProvider providerListFolders
     */
    public function testListFolders(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('listFolders')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['folderId']);
                return true;
            }));
        $this->facade->listFolders($args);
    }


    /**
     * Data provider for the createFolder test
     *
     * @return array
     */
    public function providerCreateFolder(): array
    {
        return [
            [
                'args' => [
                    'folderId' => 'x123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's createFolder
     *
     * @param array $args
     *
     * @dataProvider providerCreateFolder
     */
    public function testCreateFolder(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('createFolder')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['folderId']);
                return true;
            }));
        $this->facade->createFolder($args);
    }

    /**
     * Data provider for the listFiles test
     *
     * @return array
     */
    public function providerListFiles(): array
    {
        return [
            [
                'args' => [
                    'folderId' => 'x123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's listFiles
     *
     * @param array $args
     *
     * @dataProvider providerListFiles
     */
    public function testListFiles(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('listFiles')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['folderId']);
                return true;
            }));
        $this->facade->listFiles($args);
    }

    /**
     * Data provider for the downloadFile test
     *
     * @return array
     */
    public function providerDownloadFile(): array
    {
        return [
            [
                'args' => [
                    'fileId' => 'x123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's downloadFile
     *
     * @param array $args
     *
     * @dataProvider providerDownloadFile
     */
    public function testDownloadFile(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('downloadFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['fileId']);
                return true;
            }));
        $this->facade->downloadFile($args);
    }

    /**
     * Data provider for the uploadFile test
     *
     * @return array
     */
    public function providerUploadFile(): array
    {
        return [
            [
                'args' => [
                    'filePath' => 'upload/x123',
                    'documentBean' => null,
                    'pathId' => '123456',
                    'largeFile' => null,
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's uploadFile
     *
     * @param array $args
     *
     * @dataProvider providerUploadFile
     */
    public function testUploadFile(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('uploadFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('upload/x123', $input['filePath']);
                $this->assertEquals(null, $input['documentBean']);
                $this->assertEquals('123456', $input['pathId']);
                $this->assertEquals(null, $input['largeFile']);
                return true;
            }));
        $this->facade->uploadFile($args);
    }

    /**
     * Data provider for the deleteFile test
     *
     * @return array
     */
    public function providerDeleteFile(): array
    {
        return [
            [
                'args' => [
                    'fileId' => '123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's deleteFile
     *
     * @param array $args
     *
     * @dataProvider providerDeleteFile
     */
    public function testDeleteFile(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('deleteFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('123', $input['fileId']);
                return true;
            }));
        $this->facade->deleteFile($args);
    }

    /**
     * Data provider for the getFile test
     *
     * @return array
     */
    public function providerGetFile(): array
    {
        return [
            [
                'args' => [
                    'fileId' => '123',
                ],
            ],
        ];
    }

    /**
     * Test if it calls Drive's getFile
     *
     * @param array $args
     *
     * @dataProvider providerGetFile
     */
    public function testGetFile(array $args): void
    {
        $this->facade->getDrive()->expects($this->once())
            ->method('getFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('123', $input['fileId']);
                return true;
            }));
        $this->facade->getFile($args);
    }
}
