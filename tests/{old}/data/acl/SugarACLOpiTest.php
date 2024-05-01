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

class SugarACLOpiTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group framework
     */
    public function testCheckRecurringSource()
    {
        $bean = BeanFactory::newBean('Meetings');

        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $_SESSION['platform'] = 'base';
        $bean->recurring_source = 'Sugar';
        $bean->fetched_row['recurring_source'] = 'Sugar';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $bean->recurring_source = 'Outlook';
        $_SESSION['platform'] = 'opi';
        $bean->recurring_source = 'Outlook';
        $bean->fetched_row['recurring_source'] = 'Outlook';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $bean->recurring_source = 'Outlook';
        $_SESSION['platform'] = 'base';
        $bean->recurring_source = 'Outlook';
        $bean->fetched_row['recurring_source'] = 'Outlook';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertFalse($canEdit);

        $bean->recurring_source = 'Sugar';
        $canList = $bean->ACLAccess('list');
        $this->assertTrue($canList);

        $bean->recurring_source = 'Outlook';
        $canList = $bean->ACLAccess('list');
        $this->assertTrue($canList);

        $bean->recurring_source = 'Sugar';
        $canView = $bean->ACLAccess('view');
        $this->assertTrue($canView);

        $bean->recurring_source = 'Outlook';
        $canView = $bean->ACLAccess('view');
        $this->assertTrue($canView);

        unset($_SESSION['platform']);
        unset($bean);
    }
}
