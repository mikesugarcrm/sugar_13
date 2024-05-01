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

use Regression\SugarCRMRegression;

class WorkflowInjectionRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return 'BR-10933: RCE through malicious "WorkFlow" beans';
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
            ->expect(function (\Psr\Http\Message\ResponseInterface $response) {
                $content = (string) $response->getBody();
                return $content === '';
            });
    }
}
