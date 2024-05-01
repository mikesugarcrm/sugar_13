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
use Sugarcrm\Sugarcrm\CloudDrive\Drives\DropboxDrive;
use Sugarcrm\Sugarcrm\CloudDrive\Model\DriveItemMapper;

class DropboxDriveTest extends TestCase
{
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
     * Test if it calls Drive's listFolders
     */
    public function testListFolders(): void
    {
        $mockClient = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['listFolder'])
            ->getMock();
        $mockClient->method('listFolder')->willReturn([
            'entries' => [
                [
                    'id' => 'testId1',
                    'name' => 'testName1',
                ],
                [
                    'id' => 'testId2',
                    'name' => 'testName2',
                ],
            ],
            'has_more' => false,
        ]);

        $mockApi = $this->getMockBuilder(DropboxDrive::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseFolderPath', 'handleErrors', 'getExternalApiClient'])
            ->getMock();
        $mockApi->method('parseFolderPath')->willReturn('');
        $mockApi->method('getExternalApiClient')->willReturn($mockClient);
        $mockApi->method('handleErrors')->willReturn([
            'success' => false,
            'message' => 'Error label',
        ]);

        $mockDriveMapperClient = $this->getMockBuilder(DriveItemMapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['mapToArray'])
            ->getMock();
        $mockDriveMapperClient->method('mapToArray')->willReturn([
            [
                'id' => 'testId1',
                'name' => 'testName1',
            ],
            [
                'id' => 'testId2',
                'name' => 'testName2',
            ],
        ]);

        $response = $mockApi->listFolders([
            'folderPath' => [
                'name' => 'My files',
            ],
            'nextPageToken' => null,
            'limit' => 100,
            'sharedWithMe' => false,
        ]);

        $this->assertEquals(safeCount($response['files']), 2);
    }

    /**
     * Test if listFiles is called
     */
    public function testListFiles(): void
    {
        $mockApi = $this->getMockBuilder(DropboxDrive::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['listFolders',])
            ->getMock();
        $mockApi->method('listFolders')->willReturn([
            'files' => [
                [
                    'id' => 'test1',
                    'name' => 'testName1',
                ],
                [
                    'id' => 'test2',
                    'name' => 'testName2',
                ],
            ],
        ]);
        $response = $mockApi->listFiles([
            'pathFolders' => 'My files',
        ]);

        $this->assertEquals(safeCount($response['files']), 2);
    }

    /**
     * Test if getFile is called
     */
    public function testGetFile(): void
    {
        $mockApi = $this->getMockBuilder(DropboxDrive::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['listFolders',])
            ->getMock();
        $mockApi->method('listFolders')->willReturn([
            'files' => [
                [
                    'id' => 'test1',
                    'name' => 'testName1',
                ],
                [
                    'id' => 'test2',
                    'name' => 'testName2',
                ],
            ],
        ]);
        $response = $mockApi->getFile([
            'pathFolders' => 'My files',
        ]);

        $this->assertEquals(safeCount($response['files']), 2);
    }
}
