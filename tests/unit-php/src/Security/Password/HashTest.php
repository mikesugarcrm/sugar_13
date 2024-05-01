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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Password;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Password\Backend\Native;
use Sugarcrm\Sugarcrm\Security\Password\Hash;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Password\Hash
 */
class HashTest extends TestCase
{
    /**
     * @covers ::verify
     * @covers ::verifyMd5
     * @dataProvider providerTestVerify
     */
    public function testVerify($password, $hash, $expected)
    {
        $backend = $this->createMock(\Sugarcrm\Sugarcrm\Security\Password\BackendInterface::class);
        $backend->expects($this->any())
            ->method('verify')
            ->with($this->equalTo(md5($password)), $this->equalTo($hash))
            ->will($this->returnValue(null));

        $sut = new Hash($backend);

        $this->assertSame($expected, $sut->verify($password, $hash));
    }

    public function providerTestVerify()
    {
        return [
            // valid md5 hash and matching password, but not allowed
            [
                'passwordgoeshere',
                '061ed5c2fdbe73d1420ec470f2c3e210',
                null,
            ],
        ];
    }

    /**
     * @covers ::verifyMd5
     * @dataProvider providerTestVerifyMd5
     */
    public function testVerifyMd5($passwordHash, $hash, $backendReturn, $expected)
    {
        $backend = $this->createMock(\Sugarcrm\Sugarcrm\Security\Password\BackendInterface::class);
        $backend->expects($this->any())
            ->method('verify')
            ->with($this->equalTo(strtolower($passwordHash)), $this->equalTo($hash))
            ->will($this->returnValue($backendReturn));

        $sut = new Hash($backend);

        $this->assertSame($expected, $sut->verifyMd5($passwordHash, $hash));
    }

    public function providerTestVerifyMd5()
    {
        return [
            [
                '061ed5c2fdbe73d1420ec470f2c3e210',
                '061ed5c2fdbe73d1420ec470f2c3e210',
                true,
                true,
            ],
            // valid md5 hash and matching password hash in uppercase, but not allowed, hits backend
            [
                strtoupper('061ed5c2fdbe73d1420ec470f2c3e210'),
                '061ed5c2fdbe73d1420ec470f2c3e210',
                true,
                true,
            ],
        ];
    }

    /**
     * Test old hash validation which might be in use on older systems
     * leveraging the native backend.
     *
     * @coversNothing
     * @dataProvider providerTestOldHashes
     */
    public function testOldHashes($algo, $password, $hash, $expected)
    {
        if (!$this->isPlatformSupportedAlgo($algo)) {
            $this->markTestSkipped("Current platform does not support hashing algorithm $algo");
        }

        $sut = new Hash(new Native());
        $this->assertEquals($expected, $sut->verify($password, $hash));
    }

    public function providerTestOldHashes()
    {
        return [
            // CRYPT_MD5 hash - valid
            [
                'CRYPT_MD5',
                'my passw0rd',
                '$1$F0l3iEs7$sT3th960AcuSzp9kiSmxh/',
                true,
            ],
            // CRYPT_MD5 hash - invalid
            [
                'CRYPT_MD5',
                'my passw1rd',
                '$1$F0l3iEs7$sT3th960AcuSzp9kiSmxh/',
                false,
            ],
            // CRYPT_EXT_DES hash - valid
            [
                'CRYPT_EXT_DES',
                'my passw0rd',
                '_.012saltIO.319ikKPU',
                true,
            ],
            // CRYPT_EXT_DES hash - invalid
            [
                'CRYPT_EXT_DES',
                'my passw1rd',
                '_.012saltIO.319ikKPU',
                false,
            ],
            // CRYPT_BLOWFISH hash, old type - valid
            [
                'CRYPT_BLOWFISH',
                'my passw0rd',
                '$2a$07$usesomesillystringforeETvnK0/TgBVIVHViQjGDve4qlnRzeWS',
                true,
            ],
            // CRYPT_BLOWFISH hash, old type - invalid
            [
                'CRYPT_BLOWFISH',
                'my passw1rd',
                '$2a$07$usesomesillystringforeETvnK0/TgBVIVHViQjGDve4qlnRzeWS',
                false,
            ],
        ];
    }

    /**
     * Verify if given hashing algorithm is supported
     * @param string $algo
     * @return boolean
     */
    protected function isPlatformSupportedAlgo($algo)
    {
        return defined($algo) && constant($algo);
    }
}
