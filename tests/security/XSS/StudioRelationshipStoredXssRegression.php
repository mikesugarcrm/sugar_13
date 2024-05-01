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

use GuzzleHttp\Psr7\Request;
use Regression\SugarCRMRegression;

class StudioRelationshipStoredXssRegression extends SugarCRMRegression
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10745]: Stored XSS At `/index.php?to_pdf=1&sugar_body_only=1` via `lhs_label` Parameter';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=relationship&new=1',
                [],
                'index.php?module=ModuleBuilder&action=index&type=studio'
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1',
                [
                    'module' => 'ModuleBuilder',
                    'action' => 'SaveRelationship',
                    'remove_tables' => 'true',
                    'view_module' => 'Accounts',
                    'relationship_lang' => 'en_us',
                    'relationship_name' => '',
                    'lhs_module' => 'Accounts',
                    'relationship_type' => 'one-to-many',
                    'rhs_module' => 'Campaigns',
                    'lhs_label' => 'a<img src=x onerror=alert()>',
                    'rhs_label' => 'Campaigns',
                    'rhs_subpanel' => 'default',
                ],
            )
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=modulefields&view_package=studio&view_module=Campaigns',
                [],
                'index.php?module=ModuleBuilder&action=index&type=studio'
            )
            ->expectStatusCode(200)
            ->expectRegexp('/labelFormatter.*(elCell.innerText)/')
            ->expectSubstring('\u003Cimg src=x onerror=alert()');
    }
}
