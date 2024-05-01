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

use PHPUnit\Framework\MockObject\Matcher\Invocation as Matcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers ACLRole::setAction
 */
class ACLRoleSetActionTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        BeanFactory::clearCache();
    }

    public function testShouldSaveNonAdminPermissionsForUsers()
    {
        $this->setRoleAction([
            'acltype' => 'module',
            'category' => 'Users',
            'name' => 'admin',
        ], $this->once());
    }

    public function testShouldNotSaveAdminPermissionsForUsers()
    {
        $this->setRoleAction([
            'acltype' => 'module',
            'category' => 'Users',
            'name' => 'access',
        ], $this->never());
    }

    private function setRoleAction(array $params, Matcher $matcher)
    {
        $role = $this->getRole();
        $role->expects($matcher)
            ->method('set_relationship');
        $action = $this->getAction($params);
        $role->setAction($role->id, $action->id, null);
    }

    /**
     * @return ACLRole|MockObject
     */
    private function getRole()
    {
        return $this->createPartialMock('ACLRole', ['set_relationship']);
    }

    /**
     * @param array $params Action parameters
     * @return ACLAction
     */
    private function getAction(array $params)
    {
        $action = new ACLAction();
        $action->populateFromRow($params);
        $action->id = create_guid();
        BeanFactory::registerBean($action);

        return $action;
    }
}
