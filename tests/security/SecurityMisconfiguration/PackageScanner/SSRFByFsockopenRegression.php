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

class SSRFByFsockopenRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10135]: pfsockopen allows SSRF attacks via MLP';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'pfsockopen'))
            ->addFile(
                'test.php',
                <<<PHP
                <?php
                pfsockopen();
                PHP,
                'custom/test.php'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('Code attempted to call denylisted function "pfsockopen"');
    }
}
