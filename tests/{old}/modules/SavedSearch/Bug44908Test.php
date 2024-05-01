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

class Bug44908Test extends TestCase
{
    public function testAdvancedSearchWithCommaSeparatedBugNumbers()
    {
        $query = [];
        $_REQUEST = [];
        $storeQuery = new StoreQuery();
        $query['action'] = 'index';
        $query['module'] = 'Bugs';
        $query['orderBy'] = 'BUG_NUMBER';
        $query['sortOrder'] = 'ASC';
        $query['query'] = 'true';
        $query['searchFormTab'] = 'advanced_search';
        $query['showSSDIV'] = 'no';
        $query['bug_number_advanced'] = '1,2,3,4,5';
        $query['name_advanced'] = '';
        $query['status_advanced'][] = 'Assigned';
        $query['favorites_only_advanced'] = '0';
        $query['search_module'] = 'Bug';
        $query['saved_search_action'] = 'save';
        $query['displayColumns'] = 'BUG_NUMBER|NAME|STATUS|TYPE|PRIORITY|FIXED_IN_RELEASE_NAME|ASSIGNED_USER_NAME';
        $storeQuery->query = $query;
        $storeQuery->populateRequest();
        $this->assertEquals('1,2,3,4,5', $_REQUEST['bug_number_advanced'], 'Assert that bug search string 1,2,3,4,5 was not formatted');
    }
}
