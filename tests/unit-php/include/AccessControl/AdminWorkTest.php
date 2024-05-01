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

namespace Sugarcrm\SugarcrmTestsUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessConfigurator;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\AdminWork;

/**
 * Class AdminWorkTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\AdminWork
 */
class AdminWorkTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::endAdminWork
     * @covers ::startAdminWork
     * @covers ::reset
     */
    public function testAdminWork()
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAdmin'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));

        global $current_user;
        $current_user = $userMock;
        AccessControlManager::instance()->setAdminWork(false);

        // ctor
        $adminWork = new AdminWork();
        $adminWork->startAdminWork();
        $this->assertTrue(AccessControlManager::instance()->getAdminWork(), 'ctor: admin work is not true');
        // reset
        $adminWork->reset(true);
        $this->assertTrue(AccessControlManager::instance()->getAdminWork(), 'reset: admin work to true');
        $adminWork->reset(false);
        $this->assertFalse(AccessControlManager::instance()->getAdminWork(), 'reset: admin work to false');
        // dtor
        $adminWork = null;
        $this->assertFalse(AccessControlManager::instance()->getAdminWork(), 'dtor: does not set admin work to false');
    }

    /**
     * @covers ::__construct
     * @covers ::__destruct
     */
    public function testAdminWorkInFunction()
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAdmin'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));

        global $current_user;
        $current_user = $userMock;
        AccessControlManager::instance()->setAdminWork(false);
        $this->functionToTest();
        $this->assertFalse(AccessControlManager::instance()->getAdminWork(), 'after function call, admin work is not false');
    }

    protected function functionToTest()
    {
        // need variable $adminWork to hold AdminWork object in the scope
        $adminWork = new AdminWork();
        $adminWork->startAdminWork();
        $this->assertTrue(AccessControlManager::instance()->getAdminWork(), 'in function, admin work is not true');
    }
}
