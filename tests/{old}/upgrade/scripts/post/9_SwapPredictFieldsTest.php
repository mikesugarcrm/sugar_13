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

require_once 'upgrade/scripts/post/9_SwapPredictFields.php';

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SugarUpgradeSwapPredictFields
 */
class SugarUpgradeSwapPredictFieldsTest extends TestCase
{
    private const VIEW_MODULE = 'FakeModule';
    private const VIEW_CLIENT = 'base';
    private const VIEW_VIEW = 'FakeView';

    private $testOpp;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        // Create the metadata file for the fake view
        $testLayout = [
            'panels' => [
                [
                    'fields' => [
                        [
                            'name' => 'potato',
                        ],
                        'field_to_replace',
                    ],
                ],
                [
                    'fields' => [
                        [
                            'name' => 'a_field',
                        ],
                        [
                            'name' => 'field_to_replace',
                            'span' => 12,
                        ],
                        [
                            'name' => 'another_field',
                        ],
                    ],
                ],
            ],
        ];
        sugar_mkdir(self::getCustomViewDirectory(), null, true);
        $arrayName = "viewdefs['" . self::VIEW_MODULE . "']['" .
            self::VIEW_CLIENT . "']['view']['" . self::VIEW_VIEW . "']";
        write_array_to_file(
            $arrayName,
            $testLayout,
            self::getCustomViewFile()
        );

        // Make sure the custom field on Opportunities exists
        $seed = BeanFactory::newBean('Opportunities');
        if (empty($seed->getFieldDefinition('ai_opp_conv_score_enum_c'))) {
            SugarTestHelper::setUpCustomField('Opportunities', [
                'name' => 'ai_opp_conv_score_enum_c',
                'type' => 'enum',
                'options' => 'ai_conv_score_classification_dropdown',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        rmdir_recursive('custom/modules/' . self::VIEW_MODULE);
        SugarTestHelper::tearDownCustomFields();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->testOpp = SugarTestOpportunityUtilities::createOpportunity();
        $this->testOpp->ai_opp_conv_score_enum_c = '03_neutral';
        $this->testOpp->ai_opp_conv_score_enum = '';
        $this->testOpp->save();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

    /**
     * Returns the custom view directory path
     *
     * @return string the custom view directory path
     */
    public static function getCustomViewDirectory()
    {
        return 'custom/modules/' . self::VIEW_MODULE . '/clients/' .
            self::VIEW_CLIENT . '/views/' . self::VIEW_VIEW;
    }

    /**
     * Returns the custom view file path
     *
     * @return string the custom view file path
     */
    public static function getCustomViewFile()
    {
        return self::getCustomViewDirectory() . '/' . self::VIEW_VIEW . '.php';
    }

    /**
     * @covers ::replacePredictFieldsInLayouts
     */
    public function testReplacePredictFieldsInLayouts()
    {
        $mockUpgrader = $this->getMockBuilder(\SugarUpgradeSwapPredictFields::class)
            ->disableOriginalConstructor()
            ->getMock();

        SugarTestReflection::callProtectedMethod(
            $mockUpgrader,
            'replacePredictFieldsInLayouts',
            [
                self::VIEW_MODULE,
                [
                    'field_to_replace' => 'replacement_field',
                ],
            ]
        );

        $viewdefs = [];
        include self::getCustomViewFile();
        $panels = $viewdefs[self::VIEW_MODULE][self::VIEW_CLIENT]['view'][self::VIEW_VIEW]['panels'] ?? null;

        // Check that the string-style field definition was updated
        $this->assertNotEmpty($panels[0]['fields'][1]);
        $this->assertEquals(
            'replacement_field',
            $panels[0]['fields'][1]
        );

        // Check that the array-style field definition was updated
        $this->assertNotEmpty($panels[1]['fields'][1]);
        $this->assertEquals(
            'replacement_field',
            $panels[1]['fields'][1]['name']
        );
        $this->assertEquals(
            12,
            $panels[1]['fields'][1]['span']
        );
    }

    /**
     * @covers ::swapDBValues
     */
    public function testSwapDBValues()
    {
        $mockUpgrader = $this->getMockBuilder(\SugarUpgradeSwapPredictFields::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockUpgrader->db = DBManagerFactory::getInstance();

        SugarTestReflection::callProtectedMethod(
            $mockUpgrader,
            'swapDBValues',
            [
                'Opportunities',
                [
                    'ai_opp_conv_score_enum_c' => 'ai_opp_conv_score_enum',
                ],
            ]
        );

        $updatedBean = BeanFactory::retrieveBean('Opportunities', $this->testOpp->id, ['use_cache' => false]);
        $this->assertEquals('03_neutral', $updatedBean->ai_opp_conv_score_enum);
    }
}
