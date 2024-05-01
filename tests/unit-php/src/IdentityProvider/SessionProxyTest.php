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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider;

use LogicException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\SessionProxy;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\SessionProxy
 */
class SessionProxyTest extends TestCase
{
    /**
     * @var null|array
     */
    protected $oldSession = null;

    /**
     * @var SessionProxy
     */
    protected $sessionProxy;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @var string
     */
    protected $sessionValue;

    /**
     * @covers ::start
     */
    public function testStart()
    {
        $this->assertTrue($this->sessionProxy->start());
    }

    /**
     * @covers ::getId
     */
    public function testGetId()
    {
        $this->assertEquals(session_id(), $this->sessionProxy->getId());
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertEquals(session_name(), $this->sessionProxy->getName());
    }

    /**
     * @covers ::isStarted
     */
    public function testIsStarted()
    {
        $this->assertTrue($this->sessionProxy->isStarted());
    }

    /**
     * Testing unsupported methods.
     * @dataProvider unsupportedMethodsProvider
     * @param $methods
     * @param $arguments
     *
     * @covers ::setId
     * @covers ::setName
     * @covers ::invalidate
     * @covers ::migrate
     * @covers ::getBag
     * @covers ::getMetadataBag
     * @covers ::registerBag
     */
    public function testUnsupportedMethods($methods, $arguments)
    {
        $this->expectException(LogicException::class);
        call_user_func_array([$this->sessionProxy, $methods], $arguments);
    }

    /**
     * DataProvider for testUnsupportedMethods.
     * @return array
     * @see testUnsupportedMethods
     */
    public function unsupportedMethodsProvider()
    {
        return [
            'setId' => ['methods' => 'setId', 'arguments' => ['someId']],
            'setName' => ['methods' => 'setName', 'arguments' => ['someName']],
            'invalidate' => ['methods' => 'invalidate', 'arguments' => []],
            'migrate' => ['methods' => 'migrate', 'arguments' => []],
            'getBag' => ['methods' => 'getBag', 'arguments' => ['someBagName']],
            'getMetadataBag' => ['methods' => 'getMetadataBag', 'arguments' => []],
            'registerBag' => [
                'methods' => 'registerBag',
                'arguments' => [$this->createMock(SessionBagInterface::class)],
            ],
        ];
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $this->sessionProxy->set($this->sessionKey, $this->sessionValue);

        $this->assertArrayHasKey($this->sessionKey, $_SESSION);
        $this->assertEquals($this->sessionValue, $_SESSION[$this->sessionKey]);
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
        $_SESSION[$this->sessionKey] = $this->sessionValue;

        $this->assertTrue($this->sessionProxy->has($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {

        $_SESSION[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionValue, $this->sessionProxy->get($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGetUndefined()
    {
        unset($_SESSION[$this->sessionKey]);

        $this->assertEquals($this->sessionValue, $this->sessionProxy->get($this->sessionKey, $this->sessionValue));
    }

    /**
     * @covers ::all
     */
    public function testAll()
    {
        $_SESSION[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($_SESSION, $this->sessionProxy->all());
    }


    /**
     * @covers ::remove
     */
    public function testRemove()
    {

        $_SESSION[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionValue, $this->sessionProxy->remove($this->sessionKey));
        $this->assertArrayNotHasKey($this->sessionKey, $_SESSION);
    }

    /**
     * @covers ::remove
     */
    public function testRemoveUndefined()
    {
        unset($_SESSION[$this->sessionKey]);

        $this->assertNull($this->sessionProxy->remove($this->sessionKey));
    }

    /**
     * @covers ::replace
     */
    public function testReplace()
    {
        $keys = [
            'sessionKey' . random_int(1000, 9999),
            'sessionKey' . random_int(1000, 9999),
            'sessionKey' . random_int(1000, 9999),
            $this->sessionKey,
        ];
        $values = [
            'sessionValue' . random_int(1000, 9999),
            'sessionValue' . random_int(1000, 9999),
            'sessionValue' . random_int(1000, 9999),
            'sessionReplacedValue' . random_int(1000, 9999),
        ];

        $replacement = array_combine($keys, $values);

        $_SESSION[$this->sessionKey] = $this->sessionValue;

        $this->sessionProxy->replace($replacement);

        foreach ($replacement as $key => $value) {
            $this->assertArrayHasKey($key, $_SESSION);
            $this->assertEquals($value, $_SESSION[$key]);
        }
    }

    /**
     * Testing dummies methods.
     * @dataProvider dummiesProvider
     * @param $methods
     * @covers ::save
     * @covers ::clear
     */
    public function testDummies($methods)
    {
        $this->assertEmpty($this->sessionProxy->$methods());
    }

    /**
     * DataProvider for testUnsupportedMethods.
     * @return array
     * @see testDummies
     */
    public function dummiesProvider()
    {
        return [
            'save' => ['methods' => 'save'],
            'clear' => ['methods' => 'clear'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (isset($_SESSION)) {
            $this->oldSession = $_SESSION;
        } else {
            $this->oldSession = null;
        }
        $_SESSION = [];
        $this->sessionProxy = new SessionProxy();
        $this->sessionKey = 'sessionKey' . random_int(1000, 9999);
        $this->sessionValue = 'sessionValue' . random_int(1000, 9999);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if (is_null($this->oldSession)) {
            unset($_SESSION);
        } else {
            $_SESSION = $this->oldSession;
        }
    }
}
