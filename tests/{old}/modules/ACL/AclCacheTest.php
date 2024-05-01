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
use Sugarcrm\Sugarcrm\ACL\Cache as AclCacheInterface;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class AclCacheTest extends TestCase
{
    /** @var AclCache */
    private $cache;

    protected function setUp(): void
    {
        $this->cache = Container::getInstance()->get(AclCacheInterface::class);
        $this->cache->clearAll();
    }

    protected function tearDown(): void
    {
        if ($this->cache) {
            $this->cache->clearAll();
        }
    }

    /**
     * @ticket BR-2747
     */
    public function testUpdate()
    {
        $this->cache->store('user_1', 'test', ['x']);
        $data = $this->cache->retrieve('user_1', 'test');
        $this->assertEquals(['x'], $data);

        $this->cache->store('user_2', 'test', ['x']);
        $data = $this->cache->retrieve('user_2', 'test');
        $this->assertEquals(['x'], $data);

        $this->cache->store('user_1', 'test', ['y']);
        $data = $this->cache->retrieve('user_1', 'test');
        $this->assertEquals(['y'], $data);

        $data = $this->cache->retrieve('user_2', 'test');
        $this->assertEquals(['x'], $data, 'The cached ACL data for user #2 should have remained unchanged');
    }

    public function testClear()
    {
        $this->cache->store('user', 'test', ['x']);
        $this->cache->clearAll();
        $value = $this->cache->retrieve('user', 'test');
        $this->assertNull($value);
        $this->cache->store('user', 'test1', ['x1']);
        $this->cache->store('user', 'test2', ['x2']);
        $this->cache->clearByUser('user');
        $value = $this->cache->retrieve('user', 'test1');
        $this->assertNull($value, 'The cached ACL data for user should be cleared');
        $value = $this->cache->retrieve('user', 'test2');
        $this->assertNull($value, 'The cached ACL data for user should be cleared');
    }
}
