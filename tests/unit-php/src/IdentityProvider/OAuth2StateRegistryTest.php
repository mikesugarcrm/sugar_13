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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\OAuth2StateRegistry;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\OAuth2StateRegistry
 */
class OAuth2StateRegistryTest extends TestCase
{
    /**
     * @var OAuth2StateRegistry
     */
    protected $stateRegistry;

    /**
     * @var string
     */
    protected $state = 'state';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->stateRegistry = new OAuth2StateRegistry();
        parent::setUp();
    }

    /**
     * @covers ::registerState()
     * @runInSeparateProcess
     */
    public function testRegisterState(): void
    {
        $this->stateRegistry->registerState($this->state);
        $this->assertTrue($_SESSION[$this->state]);
    }

    /**
     * @covers ::isStateRegistered()
     * @runInSeparateProcess
     */
    public function testIsStateRegisteredButStateIsNotRegistered(): void
    {
        $this->assertFalse($this->stateRegistry->isStateRegistered($this->state));
    }

    /**
     * @covers ::isStateRegistered()
     * @runInSeparateProcess
     */
    public function testIsStateRegisteredStateIsRegistered(): void
    {
        $this->stateRegistry->registerState($this->state);
        $this->assertTrue($this->stateRegistry->isStateRegistered($this->state));
    }

    /**
     * @covers ::unregisterState()
     * @runInSeparateProcess
     */
    public function testUnregisterState(): void
    {
        $this->stateRegistry->registerState($this->state);
        $this->stateRegistry->unregisterState($this->state);
        $this->assertArrayNotHasKey($this->state, $_SESSION);
    }
}
