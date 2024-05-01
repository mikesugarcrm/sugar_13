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
use Regression\SugarCRMScenario;

class CronJobsRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10142]: SugarCronParallelJobs can be used to run shell commands manipulating global variable';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'cronJobs'))
            ->addFile(
                'test.php',
                <<<'PHP'
                <?php

                class SugarCronJobs
                {
                    public function getMyId()
                    {
                        return 'hacked';
                    }
                }

                require 'include/SugarQueue/SugarCronParallelJobs.php';

                $GLOBALS['sugar_config']['cron']['php_binary'] = 'cat "<?php system(\'ls -l\');?>" > cron_shell.php && echo ';

                class job
                {
                    public $id = 'foo';
                }
                (new SugarCronParallelJobs)->runShell(new job);
                (new SugarCronJobs)->getMyId();
                require 'cron_shell.php';
                PHP
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('Code attempted to instantiate denylisted class "SugarCronParallelJobs"')
            ->expectSubstring('Code attempted to instantiate denylisted class "SugarCronJobs"');
    }
}
