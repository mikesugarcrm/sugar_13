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

namespace Sugarcrm\SugarcrmTestsUnit\inc\MetaDataManager;

use PHPUnit\Framework\TestCase;

/**
 * Class MetaDataManagerMobileTest
 *
 * @coversDefaultClass \MetaDataManagerMobile
 */
class MetaDataManagerMobileTest extends TestCase
{
    /**
     * @covers ::getSupportingModules
     * @dataProvider modListProvider
     */
    public function testGetSupportingModules($mods, $supportList, $expectedModules)
    {
        $metadataManagerMobileMock = $this->createPartialMock(
            '\MetaDataManagerMobile',
            ['retrieveSupportingModuleListByBeanName']
        );
        $metadataManagerMobileMock->method('retrieveSupportingModuleListByBeanName')->will($this->returnCallback(
            function ($beanName) use ($supportList) {
                return $supportList[$beanName];
            }
        ));

        $modules = $metadataManagerMobileMock->getSupportingModules($mods);
        $this->assertEquals($expectedModules, $modules);
    }

    /**
     * DataProvider for MetaDataManagerMobileTest
     */
    public function modListProvider()
    {
        return [
            [
                ['Mod0', 'Mod1', 'Mod2', 'Mod3',],
                [
                    'Mod0' => [],
                    'Mod1' => ['Sup1', 'Sup2',],
                    'Mod2' => [],
                    'Mod3' => ['Sup3', 'Sup4',],
                ],
                ['Sup1', 'Sup2', 'Sup3', 'Sup4',],
            ],
            [
                ['Mod0', 'Mod1', 'Mod2', 'Mod3',],
                ['Mod0' => [], 'Mod1' => [], 'Mod2' => [], 'Mod3' => [],],
                [],
            ],
            [
                ['Mod0', 'Mod1', 'Mod2', 'Mod3'],
                [
                    'Mod0' => ['Sup1',],
                    'Mod1' => ['Sup2',],
                    'Mod2' => ['Sup3',],
                    'Mod3' => ['Sup4',],
                ],
                ['Sup1', 'Sup2', 'Sup3', 'Sup4',],
            ],
        ];
    }
}
