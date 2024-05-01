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

use Regression\SugarCRMAssessment;

class ExportSQLInjectionAssessment extends SugarCRMAssessment
{
    public function getAssessmentDescription(): string
    {
        return 'SQL Injection through "export" entry point';
    }

    public function run(): void
    {
        $sql = "(SELECT 1 "; // broken SQL
        $payload = urlencode(base64_encode(serialize(['searchFormTab' => '1',  'range_date_modified' => $sql, 'date_modified_1_range_choice' => 'in'])));
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->get("/index.php?entryPoint=export&module=Bugs&current_post={$payload}")
            ->assumeSubstring('Database failure', $error)
            ->checkAssumptions('date_modified is NOT escaped for SQL', $error);
    }
}
