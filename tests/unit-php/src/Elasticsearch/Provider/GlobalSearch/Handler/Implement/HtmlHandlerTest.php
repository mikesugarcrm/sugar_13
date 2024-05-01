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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\HtmlHandler
 */
class HtmlHandlerTest extends TestCase
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
        $implements = class_implements($nsPrefix . '\Implement\HtmlHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::processDocumentPreIndex
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex(
        array $ftsFields,
        array $beanFields,
        array $expected
    ) {

        $bean = $this->getSugarBeanMock($beanFields);

        $sut = $this->getMockBuilder(
            \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\HtmlHandler::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getFtsHtmlFields'])
            ->getMock();

        // stub fts fields
        $sut->expects($this->any())
            ->method('getFtsHtmlFields')
            ->will($this->returnValue($ftsFields));

        $document = new Document();

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            // no html field
            [
                [],
                ['name' => 'Aim Capital'],
                [],
            ],
            // use </p> in the html field
            [
                ['KBContents__body'],
                ['KBContents__body' => '&lt;p&gt;use any application that accesses the Internet. &lt;/p&gt;'],
                ['KBContents__body' => 'use any application that accesses the Internet. '],
            ],
            // use <br/> in the html field
            [
                ['KBContents__body'],
                ['KBContents__body' => 'use any application &lt;br/&gt;that accesses the Internet.&lt;br/&gt;'],
                ['KBContents__body' => 'use any application that accesses the Internet.'],
            ],
            // use <li/> in the html field
            [
                ['KBContents__body'],
                ['KBContents__body' => '&lt;ul&gt;&lt;li&gt;A&lt;/li&gt;&lt;li&gt;B&lt;/li&gt;&lt;/ul&gt;'],
                ['KBContents__body' => 'AB'],
            ],
            // use the unescaped html tags </p> in the html field
            [
                ['KBContents__body'],
                ['KBContents__body' => '<p>use any application that accesses the Internet. </p>'],
                ['KBContents__body' => 'use any application that accesses the Internet. '],
            ],
            // use the unescaped html tags <br/> in the html field
            [
                ['KBContents__body'],
                ['KBContents__body' => 'use any application <br/>that accesses the Internet. '],
                ['KBContents__body' => 'use any application that accesses the Internet. '],
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
