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

namespace Sugarcrm\SugarcrmTestsUnit\ModuleInstall;

use LoggerManager;
use PHPUnit\Framework\TestCase;
use ModuleScanner;
use SugarTestReflection;

require_once 'ModuleInstall/ModuleScanner.php';
require_once 'include/dir_inc.php';
require_once 'include/utils/sugar_file_utils.php';

/**
 * @coversDefaultClass ModuleScanner
 */
class ModuleScannerTest extends TestCase
{
    public $fileLoc = 'cache/moduleScannerTemp.php';

    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->createMock(LoggerManager::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function phpSamples()
    {
        return [
            ['<?php echo blah;', true],
            ['<? echo blah;', true],
            ['blah <? echo blah;', true],
            ['blah <?xml echo blah;', true],
            ['<?xml version="1.0"></xml>', false],
            ["<?xml \n echo blah;", true],
            ['<?xml version="1.0"><? blah ?></xml>', true],
            ['<?xml version="1.0"><?php blah ?></xml>', true],
        ];
    }

    public function providerIsCreateActionsFile(): array
    {
        return [
            ['clients/base/views/create-actions/create-actions.php', true],
            ['clients/base/views/create-actions/create-actions.js', true],
            ['clients/base/views/create-actions/create-actions.hbs', true],
            ['clients/base/views/create-actions/create-actions.jpg', false],
            ['clients/base/views/create/create.php', false],
        ];
    }

    /**
     * @covers ::isCreateActionsFile
     * @param string $filename
     * @param bool $expected
     * @dataProvider providerIsCreateActionsFile
     */
    public function testIsCreateActionsFile(string $filename, bool $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($ms, 'isCreateActionsFile', [$filename]));
    }

    public function providerIsPhpFile(): array
    {
        return [
            ['clients/base/views/create-actions/create-actions.php', true],
            ['clients/base/views/create-actions/create-actions.js', false],
            ['clients/base/views/create-actions/create-actions.php.js', false],
        ];
    }

