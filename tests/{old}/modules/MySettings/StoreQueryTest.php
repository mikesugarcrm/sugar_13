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

class StoreQueryTest extends TestCase
{
    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testGetStoredQuery()
    {
        $query = StoreQuery::getStoredQueryForUser('Contacts');
        $this->assertTrue(empty($query), 'StoreQuery::getStoredQueryForUser is not empty.');
    }

    public function testPopulateRequestOverride()
    {
        $_REQUEST['lvso'] = 'desc';
        $_REQUEST['foo'] = 'bar';

        $sq = new StoreQuery();
        $sq->loadQuery('Contacts');

        //StoreQuery should override foo while leaving lvso untouched
        $sq->query['lvso'] = 'asc';
        $sq->query['foo'] = 'overridden';

        $sq->populateRequest();

        $this->assertEquals($_REQUEST['lvso'], 'desc');
        $this->assertEquals($_REQUEST['foo'], 'overridden');
    }
}
