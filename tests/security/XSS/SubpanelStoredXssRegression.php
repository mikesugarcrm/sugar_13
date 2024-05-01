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

use Regression\Helpers\MLPBuilder;
use Regression\SugarCRMRegression;

class SubpanelStoredXssRegression extends SugarCRMRegression
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10425]: Stored XSS via Subpanel title.';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();
        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'SubpanelXSS'))
            ->withManifest([
                'acceptable_sugar_versions' => [
                    'regex_matches' => ['.*',],
                ],
                'version' => time(),
            ])
            ->addFile(
                'label.php',
                <<<'PHP'
                <?php
                $mod_strings['LBL_CALLS_SUBPANEL_TITLE'] = '\'"“></script><img src=x onerror=alert()>{{\'7\'*7}}';
                PHP
                ,
                'custom/Extension/modules/Accounts/Ext/Language/en_us.xss.php'
            )
            ->build();

        $this
            ->installMLP($mlpBuilder->getPath())
            ->submitForm(
                'index.php?to_pdf=1&sugar_body_only=1&module=ModuleBuilder&action=editLayout&view=ListView&view_module=Accounts&subpanel=calls&subpanelLabel=%27%22%E2%80%9C%3E%3C%2Fscript%3E%3Cimg+src%3Dx+onerror%3Dalert%28%29%3E%7B%7B%277%27%2A7%7D%7D',
                [],
                'index.php?module=ModuleBuilder&action=index&type=studio'
            )
            ->expectSubstring('\u0027\u0026#039;\u0026quot;\u201c\u0026gt;\u0026lt;\/script\u0026gt;\u0026lt;img src=x onerror=alert()\u0026gt;{{\u0026#039;7\u0026#039;*7}}')
            ->expectStatusCode(200);
    }
}
