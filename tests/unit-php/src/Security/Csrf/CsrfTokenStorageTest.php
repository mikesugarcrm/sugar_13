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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Csrf;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Csrf\CsrfTokenStorage;
use Sugarcrm\Sugarcrm\Session\SessionStorage;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Csrf\CsrfTokenStorage
 */
class CsrfTokenStorageTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        unset($_SESSION);
    }

    /**
     * @covers ::getToken
     * @covers ::setToken
     * @covers ::hasToken
     * @covers ::removeToken
     * @dataProvider providerTestTokenStorage
     */
    public function testTokenStorage($tokenId, $token)
    {
        $storage = new CsrfTokenStorage(new SessionStorage());

        // no token should be found here
        $this->assertFalse($storage->hasToken($tokenId));

        // add token
        $storage->setToken($tokenId, $token);
        $this->assertTrue($storage->hasToken($tokenId));
        $this->assertSame($token, $storage->getToken($tokenId));

        // remove token
        $storage->removeToken($tokenId);
        $this->assertFalse($storage->hasToken($tokenId));

        // try to remove it again
        $storage->removeToken($tokenId);
        $this->assertFalse($storage->hasToken($tokenId));
    }

    public function providerTestTokenStorage()
    {
        return [
            [
                'csrf_tokens',
                '1234567890',
            ],
            [
                'randomstuff',
                '0987654321',
            ],
        ];
    }

    /**
     * @covers ::getToken
     */
    public function testGetTokenException()
    {
        $storage = new CsrfTokenStorage(new SessionStorage());

        $this->expectException(TokenNotFoundException::class);
        $storage->getToken('foobar');
    }
}
