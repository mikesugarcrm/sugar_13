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

use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory;
use Sugarcrm\SugarcrmTests\Cache\Middleware\MultiTenant\KeyStorageTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory
 */
final class InMemoryTest extends KeyStorageTest
{
    protected function newInstance(): KeyStorage
    {
        return new InMemory();
    }
}
