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

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\Uuid;
use Sugarcrm\Sugarcrm\Cache\Backend\InMemory as InMemoryBackend;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory as InMemoryKeyStorage;
use Sugarcrm\SugarcrmTests\CacheTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant
 * @uses   \Sugarcrm\Sugarcrm\Cache\Backend\InMemory
 * @uses   \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory
 */
final class MultiTenantTest extends CacheTest
{
    protected function newInstance(): CacheInterface
    {
        return new MultiTenant(
            Uuid::uuid4()->toString(),
            new InMemoryKeyStorage(),
            new InMemoryBackend(),
            $this->createMock(LoggerInterface::class)
        );
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
    public function decryptionFailure()
    {
        $backend = $this->createMock(CacheInterface::class);
        $backend->expects($this->once())
            ->method('get')
            ->willReturn('garbage');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('garbage-key'));

        $middleware = new MultiTenant(
            Uuid::uuid4()->toString(),
            $this->createMock(KeyStorage::class),
            $backend,
            $logger
        );

        $middleware->get('garbage-key');
    }
}
