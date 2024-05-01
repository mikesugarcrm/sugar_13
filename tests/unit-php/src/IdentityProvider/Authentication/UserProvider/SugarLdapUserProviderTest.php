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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\UserProvider;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLdapUserProvider;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\LdapInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLdapUserProvider
 */
class SugarLdapUserProviderTest extends TestCase
{
    /**
     * @covers ::loadUser
     */
    public function testLoadUser()
    {
        $entry = new Entry('dn', [($key = 'userPrincipalName') => ['uuid']]);

        $query = $this->getMockBuilder(QueryInterface::class)->getMock();
        $query->method('execute')->willReturn([$entry]);

        $ldap = $this->getMockBuilder(LdapInterface::class)->getMock();
        $ldap->expects($this->once())->method('escape')->willReturnArgument(0);
        $ldap->expects($this->once())->method('query')->willReturn($query);

        $userProvider = new SugarLdapUserProvider($ldap, 'dn', null, null, [], $key);

        $user = $userProvider->loadUserByUsername('user1');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($entry, $user->getAttribute('entry'));
    }
}
