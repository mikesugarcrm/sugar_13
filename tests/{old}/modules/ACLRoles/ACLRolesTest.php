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

/**
 * Class ACLRolesTest
 *
 * Class containing tests for ACLRole bean.
 *
 * @coversDefaultClass ACLRole
 */
class ACLRolesTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var ACLRole
     */
    private $role;

    /**
     * @var AclCache
     */
    private $cache;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('timedate');

        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->role = SugarTestACLUtilities::createRole('test-role', ['Accounts'], ['access']);
        $this->role->load_relationship('users');
        $this->role->users->add($this->user->id);
        $this->role->save();

        // Some manipulation to set user date_modified to some date in the past.
        $this->user->setModifiedDate(TimeDate::getInstance()->asDb(new SugarDateTime('2016-01-01')));
        $oldUpdateDateModified = $this->user->update_date_modified;
        $this->user->update_date_modified = false;
        $this->user->save();
        $this->user->update_date_modified = $oldUpdateDateModified;

        $this->cache = Container::getInstance()->get(AclCacheInterface::class);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestACLUtilities::tearDown();
        if ($this->cache) {
            $this->cache->clearAll();
        }
    }

    /**
     * Test to check that when we unlink user from some role, user hash is updated.
     */
    public function testUserHashChangedWhenUserUnlinked()
    {
        $oldUserMDHash = $this->user->getUserMDHash();
        $this->cache->store($this->user->id, 'test', ['x']);

        $this->role->load_relationship('users');
        $this->role->users->delete($this->role->id, $this->user->id);

        $value = $this->cache->retrieve($this->user->id, 'test');
        $this->assertNull($value, 'The cached ACL data for user should be cleared');

        $this->user->retrieve();
        $this->assertNotEquals($oldUserMDHash, $this->user->getUserMDHash());
    }

    /**
     * Test to check that when we add user to a role, cached acl data is cleared.
     */
    public function testUserAclCacheClearedWhenUserlinked()
    {
        $this->role->load_relationship('users');
        $this->role->users->delete($this->role->id, $this->user->id);
        $this->cache->store($this->user->id, 'test', ['x']);
        $this->role->users->add($this->user->id);
        $this->role->save();
        $value = $this->cache->retrieve($this->user->id, 'test');
        $this->assertNull($value, 'The cached ACL data for user should be cleared');
    }

    public function testRoleUsersAreModifiedWhenRoleIsSaved()
    {
        global $timedate;
        global $disable_date_format;

        $disable_date_format = true;

        $timedate->setNow($timedate->fromString('2017-01-01 00:00:00'));
        $this->role->updateUsersACLInfo();

        $this->user->retrieve();
        $this->assertEquals('2017-01-01 00:00:00', $this->user->date_modified);
    }

    /**
     * @covers ::mark_deleted
     */
    public function testUserAclCacheClearedWhenRoleDeleted()
    {
        $this->cache->store($this->user->id, 'test', ['x']);

        $this->assertNotNull($this->cache->retrieve($this->user->id, 'test'));
        $this->role->mark_deleted($this->role->id);
        $this->assertNull($this->cache->retrieve($this->user->id, 'test'));
    }

    /**
     * @covers ::mark_deleted
     */
    public function testRoleUsersAreModifiedWhenRoleIsDeleted()
    {
        $oldDateModified = $this->user->date_modified;

        $this->role->mark_deleted($this->role->id);

        $this->user->retrieve();
        $this->assertNotEquals($oldDateModified, $this->user->date_modified);
    }
}
