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
 *  Bug #48901
 *      Quotas Continue to Display for Deleted Users
 * @ticket 48901
 * @author arymarchik@sugarcrm.com
 */
class Bug48901Test extends TestCase
{
    private $timePeriod;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true]);
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->timePeriod = SugarTestTimePeriodUtilities::createTimePeriod();
    }

    /**
     * @group 48901
     * @outputBuffering disabled
     */
    public function testQuotasDeletedUsers()
    {
        global $current_user;
        $ammount_diff = 100;
        $focus = new Quota();
        $amount = $focus->getGroupQuota($this->timePeriod->id, false);
        $user = SugarTestUserUtilities::createAnonymousUser(false);
        $user->reports_to_id = $current_user->id;
        $user->save();

        $bean = new Quota();
        $bean->quota_type = 'Direct';
        $bean->created_by = $current_user->id;
        $bean->user_id = $user->id;
        $bean->timeperiod_id = $this->timePeriod->id;
        $bean->amount = $ammount_diff;
        $bean->amount_base_currency = $ammount_diff;
        $bean->currency_id = -99;
        $bean->committed = 0;
        $bean->save();

        SugarTestQuotaUtilities::setCreatedQuota([$bean->id]);

        $amount2 = $focus->getGroupQuota($this->timePeriod->id, false);

        $this->assertEquals($amount2 - $amount, $ammount_diff);
        $data = SugarTestReflection::callProtectedMethod($bean, 'getUserManagedSelectData', [
            $this->timePeriod->id,
        ]);
        $this->assertContains($user->id, $this->getUsersArray($data));

        $user->mark_deleted($user->id);

        $amount2 = $focus->getGroupQuota($this->timePeriod->id, false);

        $this->assertEquals($amount, $amount2);
        $data = SugarTestReflection::callProtectedMethod($bean, 'getUserManagedSelectData', [
            $this->timePeriod->id,
        ]);
        $this->assertNotContains($user->id, $this->getUsersArray($data));
    }

    private function getUsersArray($data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            array_push($result, $v['user_id']);
        }
        return $result;
    }

    protected function tearDown(): void
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
    }
}
