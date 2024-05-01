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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ModuleBuilder\parsers\views;

use PHPUnit\Framework\TestCase;
use SugarTestReflection;

/**
 * @coversDefaultClass \DeployedMetaDataImplementation
 */
class DeployedMetaDataImplementationTest extends TestCase
{
    /**
     * Check getDefsFromRecord for Preview
     *
     * @covers ::getDefsFromRecord
     */
    public function testGetDefsFromRecordForPreview()
    {
        $defs = [];
        $fields = [
            'name',
            [
                'name' => 'desc',
                'type' => 'name',
            ],
        ];
        $defs['base']['view']['record'] = [
            'panels' => [
                [
                    'fields' => [
                        'name',
                        [
                            'name' => 'favorite',
                            'type' => 'favorite',
                        ],
                        [
                            'name' => 'desc',
                            'type' => 'name',
                        ],
                    ],
                ],
            ],
        ];
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->disableOriginalConstructor()->setMethods(null)->getMock();
        $defs = SugarTestReflection::callProtectedMethod($impl, 'getDefsFromRecord', [$defs, 'preview']);
        $this->assertArrayHasKey('preview', $defs['base']['view']);
        $this->assertArrayHasKey('templateMeta', $defs['base']['view']['preview']);
        $this->assertArrayHasKey('maxColumns', $defs['base']['view']['preview']['templateMeta']);
        $this->assertEquals(1, $defs['base']['view']['preview']['templateMeta']['maxColumns']);
        $this->assertArrayHasKey('panels', $defs['base']['view']['preview']);
        $this->assertArrayHasKey('fields', $defs['base']['view']['preview']['panels'][0]);
        $this->assertEquals($fields, array_values($defs['base']['view']['preview']['panels'][0]['fields']));
    }


    /**
     * Check getDefsFromRecord for RecordDashlet
     *
     * @covers ::getDefsFromRecord
     */
    public function testGetDefsFromRecordForRecordDashlet()
    {
        $defs = [];
        $buttons = [
            [
                'type' => 'rowaction',
                'name' => 'edit_button',
            ],
        ];
        $fields = [
            [
                'name' => 'picture',
                'type' => 'avatar',
            ],
            'name',
        ];
        $defs['base']['view']['record'] = [
            'buttons' => [
                [
                    'name' => 'main_dropdown',
                    'buttons' => [
                        [
                            'type' => 'rowaction',
                            'name' => 'edit_button',
                        ],
                        [
                            'type' => 'divider',
                        ],
                        [
                            'type' => 'rowaction',
                            'name' => 'audit_button',
                        ],
                    ],
                ],
            ],
            'panels' => [
                [
                    'name' => 'panel_header',
                    'fields' => [
                        [
                            'name' => 'picture',
                            'type' => 'avatar',
                        ],
                        'name',
                        [
                            'name' => 'favorite',
                            'type' => 'favorite',
                        ],
                    ],
                ],
            ],
        ];
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $defs = SugarTestReflection::callProtectedMethod($impl, 'getDefsFromRecord', [$defs, 'recorddashlet']);

        $this->assertArrayHasKey('recorddashlet', $defs['base']['view']);

        $this->assertArrayHasKey('buttons', $defs['base']['view']['recorddashlet']);
        $this->assertArrayHasKey('buttons', $defs['base']['view']['recorddashlet']['buttons'][0]);
        $this->assertEquals($buttons, array_values($defs['base']['view']['recorddashlet']['buttons'][0]['buttons']));

        $this->assertArrayHasKey('panels', $defs['base']['view']['recorddashlet']);
        $this->assertArrayHasKey('fields', $defs['base']['view']['recorddashlet']['panels'][0]);
        $this->assertEquals($fields, array_values($defs['base']['view']['recorddashlet']['panels'][0]['fields']));
    }
}
