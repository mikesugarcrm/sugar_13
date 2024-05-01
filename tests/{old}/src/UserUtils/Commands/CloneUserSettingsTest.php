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

namespace Sugarcrm\SugarcrmTests\UserUtils\Commands;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneUserSettings;
use Sugarcrm\Sugarcrm\UserUtils\Managers\UserSettingsManager;
use SugarTestHelper;

class CloneUserSettingsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneUserSettings|mixed
     */
    public $cloneUserSettings;

    protected function setUp(): void
    {
        $this->cloneUserSettings = $this->getMockBuilder(CloneUserSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getManager', 'execute'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::testExecute
     */
    public function testExecute()
    {
        $manager = $this->getMockBuilder(UserSettingsManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['cloneUserSettings'])
            ->getMock();

        $this->cloneUserSettings->expects($this->once())->method('execute');
        $this->cloneUserSettings->execute();
        $this->cloneUserSettings->expects($this->once())->method('getManager');
        $this->cloneUserSettings->getManager();
        $manager->expects($this->once())->method('cloneUserSettings');
        $manager->cloneUserSettings();
    }
}
