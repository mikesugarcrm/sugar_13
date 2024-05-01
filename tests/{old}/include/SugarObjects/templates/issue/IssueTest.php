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

class IssueTest extends TestCase
{
    protected $cases = [];
    protected $changeTimers = [];
    protected $caseIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        if (!empty($this->changeTimers)) {
            $GLOBALS['db']->query('DELETE FROM changetimers WHERE id IN (\'' . implode("', '", $this->changeTimers) . '\')');
        }
        if (!empty($this->caseIds)) {
            DBManagerFactory::getInstance()->query(
                'DELETE FROM cases WHERE id IN (\'' . implode("', '", $this->caseIds) . '\')'
            );
        }
        SugarBean::leaveOperation('saving_change_timer');

        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    public function testSave()
    {
        $properties = [
            'assigned_user_id' => '1',
            'status' => 'New',
        ];
        $case = SugarTestCaseUtilities::createCase(null, $properties);
        $this->cases[] = $case->id;
        global $db;
        $sql = 'SELECT id, field_name, value_string FROM changetimers WHERE parent_id=' . $db->quoted($case->id);
        $rows = $db->query($sql);
        $results = [];
        while ($rows && $row = $db->fetchByAssoc($rows)) {
            $results[$row['field_name']] = $row['value_string'];
            $this->changeTimers[] = $row['id'];
        }
        $this->assertEquals($properties, $results);
    }

    public function testCreateNewCTRecord()
    {
        $case = new CaseMock();
        $case->id = '1234567';
        $case->status = 'Assigned';
        $now = TimeDate::getInstance()->nowDb();
        $case->date_modified = $now;

        $id = $case->createNewCTRecordMock('status');

        $this->assertNotEmpty($id);
        $this->changeTimers[] = $id;

        $stBean = BeanFactory::retrieveBean('ChangeTimers', $id);
        $this->assertSame($stBean->parent_type, 'Cases');
        $this->assertSame($stBean->parent_id, $case->id);
        $this->assertSame($stBean->field_name, 'status');
        $this->assertSame($stBean->value_string, 'Assigned');
        $this->assertSame($stBean->date_modified, $now);
    }

    /**
     * @param $field
     * @param $value
     * @param $lastRecord
     * @param $expected
     * @dataProvider shouldNotProcessProvider
     */
    public function testShouldNotProcess($field, $value, $lastRecord, $expected)
    {
        $case = new CaseMock();
        $case->$field = $value;
        $result = $case->shouldNotProcessMock($field, $lastRecord);
        $this->assertSame($expected, $result);
    }

    public function shouldNotProcessProvider(): array
    {
        return [
            ['status', 'New', ['value_string' => 'New'], true],
            ['status', 'New', ['value_string' => 'Assigned'], false],
            ['status', 'New', ['value_string' => ''], false],
            ['status', '', ['value_string' => 'New'], false],
            ['status', '', ['value_string' => ''], true],
            ['status', 'New', [], false], // no last record
        ];
    }

    public function save2Provider()
    {
        return [
            [true, 0],
            [false, 1],
        ];
    }

    /**
     * @param $setOperation should set 'saving_change_timer' operation
     * @param $expected how many times should processChangeTimers be called
     * @dataProvider save2Provider
     */
    public function testSave2($setOperation, $expected)
    {
        $issue = $this->createPartialMock(\aCase::class, [
            'getChangeTimerFields',
            'processChangeTimers',
            'isNewlyResolved',
            'populateFetchedEmail',
            'fixUpFormatting',
            'commitAuditedStateChanges',
            'saveData',
            'call_custom_logic',
        ]);
        $issue->method('getChangeTimerFields')->willReturn(['status']);
        $issue->field_defs = ['id' => ['name' => 'id', 'type' => 'id']];
        $issue->db = DBManagerFactory::getInstance();
        $issue->id = 'foo';
        $issue->new_with_id = true;
        $this->caseIds[] = 'foo';

        $issue->expects($this->exactly($expected))->method('processChangeTimers');
        if ($setOperation) {
            SugarBean::enterOperation('saving_change_timer');
        }
        $issue->save();
    }
}

class CaseMock extends aCase
{
    public function createNewCTRecordMock(string $field)
    {
        return parent::createNewCTRecord($field);
    }

    public function shouldNotProcessMock(string $field, array $lastRecord): bool
    {
        return parent::shouldNotProcess($field, $lastRecord);
    }
}
