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

class SugarConfigTest extends TestCase
{
    /**
     * @var string|mixed
     */
    //@codingStandardsIgnoreStart
    public $_random;
    //@codingStandardsIgnoreEnd

    private $oldSugarConfig;

    protected function setUp(): void
    {
        $this->oldSugarConfig = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = [];
    }

    protected function tearDown(): void
    {
        $config = SugarConfig::getInstance();
        $config->clearCache();
        $GLOBALS['sugar_config'] = $this->oldSugarConfig;
    }

    public function defaultConfigValuesProvider()
    {
        return [
            [
                'key' => 'preview_edit',
                'value' => true,
            ],
            [
                'key' => 'cache_dir',
                'value' => 'cache/',
            ],
        ];
    }

    /**
     * Tests OOTB config values
     * @param string $key
     * @param mixed $value
     * @dataProvider defaultConfigValuesProvider
     */
    public function testDefaultConfigValues($key, $value)
    {
        $this->assertArrayHasKey($key, $this->oldSugarConfig);
        $this->assertSame($this->oldSugarConfig[$key], $value);
    }

    /**
     * Stores a key/value pair in the config
     *
     * @param string $key
     * @param string $value
     * @internal override this in sub-classes if you are testing with the
     *           config data stored somewhere other than the $sugar_config
     *           super global
     */
    private function addKeyValueToConfig(
        $key,
        $value
    ) {

        $GLOBALS['sugar_config'][$key] = $value;
    }

    private function generateRandomValue()
    {
        $this->_random = 'Some Random Foobar: ' . random_int(10000, 20000);
        return $this->getLastRandomValue();
    }

    private function getLastRandomValue()
    {
        return $this->_random;
    }

    public function testGetInstanceReturnsASugarConfigObject()
    {
        $this->assertTrue(SugarConfig::getInstance() instanceof SugarConfig, 'Returned object is not a SugarConfig object');
    }

    public function testGetInstanceReturnsASingleton()
    {
        $one = SugarConfig::getInstance();
        $two = SugarConfig::getInstance();
        $this->assertSame($one, $two);
    }

    public function testReadsGlobalSugarConfigArray()
    {
        $rawConfigArray = [];
        for ($i = 0; $i < 10; $i++) {
            $anonymous_key = 'key-' . $i;
            $random_value = random_int(10000, 20000);
            $rawConfigArray[$anonymous_key] = $random_value;
            $this->addKeyValueToConfig($anonymous_key, $random_value);
        }

        $config = SugarConfig::getInstance();
        foreach ($rawConfigArray as $key => $value) {
            $this->assertEquals(
                $config->get($key),
                $value,
                "SugarConfig::get({$key}) should be equal to {$value}, got " . $config->get($key)
            );
        }
    }

    public function testAllowDotNotationForSubValuesWithinTheConfig()
    {
        $random_value = 'Some Random Integer: ' . random_int(1000, 2000);
        $this->addKeyValueToConfig('grandparent', [
            'parent' => [
                'child' => $random_value,
            ],
        ]);

        $config = SugarConfig::getInstance();
        $this->assertEquals($random_value, $config->get('grandparent.parent.child'));
    }

    public function testReturnsNullOnUnknownKey()
    {
        $config = SugarConfig::getInstance();
        $this->assertNull($config->get('unknown-and-unknowable'));
    }

    public function testReturnsNullOnUnknownKeyWithinAHeirarchy()
    {
        $this->addKeyValueToConfig('grandparent', [
            'parent' => [
                'child' => 'foobar',
            ],
        ]);
        $config = SugarConfig::getInstance();

        $this->assertNull($config->get('some-unknown-grandparent.parent.child'));
        $this->assertNull($config->get('grandparent.some-unknown-parent.child'));
        $this->assertNull($config->get('grandparent.parent.some-unknown-child'));
    }

    public function testAllowSpecifyingDefault()
    {
        $config = SugarConfig::getInstance();

        $random = random_int(10000, 20000);
        $this->assertSame($random, $config->get('unknown-and-unknowable', $random));
    }

    public function testAllowSpecifyingDefaultForSubValues()
    {
        $this->addKeyValueToConfig('grandparent', [
            'parent' => [
                'child' => 'foobar',
            ],
        ]);
        $config = SugarConfig::getInstance();

        $this->assertEquals(
            $this->generateRandomValue(),
            $config->get(
                'some-unknown-grandparent.parent.child',
                $this->getLastRandomValue()
            )
        );
        $this->assertEquals(
            $this->generateRandomValue(),
            $config->get(
                'grandparent.some-unknown-parent.child',
                $this->getLastRandomValue()
            )
        );
        $this->assertEquals(
            $this->generateRandomValue(),
            $config->get(
                'grandparent.parent.some-unknown-child',
                $this->getLastRandomValue()
            )
        );
    }

    public function testStoresValuesInMemoryAfterFirstLookup()
    {
        $this->addKeyValueToConfig('foobar', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo');

        $this->addKeyValueToConfig('foobar', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should still be equal "barfoo": got ' . $config->get('foobar'));
    }

    public function testCanClearsCachedValues()
    {
        $this->addKeyValueToConfig('foobar', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check');
        $this->addKeyValueToConfig('foobar', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check');

        $config->clearCache();
        $this->assertEquals($config->get('foobar'), 'foobar', 'after clearCache() call, new value should be used');
    }

    public function testCanCherryPickKeyToClear()
    {
        $this->addKeyValueToConfig('foobar', 'barfoo');
        $this->addKeyValueToConfig('barfoo', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check, got: ' . $config->get('foobar'));
        $this->assertEquals($config->get('barfoo'), 'barfoo', 'sanity check');

        $this->addKeyValueToConfig('foobar', 'foobar');
        $this->addKeyValueToConfig('barfoo', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should still be equal to "barfoo", got: ' . $config->get('barfoo'));
        $this->assertEquals($config->get('barfoo'), 'barfoo', 'should still be equal to "barfoo", got: ' . $config->get('barfoo'));

        $config->clearCache('barfoo');
        $this->assertEquals($config->get('barfoo'), 'foobar', 'should be equal to "foobar" after cherry picked for clearing');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should not be effected by cherry picked clearCache() call');
    }

    public function testDemonstrateGrabbingSiblingNodes()
    {
        $this->addKeyValueToConfig('foobar', [
            'foo' => [
                [
                    'first' => 'one',
                ],
                [
                    'first' => 'uno',
                ],
            ],
        ]);

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar.foo.0.first'), 'one');
        $this->assertEquals($config->get('foobar.foo.1.first'), 'uno');
    }
}
