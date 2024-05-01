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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Analysis;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder
 */
class AnalysisBuilderTest extends TestCase
{
    /**
     * @covers ::addCustomAnalyzer
     * @covers ::addAnalysis
     * @dataProvider providerTestAddCustomAnalyzer
     */
    public function testAddCustomAnalyzer($name, $tokenizer, array $filters, array $charFilters, array $output)
    {
        $builder = $this->getAnalysisBuilderMock();
        $builder2 = $builder->addCustomAnalyzer($name, $tokenizer, $filters, $charFilters);

        $value = TestReflection::getProtectedValue($builder2, 'analysis');
        $this->assertEquals($value[AnalysisBuilder::ANALYZER][$name], $output);
    }

    public function providerTestAddCustomAnalyzer()
    {
        return [
            [
                'gs_analyzer_string',
                'standard',
                ['lowercase'],
                [],
                [
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'standard',
                    AnalysisBuilder::TOKENFILTER => ['lowercase'],
                ],
            ],
            [
                'gs_analyzer_phone',
                'whitespace',
                [],
                ['gs_char_num_pattern'],
                [
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'whitespace',
                    AnalysisBuilder::CHARFILTER => ['gs_char_num_pattern'],
                ],
            ],
            [
                'gs_analyzer_phone_ngram',
                'whitespace',
                ['gs_filter_ngram_3_15'],
                ['gs_char_num_pattern'],
                [
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'whitespace',
                    AnalysisBuilder::TOKENFILTER => ['gs_filter_ngram_3_15'],
                    AnalysisBuilder::CHARFILTER => ['gs_char_num_pattern'],
                ],
            ],
        ];
    }


    /**
     * @covers ::addAnalyzer
     * @covers ::addTokenizer
     * @covers ::addFilter
     * @covers ::addCharFilter
     * @covers ::addAnalysis
     * @dataProvider providerTestAddAnalyzer
     */
    public function testAddAnalyzer($name, $base, $type, array $options, array $output)
    {
        $builder2 = null;
        $builder = $this->getAnalysisBuilderMock();
        switch ($base) {
            case AnalysisBuilder::ANALYZER:
                $builder2 = $builder->addAnalyzer($name, $type, $options);
                break;
            case AnalysisBuilder::TOKENIZER:
                $builder2 = $builder->addTokenizer($name, $type, $options);
                break;
            case AnalysisBuilder::TOKENFILTER:
                $builder2 = $builder->addFilter($name, $type, $options);
                break;
            case AnalysisBuilder::CHARFILTER:
                $builder2 = $builder->addCharFilter($name, $type, $options);
                break;
            default:
                break;
        }

        $value = TestReflection::getProtectedValue($builder2, 'analysis');
        $this->assertEquals($value[$base][$name], $output);
    }

    public function providerTestAddAnalyzer()
    {
        return [
            [
                'gs_analyzer_standard',
                AnalysisBuilder::ANALYZER,
                'standard',
                [],
                [
                    'type' => 'standard',
                ],
            ],
            [
                'gs_analyzer_whitespace',
                AnalysisBuilder::TOKENIZER,
                'whitespace',
                [],
                [
                    'type' => 'whitespace',
                ],
            ],
            [
                'gs_filter_ngram_1_15',
                AnalysisBuilder::TOKENFILTER,
                'ngram',
                ['min_gram' => 1, 'max_gram' => 15],
                [
                    'type' => 'ngram',
                    'min_gram' => 1,
                    'max_gram' => 15,
                ],
            ],
            [
                'gs_char_num_pattern',
                AnalysisBuilder::CHARFILTER,
                'pattern_replace',
                ['pattern' => '[^\\d\\s]+', 'replacement' => ''],
                [
                    'type' => 'pattern_replace',
                    'pattern' => '[^\\d\\s]+',
                    'replacement' => '',
                ],
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::compile
     */
    public function testConstructor()
    {
        $analysisBuilderMock = TestMockHelper::getObjectMock(
            $this,
            \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder::class,
            null,
            false
        );
        $expected = [
            AnalysisBuilder::ANALYZER => [],
            AnalysisBuilder::TOKENIZER => [],
            AnalysisBuilder::TOKENFILTER => [],
            AnalysisBuilder::CHARFILTER => [],
        ];

        $this->assertSame($expected, TestReflection::getProtectedValue($analysisBuilderMock, 'analysis'));

        $analysisBuilderMock->compile();
        $this->assertSame([AnalysisBuilder::ANALYSIS => $expected], $analysisBuilderMock->compile());
    }

    /**
     * Get AnalysisBuilderTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder
     */
    protected function getAnalysisBuilderMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
