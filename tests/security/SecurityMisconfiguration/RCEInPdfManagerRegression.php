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
use Regression\SugarCRMRegression;

class RCEInPdfManagerRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-10790]: RCE via PHP Object injection in TCPDF';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?module=PdfManager&action=EditView',
                [],
                'index.php?module=PdfManager&action=EditView',
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'PdfManager',
                    'action' => 'Save',
                    'isDuplicate' => false,
                    'base_module_history' => 'Quotes',
                    'name' => 'regression_template_' . time(),
                    'team_name_new_on_update' => false,
                    'team_name_allow_new' => true,
                    'id_team_name_collection_0' => '1',
                    'base_module' => 'Quotes',
                    'published' => 'yes',
                    'field' => 'phone_alternate',
                    'author' => 'SugarCRM',
                    'body_html' => '<p><tcpdf method="setHeader"params="O%3A32%3A%22Monolog%5CHandler%5CSyslogUdpHandler%22%3A1%3A%7Bs%3A9%3A%22%00%2A%00socket%22%3BO%3A29%3A%22Monolog%5CHandler%5CBufferHandler%22%3A7%3A%7Bs%3A10%3A%22%00%2A%00handler%22%3Br%3A2%3Bs%3A13%3A%22%00%2A%00bufferSize%22%3Bi%3A-1%3Bs%3A9%3A%22%00%2A%00buffer%22%3Ba%3A1%3A%7Bi%3A0%3Ba%3A2%3A%7Bi%3A0%3Bs%3A53%3A%22++++echo+%27%3C%3Fphp+system%28%24_GET%5B%22cmd%22%5D%29%3B%3F%3E%27+%3E+my_cmd.php%22%3Bs%3A5%3A%22level%22%3BN%3B%7D%7Ds%3A8%3A%22%00%2A%00level%22%3BN%3Bs%3A14%3A%22%00%2A%00initialized%22%3Bb%3A1%3Bs%3A14%3A%22%00%2A%00bufferLimit%22%3Bi%3A-1%3Bs%3A13%3A%22%00%2A%00processors%22%3Ba%3A2%3A%7Bi%3A0%3Bs%3A7%3A%22current%22%3Bi%3A1%3Bs%3A6%3A%22system%22%3B%7D%7D%7D"/></p>',
                ],
            )
            ->extractRegexp('templateId', '/<input type="hidden" name="record" value="(.*?)"/');

        $templateId = $scenario->getVar('templateId');

        $scenario
            ->apiCall(
                '/Quotes?erased_fields=true&viewed=1',
                'POST',
                [
                    'name' => 'regression_quote_' . time(),
                    'deleted' => false,
                    'taxrate_value' => 8.25,
                    'show_line_nums' => true,
                    'shipping' => '0',
                    'discount' => '0',
                    'deal_tot_discount_percentage' => '0',
                    'tax' => '0.000000',
                    'renewal' => false,
                    'currency_id' => '-99',
                    'assigned_user_id' => '1',
                    'date_quote_expected_closed' => date('Y-m-d'),
                ],
            )
            ->extract('quoteId', function (ResponseInterface $response) {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['id'] ?? null;
            });

        $quoteId = $scenario->getVar('quoteId');

        if (!$quoteId) {
            throw new \RuntimeException('Quote Creation was failed.');
        }

        $packageName = 'K_TCPDF_CALLS_IN_HTML_regression_' . time();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], $packageName))
            ->addFile(
                'define.php',
                <<<'PHP'
                <?php
                define('K_TCPDF_CALLS_IN_HTML', true);
                PHP,
                'custom/Extension/application/Ext/Include/define.php',
            )
            ->build();

        $scenario
            ->uploadMLP($mlpBuilder->getPath())
            ->extractRegexp('packages', '/var mti_data = (\[.*?]])/');

        $packages = json_decode($scenario->getVar('packages'), true);

        $package = array_filter($packages, function (array $package) use ($packageName) {
            return $package[0] === $packageName;
        });

        if (empty($package)) {
            throw new \RuntimeException('Package wasn\'t uploaded.');
        }

        $package = reset($package);
        $packageId = $package[1];

        $scenario
            ->submitForm(
                'index.php?module=Administration&action=UpgradeWizard&view=module',
                [],
                'index.php?module=Administration&action=UpgradeWizard&view=module',
            )
            ->submitForm(
                'index.php?module=Administration&view=module&action=UpgradeWizard_commit',
                [
                    'mode' => 'Install',
                    'package_id' => $packageId,
                ],
            );

        $quotePdfRequest = new Request(
            'GET',
            "index.php?action=sugarpdf&module=Quotes&sugarpdf=pdfmanager&record=$quoteId&pdf_template_id=$templateId",
        );

        $myCmdRequest = new Request(
            'GET',
            'my_cmd.php?cmd=whoami',
        );

        $scenario
            ->send($quotePdfRequest)
            ->expectStatusCode(200)
            ->send($myCmdRequest)
            ->expectStatusCode(404)
            ->expectSubstring('The requested document was not found or you do not have sufficient permissions to view it');
    }
}
