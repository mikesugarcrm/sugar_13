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

class DropdownNameRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return "BR-11034: Stored XSS via dropdown_name allows to attack all the user's in the organization.";
    }

    public function run(): void
    {
        $prefix = bin2hex(random_bytes(2));
        $xss = $prefix . 'XSS");alert();x()("';
        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=dropdown&refreshTree=1',
                [],
                'index.php?module=ModuleBuilder&action=index&type=dropdowns'
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1',
                [
                    'module' => 'ModuleBuilder',
                    'action' => 'savedropdown',
                    'to_pdf' => 'true',
                    'view_module' => '',
                    'view_package' => 'studio',
                    'list_value' => '[["foo","bar"]]',
                    'refreshTree' => 1,
                    'new' => 1,
                    'dropdown_name' => $xss,
                    'dropdown_lang' => 'en_us',
                    'drop_name' => '',
                    'drop_value' => '',
                ]
            )
            ->expectSubstring($prefix . 'XSS%26quot%3B%29%3Balert%28%29%3Bx');
    }
}
