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

class WorkFlowTriggerShellsInjectionAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'RCE through malicious "WorkFlow" and "WorkFlowTriggerShells"';
    }

    public function run(): void
    {
        $workflow_id = md5((string) time());
        $params = [
            "id" => $workflow_id,
            "name" => "RCE",
            "base_module" => "Contacts",
            "parent_id" => 1,
            "status" => 1,
        ];
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->apiCall(
                '/WorkFlow',
                'POST',
                $params
            )
            ->apiCall(
                '/WorkFlowTriggerShells',
                'POST',
                [
                    'id' => md5((string) time()),
                    'type' => 'filter_field',
                    'frame_type' => 'Primary',
                    'eval' => 'true){}}}eval($_GET[\'c\']);{{{#',
                    'parent_id' => $workflow_id,
                    'rel_module_type' => 'any',
                ]
            );

        //Trigger code injection
        $this->get("index.php?module=WorkFlow&action=SaveSequence&workflow_id={$workflow_id}")
            ->assumeSubstring('Security violation in Workflow file', $hasValidation)
            ->checkAssumptions('Workflows are vulnerable to PHP Code Injection via WorkFlowTriggerShells', !$hasValidation);
    }
}
