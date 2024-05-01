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
use GuzzleHttp\Cookie\SetCookie;
use Regression\SugarCRMScenario;

class ImportAccountsRegression extends SugarCRMScenario
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
            'GET',
            'index.php?module=Import&action=Step1&import_module=Accounts'
        );

        $scenario = $this->login('admin', 'asdf')
            ->bwcLogin();

        $cookieJar = $scenario->client->getConfig('cookies');
        $sid = $cookieJar->getCookieByName('PHPSESSID');
        $cookieJar->setCookie(new SetCookie([
            'Domain' => $sid->getDomain(),
            'Name' => 'appearance',
            'Value' => 'lightemb29%22%3e%3cscript%3ealert(document.cookie)%3c%2fscript%3eca7x9jawv02',
            'Discard' => true,
        ]));


        $scenario->send($request, ['cookies' => $cookieJar])
            ->expectStatusCode(200)
            ->expectRegexp('~\<html[^>]*&quot;&gt;&lt;script&gt;alert\(document\.cookie\)&lt;\/script&gt;[^>]*\>~is')
            ->expectRegexp('~\<body[^>]*&quot;&gt;&lt;script&gt;alert\(document\.cookie\)&lt;\/script&gt;[^>]*\>~is');
    }
}
