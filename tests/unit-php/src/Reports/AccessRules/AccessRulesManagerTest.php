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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\AccessRules;

use Sugarcrm\Sugarcrm\Reports\AccessRules\AccessRulesManager;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SavedReport;
use User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\AccessRules\AccessRulesManager
 */
class AccessRulesManagerTest extends TestCase
{
    /**
     * User
     */
    protected $userMock;

    /**
     * string
     */
    protected $userIdTest = 'testUser';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAdmin'])
            ->getMock();
        $this->userMock->id = $this->userIdTest;
        $this->userMock->is_admin = '1';
        $this->userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
    }

    public function testGetInstance()
    {
        $manager = AccessRulesManager::getInstance();

        $manager->setUser($this->userMock);

        $managerUser = TestReflection::getProtectedValue($manager, 'user');

        $this->assertInstanceOf(User::class, $managerUser);
        $this->assertEquals($this->userIdTest, $managerUser->id);
    }

    /**
     * validate function
     */
    public function testValidate()
    {
        $savedReportMock = $this->getMockBuilder(SavedReport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = AccessRulesManager::getInstance();
        $manager->setRules(['accessFieldsRights']);

        $manager->setUser($this->userMock);
        $validationResult = $manager->validate($savedReportMock);

        $this->assertTrue($validationResult);
    }
}
