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

class RestBug56791Test extends TestCase
{
    public function testEmptyQueryReturn()
    {
        $api = new RestService();
        $args = ['q' => '', 'moduleList' => 'Accounts'];
        $options = [];

        $usm = new UnifiedSearchApiMock();
        $result = $usm->determineSugarSearchEngine($api, $args, $options);
        $this->assertEquals('SugarSearchEngine', $result, 'Did not equal SugarSearchEngine, instead was: ' . $result);
    }
}

class UnifiedSearchApiMock extends UnifiedSearchApi
{
    public function determineSugarSearchEngine(ServiceBase $api, array $args, array $options)
    {
        return parent::determineSugarSearchEngine($api, $args, $options);
    }
}
