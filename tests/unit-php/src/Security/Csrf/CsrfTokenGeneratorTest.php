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
use Sugarcrm\Sugarcrm\Security\Csrf\CsrfTokenGenerator;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Csrf\CsrfTokenGenerator
 */
class CsrfTokenGeneratorTest extends TestCase
{
    /**
     * @covers ::generateToken
     * @covers ::setSize
     * @dataProvider providerTestGenerateToken
     */
    public function testGenerateToken($size, $token, $expected)
    {
        $csprng = $this->getCSPRNGMock(['generate']);
        $csprng->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($size), $this->equalTo(true))
            ->will($this->returnValue($token));

        $generator = new CsrfTokenGenerator($csprng);
        $generator->setSize($size);

        $this->assertEquals($expected, $generator->generateToken());
    }

    public function providerTestGenerateToken()
    {
        return [
            [
                10,
                '1234567890',
                '1234567890',
            ],
            [
                32,
                '12+/3+4/56',
                '12-_3-4_56',
            ],
        ];
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG
     */
    protected function getCSPRNGMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
