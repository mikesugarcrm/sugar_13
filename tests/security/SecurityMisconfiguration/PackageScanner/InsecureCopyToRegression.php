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

use Regression\SugarCRMScenario;
use Regression\Helpers\MLPBuilder;

class InsecureCopyToRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return <<<'TEXT'
[BR-10148] Bypassing forbidden extensions. 
Package Scanner checks if the file in the MLP has a valid extension ('png', 'gif', 'jpg', 'css', 'js', 'php', 'txt', 
'html', 'htm', 'tpl', 'pdf', 'md5', 'xml', 'hbs', 'less', 'wsdl') only for `from` files but NOT `to` files
TEXT;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'forbidden_ext'))
            ->addFile(
                'inc.php',
                '$2y$10$FoVDhrsNqFS27c8GAONDReF7SfHIjQgvMyvF.76gDcXgC4Gy8bFry',
                'custom/forbidden.ext'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('Copy Issues')
            ->expectSubstring('custom/forbidden.ext')
            ->expectSubstring('Invalid file extension');
    }
}
