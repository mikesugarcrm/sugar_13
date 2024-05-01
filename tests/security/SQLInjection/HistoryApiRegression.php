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
declare(strict_types=1);

use Regression\SugarCRMScenario;

class HistoryApiRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10210]: [Bug Bounty] SQL Injection through "/link/history" REST API endpoints';
    }

    public function run(): void
    {
        $concat = "CONCAT(id,'||',user_name,'||',user_hash)";
        $sql = urlencode("AS date_modified, 'Users' AS module, {$concat} AS id FROM users WHERE 1=1 OR 1 IN (?,?,?,?,?) UNION SELECT 1,2,3 FROM purchased_line_items WHERE 1=1 OR id IN (?, ?, ?, ?) UNION SELECT 1,'Users',3 FROM emails WHERE ((emails.team_set_id IN (SELECT tst.team_set_id FROM team_sets_teams tst))))#");
        $params = "module_list=Emails&placeholder_fields[Emails][{$sql}]={$sql}";
        $regex = '/"id":"([^"]+)"/';

        $this
            ->login('admin', 'asdf')
            ->apiCall('/Notes')
            ->expectStatusCode(200)
            ->expectRegexp($regex);

        preg_match('/"id":"([^"]+)"/', (string)$this->lastResponse->getBody(), $record);

        $this
            ->apiCall("/Notes/{$record[1]}/link/history?{$params}")
            ->expectStatusCode(500)
            ->expectSubstring('db_error');
    }
}
