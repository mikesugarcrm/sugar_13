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

namespace Sugarcrm\SugarcrmTests\UserUtils;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerUserSettingsPayload;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\CommandFactory
 */
class InvokerUserSettingsPayloadTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::setUserSettings and getUserSettings
     */
    public function testSetAndGetUserSettings()
    {
        $dashboardsPayload = new InvokerUserSettingsPayload([]);
        $dashboardsPayload->setUserSettings(['settings']);
        $settings = $dashboardsPayload->getUserSettings();
        $this->assertEquals(['settings'], $settings);
    }
}
