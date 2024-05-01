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

/**
 * RecordListFactory Test
 */
class RecordListFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testGetRecordList()
     *
     * @return array
     */
    public function getRecordListDataProvider()
    {
        return [
            [
                'Accounts',
                [1, 2, 3, 4, 5],
                [1, 2, 3, 4, 5],
            ],
            [
                'Cases',
                [],
                [],
            ],
            [
                'Contacts',
                [99, 30, 6],
                [99, 30, 6],
            ],
        ];
    }

    /**
     * Test for static method RecordListFactory::getRecordList()
     *
     * @dataProvider getRecordListDataProvider
     */
    public function testGetRecordList($module, array $recordList, array $expected)
    {
        $id = RecordListFactory::saveRecordList($recordList, $module);
        $this->assertNotEmpty($id);

        $result = RecordListFactory::getRecordList($id);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        $this->arrayHasKey('records');
        $this->arrayHasKey('module_name');

        $this->assertEquals($expected, $result['records']);
        $this->assertEquals($module, $result['module_name']);
    }

    /**
     * Data provider for testSaveRecordList()
     *
     * @return array
     */
    public function saveRecordListDataProvider()
    {
        return [
            [
                'Accounts',
                [1, 2, 3, 4, 5],
                [7, 8, 9],
            ],
            [
                'Contacts',
                [],
                [22, 34, 56],
            ],
            [
                'Bugs',
                [22, 34, 56],
                [],
            ],
            [
                'Cases',
                [],
                [],
            ],
        ];
    }

    /**
     * Test for static method RecordListFactory::saveRecordList()
     *
     * @dataProvider saveRecordListDataProvider
     */
    public function testSaveRecordList($module, array $recordListForSave, array $recordListForUpdate)
    {
        // test create a new list.
        $recordListId = RecordListFactory::saveRecordList($recordListForSave, $module);

        $this->assertNotEmpty($recordListId);

        $records = RecordListFactory::getRecordList($recordListId);

        $this->assertNotEmpty($records);
        $this->assertIsArray($records);
        $this->arrayHasKey('records');
        $this->arrayHasKey('module_name');
        $this->assertEquals($module, $records['module_name']);
        $this->assertEquals($recordListForSave, $records['records']);

        // test update created list
        $newRecordListId = RecordListFactory::saveRecordList($recordListForUpdate, $module, $recordListId);

        $this->assertEquals($recordListId, $newRecordListId);

        $records = RecordListFactory::getRecordList($newRecordListId);

        $this->assertNotEmpty($records);
        $this->assertIsArray($records);
        $this->arrayHasKey('records');
        $this->arrayHasKey('module_name');
        $this->assertEquals($module, $records['module_name']);
        $this->assertEquals($recordListForUpdate, $records['records']);
    }


    /**
     * Test for static method RecordListFactory::deleteRecordList()
     */
    public function testDeleteRecordList()
    {
        $id = RecordListFactory::saveRecordList([1, 2, 3], 'Accounts');
        $this->assertNotEmpty($id);

        $result = RecordListFactory::deleteRecordList($id);
        $this->assertNotEmpty($result);

        $result = RecordListFactory::getRecordList($id);
        $this->assertEmpty($result);
    }
}
