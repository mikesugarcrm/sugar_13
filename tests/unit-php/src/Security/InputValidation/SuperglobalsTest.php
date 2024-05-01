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

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SuperglobalException;
use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals
 */
class SuperglobalsTest extends TestCase
{
    /**
     * @var Superglobals
     */
    protected $globals;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Fixture $_GET values
     * @var array
     */
    protected $inputGet = [
        'batman' => 'robin',
        'superman' => ['fly' => 'away'],
    ];

    /**
     * Fixture $_POST values
     * @var array
     */
    protected $inputPost = [
        'batman' => 'catwoman',
        'green' => ['lantarn' => 'man'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->globals = new Superglobals($this->inputGet, $this->inputPost, $this->logger);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        // reset superglobals
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
    }

    /**
     * @covers ::setRawGet
     * @covers ::setRawPost
     * @covers ::getRawGet
     * @covers ::getGet
     * @covers ::getRawPost
     * @covers ::getPost
     * @covers ::getRawRequest
     * @covers ::getRequest
     * @covers ::hasRawGet
     * @covers ::hasGet
     * @covers ::hasRawPost
     * @covers ::hasPost
     * @covers ::hasRawRequest
     * @covers ::hasRequest
     */
    public function testGetPostMerge()
    {
        // start situation
        $this->assertSame($this->inputGet, TestReflection::getProtectedValue($this->globals, 'rawGet'));
        $this->assertSame($this->inputPost, TestReflection::getProtectedValue($this->globals, 'rawPost'));


        // set $_GET which is not present in $_POST and should reflect in $_REQUEST
        $this->globals->setRawGet('more', 'beer');

        $this->assertTrue($this->globals->hasRawGet('more'));
        $this->assertTrue($this->globals->hasGet('more'));
        $this->assertSame('beer', $this->globals->getRawGet('more'));
        $this->assertSame('beer', $this->globals->getGet('more'));

        $this->assertFalse($this->globals->hasRawPost('more'));
        $this->assertFalse($this->globals->hasPost('more'));
        $this->assertNull($this->globals->getRawPost('more'));
        $this->assertNull($this->globals->getPost('more'));

        $this->assertTrue($this->globals->hasRawRequest('more'));
        $this->assertTrue($this->globals->hasRequest('more'));
        $this->assertSame('beer', $this->globals->getRawRequest('more'));
        $this->assertSame('beer', $this->globals->getRequest('more'));


        // set $_POST which should overwrite the one from $_GET in $_REQUEST
        $this->globals->setRawPost('more', 'coke');
        $this->assertSame('beer', $this->globals->getRawGet('more'));
        $this->assertSame('beer', $this->globals->getGet('more'));
        $this->assertSame('coke', $this->globals->getRawPost('more'));
        $this->assertSame('coke', $this->globals->getPost('more'));
        $this->assertSame('coke', $this->globals->getRawRequest('more'));
        $this->assertSame('coke', $this->globals->getRequest('more'));

        // test defaults for unknown keys
        $this->assertSame('default', $this->globals->getRawGet('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getGet('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getRawPost('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getPost('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getRawRequest('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getRequest('doesnotexist', 'default'));
    }

    /**
     * Test compatibility layer being able to alter super globals direclty.
     * @covers ::enableCompatMode
     * @covers ::getCompatMode
     * @covers ::getCompatValue
     * @covers ::getGet
     * @covers ::getPost
     * @covers ::getRequest
     */
    public function testCompatLayer()
    {
        // Setup test data
        $_GET = [
            'foo' => 'javascript:alert("module")',
            'bar' => '<p>123-456-789</p>',
            'good' => '123-456-789',
        ];

        $_POST = [
            'sub' => 'javascript:alert("sub")',
            'pub' => [
                'javascript:alert("more")',
                'javascript:alert("beer")',
            ],
        ];

        $_REQUEST = array_merge($_GET, $_POST);

        // Instantiate superglobals for raw values
        $superglobals = new Superglobals($_GET, $_POST, $this->logger);
        $superglobals->enableCompatMode();

        $this->assertTrue($superglobals->getCompatMode());

        /*
         * Test data setup
         */

        // Get default vakue for non-existing key
        $this->assertEquals('default1', $superglobals->getGet('notexists', 'default1'));

        // We should have $_GET['foo']
        $this->assertTrue($superglobals->hasGet('foo'));

        // We expect the raw value when accessing $_GET['foo'] as it was known from the request
        $this->assertEquals('javascript:alert("module")', $superglobals->getGet('foo'));

        // The direct $_GET['foo'] value is expected to be cleaned/sanitized
        $this->assertEquals('java script:alert(&quot;module&quot;)', $_GET['foo']);


        /*
         * Manually set new superglobal
         */
        $_GET['foobar'] = 'javascript:alert("foobar")';

        // We expect the value as it has been set as the key didn't exist from the request
        $this->assertTrue($superglobals->hasGet('foobar'));
        $this->assertEquals('javascript:alert("foobar")', $superglobals->getGet('foobar'));

        // We expect a default to be returned as this key didn't exist from the request
        $this->assertEquals('default2', $superglobals->getRawGet('foobar', 'default2'));


        /*
         * Manually override existing superglobal
         */
        $_POST['sub'] = '<p>new</p>';

        // We should get non-sanitzed value here as no sanitizing happened on it
        $this->assertTrue($superglobals->hasPost('sub'));
        $this->assertEquals('<p>new</p>', $superglobals->getPost('sub'));

        // We still have the original raw value from the request
        $this->assertEquals('javascript:alert("sub")', $superglobals->getRawPost('sub'));


        /*
         * Unset previously set superglobal from request
         */
        unset($_REQUEST['bar']);

        // We expect the default to be returned
        $this->assertFalse($superglobals->hasRequest('bar'));
        $this->assertEquals('default3', $superglobals->getRequest('bar', 'default3'));

        // We still have the raw request value
        $this->assertEquals('<p>123-456-789</p>', $superglobals->getRawRequest('bar'));
    }

    /**
     * @covers ::getCompatValue
     */
    public function testRejectInvalidType()
    {
        $this->expectException(SuperglobalException::class);
        TestReflection::callProtectedMethod($this->globals, 'getCompatValue', ['XXX', 'test']);
    }
}
