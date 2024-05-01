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

require_once 'include/EditView/EditView2.php';

class Bug46365Test extends TestCase
{
    private $o;

    protected function setUp(): void
    {
        $this->o = $this->getMockBuilder('EditView')
            ->setMethods(['requiredFirst'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function provider()
    {
        return [
            // 1 row, 1 element, 2 columns
            [
                [
                    'templateMeta' => [
                        'maxColumns' => '2',
                        'widths' => [
                            ['label' => '10', 'field' => '30'],
                            ['label' => '10', 'field' => '30'],
                        ],
                    ],
                    'panels' => [
                        'panel1' => [
                            [
                                ['name' => 'name1',],
                            ],
                        ],
                    ],
                ],
            ],

            // 1 row, 2 elements, 3 columns
            [
                [
                    'templateMeta' => [
                        'maxColumns' => '3',
                        'widths' => [
                            ['label' => '10', 'field' => '30'],
                            ['label' => '10', 'field' => '30'],
                            ['label' => '10', 'field' => '30'],
                        ],
                    ],
                    'panels' => [
                        'panel1' => [
                            [
                                ['name' => 'name1',],
                                ['name' => 'name2',],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testPanelWithOneFullWidthItem($defs)
    {
        $this->o->defs = $defs;
        $this->o->render();

        $this->assertEquals($defs['panels'], $this->o->defs['panels']);
    }
}
