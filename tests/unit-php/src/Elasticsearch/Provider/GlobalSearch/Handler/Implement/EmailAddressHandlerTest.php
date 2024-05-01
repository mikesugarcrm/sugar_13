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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\EmailAddressHandler
 */
class EmailAddressHandlerTest extends TestCase
{
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
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        ];
        $implements = class_implements($nsPrefix . '\Implement\EmailAddressHandler');
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

        $sut = $this->getEmailAddressHandlerMock();

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
                ['email'],
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
                ['email_search' => 'email'],
            ],
            [
                null,
                [],
                'addSkipTypesFromQueue',
                ['email'],
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
        $sut = $this->getEmailAddressHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = [
            'analysis' => [
                'analyzer' => [
                    'gs_analyzer_email' => [
                        'tokenizer' => 'whitespace',
                        'filter' => [
                            'lowercase',
                        ],
                        'type' => 'custom',
                    ],
                    'gs_analyzer_email_ngram' => [
                        'tokenizer' => 'whitespace',
                        'filter' => [
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ],
                        'type' => 'custom',
                    ],
                ],
                'tokenizer' => [],
                'filter' => [],
                'char_filter' => [],
            ],
        ];

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $field, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getEmailAddressHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return [
            // test 'email' type for 'email' field
            [
                'testModule',
                'email',
                [
                    'name' => 'email',
                    'type' => 'email',
                ],
                [
                    'testModule__email_search' => [
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => true,
                        'properties' => [
                            'primary' => [
                                'type' => 'keyword',
                                'index' => false,
                                'fields' => [
                                    'gs_email' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ],
                                    'gs_email_wildcard' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_email_ngram',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ],
                                ],
                            ],
                            'secondary' => [
                                'type' => 'keyword',
                                'index' => false,
                                'fields' => [
                                    'gs_email' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ],
                                    'gs_email_wildcard' => [
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_email_ngram',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'testModule__email' => [
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => false,
                    ],
                ],
            ],
            // test 'email' type for non 'email' field
            [
                'Accounts',
                'other_email',
                [
                    'name' => 'other_email',
                    'type' => 'email',
                ],
                [],
            ],
            // test non 'email' type for 'email' field
            [
                'Contacts',
                'email',
                [
                    'name' => 'email',
                    'type' => 'non_email',
                ],
                [],
            ],
            // test non 'email' type for non 'email' field
            [
                'Leads',
                'other_email',
                [
                    'name' => 'other_email',
                    'type' => 'non_email',
                ],
                [],
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
        $sut = $this->getEmailAddressHandlerMock();
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
            // email field
            [
                'Contacts',
                'email',
                [
                    'name' => 'email',
                    'type' => 'email',
                ],
                [
                    'Contacts__email_search.primary.gs_email',
                    'Contacts__email_search.primary.gs_email_wildcard',
                    'Contacts__email_search.secondary.gs_email',
                    'Contacts__email_search.secondary.gs_email_wildcard',
                ],
            ],
            // non email type/field
            [
                'Contacts',
                'first_name',
                [
                    'name' => 'first_name',
                    'type' => 'varchar',
                ],
                [],
            ],
            // email field, non email type
            [
                'Contacts',
                'email',
                [
                    'name' => 'email',
                    'type' => 'varchar',
                ],
                [],
            ],
            // non email field, email type
            [
                'Contacts',
                'other_email',
                [
                    'name' => 'other_email',
                    'type' => 'email',
                ],
                [],
            ],
        ];
    }

    /**
     * @covers ::processDocumentPreIndex
     * @covers ::getEmailAddressesForBean
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex($module, array $beanFields, $fetch, array $expected)
    {
        $bean = $this->getSugarBeanMock($beanFields);
        $sut = $this->getEmailAddressHandlerMock(['fetchEmailAddressesFromDatabase']);

        // stub db fetch
        if ($fetch === null) {
            $sut->expects($this->never())
                ->method('fetchEmailAddressesFromDatabase');
        } else {
            $sut->expects($this->once())
                ->method('fetchEmailAddressesFromDatabase')
                ->will($this->returnValue($fetch));
        }

        $document = new Document();
        $document->setType($module);

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            // missing email field in bean field_defs
            [
                'Contacts',
                [
                    'module_name' => 'Contacts',
                    'first_name' => 'Jelle',
                    'last_name' => 'Vink',
                    'field_defs' => [
                        'name',
                    ],
                ],
                null,
                [],
            ],
            // no emailAddress object means no fetch and empty result
            [
                'Accounts',
                [
                    'module_name' => 'Accounts',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                null,
                [
                    'Accounts__email' => [],
                    'Accounts__email_search' => [
                        'primary' => '',
                        'secondary' => [],
                    ],
                ],
            ],
            // emailAddress present but not correct object
            [
                'Accounts',
                [
                    'module_name' => 'Accounts',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => '',
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                null,
                [
                    'Accounts__email' => [],
                    'Accounts__email_search' => [
                        'primary' => '',
                        'secondary' => [],
                    ],
                ],
            ],
            // emailAddress present and fetched
            [
                'Leads',
                [
                    'module_name' => 'Leads',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        false,
                        ['first@gmail.com', 'second@sugarcrm.com', 'ok@more.co.uk']
                    ),
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                null,
                [
                    'Leads__email' => [
                        [
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                        [
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                        [
                            'email_address' => 'ok@more.co.uk',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                    ],
                    'Leads__email_search' => [
                        'primary' => 'first@gmail.com',
                        'secondary' => ['second@sugarcrm.com', 'ok@more.co.uk'],
                    ],
                ],
            ],
            // emailAddress present with fetched and dontLegacySave
            [
                'Leads',
                [
                    'module_name' => 'Leads',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        true,
                        ['first@gmail.com', 'second@sugarcrm.com']
                    ),
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                null,
                [
                    'Leads__email' => [
                        [
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                        [
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                    ],
                    'Leads__email_search' => [
                        'primary' => 'first@gmail.com',
                        'secondary' => ['second@sugarcrm.com'],
                    ],
                ],
            ],
            // emailAddress present with fetched and dontLegacySave
            [
                'Accounts',
                [
                    'module_name' => 'Accounts',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        true,
                        ['first@gmail.com']
                    ),
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                null,
                [
                    'Accounts__email' => [
                        [
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                    ],
                    'Accounts__email_search' => [
                        'primary' => 'first@gmail.com',
                        'secondary' => [],
                    ],
                ],
            ],
            // emailAddress present with fetch from database
            [
                'Leads',
                [
                    'module_name' => 'Leads',
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(false, false),
                    'field_defs' => [
                        'email' => ['name' => 'email', 'type' => 'email'],
                    ],
                ],
                $this->getEmailsFixture(['first@gmail.com', 'second@sugarcrm.com', 'ok@more.co.uk']),
                [
                    'Leads__email' => [
                        [
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                        [
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                        [
                            'email_address' => 'ok@more.co.uk',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ],
                    ],
                    'Leads__email_search' => [
                        'primary' => 'first@gmail.com',
                        'secondary' => ['second@sugarcrm.com', 'ok@more.co.uk'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get EmailAddressHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\EmailAddressHandler
     */
    protected function getEmailAddressHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\EmailAddressHandler::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param boolean $hasFetched
     * @param boolean $dontLegacySave
     * @param array $values
     * @return \SugarEmailAddress
     */
    protected function getSugarEmailAddressFixture($hasFetched, $dontLegacySave, array $values = null)
    {
        $email = $this->getMockBuilder('SugarEmailAddress')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $email->hasFetched = $hasFetched;
        $email->dontLegacySave = $dontLegacySave;

        if ($values) {
            $email->addresses = $this->getEmailsFixture($values);
        }

        return $email;
    }

    /**
     * Get raw email address fixture
     * @param array $values
     * @return array
     */
    protected function getEmailsFixture(array $values)
    {
        $fixture = [];
        foreach ($values as $email) {
            $fixture[] = [
                'email_address' => $email,
                'primary_address' => empty($fixture) ? true : false,
                'reply_to_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
            ];
        }
        return $fixture;
    }

    /**
     * Get SugarBean mock
     * @param array $beanFields
     * @return \SugarBean
     */
    protected function getSugarBeanMock(array $beanFields)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        foreach ($beanFields as $property => $value) {
            $bean->$property = $value;
        }

        return $bean;
    }
}
