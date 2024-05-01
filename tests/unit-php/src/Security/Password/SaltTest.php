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
use Sugarcrm\Sugarcrm\Security\Password\Salt;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Password\Salt
 */
class SaltTest extends TestCase
{
    /**
     * @covers ::generate
     * @covers ::setSubstitution
     * @covers ::substitute
     * @dataProvider providerTestGenerate
     */
    public function testGenerate($size, $gen, $subst, $expected)
    {
        // mock CSPRNG
        $csprng = $this->getCSPRNGMock(['generate']);
        $csprng->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($size), $this->equalTo(true))
            ->will($this->returnValue($gen));

        $salt = new Salt($csprng);
        $salt->setSubstitution($subst);
        $this->assertEquals($expected, $salt->generate($size));
    }

    public function providerTestGenerate()
    {
        return [
            [
                9,
                'random123',
                '',
                'random123',
            ],
            [
                10,
                'ABBCCCDDDD',
                'XYZ',
                'XYYZZZDDDD',
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
