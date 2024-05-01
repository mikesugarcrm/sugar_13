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
use Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetTranslator;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetTranslator
 */
class SweetTranslatorTest extends TestCase
{
    public function codeDataProvider()
    {
        return [
            [//Comments stay untouched
                <<<'PHP'
<?php

/**
 * multiline comment
 * stays as is
 */
// inline comment 
PHP
                ,
                <<<'PHP'
<?php
/**
 * multiline comment
 * stays as is
 */
// inline comment 
PHP
                ,
            ],
            [// declare() stays as is
                <<<'PHP'
<?php

declare (strict_types=1);
PHP
                ,
                <<<'PHP'
<?php
declare(strict_types=1);
PHP
                ,
            ],
            [// interface stays as is
                <<<'PHP'
<?php

interface Foobar
{
}
PHP
                ,
                <<<'PHP'
<?php
interface Foobar
{

}
PHP
                ,
            ],
            [// \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface interface should be added to class declaration
                <<<'PHP'
<?php

class Foobar implements \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface
{
}
PHP
                ,
                <<<'PHP'
<?php
class Foobar
{

}
PHP
                ,
            ],
            [// Properties and methods declarations stay as is, methods calls via $this stay untouched
                <<<'PHP'
<?php

class Foobar implements \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface
{
    public string $string;
    public string $anotherString = 'default';
    protected int $int;
    protected int $anotherInt = 42;
    private array $array;
    private array $anotherArray = ['default', 'key' => 'value', 42];
    public static $static;
    public function process(string $key, ?int $value = null) : array
    {
        $this->validate($key);
        return [$key => $value];
    }
    private function validate(string $key) : bool
    {
        if (!is_string($key)) {
            throw new \DomainException();
        }
    }
}
PHP
                ,
                <<<'PHP'
<?php

class Foobar
{
    public string $string;
    public string $anotherString = 'default';
    
    protected int $int;
    protected int $anotherInt = 42;
    
    private array $array;
    private array $anotherArray = ['default', 'key' => 'value', 42];
    
    public static $static;
    
    public function process(string $key, ?int $value = null): array
    {
        $this->validate($key);
        return [$key => $value];
    }
    
    private function validate(string $key): bool
    {
        if (!is_string($key)) {
            throw new \DomainException();
        }
    }
}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php

$o = new FooBar();
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callMethod($o, 'method', 'one', 2, ['value', 'key' => 'val']);
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callMethod($o, 'staticMethod', 'one', 2, ['value', 'key' => 'val']);
FooBar::staticMethod('one', 2, ['value', 'key' => 'val']);
PHP
                ,
                <<<'PHP'
<?php

$o = new FooBar();
$o->method('one', 2, ['value', 'key' => 'val']);
$o::staticMethod('one', 2, ['value', 'key' => 'val']);
FooBar::staticMethod('one', 2, ['value', 'key' => 'val']);
PHP
                ,
            ],
            [// Statically resolvable filenames should not be wrapped
                <<<'PHP'
<?php

require 'foo/bar.php';
require_once 'foo2/bar.php';
include 'foo/include.php';
include_once 'foo2/include.php';
PHP
                ,
                <<<'PHP'
<?php

require 'foo/bar.php';
require_once 'foo2/bar.php';
include 'foo/include.php';
include_once 'foo2/include.php';

PHP
                ,
            ],
            [// Statically unresolvable filenames should be wrapped
                <<<'PHP'
<?php

$filename = 'foo/bar';
require \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
require_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
include \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
include_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
PHP
                ,
                <<<'PHP'
<?php

$filename = 'foo/bar';
require $filename;
require_once $filename;
include $filename;
include_once $filename;

PHP
                ,
            ],
            [
                <<<'PHP'
<?php

$len = strlen($_GET['payload']);
$function = 'system';
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction($function, 'ls -l');
PHP
                ,
                <<<'PHP'
<?php
$len = strlen($_GET['payload']);
$function = 'system';
$function('ls -l');
PHP
                ,
            ],
            [
                <<<'PHP'
<?php

// direct call to internal strlen(), should not be wrapped
strlen('World');
$f = 'test';
// dynamic call should be wrapped
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction($f, 'World');
// Encapsed string, wrap function call
$st = 'st';
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction("te{$st}", 'World', ['hello' => 'world', 123]);
// function name as an array value, should be wrapped
$a['f'] = 'test';
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction($a['f'], 'Hello world', ['hello' => 'world', 123]);
// function functions, statically resolvable name, should be wrapped once
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction(getName('test'), 'Hello world', ['hello' => 'world', 123]);
//classes
$class = 'My';
// wrap method call
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callMethod(new $class(), 'foobar');
PHP
                ,
                <<<'PHP'
<?php
// direct call to internal strlen(), should not be wrapped
strlen('World');
$f = 'test';
// dynamic call should be wrapped
$f('World');
// Encapsed string, wrap function call 
$st = 'st';
"te$st"('World', ['hello' => 'world', 123]);
// function name as an array value, should be wrapped
$a['f'] = 'test';
$a['f']('Hello world', ['hello' => 'world', 123]);
// function functions, statically resolvable name, should be wrapped once
getName('test')('Hello world', ['hello' => 'world', 123]);
//classes
$class = 'My';
// wrap method call
(new $class)->foobar();
PHP
                ,
            ],
            [
                <<<'PHP'
<?php

// allowed functions base64_encode and serialize should not be wrapped;
$str = base64_encode(serialize(['foo' => 'bar']));
// Enforce the second param to prevent PHP object injection
unserialize(base64_decode($str), array('allowed_classes' => false));
$dynamicUnserialize = 'unserialize';
\Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::callFunction($dynamicUnserialize, base64_decode($str));
PHP
                ,
                <<<'PHP'
<?php
// allowed functions base64_encode and serialize should not be wrapped;
$str = base64_encode(serialize(['foo' => 'bar']));
// Enforce the second param to prevent PHP object injection
unserialize(base64_decode($str));
$dynamicUnserialize = 'unserialize';
$dynamicUnserialize(base64_decode($str));
PHP
                ,

            ],
            [// SweetInterface interface should not be added if class already implements it
                <<<'PHP'
                <?php
                
                class Foobar implements \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface, \Baz\QuxInterface
                {
                }
                PHP,
                <<<'PHP'
                <?php
                class Foobar implements \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface, \Baz\QuxInterface
                {
                
                }
                PHP,
            ],
            [// include/require should be wrapped only once
                <<<'PHP'
                <?php
                
                $filename = 'foo/bar';
                require \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                require_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                include \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                include_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                PHP,
                <<<'PHP'
                <?php
                
                $filename = 'foo/bar';
                require \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                require_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                include \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                include_once \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield::validPath($filename);
                PHP,
            ],
            [// functions should be renamed and proxies created
                <<<'PHP'
                <?php

                function sooo_sweeet_some_custom_function()
                {
                    echo 'sweet';
                }
                function some_custom_function()
                {
                    return sooo_sweeet_some_custom_function();
                }
                PHP,
                <<<'PHP'
                <?php

                function some_custom_function()
                {
                    echo 'sweet';
                }
                PHP,
            ],
            [// do not touch already renamed functions
                <<<'PHP'
                <?php

                function sooo_sweeet_some_custom_function()
                {
                    echo 'sweet';
                }
                PHP,
                <<<'PHP'
                <?php

                function sooo_sweeet_some_custom_function()
                {
                    echo 'sweet';
                }
                PHP,
            ],
            [// do not rename proxy functions
                <<<'PHP'
                <?php

                function sooo_sweeet_some_custom_function()
                {
                    echo 'sweet';
                }
                function some_custom_function()
                {
                    return sooo_sweeet_some_custom_function();
                }
                PHP,
                <<<'PHP'
                <?php

                function sooo_sweeet_some_custom_function()
                {
                    echo 'sweet';
                }

                function some_custom_function()
                {
                    return sooo_sweeet_some_custom_function();
                }
                PHP,
            ],

        ];
    }

    /**
     * @dataProvider codeDataProvider
     * @param string $expected
     * @param string $code
     * @return void
     */
    public function testTranslate(string $expected, string $code)
    {
        $this->assertEquals(trim($expected), SweetTranslator::translate($code));
    }
}
