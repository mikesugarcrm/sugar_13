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

namespace Sugarcrm\SugarcrmTests\Cache\Backend;

use Psr\SimpleCache\CacheInterface;
use Sugarcrm\Sugarcrm\Cache\Backend\InMemory;
use Sugarcrm\SugarcrmTests\CacheTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Backend\InMemory
 */
final class InMemoryTest extends CacheTest
{
    protected function newInstance(): CacheInterface
    {
        return new InMemory();
    }

    /**
     * @test
     */
    public function expiration()
    {
        $this->markTestSkipped('Expiration is not supported by in-memory backend by design');
    }
}
