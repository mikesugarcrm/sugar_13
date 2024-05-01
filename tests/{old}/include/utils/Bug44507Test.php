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
 * Bug44507Test
 * This test simulates the query that is run when a non-admin user makes a call to the get_bean_select_array method
 * in include/utils.php.  Bug 44507 is due to the problem
 */
class Bug44507Test extends TestCase
{
    public $disableCountQuery;
    public $skipped = false;

    protected function setUp(): void
    {
        if ($GLOBALS['db']->variant != 'mysql' || !function_exists('mysqli_connect')) {
            $this->skipped = true;
            $this->markTestSkipped('Skipping Test Bug44507');
            return;
        }

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = false;

        $randomTeam = SugarTestTeamUtilities::createAnonymousTeam();
        $randomTeam->add_user_to_team($GLOBALS['current_user']->id);

        global $sugar_config;
        $this->disableCountQuery = $sugar_config['disable_count_query'] ?? false;
        $sugar_config['disable_count_query'] = true;

        global $beanList;
        global $beanFiles;
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    protected function tearDown(): void
    {
        if ($this->skipped) {
            return;
        }
        DBManagerFactory::disconnectAll();
        unset($GLOBALS['sugar_config']['dbconfig']['db_manager_class']);
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        global $sugar_config;
        $sugar_config['disable_count_query'] = $this->disableCountQuery;

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    public function testGetBeanSelectArray()
    {
        if ($this->skipped) {
            return;
        }

        //From EmailMarketing/DetailView this covers most of the cases where EmailTemplate module is queries against
        DBManagerFactory::disconnectAll();
        $GLOBALS['sugar_config']['dbconfig']['db_manager_class'] = 'Bug44507SqlManager';
        $localDb = DBManagerFactory::getInstance();

        $this->assertInstanceOf('Bug44507SqlManager', $localDb);

        get_bean_select_array('true', 'EmailTemplate', 'name');
        $sql = $localDb->getExpectedSql();
        $this->assertMatchesRegularExpression('/email_templates\.id/', $sql);
        $this->assertFalse($localDb->lastError(), "Assert we could run SQL:{$sql}");

        //From Emailmarketing/EditView
        get_bean_select_array(true, 'EmailTemplate', 'name', '', 'name');
        $sql = $localDb->getExpectedSql();
        $this->assertMatchesRegularExpression('/email_templates\.id/', $sql);
        $this->assertFalse($localDb->lastError(), "Assert we could run SQL:{$sql}");

        //From Expressions/Expressions.php
        get_bean_select_array(true, 'ACLRole', 'name');
        $sql = $localDb->getExpectedSql();
        $this->assertMatchesRegularExpression('/acl_roles\.id/', $sql);
        $this->assertFalse($localDb->lastError(), "Assert we could run SQL:{$sql}");

        //From Contracts/Contract.php
        get_bean_select_array(true, 'ContractType', 'name', 'deleted=0', 'list_order');
        $sql = $localDb->getExpectedSql();
        $this->assertMatchesRegularExpression('/contract_types\.id/', $sql);
        $this->assertFalse($localDb->lastError(), "Assert we could run SQL:{$sql}");
    }
}

class Bug44507SqlManager extends MysqliManager
{
    public $expectedSql;

    protected function addDistinctClause(&$sql)
    {
        parent::addDistinctClause($sql);
        $this->expectedSql = $sql;
    }

    public function getExpectedSql()
    {
        return $this->expectedSql;
    }
}
