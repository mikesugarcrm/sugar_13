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

class SessionSavePathRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return <<<'TEXT'
[BR-10134] session_save_path() allows creation of files with php shell 
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

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'session_exploit'))
            ->addFile(
                'inc.php',
                <<<'PHP'
<?php
session_save_path(__DIR__);
session_start();
$_SESSION['shell'] = '<?php system("ls -l");?>';
session_write_close();
require __DIR__ . '/sess_' . session_id();
PHP,
                'custom/inc.php'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('session_save_path');
    }
}
