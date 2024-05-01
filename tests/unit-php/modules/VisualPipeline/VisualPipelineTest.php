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
 * @coversDefaultClass \VisualPipeline
 */
class VisualPipelineTest extends TestCase
{
    /**
     * @covers ::isEnabledForModule
     * @dataProvider providerTestIsEnabledForModule
     */
    public function testIsEnabledForModule($module_enabled, $module, $result)
    {
        $visual = $this->getMockBuilder('VisualPipeline')
            ->setMethods(['getEnabledModules'])
            ->disableOriginalConstructor()
            ->getMock();

        $visual->expects($this->once())
            ->method('getEnabledModules')
            ->willReturn($module_enabled);

        $this->assertSame($visual->isEnabledForModule($module), $result);
    }

    public function providerTestIsEnabledForModule(): array
    {
        return [
            [
                [
                    'Opportunities',
                    'Tasks',
                    'Contacts',
                ],
                'Calendar',
                false,
            ],
            [
                [
                    'Opportunities',
                    'Tasks',
                    'Contacts',
                ],
                'Tasks',
                true,
            ],
            [
                [],
                'Opportunities',
                false,
            ],
            [
                ['InvalidString'],
                'Tasks',
                false,
            ],
        ];
    }

    /**
     * @covers ::isModulePipelineExcluded
     * @dataProvider providerTestIsModulePipelineExcluded
     */
    public function testIsModulePipelineExcluded($module, $result)
    {
        $visual = $this->getMockBuilder('VisualPipeline')
            ->setMethods(['getEnabledModules'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($visual->isModulePipelineExcluded($module), $result);
    }

    public function providerTestIsModulePipelineExcluded(): array
    {
        return [
            [
                'Calendar',
                true,
            ],
            [
                'Leads',
                false,
            ],
            [
                'Accounts',
                false,
            ],
        ];
    }
}
