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

class FilesCreationWithPhpShellRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10136]: history related functions allow creation of files with php shell';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->uploadMLP(__DIR__ . '/read_write_history.zip')
            ->expectSubstring('Code attempted to call denylisted function "readline_add_history"')
            ->expectSubstring('Code attempted to call denylisted function "readline_write_history"');
    }
}
