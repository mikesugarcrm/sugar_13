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

class SugarJobActivityStreamPurgerTest extends TestCase
{
    protected $sugarConfig;
    protected $schedulersJob;

    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'];

        $this->schedulersJob = $this->createMock('SchedulersJob');
        $this->schedulersJob->expects($this->any())->method('succeedJob')->will($this->returnValue(true));
    }

    protected function tearDown(): void
    {
        SugarTestActivityUtilities::removeAllCreatedActivities();
        $GLOBALS['sugar_config'] = $this->sugarConfig;
    }

    public function testRunPurger_RemoveActivitiesThatHaveBeenSoftDeleted()
    {
        $vars = [
            'activity_type' => 'create',
            'parent_type' => 'Meetings',
            'deleted' => 1,
        ];
        $deletedActivity = SugarTestActivityUtilities::createActivity('', $vars);

        $this->assertTrue(
            SugarTestActivityUtilities::activityExists($deletedActivity->id, true),
            'deleted activity not found'
        );

        $job = new SugarJobActivityStreamPurger();
        $job->setJob($this->schedulersJob);

        $result = $job->run(null);
        $this->assertTrue($result, 'Job did not complete with true as expected');

        $this->assertFalse(
            SugarTestActivityUtilities::activityExists($deletedActivity->id, true),
            'deleted activity should not have been purged'
        );
    }

    public function testRunPurger_RemoveAllActivitiesGreaterThanConfiguredMonths()
    {
        $GLOBALS['sugar_config']['activitystreamcleaner']['keep_all_relationships_activities'] = false;
        $GLOBALS['sugar_config']['activitystreamcleaner']['months_to_keep'] = 15;

        $vars = [
            'date_entered' => date('Y-m-d H:i:s', strtotime('-14 months')),
            'activity_type' => 'create',
            'parent_type' => 'Meetings',
        ];
        $activityLessThanConfiguredMonths = SugarTestActivityUtilities::createActivity('', $vars);

        $vars = [
            'date_entered' => date('Y-m-d H:i:s', strtotime('-16 months')),
            'activity_type' => 'create',
            'parent_type' => 'Calls',
        ];
        $createActivityGreaterThanConfiguredMonths = SugarTestActivityUtilities::createActivity('', $vars);

        $vars = [
            'date_entered' => date('Y-m-d H:i:s', strtotime('-23 months')),
            'activity_type' => 'link',
            'parent_type' => 'Contacts',
        ];
        $linkActivityGreaterThanConfiguredMonths = SugarTestActivityUtilities::createActivity('', $vars);

        $job = new SugarJobActivityStreamPurger();
        $job->setJob($this->schedulersJob);

        $result = $job->run(null);
        $this->assertTrue($result, 'Job did not complete with true as expected');

        $this->assertTrue(
            SugarTestActivityUtilities::activityExists($activityLessThanConfiguredMonths->id),
            'activity should not have been purged'
        );
        $this->assertFalse(
            SugarTestActivityUtilities::activityExists($createActivityGreaterThanConfiguredMonths->id),
            'create activity should have been purged'
        );
        $this->assertFalse(
            SugarTestActivityUtilities::activityExists($linkActivityGreaterThanConfiguredMonths->id),
            'link activity should have been purged'
        );
    }

    public function testRunPurger_KeepLinkActivities()
    {
        $GLOBALS['sugar_config']['activitystreamcleaner']['keep_all_relationships_activities'] = true;
        $GLOBALS['sugar_config']['activitystreamcleaner']['months_to_keep'] = 10;

        $vars = [
            'date_entered' => date('Y-m-d H:i:s', strtotime('-12 months')),
            'activity_type' => 'link',
            'parent_type' => 'Contact',
        ];
        $linkActivityGreaterThanConfiguredMonths = SugarTestActivityUtilities::createActivity('', $vars);

        $job = new SugarJobActivityStreamPurger();
        $job->setJob($this->schedulersJob);

        $result = $job->run(null);
        $this->assertTrue($result, 'Job did not complete with true as expected');

        $this->assertTrue(
            SugarTestActivityUtilities::activityExists($linkActivityGreaterThanConfiguredMonths->id),
            'link activity should not have been purged'
        );
    }
}
