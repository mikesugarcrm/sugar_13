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
use Psr\Http\Message\ResponseInterface;
use Regression\Severity;
use Regression\SugarCRMRegression;

class PharDeserializationRegression extends SugarCRMRegression
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getRegressionDescription(): string
    {
        return '[BR-10791]: RCE via phar deserialization in a Scheduled Task < PHP 74';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?module=Administration&action=Diagnostic',
                [],
                'index.php?module=Administration&action=Diagnostic',
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Administration',
                    'action' => 'DiagnosticRun',
                    'configphp' => 'on',
                    'phpinfo' => 'on',
                ],
            )
            ->extractRegexp('guid', '/<a href="index.php\?module=Administration&action=DiagnosticDownload&guid=(.*?)&time/')
            ->extractRegexp('time', '/index.php\?module=Administration&action=DiagnosticDownload&guid=.*?&time=(.*?)&to_pdf=1/');

        $guid = $scenario->getVar('guid');
        $time = $scenario->getVar('time');

        $request = new Request(
            'GET',
            "index.php?module=Administration&action=DiagnosticDownload&guid=$guid&time=$time&to_pdf=1"
        );

        $scenario
            ->send($request)
            ->expect(
                function (ResponseInterface $response) {
                    $zipPath = sys_get_temp_dir() . '/config.zip';
                    $content = (string)$response->getBody();
                    file_put_contents($zipPath, $content);
                    $zip = new ZipArchive();
                    $zip->open($zipPath);

                    $configContent = $zip->getFromName('config.php');
                    $phpInfoContent = $zip->getFromName('phpinfo.html');

                    preg_match('/\'enableSweetTranslator\' => true/', $configContent, $m1);
                    preg_match('/PHP Version (.*?)<\/h1>/', $phpInfoContent, $m2);

                    $zip->close();
                    unlink($zipPath);

                    return !empty($m1[1]) || $m2[1] >= '8.0.0';
                },
                'RCE via .phar-archive deserialization was found.'
            );
    }
}
