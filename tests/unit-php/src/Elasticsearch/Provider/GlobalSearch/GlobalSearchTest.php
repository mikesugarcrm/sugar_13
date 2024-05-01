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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchField;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\MultiFieldHandler;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
 */
class GlobalSearchTest extends TestCase
{
    /**
     * @covers ::getHandlers
     * @covers ::registerHandlers
     * @covers ::__construct
     */
    public function testGetHandlers()
    {
        $sut = new GlobalSearch();
        $this->assertInstanceOf('Iterator', $sut->getHandlers());
        $this->assertCount(10, $sut->getHandlers());
    }

    /**
     * @covers ::getStudioSupportedTypes
     */
    public function testGetStudioSupportedTypes()
    {
        $supported = [
            'varchar',
            'name',
            'text',
            'int',
            'phone',
            'url',
            'longtext',
            'htmleditable_tinymce',
            'email',
            'commentlog',
            'autoincrement',
        ];
        $sut = new GlobalSearch();
        $this->assertEquals($supported, $sut->getStudioSupportedTypes());
    }

    /**
     * @covers ::isValidTypeField
     * @dataProvider providerTestIsValidTypeField
     */
    public function testIsValidTypeField($type, $fromQueue, $isSupported, $isSkipped, $expected)
    {
        $globalSearch = $this->getGlobalSearchMock(
            [
                'isSupportedType',
                'isSkippedType',
            ]
        );

        $globalSearch->expects($this->any())
            ->method('isSupportedType')
            ->will($this->returnValue($isSupported));

        $globalSearch->expects($this->any())
            ->method('isSkippedType')
            ->will($this->returnValue($isSkipped));

        $res = $globalSearch->isValidTypeField($type, $fromQueue);
        $this->assertEquals($expected, $res);
    }

    public function providerTestIsValidTypeField()
    {
        return [
            [
                'string',
                false,
                true,
                false,
                true,
            ],
            [
                'datetimecombo',
                false,
                false,
                false,
                false,
            ],
            [
                'string',
                true,
                false,
                false,
                false,
            ],
            [
                'email',
                true,
                true,
                true,
                false,
            ],
        ];
    }

    /**
     * @covers ::registerHandlers
     */
    public function testStockHandlers()
    {
        $sut = new GlobalSearch();
        $this->assertTrue($sut->hasHandler('MultiFieldHandler'));
        $this->assertTrue($sut->hasHandler('AutoIncrementHandler'));
        $this->assertTrue($sut->hasHandler('EmailAddressHandler'));
        $this->assertTrue($sut->hasHandler('CrossModuleAggHandler'));
        $this->assertTrue($sut->hasHandler('TagsHandler'));
        $this->assertTrue($sut->hasHandler('FavoritesHandler'));
        $this->assertTrue($sut->hasHandler('HtmlHandler'));
    }

    /**
     * @covers ::addHandler
     * @covers ::hasHandler
     * @covers ::getHandler
     * @covers ::removeHandler
     */
    public function testHandlers()
    {
        $ns1 = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement';
        $ns2 = 'Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures';
        $sut = new GlobalSearch();

        // stock handler
        $this->assertTrue($sut->hasHandler('MultiFieldHandler'));
        $this->assertInstanceOf($ns1 . '\MultiFieldHandler', $sut->getHandler('MultiFieldHandler'));

        // add custom multi field handler
        $sut->addHandler(new MultiFieldHandler());
        $this->assertTrue($sut->hasHandler('MultiFieldHandler'));
        $this->assertInstanceOf($ns2 . '\MultiFieldHandler', $sut->getHandler('MultiFieldHandler'));

        // remove handler
        $sut->removeHandler('MultiFieldHandler');
        $this->assertFalse($sut->hasHandler('MultiFieldHandler'));
    }

    /**
     * @covers ::createMultiMatchQuery
     *
     * @dataProvider providerTestCreateMultiMatchQuery
     */
    public function testCreateMultiMatchQuery(array $modules, array $moduleFields, array $expected)
    {
        $searchField = [];
        $sfs = new SearchFields();
        foreach ($moduleFields as $module => $fields) {
            foreach ($fields as $field) {
                $sfs->addSearchField(new SearchField($module, $field, []));
            }
        }

        $gsMock = $this->getGlobalSearchMock(['buildSearchFields']);
        $gsMock->expects($this->any())
            ->method('buildSearchFields')
            ->with($this->logicalNot($this->containsEqual('Tags')))
            ->willReturn($sfs);

        $containerMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProvider'])
            ->getMock();

        $containerMock->expects($this->any())
            ->method('getProvider')
            ->willReturn($this->createMock(Visibility::class));

        TestReflection::setProtectedValue($gsMock, 'container', $containerMock);
        TestReflection::setProtectedValue($gsMock, 'term', 'abcd');
        TestReflection::setProtectedValue($gsMock, 'user', $this->createMock(\User::class));
        TestReflection::setProtectedValue($gsMock, 'modules', $modules);
        TestReflection::setProtectedValue($gsMock, 'getTags', true);

        $mmQuery = TestReflection::callProtectedMethod($gsMock, 'createMultiMatchQuery');

        // no extra module is added
        $this->assertSame($modules, TestReflection::getProtectedValue($gsMock, 'modules'));

        // check fields
        $searchFields = TestReflection::getProtectedValue($mmQuery, 'searchFields');
        foreach ($searchFields as $field) {
            $searchField[] = $field->compile();
        }

        $this->assertSame($expected, $searchField);
    }

    public function providerTestCreateMultiMatchQuery()
    {
        return [
            [
                ['Multi_Modules_1', 'Multi_Modules_2'],
                ['Multi_Modules_1' => ['field1', 'field2'], 'Multi_Modules_2' => ['field1']],
                ['Multi_Modules_1__field1', 'Multi_Modules_1__field2', 'Multi_Modules_2__field1'],
            ],
            [
                ['Single_Module'],
                ['Single_Module' => ['field1', 'field2']],
                ['Single_Module__field1', 'Single_Module__field2'],
            ],
        ];
    }

    /**
     * @covers ::searchTags
     */
    public function testSearchTags()
    {
        $globalSearch = $this->getGlobalSearchMock(['getUserModules']);
        $globalSearch->expects($this->any())
            ->method('getUserModules')
            ->will($this->returnValue(['Accounts', 'Leads']));
        $this->assertEmpty($globalSearch->searchTags());
    }

    /**
     * get Mock object of GlobalSearch
     * @param array|null $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getGlobalSearchMock(array $methods = null)
    {
        return $this->getMockBuilder(GlobalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
