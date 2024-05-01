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
use Sugarcrm\Sugarcrm\CloudDrive\DriveFactory;
use Sugarcrm\Sugarcrm\CloudDrive\Drives\GoogleDrive;
use Sugarcrm\Sugarcrm\CloudDrive\Drives\OneDrive;

use function PHPUnit\Framework\assertInstanceOf;

class DriveFactoryTest extends TestCase
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
     * Test factory
     */
    public function testGetDrive(): void
    {
        $factory = new DriveFactory();
        $object = $factory::getDrive(DriveType::GOOGLE);
        $googleClass = GoogleDrive::class;
        assertInstanceOf($googleClass, $object);
        $object = $factory::getDrive(DriveType::ONEDRIVE);
        $oneDriveClass = OneDrive::class;
        assertInstanceOf($oneDriveClass, $object);
    }
}
