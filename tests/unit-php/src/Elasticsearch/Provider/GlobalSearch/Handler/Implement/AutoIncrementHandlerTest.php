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

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\AutoIncrementHandler
 */
class AutoIncrementHandlerTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = [
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        ];
        $implements = class_implements($nsPrefix . '\Implement\AutoIncrementHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::processDocumentPreIndex
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex(array $ftsFields, array $beanFields, $retrieve, $retrieveValue, array $expected)
    {
        $bean = $this->getSugarBeanMock($beanFields);

        $sut = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\AutoIncrementHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFtsAutoIncrementFields', 'retrieveFieldByQuery'])
            ->getMock();

        // stub fts fields
        $sut->expects($this->any())
            ->method('getFtsAutoIncrementFields')
            ->will($this->returnValue($ftsFields));

        // stub db retrieval
        $hitRetrieve = $retrieve ? 1 : 0;
        $sut->expects($this->exactly($hitRetrieve))
            ->method('retrieveFieldByQuery')
            ->will($this->returnValue($retrieveValue));

        $document = new Document();

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            // no fts fields
            [
                [],
                ['name' => 'hello'],
                null,
                null,
                [],
            ],
            // auto increment already set
            [
                ['case_number'],
                ['case_number' => 1],
                null,
                null,
                [],
            ],
            // auto increment not set and available from db
            [
                ['case_number'],
                [],
                true,
                '2',
                ['case_number' => 2],
            ],
            // auto increment not set and not available from db
            [
                ['case_number'],
                [],
                true,
                '',
                [],
            ],
        ];
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
