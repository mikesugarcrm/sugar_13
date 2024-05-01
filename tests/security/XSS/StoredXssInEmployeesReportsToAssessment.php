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

class StoredXssInEmployeesReportsToAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS in Employee Detail View';
    }

    public function run(): void
    {
        $stamp = time();

        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                "index.php?module=Employees&offset=7&stamp=$stamp&return_module=Employees&action=DetailView&record=1",
                [],
                "index.php?module=Employees&offset=7&stamp=$stamp&return_module=Employees&action=DetailView&record=1",
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Employees',
                    'record' => '1',
                    'isDuplicate' => false,
                    'action' => 'Save',
                    'relate_to' => 'Employees',
                    'relate_id' => '1',
                    'offset' => '7',
                    'employee_status' => 'Active',
                    'first_name' => '"<img src=x onerror=alert(1)>',
                    'last_name' => '"<img src=x onerror=alert(1)>',
                    'title' => 'Administrator',
                ],
            )
            ->submitForm(
                "index.php?module=Employees&offset=2&stamp=$stamp&return_module=Employees&action=DetailView&record=seed_sarah_id",
                [],
                "index.php?module=Employees&offset=2&stamp=$stamp&return_module=Employees&action=DetailView&record=seed_sarah_id"
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Employees',
                    'record' => 'seed_sarah_id',
                    'action' => 'Save',
                    'relate_to' => 'Employees',
                    'relate_id' => '1',
                    'employee_status' => 'Active',
                    'reports_to_id' => '1',
                ],
            )
            ->submitForm(
                'index.php?action=DetailView&module=Employees&record=seed_sarah_id',
                [],
            )
            ->assumeSubstring('"<img src=x onerror=alert(1)> "<img src=x onerror=alert(1)>', $xss)
            ->checkAssumptions('Stored XSS via [first_name] and [last_name] was found in Employee Detail View.', $xss);
    }
}
