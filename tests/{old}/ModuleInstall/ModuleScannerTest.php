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

use PHPUnit\Framework\TestCase;

require_once 'ModuleInstall/ModuleScanner.php';

class ModuleScannerTest extends TestCase
{
    public $fileLoc = 'cache/moduleScannerTemp.php';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->fileLoc);
        SugarTestHelper::saveFile('files.md5');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        if (is_dir(sugar_cached('ModuleScannerTest'))) {
            rmdir_recursive(sugar_cached('ModuleScannerTest'));
        }
    }

    public function testConfigChecks()
    {
        $isconfig = [
            'config.php',
            'config_override.php',
            'custom/../config_override.php',
            'custom/.././config.php',
        ];

        // Disallowed file names
        $notconfig = [
            'custom/config.php',
            'custom/modules/config.php',
            'cache/config_override.php',
            'modules/Module/config.php',
        ];

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($isconfig as $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertTrue($valid, "$file should be recognized as config file");
        }

        // Test not valid
        foreach ($notconfig as $ext => $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertFalse($valid, "$file should not be recognized as config file");
        }
    }

    /**
     * @group bug58072
     */
    public function testLockConfig()
    {
        $fileModContents = <<<'EOQ'
<?php
$GLOBALS['sugar_config']['moduleInstaller']['test'] = true;
$manifest = [];
$installdefs = [];
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new MockModuleScanner();
        $ms->config['test'] = false;
        $ms->lockConfig();
        MSLoadManifest($this->fileLoc);
        $errors = $ms->checkConfig($this->fileLoc);
        $this->assertTrue(!empty($errors), 'Not detected config change');
        $this->assertFalse($ms->config['test'], 'config was changed');
    }


    /**
     * @dataProvider scanCopyProvider
     * @param string $from
     * @param string $to
     * @param bool $ok is it supposed to be ok?
     */
    public function testScanCopy($file, $from, $to, $ok)
    {
        copy(__DIR__ . '/../upgrade/files.md5', 'files.md5');
        // ensure target file exists
        $from = sugar_cached("ModuleScannerTest/$from");
        $file = sugar_cached("ModuleScannerTest/$file");
        mkdir_recursive(dirname($file));
        SugarTestHelper::saveFile($file);
        sugar_touch($file);

        $ms = new ModuleScanner();
        $ms->scanCopy($from, $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), 'Issue found where it should not be');
        } else {
            $this->assertNotEmpty($ms->getIssues(), 'Issue not detected');
        }
        // check with dir
        $ms->scanCopy(dirname($from), $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), 'Issue found where it should not be');
        } else {
            $this->assertNotEmpty($ms->getIssues(), 'Issue not detected');
        }
    }

    public function scanCopyProvider()
    {
        return [
            [
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                'modules/Audit',
                false,
            ],
            [
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                'modules/Audit/Audit.php',
                false,
            ],
            [
                'copy/modules/Audit/Audit.php',
                'copy',
                '.',
                false,
            ],
            [
                'copy/modules/Audit/SomeFile.php',
                'copy',
                '.',
                true,
            ],
            [
                'copy/modules/Audit/SomeFile.php',
                'copy/modules/Audit/SomeFile.php',
                'copy/modules/Audit/SomeFile',
                true,
            ],
            [
                'copy/modules/Audit/SomeFile.php',
                'copy/modules/Audit/SomeFile.php',
                'copy/modules/Audit/SomeFile.ext',
                false,
            ],
        ];
    }
}

class MockModuleScanner extends ModuleScanner
{
    public $config;
}
