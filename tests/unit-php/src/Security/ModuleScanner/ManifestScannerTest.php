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

namespace Sugarcrm\SugarcrmTestsUnit\src\Security\ModuleScanner;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\ForbiddenManifestExpressionUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\InvalidManifestFormat;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\SyntaxError;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\ManifestScanner;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\ModuleScanner\ManifestScanner
 */
class ManifestScannerTest extends TestCase
{
    /**
     * @var ManifestScanner
     */
    private ManifestScanner $manifestScanner;

    public function setUp(): void
    {
        $this->manifestScanner = new ManifestScanner();
    }

    public function allowedCodeProvider(): array
    {
        return [
            [
                <<<'PHP'
<?php
$manifest = array (
  'built_in_version' => '12.3.0',
  'acceptable_sugar_versions' => 
  array (
    1 => '11.*.*',
    2 => '12.*.*',
  ),
  'acceptable_sugar_flavors' => 
  array (
    0 => 'ENT',
    1 => 'ULT',
  ),
  'readme' => '',
  'key' => '',
  'author' => '',
  'description' => '',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'MLP',
  'published_date' => '2021-04-19 16:45:04',
  'type' => 'module',
  'version' => 1618850705,
  'remove_tables' => 'prompt',
);


$installdefs = array (
  'id' => 'MLP',
  'relationships' => 
  array (
  ),
  'copy' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/inc.php',
      'to' => 'custom/inc.php',
    ),
  ),
  'roles' => 
  array (
  ),
);
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
$manifest = '';
$installdefs = '';
PHP
                ,
            ],
        ];
    }

    /**
     * @dataProvider allowedCodeProvider
     * @covers ::scan
     */
    public function testScanCodeSucceeded(string $code): void
    {
        $issues = $this->manifestScanner->scan($code);
        $this->assertCount(0, $issues);
    }

    public function forbiddenStatementProvider(): array
    {
        return [
            [
                <<<'PHP'
<?php
system('ls');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
'system'('ls');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
"sys$tem"('ls');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$object->setLevel();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$object->{'setLevel'}();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$object->{"setLeve$l"}();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$foo = new ReflectionClass('FOO');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
class MyReflection extends ReflectionClass
{
}
PHP
                ,
                InvalidManifestFormat::class,
            ],
            [
                <<<'PHP'
<?php
AnyClass::{'setLevel'}('foo');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
eval('system("ls");');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
`cat /etc/passwd`;
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$function();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$o->$method();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
Foo::$a();
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
$a = ['shell_exec'];
echo $a[0]('id');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
echo ('shell_exec')('id');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
echo ((new foo)->a)('id');
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
            [
                <<<'PHP'
<?php
include '/absolute/path/to_file.php';
PHP
                ,
                ForbiddenManifestExpressionUsed::class,
            ],
        ];
    }

    /**
     * @dataProvider forbiddenStatementProvider
     * @covers ::scan
     */
    public function testForbiddenStatement(string $code, string $exepectedIssue)
    {
        $issues = $this->manifestScanner->scan($code);
        $this->assertInstanceOf($exepectedIssue, $issues[0]);
    }

    /**
     * @covers ::scan
     */
    public function testSyntaxError()
    {
        $code = <<<'PHP'
<?php
$foo());
PHP;
        $issues = $this->manifestScanner->scan(($code));
        $this->assertInstanceOf(SyntaxError::class, $issues[0]);
    }
}
