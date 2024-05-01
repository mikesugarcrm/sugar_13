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

class CaseTest extends TestCase
{
    /**
     * @var \Account|mixed
     */
    public $relatedAccount;
    private $case;
    private $old_sugar_config;

    protected $businessCenters = [];

    protected function setUp(): void
    {
        global $sugar_config;

        $this->old_sugar_config = $sugar_config;
    }

    protected function tearDown(): void
    {
        global $sugar_config;

        unset($this->case);

        if (!empty($this->businessCenters)) {
            $sql = 'DELETE FROM business_centers WHERE id IN (\'' . implode("', '", $this->businessCenters) . '\')';
            $GLOBALS['db']->query($sql);
        }
        unset($this->businessCenters);

        $sugar_config = $this->old_sugar_config;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Test whether save method update resolved_datetime when Case is resolved
     * @param string $status The status of a case
     * @param bool $emptyResolvedDate Whether resolved_datetime should be empty
     * @dataProvider updateResolvedDateOnSaveProvider
     */
    public function testUpdateResolvedDateOnSave(string $status, bool $emptyResolvedDate)
    {
        $this->case = SugarTestCaseUtilities::createCase(null, ['status' => $status]);
        $this->case->save();
        $this->assertSame($emptyResolvedDate, empty($this->case->resolved_datetime));
    }

    public function updateResolvedDateOnSaveProvider(): array
    {
        return [
            ['New', true],
            ['Assigned', true],
            ['Closed', false],
            ['Pending Input', true],
            ['Rejected', false],
            ['Duplicate', false],
        ];
    }

    /**
     * @param string $primary_contact_id The Primary Contact Id
     * @dataProvider updatePrimaryContactOnSaveProvider
     */
    public function testUpdatePrimaryContactOnSave(string $primary_contact_id)
    {
        $this->case = SugarTestCaseUtilities::createCase(
            null,
            [
                'new_rel_id' => '0123456789',
                'new_rel_relname' => 'case_contact',
                'primary_contact_id' => $primary_contact_id,
            ]
        );
        $this->case->save();
        $this->assertSame($primary_contact_id, $this->case->new_rel_id);
    }

    public function updatePrimaryContactOnSaveProvider(): array
    {
        return [
            [''],
            ['0123456789'],
            ['9876543210'],
        ];
    }

    /**
     * Test whether save method update business center from related account when empty
     * @param null|string $businessCenterId The id of business center
     * @param string $accountId The id of account
     * @param null|string $relatedBusinessCenterId The id of business center on a related account
     * @param null|string $expectedBusinessCenterId The expected id of business center on the case
     * @dataProvider updateBusinessCenterOnSaveProvider
     */
    public function testUpdateBusinessCenterOnSave(?string $businessCenterId, string $accountId, ?string $relatedBusinessCenterId, ?string $expectedBusinessCenterId)
    {
        $this->relatedAccount = SugarTestAccountUtilities::createAccount(null, [
            'id' => $accountId,
            'business_center_id' => $relatedBusinessCenterId,
        ]);
        $this->relatedAccount->save();
        $this->case = SugarTestCaseUtilities::createCase(null, [
            'account_id' => $accountId,
            'business_center_id' => $businessCenterId,
        ]);
        $this->case->save();
        $this->assertSame($this->case->business_center_id, $expectedBusinessCenterId);
    }

    public function updateBusinessCenterOnSaveProvider(): array
    {
        return [
            ['1234', '9999', '4321', '1234'],
            [null, '9999', '4321', '4321'],
            [null, '9999', null, null],
        ];
    }

    public function handleSLAFieldsProvider(): array
    {
        return [
            ['2019-10-01 00:05:25', '1234'],
            [null, '1234'],
        ];
    }

    /**
     * Test handleSLAFields
     * @param string|null $followUpTime follow up time
     * @param string $assignedUserId assigned user id
     * @covers       aCase::handleSLAFields
     * @dataProvider handleSLAFieldsProvider
     */
    public function testHandleSLAFields($followUpTime, $assignedUserId)
    {
        $case = SugarTestCaseUtilities::createCase('', ['assigned_user_id' => $assignedUserId]);
        if ($followUpTime) {
            $case->follow_up_datetime = $followUpTime;
        }
        $case->handleSLAFields();

        $this->assertNotEmpty($case->first_response_actual_datetime);
        $this->assertNotNull($case->hours_to_first_response);
        $this->assertNotNull($case->business_hrs_to_first_response);
        $this->assertSame($assignedUserId, $case->first_response_user_id);
        if ($followUpTime) {
            $this->assertNotEmpty($case->first_response_target_datetime);
            $this->assertNotEmpty($case->first_response_var_from_target);
        } else {
            $this->assertEmpty($case->first_response_target_datetime);
            $this->assertEmpty($case->first_response_var_from_target);
        }
    }

    /**
     * @return array
     */
    public function getFirstResponseVarianceProvider(): array
    {
        return [
            ['2019-11-08 12:07:24', '2019-11-10 12:17:24', -9.0],
            ['2019-11-10 12:17:24', '2019-11-08 12:07:24', 9.0],
        ];
    }

    /**
     * @param string $actual
     * @param string $target
     * @param float $expected
     * @covers       aCase::getFirstResponseVariance
     * @dataProvider getFirstResponseVarianceProvider
     */
    public function testGetFirstResponseVariance(string $actual, string $target, float $expected)
    {
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $weekend = ['saturday', 'sunday'];
        $bc = BeanFactory::newBean('BusinessCenters');
        $bc->name = 'LA';
        $bc->timezone = 'America/Los_Angeles';
        foreach ($weekdays as $day) {
            $is_open = 'is_open_' . $day;
            $open_hour = $day . '_open_hour';
            $open_min = $day . '_open_minutes';
            $close_hour = $day . '_close_hour';
            $close_min = $day . '_close_minutes';
            $bc->$is_open = true;
            $bc->$open_hour = 8;
            $bc->$open_min = 0;
            $bc->$close_hour = 17;
            $bc->$close_min = 0;
        }
        foreach ($weekend as $day) {
            $is_open = 'is_open_' . $day;
            $open_hour = $day . '_open_hour';
            $open_min = $day . '_open_minutes';
            $close_hour = $day . '_close_hour';
            $close_min = $day . '_close_minutes';
            $bc->$is_open = false;
            $bc->$open_hour = 0;
            $bc->$open_min = 0;
            $bc->$close_hour = 0;
            $bc->$close_min = 0;
        }

        $bc->save();
        $this->businessCenters[] = $bc->id;

        $case = SugarTestCaseUtilities::createCase('');
        $case->business_center_id = $bc->id;
        $bHours = SugarTestReflection::callProtectedMethod(
            $case,
            'getFirstResponseVariance',
            [$actual, $target]
        );
        $this->assertSame($expected, $bHours);
    }

    /**
     * Test whether resolved_datetime is cleared or not based on the sugar config setting
     *
     * @param string $resolvedDate The original resolved datetime
     * @param string $fromStatus The original status
     * @param string $toStatus The new status
     * @param bool $clearOrNot The flag that is set to clear the resolved datetime or not
     * @param string $expect The expected result
     * @dataProvider clearResolvedDateProvider
     */
    public function testClearResolvedDate(string $resolvedDate, string $fromStatus, string $toStatus, bool $clearOrNot, string $expect)
    {
        global $sugar_config;

        $this->case = SugarTestCaseUtilities::createCase(null, [
            'resolved_datetime' => $resolvedDate,
            'status' => $toStatus,
        ]);
        \SugarConfig::getInstance()->clearCache('clear_resolved_date');
        $this->case->fetched_row['status'] = $fromStatus;
        $sugar_config['clear_resolved_date'] = $clearOrNot;
        $this->case->save();
        $this->assertSame($this->case->resolved_datetime, $expect);
    }

    public function clearResolvedDateProvider(): array
    {
        return [
            ['2019-10-04 16:30:00', 'Rejected', 'Pending Input', false, '2019-10-04 16:30:00'],
            ['2019-10-05 17:00:00', 'Closed', 'Assigned', true, ''],
        ];
    }
}
