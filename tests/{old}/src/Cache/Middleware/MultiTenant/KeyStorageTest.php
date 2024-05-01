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

namespace Sugarcrm\SugarcrmTests\Cache\Middleware\MultiTenant;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage;

/**
 * @coversNothing
 */
abstract class KeyStorageTest extends TestCase
{
    /**
     * @var KeyStorage
     */
    protected $storage;

    protected function setUp(): void
    {
        $this->storage = $this->newInstance();
    }

    abstract protected function newInstance(): KeyStorage;

    /**
     * @test
     */
    public function updateAndGet()
    {
        $key = Uuid::uuid4();
        $this->storage->updateKey($key);

        $this->assertEquals($key, $this->storage->getKey());
    }
}
