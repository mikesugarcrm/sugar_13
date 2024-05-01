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

class ExpressionFilterSelectorRegression extends SugarCRMRegression
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10913] Reflected XSS at https://{sugar}/index.php?module=Expressions&action=Filter_Selector&to_pdf=true&record=';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->get('/index.php?module=Expressions&action=Filter_Selector&to_pdf=true&record=1f8c0ace-55db-11ee-ac8b-06de1b3d51c7&lhs_module=Accounts&lhs_field=hint_account_naics_code_lbl&rhs_value=xss"\'></textarea></script><img src=x onerror=alert()>&return_prefix=trigger&operator=Equals&exp_meta_type=count_trigger&parent_type=trigger')
            ->expectSubstring('Violation for REQUEST');
    }
}
