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
use Regression\Helpers\MLPBuilder;
use Regression\Severity;
use Regression\SugarCRMAssessment;

class PharDeserializationAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'RCE via phar deserialization';
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
            ->assume(
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

                    return empty($m1[1]) && $m2[1] < '8.0.0';
                },
                $vulnerable
            )
            ->checkAssumptions(
                <<<'TEXT'
RCE can be reproduced via include/require .phar-archive because SweetTranslator is disabled and PHP version less
then 8.0.0
TEXT,
                $vulnerable
            );
    }
}
