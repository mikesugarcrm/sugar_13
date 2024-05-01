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

class XSRFDetectionRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-9150] Cross-Site Scripting via Cookie Parameter #PT9735_13 on SugarCRM On-Demand and IDM - March 2022';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $request = new Request(
            'POST',
            'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=package&package=',
            ['Referer' => 'https://<img src=x onerror=prompt(document.domain)>'],
            'action=savedropdown'
        );

        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->send($request)
            ->expectStatusCode(200)
            ->expectSubstring('<h3>&lt;img src=x onerror=prompt(document.domain)&gt;</h3>')
            ->expectSubstring('<pre>$sugar_config[\'http_referer\'][\'list\'][] = \'&lt;img src=x onerror=prompt(document.domain)&gt;\';</pre>');
    }
}
