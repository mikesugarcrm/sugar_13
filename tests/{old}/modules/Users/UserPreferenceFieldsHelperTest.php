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
 * @coversDefaultClass UserPreferenceFieldsHelper
 */
class UserPreferenceFieldsHelperTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that getPreferenceField uses the custom get function for the
     * particular preference if one is defined
     *
     * @covers ::getPreferenceField
     */
    public function testGetPreferenceFieldUsesCustomFunctions()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods([])
            ->getMock();

        $helper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_appearance'])
            ->getMock();

        $helper->expects($this->once())
            ->method('get_appearance')
            ->with($user);
        $helper->getPreferenceField($user, 'appearance');
    }

    /**
     * Tests that getPreferenceField falls back to using $bean->getPreference
     * if not custom get function is defined
     *
     * @covers ::getPreferenceField
     */
    public function testGetPreferenceFieldUsesStandardFunction()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getPreference'])
            ->getMock();

        $helper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $preference = 'some_preference';

        $user->expects($this->once())
            ->method('getPreference')
            ->with($preference);
        $helper->getPreferenceField($user, $preference);
    }

    /**
     * Tests that getPreferenceField returns the correct preference result
     *
     * @covers ::getPreferenceField
     */
    public function testGetPreferenceGetsCorrectResult()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods([])
            ->getMock();
        $user->id = 'fake-ID';

        $helper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $user->setPreference('appearance', 'dark');
        $result = $helper->getPreferenceField($user, 'appearance');
        $this->assertEquals('dark', $result);
    }

    /**
     * Tests that setPreferenceField uses the custom set function for the
     * particular preference if one is defined
     *
     * @covers ::setPreferenceField
     */
    public function testSetPreferenceUsesCustomFunction()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods([])
            ->getMock();

        $helper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set_use_real_names'])
            ->getMock();

        $helper->expects($this->once())
            ->method('set_use_real_names')
            ->with($user);
        $helper->setPreferenceField($user, 'use_real_names', true);
    }

    /**
     * Tests that setPreferenceField falls back to using $bean->setPreference
     * if not custom set function is defined
     *
     * @covers ::setPreferenceField
     */
    public function testSetPreferenceUsesStandardFunction()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['setPreference'])
            ->getMock();

        $helper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $preference = 'some_preference';
        $value = 'some_value';

        $user->expects($this->once())
            ->method('setPreference')
            ->with($preference, $value);
        $helper->setPreferenceField($user, $preference, $value);
    }
}
