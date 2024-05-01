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

require_once 'ModuleInstall/ModuleInstaller.php';

class ExtTest extends TestCase
{
    /**
     * @var string|mixed
     */
    public $testvalue;
    protected $module_installer;

    public static function setUpBeforeClass(): void
    {
        if (extension_loaded('shadow')) {
            self::markTestSkipped('Does not work on Shadow because of a custom module installed before test run, see TDD-80');
        }
        $GLOBALS['current_language'] = 'en_us';

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        $GLOBALS['current_user']->is_admin = '1';
        mkdir_recursive('cache/ExtTest');
        mkdir_recursive('custom/modules/ExtFrameworkTest');
    }

    protected function setUp(): void
    {
        $this->module_installer = new ModuleInstaller();
        $this->module_installer->silent = true;
        $this->module_installer->base_dir = 'cache/ExtTest';
        $this->module_installer->id_name = 'ExtFrameworkTest';
        $this->testvalue = uniqid('ext', true);
        file_put_contents($this->module_installer->base_dir . '/test.ext.php', "<?php \$testvalue = '$this->testvalue';");
        file_put_contents('custom/modules/ExtFrameworkTest/ExtFrameworkTest.php', '<?php class ExtFrameworkTest extends SugarBean {}');
    }

    protected function tearDown(): void
    {
        if ($this->module_installer) {
            $this->module_installer->uninstall_extensions();
        }
        if (file_exists($this->module_installer->base_dir . '/test.ext.php')) {
            @unlink($this->module_installer->base_dir . '/test.ext.php');
        }
        if (file_exists('custom/modules/ExtFrameworkTest/test.ext.php')) {
            @unlink('custom/modules/ExtFrameworkTest/test.ext.php');
        }
        SugarCache::$isCacheReset = false;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
        if (file_exists('cache/ExtTest/test.ext.php')) {
            @unlink('cache/ExtTest/test.ext.php');
        }
        rmdir_recursive('cache/ExtTest');
    }

    public function getExt()
    {
        $extensions = [];
        include 'ModuleInstall/extensions.php';

        foreach ($extensions as $name => $ext) {
            if (empty($ext['section'])) {
                continue;
            }

            switch ($name) {
                case 'modules':
                case 'sidecar':
                case 'dropdown_filters':
                    break;
                default:
                    yield [
                        $name,
                        $ext['section'],
                        $ext['extdir'],
                        $ext['file'],
                        $ext['module'] ?? 'application',
                    ];
                    break;
            }
        }
    }

    /**
     * @dataProvider getExt
     * @param string $extname
     * @param string $section
     * @param string $dir
     * @param string $file
     * @param string $module
     */
    public function testExtFramework($extname, $section, $extdir, $file, $module)
    {
        $this->module_installer->installdefs[$section] = [
            ['from' => '<basepath>/test.ext.php', 'to_module' => $module],
        ];
        $prefix = '';
        $srcFileName = 'test.ext.php';
        if ($extname == 'languages') {
            $this->module_installer->installdefs[$section][0]['language'] = 'en_us';
            $prefix = 'en_us.';
            $file = 'lang.ext.php';
            $srcFileName = 'ExtFrameworkTest.php';
        }
        if ($module == 'application') {
            $srcfile = "custom/Extension/application/Ext/$extdir/{$prefix}{$srcFileName}";
            $dstfile = "custom/application/Ext/$extdir/{$prefix}$file";
        } else {
            $srcfile = "custom/Extension/modules/$module/Ext/$extdir/{$prefix}{$srcFileName}";
            $dstfile = "custom/modules/$module/Ext/$extdir/{$prefix}$file";
        }
        $this->module_installer->install_extensions();
        // check file is there
        $this->assertFileExists($srcfile);
        $testvalue = null;
        // check it works
        include $dstfile;
        $this->assertEquals($this->testvalue, $testvalue);
        $testvalue = null;
        // check disable
        $this->module_installer->disable_extensions();
        if (file_exists($dstfile)) {
            include $dstfile;
        }
        $this->assertNull($testvalue);
        // check enable
        $this->module_installer->enable_extensions();
        $this->assertFileExists($srcfile);
        include $dstfile;
        $this->assertEquals($this->testvalue, $testvalue);
        $testvalue = null;
        // check uninstall
        $this->module_installer->uninstall_extensions();
        if (file_exists($dstfile)) {
            include $dstfile;
        }
        $this->assertNull($testvalue);
    }

    public function testExtModules()
    {
        $this->module_installer->installdefs['beans'] = [
            [
                'module' => 'ExtFrameworkTest',
                'class' => 'ExtFrameworkTest',
                'path' => 'custom/modules/ExtFrameworkTest/ExtFrameworkTest.php',
                'tab' => true,
            ],
        ];
        $srcfile = 'custom/Extension/application/Ext/Include/ExtFrameworkTest.php';
        $dstfile = 'custom/application/Ext/Include/modules.ext.php';
        $this->module_installer->install_extensions();
        // check file is there
        $this->assertFileExists($srcfile);
        $beanList = null;
        // check it works
        include $dstfile;
        $this->assertEquals('ExtFrameworkTest', $beanList['ExtFrameworkTest']);
        // check disable
        $this->module_installer->disable_extensions();
        $beanList = [];
        if (file_exists($dstfile)) {
            include $dstfile;
        }
        $this->assertArrayNotHasKey('ExtFrameworkTest', $beanList);
        // check enable
        $beanList = [];
        $this->module_installer->enable_extensions();
        $this->assertFileExists($srcfile);
        include $dstfile;
        $this->assertEquals('ExtFrameworkTest', $beanList['ExtFrameworkTest']);
        $beanList = [];
        // check uninstall
        $this->module_installer->uninstall_extensions();
        if (file_exists($dstfile)) {
            include $dstfile;
        }
        $this->assertArrayNotHasKey('ExtFrameworkTest', $beanList);
    }
}
