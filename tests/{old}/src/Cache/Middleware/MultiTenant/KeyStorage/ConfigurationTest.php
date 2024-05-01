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

namespace Sugarcrm\SugarcrmTests\Cache\Middleware\MultiTenant\KeyStorage;

use Ramsey\Uuid\Uuid;
use SugarConfig;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\Configuration;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\SugarcrmTests\Cache\Middleware\MultiTenant\KeyStorageTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\Configuration
 */
final class ConfigurationTest extends KeyStorageTest
{
    protected function newInstance(): KeyStorage
    {
        return new Configuration(
            Container::getInstance()->get(SugarConfig::class)
        );
    }

    /**
     * @test
     */
    public function unexpectedValueStored()
    {
        global $sugar_config;

        $key = Uuid::uuid4();
        $this->storage->updateKey($key);

        $sugar_config['cache']['encryption_key'] = 'garbage';

        $this->assertNull($this->storage->getKey());
    }

    public function testNoEncryptionKey()
    {
        $sugarConfigMock = $this->createMock(SugarConfig::class);
        $storage = new Configuration($sugarConfigMock);

        $this->assertNull($storage->getKey());
    }
}
