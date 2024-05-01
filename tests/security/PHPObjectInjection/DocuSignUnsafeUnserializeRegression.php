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

class DocuSignUnsafeUnserializeRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10208] Second-Order PHP Object Injection in DocuSign';
    }

    public function run(): void
    {
        /**
         * Serialized and base64 encoded POP chain based on
         * \Monolog\Handler\SyslogUdpHandler and \Monolog\Handler\BufferHandler
         */
        $pop = 'TzozMjoiTW9ub2xvZ1xIYW5kbGVyXFN5c2xvZ1VkcEhhbmRsZXIiOjE6e3M6OToiACoAc29ja2V0IjtPOjI5OiJNb25vbG9nXEhhbmRsZXJcQnVmZmVySGFuZGxlciI6Nzp7czoxMDoiACoAaGFuZGxlciI7cjoyO3M6MTM6IgAqAGJ1ZmZlclNpemUiO2k6LTE7czo5OiIAKgBidWZmZXIiO2E6MTp7aTowO2E6Mjp7aTowO3M6NToibHMgLWwiO3M6NToibGV2ZWwiO047fX1zOjg6IgAqAGxldmVsIjtOO3M6MTQ6IgAqAGluaXRpYWxpemVkIjtiOjE7czoxNDoiACoAYnVmZmVyTGltaXQiO2k6LTE7czoxMzoiACoAcHJvY2Vzc29ycyI7YToyOntpOjA7czo3OiJjdXJyZW50IjtpOjE7czo2OiJzeXN0ZW0iO319fQ==';
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->apiCall('/Administration/config/Docusign', 'POST', ['Docusign_GlobalSettings' => $pop])
            ->apiCall('/DocuSign/getGlobalConfig')
            ->expectStatusCode(200)
            ->expectSubstring('"__PHP_Incomplete_Class_Name"');
    }
}
