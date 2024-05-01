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
use Sugarcrm\Sugarcrm\Cache\Backend\APCu;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\SugarcrmTests\CacheTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Backend\APCu
 */
final class APCuTest extends CacheTest
{
    protected function newInstance(): CacheInterface
    {
        return Container::getInstance()->get(APCu::class);
    }

    /**
     * @test
     * @see http://php.net/manual/en/function.apcu-store.php#refsect1-function.apcu-store-parameters
     */
    public function expiration()
    {
        $this->markTestSkipped('Cannot test APCu expiration since the value is cleaned on the next request.');
    }
}
