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
use Regression\SugarCRMScenario;

class PdfManagerStoredXssRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10704]: Stored XSS at `#bwc/index.php` #PT16493_10 on SugarCRM On-Demand - March 2023';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php',
                [
                    'module' => 'PdfManager',
                    'record' => '',
                    'action' => 'Save',
                    'return_module' => 'PdfManager',
                    'return_id' => '',
                    'return_action' => 'index',
                    'old_record_id' => '',
                    'is_duplicate' => '',
                    'button' => 'Save',
                    'name' => 'Test',
                    'team_name_collection_0' => 'Global',
                    'base_module' => 'Accounts',
                    'author' => 'SugarCRM',
                    'body_html' => '<center onmouseover="alert(document.location)">test</center>',
                ],
                'index.php?module=PdfManager&action=EditView&bwcFrame=1&return_module=PdfManager'
            )
            ->expectStatusCode(200)
            ->extractRegexp('templateId', '/<a href=".*record=([\w]+-[\w]+-[\w]+-[\w]+-[\w]+)">[\n\r]+Test[\n\r]+<\/a>/');

        $templateId = $this->getVar('templateId');

        $scenario
            ->send(
                new Request(
                    'POST',
                    "index.php?module=PdfManager&action=EditView&record=$templateId&bwcFrame=1"
                )
            )
            ->expectStatusCode(200)
            ->expectSubstring('&lt;center&gt;test&lt;/center&gt;');
    }
}
