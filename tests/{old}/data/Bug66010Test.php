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

/**
 * @ticket 66010
 */
class Bug66010Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::newBean('Accounts');
        $query = $bean->create_new_list_query('', '', ['create_by_name', 'modified_by_name'], [], 0, '', true);
        $this->assertEquals(1, substr_count($query['select'], 'accounts.created_by'));
        $this->assertEquals(1, substr_count($query['select'], 'accounts.modified_user_id'));
        $query = $bean->create_new_list_query('', '', [], [], 0, '', true);
        $this->assertEquals(0, substr_count($query['select'], 'accounts.modified_user_id'));
        $query = $bean->create_new_list_query('', '', ['modified_by_name', 'modified_user_id'], [], 0, '', true);
        $this->assertEquals(1, substr_count($query['select'], 'accounts.modified_user_id'));
    }
}
