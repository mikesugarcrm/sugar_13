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

class ManagerTest extends TestCase
{
    /** @var Manager */
    private $manager;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->manager = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Hint\Manager::class)
            ->onlyMethods(['readOptionFromConfigurations'])
            ->getMock();
    }

    /**
     * Provides data for readOptionFromConfigurations
     *
     * @return array
     */
    public function readOptionFromConfigurationsProvider()
    {
        return [
            'buildConfig' => [
                'buildConfig' => [
                    'geo1' => [
                        'option1' => 'val1',
                    ],
                ],
                'geo' => 'geo1',
                'option' => 'option1',
                'expected' => 'val1',
            ],
            'empty buildConfig' => [
                'buildConfig' => [
                    'geo1' => [
                        'option1' => 'val2',
                    ],
                ],
                'geo' => 'geo1',
                'option' => 'option1',
                'expected' => 'val2',
            ],
        ];
    }

    /**
     * Test readOptionFromConfigurations
     *
     * @param $buildConfig
     * @param $geo
     * @param $option
     * @param $expected
     *
     * @dataProvider readOptionFromConfigurationsProvider
     */
    public function testReadOptionFromConfigurations($buildConfig, $geo, $option, $expected)
    {
        $res = SugarTestReflection::callProtectedMethod($this->manager, 'readOptionFromConfigurations', [
            $buildConfig, $geo, $option,
        ]);

        $this->assertEquals($expected, $res);
    }

    public function testRetrieveUserData()
    {
        global $current_user;

        $beanName = 'HintNotificationTargets';
        $testBean = BeanFactory::newBean($beanName);
        $testBean->name = 'test';
        $testBean->assigned_user_id = $current_user->id;
        $testBean->save();
        $res = $this->manager->retrieveUserData($beanName, $current_user->id);

        $testBean->deleted = 1;
        $testBean->save();
        $this->assertArrayHasKey('credentials', $res[0]);
    }
}
