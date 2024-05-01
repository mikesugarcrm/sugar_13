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
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\CommentLogHandler
 */
class CommentLogHandlerTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = [
            $nsPrefix . '\MappingHandlerInterface',
            $nsPrefix . '\SearchFieldsHandlerInterface',
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        ];
        $implements = class_implements($nsPrefix . '\Implement\CommentLogHandler');
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

        $sut = $this->getCommentLogHandlerMock();

        if ($property !== null) {
            TestReflection::setProtectedValue($sut, $property, $value);
        }

        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
    {
        return [
            [
                null,
                [],
                'addSupportedTypes',
                ['commentlog'],
            ],
            [
                'highlighterFields',
                ['stuff'],
                'addHighlighterFields',
                ['stuff'],
            ],
            [
                'weightedBoost',
                ['morestuff'],
                'addWeightedBoosts',
                ['morestuff'],
            ],
            [
                null,
                [],
                'addFieldRemap',
                ['commentlog_search' => 'commentlog'],
            ],
            [
                null,
                [],
                'addSkipTypesFromQueue',
                ['commentlog'],
            ],
        ];
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $field, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getCommentLogHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return [
            // test 'commentlog' type for 'commentlog' field
            [
                'testModule',
                'commentlog',
                [
                    'name' => 'commentlog',
                    'type' => 'commentlog',
                ],
                [
                    'testModule__commentlog_search' => [
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => true,
                        'properties' => [
                            'commentlog_entry' => [
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
                        ],
                    ],
                    'testModule__commentlog' => [
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::buildSearchFields
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields($module, $field, array $defs, array $expected)
    {
        $sfs = new SearchFields();
        $sut = $this->getCommentLogHandlerMock();
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
            // commentlog field
            [
                'Contacts',
                'commentlog',
                [
                    'name' => 'commentlog',
                    'type' => 'commentlog',
                ],
                [
                    'Contacts__commentlog_search.commentlog_entry.gs_string',
                    'Contacts__commentlog_search.commentlog_entry.gs_string_wildcard',
                ],
            ],
            // non commentlog type/field
            [
                'Contacts',
                'first_name',
                [
                    'name' => 'first_name',
                    'type' => 'varchar',
                ],
                [],
            ],
            // non commentlog field, commentlog type
            [
                'Contacts',
                'other_commentlog',
                [
                    'name' => 'other_commentlog',
                    'type' => 'commentlog',
                ],
                [],
            ],
        ];
    }

    /**
     * Get CommentLogHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\CommentLogHandler
     */
    protected function getCommentLogHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\CommentLogHandler::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
