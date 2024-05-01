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

namespace Sugarcrm\SugarcrmTestsUnit\CloudDrive\Paths;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\CloudDrive\Paths\CloudDrivePath;
use LoggerManager;
use Sugarcrm\Sugarcrm\CloudDrive\Paths\Model\Types\CloudDrivePathDropbox;

require_once 'include/SugarCache/SugarCache.php';

class CloudDrivePathTest extends TestCase
{
    protected $drivePath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $GLOBALS['bwcModules'] = [];
        $GLOBALS['log'] = $this->createMock(LoggerManager::class);

        \BeanFactory::setBeanClass('CloudDrivePaths', \CloudDrivePath::class);
        $this->drivePath = new CloudDrivePath('dropbox');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['bwcModules']);
        unset($GLOBALS['log']);
        \BeanFactory::unsetBeanClass('CloudDrivePaths');
        parent::tearDown();
    }

    public function getDrivePathProvider()
    {
        return  [
            [
                'optionsSet1' => [
                    'type' => 'dropbox',
                    'layoutName' => 'record',
                    'module' => 'Accounts',
                ],
                'optionsSet2' => [
                    'type' => 'dropbox',
                    'module' => 'Accounts',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDrivePathProvider
     */
    public function testGetDrivePath(array $optionsSet1, array $optionsSet2)
    {
         // Create a partial mock for the 'model' property
         $modelMock = $this->getMockBuilder(CloudDrivePathDropbox::class)
         ->setMethods(['findRoot', 'getDrivePathsUtils'])  // Mock the protected method 'findRoot'
         ->getMock();

        // Set up the expected behavior for the findRoot method
        $modelMock->expects($this->exactly(2))
            ->method('findRoot')
            ->willReturn(null);
        $modelMock->expects($this->exactly(2))
            ->method('getDrivePathsUtils')
            ->willReturn([]);

        // Set the 'model' property of the $drivePath to the mocked instance
        $reflection = new \ReflectionProperty(CloudDrivePath::class, 'model');
        $reflection->setAccessible(true);
        $reflection->setValue($this->drivePath, $modelMock);

        // Call the method that uses the 'model' property and the findRoot method
        $drivePath1 = $this->drivePath->getDrivePath($optionsSet1);
        $drivePath2 = $this->drivePath->getDrivePath($optionsSet2);

        // Assert the result
        $this->assertIsArray($drivePath1);
        $this->assertIsArray($drivePath2);
    }
}
