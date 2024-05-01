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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface;
use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine
 */
class SearchEngineTest extends TestCase
{
    /**
     * @covers ::hasCapability
     * @dataProvider dataProviderTestHasCapability
     *
     * @param string $interface
     * @param string $capability
     * @param boolean $expected
     */
    public function testHasCapability($interface, $capability, $expected)
    {
        $engine = new SearchEngine($this->createMock($interface));
        $this->assertSame($expected, $engine->hasCapability($capability));
    }

    public function dataProviderTestHasCapability()
    {
        return [
            [
                \Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface::class,
                'DoesNotExist',
                false,
            ],
            [
                \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable::class,
                'FakeCapability',
                false,
            ],
            [
                \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable::class,
                'GlobalSearch',
                true,
            ],
        ];
    }

    /**
     * @covers ::getEngine
     * @dataProvider dataProviderTestGetEngine
     *
     * @param EngineInterface $engineObject
     */
    public function testGetEngine(EngineInterface $engineObject)
    {
        $engine = new SearchEngine($engineObject);
        $this->assertSame($engineObject, $engine->getEngine());
    }

    public function dataProviderTestGetEngine()
    {
        return [
            [$this->createMock(\Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface::class)],
            [$this->createMock(\Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable::class)],
        ];
    }

    /**
     * @covers ::newEngine
     */
    public function testNewEngineExceptions()
    {
        $this->expectException(RuntimeException::class);
        SearchEngine::newEngine('Unknown');
    }

    /**
     * @covers ::newEngine
     * @dataProvider dataProviderTestNewEngine
     *
     *
     * @param string $type
     * @param array $config
     */
    public function testNewEngine($type, array $config)
    {
        $engine = SearchEngine::newEngine($type, $config);
        $this->assertSame($config, $engine->getEngineConfig());
    }

    public function dataProviderTestNewEngine()
    {
        return [
            [
                'Elastic',
                ['foo' => 'bar', 0 => 'sweet', 'config' => true],
            ],
        ];
    }
}
