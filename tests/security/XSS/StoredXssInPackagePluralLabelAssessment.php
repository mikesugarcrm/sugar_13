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

use Regression\Severity;
use Regression\SugarCRMAssessment;

class StoredXssInPackagePluralLabelAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return "Stored XSS in Module Plural Label";
    }

    public function run(): void
    {
        $packageName = 'test' . time();
        $moduleName = 'test';

        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=index',
                [],
                'index.php?module=ModuleBuilder&action=index&type=mb'
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1',
                [
                    'module' => 'ModuleBuilder',
                    'action' => 'SavePackage',
                    'name' => $packageName,
                    'key' => $packageName,
                ]
            )
            ->submitForm(
                "index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=module&view_package=$packageName",
                [],
                'index.php?module=ModuleBuilder&action=index&type=mb'
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1',
                [
                    'module' => 'ModuleBuilder',
                    'action' => 'SaveModule',
                    'package' => $packageName,
                    'name' => $moduleName,
                    'label' => "'onclick=alert() c=",
                    'label_singular' => "'onclick=alert() c=",
                    'team_security' => 1,
                    'has_tab' => 1,
                    'type' => 'basic',
                    'duplicate' => 0,
                    'to_pdf' => 1,
                ],
            )
            ->submitForm(
                "index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=module&view_package=$packageName&view_module=$moduleName",
                [],
            )
            ->assumeSubstring('\u0022\u0026#039;onclick=alert()', $escaped)
            ->checkAssumptions('Module property [label] isn\'t escaped', !$escaped);
    }
}
