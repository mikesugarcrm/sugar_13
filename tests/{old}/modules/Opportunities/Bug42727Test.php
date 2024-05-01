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

use PHPUnit\Framework\TestCase;

class Bug42727Test extends TestCase
{
    private $opportunity;
    private $opportunityIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->opportunity = $this->getMockBuilder('Opportunity')
            ->setMethods(['send_assignment_notifications'])
            ->getMock();
        $this->opportunity->mailWasSent = false;
        $this->opportunity->notify_inworkflow = true;
        $this->opportunity->set_created_by = false;
        $this->opportunity->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $this->opportunityIds) . '\')');
        SugarTestHelper::tearDown();
    }


    public function testSentMail()
    {
        $this->opportunity->created_by = $this->opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->opportunity->expects($this->never())
            ->method('send_assignment_notifications');
        $this->opportunityIds[] = $this->opportunity->save();
        $this->assertTrue($this->opportunity->isOwner($this->opportunity->created_by));
    }

    public function testNotSentMail()
    {
        $this->opportunity->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->opportunity->expects($this->once())
            ->method('send_assignment_notifications');
        $this->opportunityIds[] = $this->opportunity->save(true);
        $this->assertFalse($this->opportunity->isOwner($this->opportunity->created_by));
    }
}
