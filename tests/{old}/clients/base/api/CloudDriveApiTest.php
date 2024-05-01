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

class CloudDriveApiTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\CloudDrive\DriveFacade|mixed
     */
    public $driveFacade;
    /**
     * @var \RestService|mixed
     */
    public $serviceMock;
    /**
     * @var CloudDriveApi
     */
    private $api;

    /**
     * setUp function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->driveFacade = $this->getMockBuilder(DriveFacade::class)
            ->setConstructorArgs([DriveType::GOOGLE])
            ->onlyMethods(['listFiles', 'listFolders', 'getFile',
                'downloadFile', 'deleteFile',])
            ->getMock();

        $this->api = $this->getMockBuilder('CloudDriveApi')
            ->onlyMethods(['getDrive', 'findRoot', 'getDrivePaths',])
            ->getMock();

        $this->api->expects($this->any())
            ->method('getDrive')
            ->will($this->returnValue($this->driveFacade));
        $this->api->expects($this->any())
            ->method('findRoot')
            ->will($this->returnValue(null));
        $this->api->expects($this->any())
            ->method('getDrivePaths')
            ->will($this->returnValue([['id' => '123']]));

        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
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
                    'type' => 'google',
                ],
            ],
        ];
    }

    /**
     * Test if the facade's listFiles is being called
     *
     * @param array $args
     * @dataProvider providerListFiles
     */
    public function testListFiles(array $args): void
    {
        $this->api->expects($this->once())->method('getDrive');
        $this->driveFacade->expects($this->once())
            ->method('listFiles')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['folderId']);
                return true;
            }));
        $this->api->listFiles($this->serviceMock, $args);
    }

    /**
     * Data provider for the listFiles test
     *
     * @return array
     */
    public function providerListFolders(): array
    {
        return [
            [
                'args' => [
                    'folderId' => 'x123',
                    'type' => 'google',
                ],
            ],
        ];
    }

    /**
     * Test if the facade's listFolders is being called
     *
     * @param array $args
     * @dataProvider providerListFolders
     */
    public function testListFolders(array $args): void
    {
        $this->api->expects($this->once())->method('getDrive');
        $this->driveFacade->expects($this->once())
            ->method('listFolders')
            ->with($this->callback(function ($input) {
                $this->assertEquals('x123', $input['folderId']);
                return true;
            }));
        $this->api->listFolders($this->serviceMock, $args);
    }

    /**
     * Data provider for the listFiles test
     *
     * @return array
     */
    public function providerGetFile(): array
    {
        return [
            [
                'args' => [
                    'fileId' => '123-456',
                    'type' => 'google',
                ],
            ],
        ];
    }

    /**
     * Test if the facade's getFile is being called
     *
     * @param array $args
     * @dataProvider providerGetFile
     */
    public function testGetFile(array $args): void
    {
        $this->api->expects($this->once())->method('getDrive');
        $this->driveFacade->expects($this->once())
            ->method('getFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('123-456', $input['fileId']);
                return true;
            }));
        $this->api->getFile($this->serviceMock, $args);
    }

    /**
     * Data provider for the listFiles test
     *
     * @return array
     */
    public function providerDownloadFile(): array
    {
        return [
            [
                'args' => [
                    'fileId' => '123-456',
                    'type' => 'google',
                ],
            ],
        ];
    }

    /**
     * Test if the facade's getFile is being called
     *
     * @param array $args
     * @dataProvider providerGetFile
     */
    public function testDownloadFile(array $args): void
    {
        $this->api->expects($this->once())->method('getDrive');
        $this->driveFacade->expects($this->once())
            ->method('downloadFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('123-456', $input['fileId']);
                return true;
            }));
        $this->api->downloadFile($this->serviceMock, $args);
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
                    'fileId' => '123-456',
                    'type' => 'google',
                ],
            ],
        ];
    }

    /**
     * Test if the facade's deleteFile is being called
     *
     * @param array $args
     * @dataProvider providerDeleteFile
     */
    public function testDeleteFile(array $args): void
    {
        $this->api->expects($this->once())->method('getDrive');
        $this->driveFacade->expects($this->once())
            ->method('deleteFile')
            ->with($this->callback(function ($input) {
                $this->assertEquals('123-456', $input['fileId']);
                return true;
            }));
        $this->api->deleteFile($this->serviceMock, $args);
    }
}
