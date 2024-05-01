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

class ConfigurationManagerTest extends TestCase
{
    /** @var ConfigurationManager */
    private $configurationManager;

    protected function setUp(): void
    {
        $this->configurationManager = new Sugarcrm\Sugarcrm\Hint\ConfigurationManager();
    }

    /**
     * Provides data for createInitialModuleConfig
     *
     * @return array
     */
    public function createInitialModuleConfigProvider()
    {
        return [
            'sugarConfig' => [
                'configFieldsForModule' => [
                    'name', 'title', 'address',
                ],
                'expected' => [
                    'name' => true,
                    'title' => true,
                    'address' => true,
                ],
            ],
            'sugarConfigEmpty' => [
                'configFieldsForModule' => [],
                'expected' => [],
            ],
        ];
    }

    /**
     * Test createInitialModuleConfig
     *
     * @param $configFieldsForModule
     * @param $expected
     *
     * @dataProvider createInitialModuleConfigProvider
     */
    public function testCreateInitialModuleConfig($configFieldsForModule, $expected)
    {
        $res = $this->configurationManager->createInitialModuleConfig($configFieldsForModule);

        $this->assertEquals($expected, $res);
    }

    public function testEnsureAdminUser()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);

        $this->configurationManager->ensureAdminUser();
    }
}
