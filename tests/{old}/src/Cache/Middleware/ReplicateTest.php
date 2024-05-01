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

namespace Sugarcrm\SugarcrmTests\Cache\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\SimpleCache\CacheInterface;
use Sugarcrm\Sugarcrm\Cache\Backend\InMemory;
use Sugarcrm\Sugarcrm\Cache\Middleware\Replicate;
use Sugarcrm\SugarcrmTests\CacheTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\Replicate
 */
final class ReplicateTest extends CacheTest
{
    protected function newInstance(): CacheInterface
    {
        return $this->createMiddleware(new InMemory(), new InMemory());
    }

    /**
     * @test
     */
    public function expiration()
    {
        $this->markTestSkipped('Cannot test expiration since the underlying in-memory backend does not support it');
    }

    /**
     * @test
     */
    public function fetchSuccessFromReplicaDoesNotHitSource()
    {
        $replica = $this->createBackend();
        $this->set($replica, 'value');

        $source = $this->createBackend();
        $source->expects($this->never())
            ->method('get');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertSame('value', $middleware->get('key'));
    }

    /**
     * @test
     */
    public function fetchSuccessFromSourceIsReplicated()
    {
        $source = $this->createBackend();
        $this->set($source, 'value');

        $replica = $this->createBackend();
        $replica->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($_, $default) {
                return $default;
            });
        $replica->expects($this->once())
            ->method('set');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertSame('value', $middleware->get('key'));
    }

    /**
     * @test
     */
    public function fetchFailure()
    {
        $source = $this->createBackend();
        $source->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($_, $default) {
                return $default;
            });
        $source->expects($this->once())
            ->method('get');

        $replica = $this->createBackend();
        $replica->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($_, $default) {
                return $default;
            });
        $replica->expects($this->once())
            ->method('get');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertSame('xyz', $middleware->get('key', 'xyz'));
    }

    /**
     * @test
     */
    public function setIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectSet($source, 'key', 'value', 120);

        $replica = $this->createBackend();
        $this->expectSet($replica, 'key', 'value', 120);

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->set('key', 'value', 120);
    }

    /**
     * @test
     */
    public function deleteIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectDelete($source, 'key');

        $replica = $this->createBackend();
        $this->expectDelete($replica, 'key');

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->delete('key');
    }

    /**
     * @test
     */
    public function clearIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectClear($source);

        $replica = $this->createBackend();
        $this->expectClear($replica);

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->clear();
    }

    private function createMiddleware(CacheInterface $source, CacheInterface $replica): Replicate
    {
        return new Replicate($source, $replica);
    }

    /**
     * @return CacheInterface|MockObject
     */
    private function createBackend(): CacheInterface
    {
        return $this->createMock(CacheInterface::class);
    }

    private function set(CacheInterface $backend, $value): void
    {
        $backend->expects($this->once())
            ->method('get')
            ->willReturn($value);
    }

    private function expectSet(CacheInterface $backend, string $key, $value, int $ttl): void
    {
        $backend->expects($this->once())
            ->method('set')
            ->with($key, $value, $ttl)
            ->willReturn(true);
    }

    private function expectDelete(CacheInterface $backend, string $key): void
    {
        $backend->expects($this->once())
            ->method('delete')
            ->with($key)
            ->willReturn(true);
    }

    private function expectClear(CacheInterface $backend): void
    {
        $backend->expects($this->once())
            ->method('clear')
            ->willReturn(true);
    }
}
