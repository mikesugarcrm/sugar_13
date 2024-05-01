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
 * @coversDefaultClass UserViewHelper
 */
class UserViewHelperTest extends TestCase
{
    protected $currUser;
    protected $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        global $current_user;
        $this->currUser = $current_user;

        parent::setUp();
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->helper = $this->getMockBuilder('UserViewHelper')
            ->disableOriginalConstructor()
            ->getMock();
        SugarTestReflection::setProtectedValue($this->helper, 'ss', new Sugar_Smarty());
        SugarTestReflection::setProtectedValue($this->helper, 'bean', $current_user);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        global $current_user;
        $current_user = $this->currUser;

        parent::tearDown();
    }

    /**
     * @dataProvider providerTestSetupAdvancedTabNavSettingsPins
     * @covers ::setupAdvancedTabNavSettings
     * @param int|null $pinnedPref The user's current number pinned preference
     * @param int|null $pinnedConfig The system's current number pinned config
     * @param bool $userAllowed Whether a user is allowed to configure pins
     * @param bool $expectedPref The user's expected number pinned preference
     */
    public function testSetupAdvancedTabNavSettingsPins($pinnedPref, $pinnedConfig, $userAllowed, $expectedPref)
    {
        global $current_user;

        $current_user->setPreference('number_pinned_modules', $pinnedPref);
        $tabController = new TabController();
        $tabController->set_number_pinned_modules($pinnedConfig);
        $tabController->set_users_pinned_modules($userAllowed);

        SugarTestReflection::callProtectedMethod($this->helper, 'setupAdvancedTabNavSettings');
        $ss = SugarTestReflection::getProtectedValue($this->helper, 'ss');
        $this->assertEquals(!$userAllowed, $ss->getTemplateVars('DISABLE_NUMBER_PINNED_MODULES'));
        $this->assertEquals($expectedPref, $ss->getTemplateVars('NUMBER_PINNED_MODULES'));
    }

    /**
     * @return array Test data for testSetupAdvancedTabNavSettings
     */
    public function providerTestSetupAdvancedTabNavSettingsPins()
    {
        return [
            [6, 4, true, 6,],
            [null, 4, true, 4,],
            [6, 4, false, null,],
        ];
    }
}
