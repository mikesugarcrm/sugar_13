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

class AuthByPassRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10138] Create security regression test for SugarCRM 0-day Auth Bypass';
    }

    public function run(): void
    {
        $firstRequest = new Request(
            'POST',
            'index.php',
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => 'PHPSESSID=b1bbba92-d916-413b-b2a7-ac8a9629a89c',
            ],
            'module=Users&action=Authenticate&user_name=1&user_password=1',
        );
        $this
            ->send($firstRequest)
            ->expectStatusCode(500);

        $secondRequest = new Request(
            'GET',
            'index.php?module=EmailTemplates&action=EditView',
        );
        $this
            ->send($secondRequest, [
                'allow_redirects' => false,
            ])
            ->expectStatusCode(302);
    }
}
