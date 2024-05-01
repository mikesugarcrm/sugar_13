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
 * Bug #RS-219
 * @description Fixes Oracle error ORA-00918 while creating new list query.
 *
 * @ticket RS219
 */
class RS219Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true]);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests SugarBean::create_new_list_query for valid count of fields.
     *
     * @param {string} $beanName
     * @param {array} $filter
     * @param {string} $needed
     *
     * @group RS219
     * @dataProvider getTestsData
     */
    public function testCreateNewListQuery($beanName, $filter, $needed)
    {
        $bean = BeanFactory::newBean($beanName);
        $query = $bean->create_new_list_query('', '', $filter, [], 0, '', true);
        $this->assertEquals(substr_count($query['select'], $needed), 1);
    }

    public function getTestsData()
    {
        return [
            /**
             * Need 2 relate fields with same link.
             * Relationship's `rel_key` value should be named like one of selected field.
             */
            [
                'Contacts',
                ['account_name', 'account_id'],
                'account_id',
            ],
            [
                'Contacts',
                ['account_name'],
                'account_id',
            ],
            [
                'Contacts',
                ['account_id'],
                'account_id',
            ],
            /**
             * Need 2 relate fields with same link.
             * One of them need to have another one in `additional_fields`.
             */
            [
                'Leads',
                ['campaign_name', 'campaign_id'],
                'campaign_id',
            ],
            [
                'Leads',
                ['campaign_name'],
                'campaign_id',
            ],
            [
                'Leads',
                ['campaign_id'],
                'campaign_id',
            ],
        ];
    }
}
