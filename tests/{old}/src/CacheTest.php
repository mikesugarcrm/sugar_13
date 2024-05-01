<?php

declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTests;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @coversNothing
 */
abstract class CacheTest extends TestCase
{
    /**
     * @var CacheInterface
     */
    protected $backend;

    protected function setUp(): void
    {
        try {
            $this->backend = $this->newInstance();
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->backend->clear();
    }

    abstract protected function newInstance(): CacheInterface;

    /**
     * @test
     * @dataProvider setGetAndHasProvider
     */
    public function setGetAndHas(string $key, $value)
    {
        $this->assertValueNotCached($this->backend, $key);
        $this->assertSet($this->backend, $key, $value);
        $this->assertValueCached($value, $this->backend, $key);
    }

    /**
     * @test
     * @dataProvider setGetAndHasProvider
     */
    public function setGetAndHasViaMultiple(string $key, $value)
    {
        $this->assertValueNotCached($this->backend, $key);
        $this->assertSetMultiple($this->backend, [$key => $value]);
        $this->assertValuesCached([$key => $value], $this->backend);
    }

    /**
     * @test
     */
    public function default()
    {
        $this->assertValueNotCached($this->backend, 'non-existing-key');

        $this->assertSame('phpunit', $this->backend->get('non-existing-key', 'phpunit'));
    }

    public static function setGetAndHasProvider()
    {
        return [
            'string' => [
                'string-key',
                'Hello, World!',
            ],
            'int' => [
                'int-key',
                1024,
            ],
            'array' => [
                'array-key',
                ['key' => 'value'],
            ],
            'binary' => [
                'binary-key',
                base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='),
            ],
            'false' => [
                'false-key',
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function delete()
    {
        $this->assertSet($this->backend, 'key1', 'X');
        $this->assertSet($this->backend, 'key2', 'Y');

        $this->assertDelete($this->backend, 'key1');

        $this->assertValueNotCached($this->backend, 'key1');
        $this->assertValueCached('Y', $this->backend, 'key2');
    }

    /**
     * @test
     */
    public function clear()
    {
        $this->assertSet($this->backend, 'key', 'value');
        $this->assertClear($this->backend);

        $this->assertValueNotCached($this->backend, 'key');
    }

    /**
     * @test
     */
    public function expiration()
    {
        $this->assertSet($this->backend, 'key', 'value', 1);
        usleep(1200000);

        $this->assertValueNotCached($this->backend, 'key');
    }

    /**
     * @test
     */
    public function setGetAndDeleteMultiple()
    {
        $this->assertValueNotCached($this->backend, 'a');
        $this->assertValueNotCached($this->backend, 'b');
        $this->assertValueNotCached($this->backend, 'c');

        $this->assertSetMultiple($this->backend, [
            'a' => 'foo',
            'b' => 'bar',
        ]);

        $this->assertValuesCached([
            'a' => 'foo',
            'c' => null,
        ], $this->backend);

        $this->assertDeleteMultiple($this->backend, ['a', 'd']);

        $this->assertValuesCached([
            'a' => 'phpunit',
            'b' => 'bar',
            'c' => 'phpunit',
        ], $this->backend, 'phpunit');
    }

    private function assertSet(CacheInterface $cache, string $key, $value, ?int $ttl = null): void
    {
        $this->assertTrue($cache->set($key, $value, $ttl));
    }

    private function assertSetMultiple(CacheInterface $cache, iterable $values): void
    {
        $this->assertTrue($cache->setMultiple($values));
    }

    private function assertDelete(CacheInterface $cache, string $key): void
    {
        $this->assertTrue($cache->delete($key));
    }

    private function assertDeleteMultiple(CacheInterface $cache, iterable $keys): void
    {
        $this->assertTrue($cache->deleteMultiple($keys));
    }

    private function assertClear(CacheInterface $cache): void
    {
        $this->assertTrue($cache->clear());
    }

    private function assertValueCached($expected, CacheInterface $cache, string $key): void
    {
        $this->assertEquals($expected, $cache->get($key));
    }

    private function assertValuesCached(array $expected, CacheInterface $cache, $default = null): void
    {
        $values = $cache->getMultiple(array_keys($expected), $default);

        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $values);
            $this->assertEquals($value, $values[$key]);
        }
    }

    private function assertValueNotCached(CacheInterface $cache, string $key): void
    {
        $this->assertFalse($cache->has($key));
        $this->assertNull($cache->get($key));
    }
}
