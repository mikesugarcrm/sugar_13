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

namespace Sugarcrm\SugarcrmTestsUnit\Session;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Session\SessionStorage;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Session\SessionStorage
 */
class SessionStorageTest extends TestCase
{
    public function sessionHasIdDataProvider()
    {
        return [
            [true, 'some-id'],
            [false, null],
        ];
    }

    /**
     * @dataProvider sessionHasIdDataProvider
     * @covers ::sessionHasId
     */
    public function testSessionHasId($expected, $getId)
    {
        $session = $this->getMockBuilder(SessionStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $session->expects($this->once())
            ->method('getId')
            ->willReturn($getId);
        $this->assertEquals($expected, $session->sessionHasId());
    }
}
