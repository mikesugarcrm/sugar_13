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

class UpgradeWizardManifestRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return 'BR-10898: PHP Object Injection through manifest.php (UpgradeWizard action)';
    }

    public function run(): void
    {
        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'manifest_shell'))
            ->addFile(
                'manifest.php',
                <<<'PHP'
                <?php unserialize(base64_decode('TzozMToiR3V6emxlSHR0cFxDb29raWVcRmlsZUNvb2tpZUphciI6Mzp7czozNjoiAEd1enpsZUh0dHBcQ29va2llXENvb2tpZUphcgBjb29raWVzIjthOjE6e2k6MDtPOjI3OiJHdXp6bGVIdHRwXENvb2tpZVxTZXRDb29raWUiOjE6e3M6MzM6IgBHdXp6bGVIdHRwXENvb2tpZVxTZXRDb29raWUAZGF0YSI7YToyOntzOjc6IkRpc2NhcmQiO2I6MDtzOjM6IlBIUCI7czo2NzoiPD9waHAgcHJpbnQoJ19fX18nKTsgdmFyX2R1bXAoc3lzdGVtKCcvYmluL2xzJykpOyBwcmludCgnX19fXycpOyA/PiI7fX19czo0MToiAEd1enpsZUh0dHBcQ29va2llXEZpbGVDb29raWVKYXIAZmlsZW5hbWUiO3M6MTg6Ii4vY3VzdG9tL3NoZWxsLnBocCI7czo1MjoiAEd1enpsZUh0dHBcQ29va2llXEZpbGVDb29raWVKYXIAc3RvcmVTZXNzaW9uQ29va2llcyI7YjoxO30=')); ?>
                PHP,
                'manifest.php'
            )
            ->build();
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('Manifest contains forbidden expression');
    }
}
