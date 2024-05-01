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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Token\OIDC;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\RefreshToken;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\RefreshToken
 */
class RefreshTokenTest extends TestCase
{
    /**
     * @covers ::getCredentials
     */
    public function testGetCredentials(): void
    {
        $token = new RefreshToken('test');
        $this->assertEquals('test', $token->getCredentials());
    }
}
