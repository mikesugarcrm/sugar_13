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
 * @ticket 67378
 */
class Bug67378Test extends TestCase
{
    /**
     * @var Contact
     */
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testSubpanelSortOn()
    {
        $data = [
            'name' => 'assigned_user_name',
            'link' => 'assigned_user_link',
            'vname' => 'LBL_ASSIGNED_TO',
            'rname' => 'full_name',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'users',
            'id_name' => 'assigned_user_id',
            'module' => 'Users',
            'duplicate_merge' => 'disabled',
            'massupdate' => false,
            'db_concat_field' => [
                0 => 'first_name',
                1 => 'last_name',
            ],
        ];

        $user = BeanFactory::newBean('Users');
        $relate_query = $user->getRelateFieldQuery($data, 'whatever');

        // key should exist
        $this->assertArrayHasKey('assigned_user_name', $relate_query['fields'], 'Result should have assigned_user_name key.');

        // sorting field should be correct
        $this->assertEquals('whatever.last_name', $relate_query['fields']['assigned_user_name']);

        // sorting field should be in the select array
        $this->assertStringContainsString('whatever.last_name assigned_user_name', $relate_query['select']);
    }
}
