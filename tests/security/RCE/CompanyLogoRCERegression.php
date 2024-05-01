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

class CompanyLogoRCERegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-10513]: Logo files uploaded are vulnerable to RCE';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?module=Configurator&action=EditView',
                [],
                'index.php?module=Configurator&action=EditView'
            );

        $csrfToken = $scenario->getVar('csrf_token');

        $request = new Request('POST', 'index.php');

        $scenario
            ->send(
                $request,
                [
                    'multipart' => [
                        [
                            'Content-Type' => 'image/png',
                            'name' => 'file_1',
                            'contents' => "<?php echo 'PHP SCRIPT'; ?>",
                            'filename' => 'logo.png',
                        ],
                        [
                            'name' => 'csrf_token',
                            'contents' => $csrfToken,
                        ],
                        [
                            'name' => 'entryPoint',
                            'contents' => 'UploadFileCheck',
                        ],
                    ],
                ]
            )
            ->expectSubstring('"data":"not_recognize"');
    }
}