    /**
     * @covers ::isPhpFile
     * @param string $filename
     * @param bool $expected
     * @dataProvider providerIsPhpFile
     */
    public function testIsPhpFile(string $filename, bool $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($ms, 'isPhpFile', [$filename]));
    }

    public function providerIsSidecarJSFile(): array
    {
        return [
            ['clients/base/fields/base/base.js', true],
            ['clients/mobile/fields/base/base.js', true],
            ['clients/base/views/create-actions/create-actions.js', true],
            ['clients/base/layouts/base/base.js', true],
            ['clients/api/layouts/base/base.js', true],
            ['clients/base/fields/base/base.hbs', false],
            ['include/javascript/cookie.js', false],
        ];
    }

    /**
     * @covers ::isSidecarJSFile
     * @param string $filename
     * @param bool $expected
     * @dataProvider providerIsSidecarJSFile
     */
    public function testIsSidecarJSFile(string $filename, bool $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($ms, 'isSidecarJSFile', [$filename]));
    }

    public function providerIsSidecarHBSFile(): array
    {
        return [
            ['clients/base/fields/base/detail.hbs', true],
            ['clients/mobile/fields/base/detail.hbs', true],
            ['clients/base/views/create-actions/create-actions.hbs', true],
            ['clients/base/layouts/base/base.hbs', true],
            ['clients/api/layouts/base/base.hbs', true],
            ['clients/base/fields/base/base.js', false],
            ['include/javascript/testfile.hbs', false],
        ];
    }

    /**
     * @covers ::isSidecarHBSFile
     * @param string $filename
     * @param bool $expected
     * @dataProvider providerIsSidecarHBSFile
     */
    public function testIsSidecarHBSFile(string $filename, bool $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($ms, 'isSidecarHBSFile', [$filename])
        );
    }

    /**
     * Tests cases for isCustomLESSFile()
     * @return array
     */
    public function providerIsCustomLESSFile(): array
    {
        return [
            ['custom/themes/custom.less', true],
            ['custom/themes/custom_file.less', true],
            ['custom/themes/custom_folder/custom.less', true],
            ['custom/themes/custom_folder/custom_folder2/custom.less', true],
            ['custom/custom.less', false],
        ];
    }

    /**
     * Run the test cases for providerIsCustomLESSFile()
     * @param string $filename is a test string
     * @param bool $expected is the expected output value from isCustomLESSFile
     * @dataProvider providerIsCustomLESSFile
     */
    public function testIsCustomLESSFile(string $filename, bool $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($ms, 'isCustomLESSFile', [$filename])
        );
    }

    /**
     * @param $module
     * @param $functionDef
     * @param $expected
     * @covers ::scanVardefFile
     * @dataProvider providerTestScanVArdefFile
     */
    public function testScanVardefFile($module, $functionDef, $expected)
    {
        $fileModContents = <<<EOQ
<?php
\$dictionary['{$module}'] = array(
    'fields' => array(
        'function_field' => array(
        'name' => 'function_field',
        {$functionDef},
        ),
    ),
);
EOQ;
        $vardefFile = 'cache/vardefs.php';
        file_put_contents($vardefFile, $fileModContents);
        $ms = new ModuleScanner();
        $errors = SugarTestReflection::callProtectedMethod($ms, 'scanVardefFile', [$vardefFile]);
        unlink($vardefFile);
        $this->assertSame($expected, empty($errors));
    }

    public function providerTestScanVArdefFile()
    {
        return [
            [
                'testModule_custom_function_name',
                "'function' => ['name' => 'sugarInternalFunction']",
                true,
            ],
            [
                'testModule_custom_function',
                "'function' => 'sugarInternalFunction'",
                true,
            ],
            [
                'testModule_blacklist_function_name',
                "'function' => ['name' => 'call_user_func_array']",
                false,
            ],
            [
                'testModule_blacklist_function',
                "'function' => 'call_user_func_array'",
                false,
            ],
        ];
    }

    /**
     * test isVardefFile
     * @param $fileName
     * @param $expected
     * @covers ::isVardefFile
     * @dataProvider providerTestIsVardefFile
     */
    public function testIsVardefFile($fileName, $expected)
    {
        $vardefsInManifest = [
            'vardefs' => [
                [
                    'from' => '<basepath>/SugarModules/relationships/vardefs/this_is_a_vardefs.php',
                    'to_module' => 'Accounts',
                ],
            ],
        ];
        $ms = new ModuleScanner();
        SugarTestReflection::setProtectedValue($ms, 'installdefs', $vardefsInManifest);
        $result = SugarTestReflection::callProtectedMethod($ms, 'isVardefFile', [$fileName]);
        $this->assertSame($expected, $result);
    }

    public function providerTestIsVardefFile()
    {
        return [
            ['anydir/vardefs.php', true],
            ['anydir/vardefs.ext.php', true],
            ['anydir/Vardefs/any_file_is_vardefs.php', true],
            ['anydir/anyfile.php', false],
            ['/SugarModules/relationships/vardefs/this_is_a_vardefs.php', true],
        ];
    }


    /**
     * @covers ::isValidExtension
     * When ModuleScanner is enabled, validating allowed and disallowed file extension names.
     */
    public function testValidExtsAllowed()
    {
        // Allowed file names
        $allowed = [
            'php' => 'test.php',
            'htm' => 'test.htm',
            'xml' => 'test.xml',
            'hbs' => 'test.hbs',
            'less' => 'test.less',
            'config' => 'custom/config.php',
        ];

        // Disallowed file names
        $notAllowed = [
            'docx' => 'test.docx',
            'docx(2)' => '../sugarcrm.xml/../sugarcrm/test.docx',
            'java' => 'test.java',
            'phtm' => 'test.phtm',
            'md5' => 'files.md5',
            'md5(2)' => '../sugarcrm/files.md5',

        ];

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($allowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertTrue($valid, "The $ext extension should be valid on $file but the ModuleScanner is saying it is not");
        }

        // Test not valid
        foreach ($notAllowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertFalse($valid, "The $ext extension should not be valid on $file but the ModuleScanner is saying it is");
        }
    }

    /**
     * @covers ::isValidExtension
     */
    public function testValidLicenseFileMissingExtension()
    {
        $ms = new ModuleScanner();
        $valid = $ms->isValidExtension('LICENSE');

        $this->assertTrue($valid);
    }

    /**
     * @covers ::normalizePath
     * @dataProvider normalizePathProvider
     * @param string $path
     * @param string $expected
     */
    public function testNormalize($path, $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, $ms->normalizePath($path));
    }

    public function normalizePathProvider()
    {
        return [
            ['./foo', 'foo'],
            ['foo//bar///baz/', 'foo/bar/baz'],
            ['./foo/.//./bar/foo', 'foo/bar/foo'],
            ['foo/../bar', false],
            ['../bar/./', false],
            ['./', ''],
            ['.', ''],
            ['', ''],
            ['/', ''],
        ];
    }
}

class MockModuleScanner extends ModuleScanner
{
    public $config;
}
