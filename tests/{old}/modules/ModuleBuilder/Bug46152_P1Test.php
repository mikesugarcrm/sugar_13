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

class Bug46152_P1Test extends TestCase
{
    public function getModuleAliasesData()
    {
        return [
            [
                'Users',
                ['Users', 'Employees'],
            ],
            [
                'Employees',
                ['Users', 'Employees'],
            ],
            [
                'Notes',
                ['Notes'],
            ],

        ];
    }

    /**
     * Testing ModuleBuilder::getModuleAliases
     *
     * @dataProvider getModuleAliasesData
     * @group 46152
     */
    public function testGetModuleAliases($module, $needAliases)
    {
        $aliases = ModuleBuilder::getModuleAliases($module);
        foreach ($needAliases as $needAlias) {
            $this->assertContains($needAlias, $aliases);
        }
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('dictionary');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
