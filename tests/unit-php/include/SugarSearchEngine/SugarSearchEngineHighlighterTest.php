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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarSearchEngine;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarSearchEngineHighlighter
 */
class SugarSearchEngineHighlighterTest extends TestCase
{
    /**
     * @covers ::processHighlightText
     * @covers ::getLabel
     * @covers ::setModule
     * @dataProvider providerTestProcessHighlightText
     */
    public function testNormalizeFieldName($module, array $results, array $fieldDefs, array $expected)
    {
        $sut = $this->getHighlighterMock(['getFieldDefs']);

        $sut->expects($this->any())
            ->method('getFieldDefs')
            ->will($this->returnValue($fieldDefs));

        $sut->setModule($module);
        $this->assertEquals($expected, $sut->processHighlightText($results));
    }

    public function providerTestProcessHighlightText()
    {
        return [

            // empty results without module specified
            [
                null,
                [],
                [],
                [],
            ],

            // empty high lights
            [
                'Accounts',
                [],
                [],
                [],
            ],

            // normal field highlights, no field defs/label
            [
                'Accounts',
                [
                    'name' => [
                        'SugarCRM <strong>Incorporated</strong>',
                        'And <strong>more</strong> hits',
                    ],
                ],
                [],
                [
                    'name' => [
                        'text' => 'SugarCRM <strong>Incorporated</strong> ... And <strong>more</strong> hits',
                        'module' => 'Accounts',
                        'label' => 'name',
                    ],
                ],
            ],

            // the field value is an array with 1 element
            [
                'Accounts',
                [
                    'email' => [
                        [0 => '<strong>beans.vegan.hr@example.name</strong>'],
                    ],
                ],
                [],
                [
                    'email' => [
                        'text' => '<strong>beans.vegan.hr@example.name</strong>',
                        'module' => 'Accounts',
                        'label' => 'email',
                    ],
                ],
            ],

            // the field value is an array with more than 1 element
            [
                'Accounts',
                [
                    'email' => [
                        [0 => '<strong>beans@example.name</strong>'],
                        [0 => '<strong>kid.air@example.name</strong>'],
                    ],
                ],
                [],
                [
                    'email' => [
                        'text' => '<strong>beans@example.name</strong> ... <strong>kid.air@example.name</strong>',
                        'module' => 'Accounts',
                        'label' => 'email',
                    ],
                ],
            ],

            // the field value is an array of multiple arrays
            [
                'Accounts',
                [
                    'email' => [
                        [
                            0 => '<strong>beans.vegan.hr@example.name</strong>',
                            1 => '<strong>kid.play@example.name</strong>',
                        ],
                    ],
                ],
                [],
                [
                    'email' => [
                        'text' => '',
                        'module' => 'Accounts',
                        'label' => 'email',
                    ],
                ],
            ],

            // testing labels and field normalization
            [
                'Accounts',
                [
                    'name' => [
                        'SugarCRM <strong>Incorporated</strong>',
                        'And <strong>more</strong> hits',
                    ],
                    'description' => [
                        '<strong>Aweseom</strong> company',
                    ],
                    'industry' => [],
                ],
                [
                    'name' => [
                        'label' => 'LBL_NAME_LABEL',
                        'vname' => 'LBL_NAME_VNAME',
                    ],
                    'description' => [
                        'label' => 'LBL_DESC_LABEL',
                    ],
                    'industry' => [
                        'vname' => 'LBL_INDUS_VNAME',
                    ],
                ],
                [
                    'name' => [
                        'text' => 'SugarCRM <strong>Incorporated</strong> ... And <strong>more</strong> hits',
                        'module' => 'Accounts',
                        'label' => 'LBL_NAME_LABEL',
                    ],
                    'description' => [
                        'text' => '<strong>Aweseom</strong> company',
                        'module' => 'Accounts',
                        'label' => 'LBL_DESC_LABEL',
                    ],
                    'industry' => [
                        'text' => '',
                        'module' => 'Accounts',
                        'label' => 'LBL_INDUS_VNAME',
                    ],
                ],
            ],

            // passing some unicode in the mix
            [
                'Accounts',
                [
                    'name' => [
                        '我知道我我知道我知道 <strong>我知道我知道我知道我知道我知道</strong> 我知道我知道我知道我知道我知道我知道我知道我知道',
                        '<strong>我知道我知道</strong> 我知道我',
                    ],
                ],
                [
                    'name' => [
                        'label' => 'LBL_NAME_LABEL',
                    ],
                ],
                [
                    'name' => [
                        'text' => '我知道我我知道我知道 <strong>我知道我知道我知道我知道我知道</strong> 我知道我知道我知道我知道我知道我知道我知道我知道 ... <strong>我知道我知道</strong> 我知道我',
                        'module' => 'Accounts',
                        'label' => 'LBL_NAME_LABEL',
                    ],
                ],
            ],
        ];
    }


    /**
     * @param null|array $methods
     * @return \SugarSearchEngineHighlighter
     */
    protected function getHighlighterMock($methods = null)
    {
        return $this->getMockBuilder('SugarSearchEngineHighlighter')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
