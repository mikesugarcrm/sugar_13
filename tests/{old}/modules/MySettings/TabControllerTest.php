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
 * @coversDefaultClass \TabController
 */
class TabControllerTest extends TestCase
{
    private $oldPortalTabs;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');

        // Save the old value of the Portal tab list to restore it after the
        // test is finished
        $this->oldPortalTabs = \TabController::getPortalTabs();
    }

    protected function tearDown(): void
    {
        // Restore the original value of the Portal tab list
        \TabController::setPortalTabs($this->oldPortalTabs);
    }

    /**
     * @covers ::getPortalTabs
     * @dataProvider providerGetPortalTabs
     * @param array $actualPortalTabs
     * @param array $expectedPortalTabs
     */
    public function testGetPortalTabs($actualPortalTabs, $expectedPortalTabs)
    {
        // Set the existing (actual) Portal tabs list to the test value
        \TabController::setPortalTabs($actualPortalTabs);

        // Check that the correct list of Portal tabs is returned
        $this->assertEquals($expectedPortalTabs, \TabController::getPortalTabs());
    }

    public function providerGetPortalTabs(): array
    {
        return [
            [null, ['Home', 'Cases', 'KBContents']],
            [['Home', 'Cases', 'KBContents', 'Bugs'], ['Home', 'Cases', 'KBContents', 'Bugs']],
        ];
    }
}
