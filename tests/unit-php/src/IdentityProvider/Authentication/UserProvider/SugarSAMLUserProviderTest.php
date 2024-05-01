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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarSAMLUserProvider;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarSAMLUserProvider
 */
class SugarSAMLUserProviderTest extends TestCase
{
    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsername(): void
    {
        $samlUserProvider = new SugarSAMLUserProvider();
        $user = $samlUserProvider->loadUserByUsername('test-name');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test-name', $user->getUsername());
    }

    /**
     * @covers ::loadUserByIdentifier
     */
    public function testLoadUserByIdentifier(): void
    {
        $samlUserProvider = new SugarSAMLUserProvider();
        $user = $samlUserProvider->loadUserByIdentifier('test-name');
        $this->assertEquals('test-name', $user->getUsername());
    }
}
