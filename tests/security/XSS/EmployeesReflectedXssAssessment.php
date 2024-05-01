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

class EmployeesReflectedXssAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return "Reflected XSS in widgets on Employees page";
    }

    public function run(): void
    {
        $this->loginAs('admin')
            ->bwcLogin()
            ->get('index.php?module=Employees&action=DetailView&record=1&apos;);x=eval&lpar;x=%60alert\x28document.cookie\x29%60&rpar;//rrrrrr=rrrrrrr')
            ->assumeSubstring('&apos;);x=eval&lpar;x=%60alert%5Cx28document.cookie%5Cx29%60&rpar;//rrrrrr=rrrrrrr', $notEscaped)
            ->checkAssumptions('User input isn\'t escaped', $notEscaped);
    }
}
