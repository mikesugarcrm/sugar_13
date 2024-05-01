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

class ReportingUsersTest extends TestCase
{
    protected static $users = [];

    protected static $oldUser;

    /**
     * @var SugarForecasting_ReportingUsers
     */
    protected static $cls;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$users['mgr'] = SugarTestUserUtilities::createAnonymousUser();

        self::$oldUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = self::$users['mgr'];

        self::$users['mgr2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr2']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr2']->save();

        self::$users['rep1'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep1']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep1']->save();

        self::$users['rep2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep2']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep2']->save();

        self::$users['mgr3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr3']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr3']->save();

        self::$users['rep3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep3']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep3']->save();

        self::$users['rep4'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep4']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep4']->save();

        self::$cls = new SugarForecasting_ReportingUsers(['user_id' => self::$users['mgr']->id]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        $GLOBALS['current_user'] = self::$oldUser;
    }

    public function testReturnTreeShouldContain3Children()
    {
        $tree = self::$cls->process();

        $this->assertEquals(3, safeCount($tree['children']));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testReturnTreeShouldContain3Children
     * @param $tree
     */
    public function testFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr']->full_name, $tree['children'][0]['data']);
    }

    /**
     * @group forecasts
     * @return array|string
     */
    public function testFetchReporteeContainsTwoNodes()
    {
        self::$cls->setArg('user_id', self::$users['mgr2']->id);

        $tree = self::$cls->process();

        $this->assertEquals(2, safeCount($tree));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstObjectIsParentLink($tree)
    {
        $this->assertEquals(self::$users['mgr']->full_name, $tree[0]['data']);
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeTreeContainsThreeChildren($tree)
    {
        $this->assertEquals(3, safeCount($tree[1]['children']));
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr2']->full_name, $tree[1]['children'][0]['data']);
    }

    public function testSubManagerParentCascadeStops()
    {
        $GLOBALS['current_user'] = self::$users['mgr2'];
        self::$cls->setArg('user_id', self::$users['rep1']->id);
        $tree = self::$cls->process();

        $this->assertEquals(self::$users['mgr2']->full_name, $tree['data']);
    }

    /**
     * Checks that the attribute ID returned for each node contains the record ID
     *
     * @group forecasts
     */
    public function testIdAttributeIsRecordId()
    {
        $GLOBALS['current_user'] = self::$users['mgr'];
        self::$cls->setArg('user_id', self::$users['mgr']->id);
        $tree = self::$cls->process();
        $this->assertEquals('jstree_node_' . $tree['metadata']['id'], $tree['attr']['id']);
        $this->assertEquals(
            'jstree_node_myopps_' . $tree['children'][0]['metadata']['id'],
            $tree['children'][0]['attr']['id']
        );
    }
}
