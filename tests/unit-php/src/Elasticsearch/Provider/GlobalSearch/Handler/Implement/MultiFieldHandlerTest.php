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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Implement;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
 */
class MultiFieldHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['enable_long_text_search']);
    }

    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = [
            $nsPrefix . '\AnalysisHandlerInterface',
            $nsPrefix . '\MappingHandlerInterface',
            $nsPrefix . '\SearchFieldsHandlerInterface',
        ];
        $implements = class_implements($nsPrefix . '\Implement\MultiFieldHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::setProvider
     * @dataProvider providerTestSetProvider
     */
    public function testSetProvider($property, array $value, $method, array $expected)
    {
        $provider = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())
            ->method($method)
            ->with($this->equalTo($expected));

        $sut = $this->getMultiFieldHandlerMock();
        TestReflection::setProtectedValue($sut, $property, $value);
        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
    {
        return [
            [
                'typesMultiField',
                [
                    'varchar' => [
                        'gs_string_default',
                        'gs_string_ngram',
                    ],
                    'name' => [
                        'gs_string_default',
                        'gs_string_ngram',
                    ],
                ],
                'addSupportedTypes',
                [
                    'varchar',
                    'name',
                ],
            ],
            [
                'weightedBoost',
                [
                    'field' => 0.35,
                ],
                'addWeightedBoosts',
                [
                    'field' => 0.35,
                ],
            ],
            [
                'highlighterFields',
                [
                    '*.field_default' => [
                        'number_of_fragments' => 0,
                    ],
                ],
                'addHighlighterFields',
                [
                    '*.field_default' => [
                        'number_of_fragments' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Validation test for implemented analysis settings
     * @covers ::buildAnalysis
     */
    public function testBuildAnalysisValidation()
    {
        $analysisBuilder = new AnalysisBuilder();
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = [
            'analysis' => [
                'analyzer' => [
                    'gs_analyzer_string' => [
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_string_ngram' => [
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_phone_ngram' => [
                        'tokenizer' => 'whitespace',
                        'filter' => [
                            'gs_filter_ngram_3_15',
                        ],
                        'char_filter' => [
                            'gs_char_num_pattern',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_phone' => [
                        'tokenizer' => 'whitespace',
                        'char_filter' => [
                            'gs_char_num_pattern',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_text_ngram' => [
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'gs_filter_ngram_3_15',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_url' => [
                        'tokenizer' => 'uax_url_email',
                        'filter' => [
                            'lowercase',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_url_ngram' => [
                        'tokenizer' => 'uax_url_email',
                        'filter' => [
                            'lowercase',
                            'gs_filter_ngram_3_15',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_string_exact' => [
                        'tokenizer' => 'whitespace',
                        'filter' => [
                            'lowercase',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_string_html' => [
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                        ],
                        'char_filter' => [
                            'html_strip',
                        ],
                        'type' => 'custom',
                    ],
                ],
                'tokenizer' => [],
                'filter' => [
                    'gs_filter_ngram_1_15' => [
                        'min_gram' => 1,
                        'max_gram' => 15,
                        'type' => 'ngram',
                    ],
                    'gs_filter_ngram_2_15' => [
                        'min_gram' => 2,
                        'max_gram' => 15,
                        'type' => 'ngram',
                    ],
                    'gs_filter_ngram_3_15' => [
                        'min_gram' => 3,
                        'max_gram' => 15,
                        'type' => 'ngram',
                    ],
                ],
                'char_filter' => [
                    'gs_char_num_pattern' => [
                        'pattern' => '[^\\d]+',
                        'replacement' => '',
                        'type' => 'pattern_replace',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * Validation test for implemented mapping
     * @coversNothing
     * @dataProvider providerTestBuildMappingValidation
     */
    public function testBuildMappingValidation($module, $field, array $defs, array $expected)
    {
        $this->toggleLongTextSearch(true);
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMappingValidation()
    {
        return [
            // test 'varchar' type
            [
                'Accounts',
                'billing_street',
                [
                    'type' => 'varchar',
                ],
                [
                    'Accounts__billing_street' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            'gs_string_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                        ],
                    ],
                    'billing_street' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__billing_street',
                        ],
                    ],
                ],
            ],
            // test 'name' type
            [
                'Opporunities',
                'name',
                [
                    'type' => 'name',
                ],
                [
                    'Opporunities__name' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            'gs_string_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                        ],
                    ],
                    'name' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Opporunities__name',
                        ],
                    ],
                ],
            ],
            // test 'text' type
            [
                'Accounts',
                'description',
                [
                    'type' => 'text',
                ],
                [
                    'Accounts__description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            'gs_string_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                        ],
                    ],
                    'description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__description',
                        ],
                    ],
                ],
            ],
            // test 'datetime' type
            [
                'Accounts',
                'date_modified',
                [
                    'type' => 'datetime',
                ],
                [
                    'Accounts__date_modified' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_datetime' => [
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ],
                        ],
                    ],
                    'date_modified' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__date_modified',
                            'Common__date_modified',
                        ],
                    ],
                    'Common__date_modified' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_datetime' => [
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ],
                        ],
                    ],
                ],
            ],
            // test 'datetimecombo' type
            [
                'Meetings',
                'date_start',
                [
                    'type' => 'datetimecombo',
                ],
                [
                    'Meetings__date_start' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_datetime' => [
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ],
                        ],
                    ],
                    'date_start' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Meetings__date_start',
                        ],
                    ],
                ],
            ],
            // test 'date' type
            [
                'Opportunities',
                'date_closed',
                [
                    'type' => 'date',
                ],
                [
                    'Opportunities__date_closed' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_date' => [
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd',
                                'store' => false,
                            ],
                        ],
                    ],
                    'date_closed' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Opportunities__date_closed',
                        ],
                    ],
                ],
            ],
            // test 'int' type
            [
                'Cases',
                'case_number',
                [
                    'type' => 'int',
                ],
                [
                    'Cases__case_number' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            'gs_string_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            'gs_integer' => [
                                'type' => 'integer',
                                'index' => false,
                                'store' => false,
                            ],
                        ],
                    ],
                    'case_number' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Cases__case_number',
                        ],
                    ],
                ],
            ],
            // test 'phone' type
            [
                'Contacts',
                'mobile',
                [
                    'type' => 'phone',
                ],
                [
                    'Contacts__mobile' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_phone_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_phone_ngram',
                                'search_analyzer' => 'gs_analyzer_phone',
                                'store' => true,
                            ],
                            'gs_not_analyzed' => [
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ],
                        ],
                    ],
                    'mobile' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Contacts__mobile',
                        ],
                    ],
                ],
            ],
            // test 'url' type
            [
                'Accounts',
                'website',
                [
                    'type' => 'url',
                ],
                [
                    'Accounts__website' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_url' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ],
                            'gs_url_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url_ngram',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ],
                        ],
                    ],
                    'website' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__website',
                        ],
                    ],
                ],
            ],
            // test 'id' type
            [
                'Accounts',
                'id',
                [
                    'type' => 'id',
                ],
                [
                    'Accounts__id' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_not_analyzed' => [
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ],
                        ],
                    ],
                    'id' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__id',
                        ],
                    ],
                ],
            ],
            // test 'exact' type
            [
                'Accounts',
                'stuff',
                [
                    'type' => 'exact',
                ],
                [
                    'Accounts__stuff' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string_exact' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_exact',
                                'store' => true,
                            ],
                        ],
                    ],
                    'stuff' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__stuff',
                        ],
                    ],
                ],
            ],
            // test 'longtext' type
            [
                'Accounts',
                'description',
                [
                    'type' => 'longtext',
                ],
                [
                    'Accounts__description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ],
                        'doc_values' => false,
                    ],
                    'description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__description',
                        ],
                        'doc_values' => false,
                    ],
                ],
            ],
            // test 'htmleditable_tinymce' type
            [
                'KBContents',
                'body',
                [
                    'type' => 'htmleditable_tinymce',
                ],
                [
                    'KBContents__body' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ],
                        'doc_values' => false,
                    ],
                    'body' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'KBContents__body',
                        ],
                        'doc_values' => false,
                    ],
                ],
            ],
            // test 'enum' type
            [
                'Bugs',
                'status',
                [
                    'type' => 'enum',
                ],
                [
                    'Bugs__status' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_not_analyzed' => [
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ],
                        ],
                    ],
                    'status' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Bugs__status',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Validation test long text for implemented mapping
     * @coversNothing
     * @dataProvider providerTestBuildMappingValidationNoLongText
     */
    public function testBuildMappingValidationNoLongText($module, $field, array $defs, array $expected)
    {
        $this->toggleLongTextSearch(false);
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMappingValidationNoLongText()
    {
        return [
            // test 'url' type
            [
                'Accounts',
                'website',
                [
                    'type' => 'url',
                ],
                [
                    'Accounts__website' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_url' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ],
                            'gs_url_wildcard' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url_ngram',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ],
                        ],
                    ],
                    'website' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__website',
                        ],
                    ],
                ],
            ],
            // test 'longtext' type
            [
                'Accounts',
                'description',
                [
                    'type' => 'longtext',
                ],
                [
                    'Accounts__description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ],
                    ],
                    'description' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__description',
                        ],
                    ],
                ],
            ],
            // test 'htmleditable_tinymce' type
            [
                'KBContents',
                'body',
                [
                    'type' => 'htmleditable_tinymce',
                ],
                [
                    'KBContents__body' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_string' => [
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ],
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ],
                    ],
                    'body' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'KBContents__body',
                        ],
                    ],
                ],
            ],
            // test 'enum' type
            [
                'Bugs',
                'status',
                [
                    'type' => 'enum',
                ],
                [
                    'Bugs__status' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'gs_not_analyzed' => [
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ],
                        ],
                    ],
                    'status' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Bugs__status',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::buildMapping
     * @covers ::getMultiFieldProperty
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $types, array $multi, $field, $defs, $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock();

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        $sut->buildMapping($mapping, $field, $defs);
        $this->assertSame($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return [
            // missing field type
            [
                'FooBar',
                [],
                [],
                'first_name',
                [
                    'name' => 'first_name',
                ],
                [],
            ],
            // missing mapping definition
            [
                'FooBar',
                [],
                [],
                'first_name',
                [
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ],
                [],
            ],
            // single definition
            [
                'custom_Module',
                [
                    'type1' => ['mapping1'],
                ],
                [
                    'mapping1' => ['type' => 'text'],
                ],
                'field1',
                [
                    'type' => 'type1',
                ],
                [
                    'custom_Module__field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'mapping1' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                    'field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'custom_Module__field1',
                        ],
                    ],
                ],
            ],
            // multi definition
            [
                'Accounts',
                [
                    'type1' => ['mapping1', 'mapping2'],
                ],
                [
                    'mapping1' => ['type' => 'text'],
                    'mapping2' => ['type' => 'integer'],
                ],
                'field1',
                [
                    'type' => 'type1',
                ],
                [
                    'Accounts__field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'mapping1' => [
                                'type' => 'text',
                            ],
                            'mapping2' => [
                                'type' => 'integer',
                            ],
                        ],
                    ],
                    'field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Accounts__field1',
                        ],
                    ],
                ],
            ],
            // sortable
            [
                'Module',
                [
                    'type1' => ['mapping1'],
                ],
                [
                    'mapping1' => ['type' => 'text'],
                ],
                'field1',
                [
                    'type' => 'type1',
                    'full_text_search' => [
                        'sortable' => true,
                    ],
                ],
                [
                    'Module__field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'mapping1' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                    'field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => [
                            'Module__field1',
                            'Common__field1',
                        ],
                    ],
                    'Common__field1' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'mapping1' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::buildSearchFields
     * @covers ::getStringFieldsForType
     * @covers ::isStringBased
     * @covers ::getMultiFieldProperty
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields(array $types, array $multi, $module, $field, array $defs, array $expected)
    {
        $sut = $this->getMultiFieldHandlerMock(['addHighlighterField']);

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        // mock SearchFields
        $sfs = new SearchFields();
        $sut->buildSearchFields($sfs, $module, $field, $defs);

        $fields = [];
        foreach ($sfs as $sf) {
            $fields[] = $sf->compile();
        }
        $this->assertEquals($expected, $fields);
    }

    public function providerTestBuildSearchFields()
    {
        return [
            // missing field type
            [
                [],
                [],
                'Contacts',
                'first_name',
                [
                    'name' => 'first_name',
                ],
                [],
            ],
            // missing mapping definition
            [
                [],
                [],
                'Contacts',
                'first_name',
                [
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ],
                [],
            ],
            // test multi field string fields only
            [
                [
                    'varchar' => [
                        'test_default',
                        'test_ngram',
                    ],
                ],
                [
                    'test_default' => ['type' => 'text'],
                    'test_ngram' => ['type' => 'text'],
                ],
                'Contacts',
                'first_name',
                [
                    'name' => 'first_name',
                    'type' => 'varchar',
                ],
                [
                    'Contacts__first_name.test_default',
                    'Contacts__first_name.test_ngram',
                ],
            ],
            // test mix string and non-string fields
            [
                [
                    'custom_type' => [
                        'test_default',
                        'test_integer',
                    ],
                ],
                [
                    'test_default' => ['type' => 'text'],
                    'test_integer' => ['type' => 'integer'],
                ],
                'CustomModule',
                'custom_field',
                [
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ],
                [
                    'CustomModule__custom_field.test_default',
                ],
            ],
        ];
    }

    /**
     * Get MultiFieldHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
     */
    protected function getMultiFieldHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler::class)
            ->setMethods($methods)
            ->getMock();
    }

    protected function toggleLongTextSearch(bool $flag)
    {
        global $sugar_config;
        $sugar_config['enable_long_text_search'] = $flag;
    }
}
