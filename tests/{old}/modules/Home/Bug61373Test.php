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

require_once 'modules/ACLFields/actiondefs.php';

/**
 * Bug #61373
 * QuickSearch doesn't have field level ACL checks
 *
 * @ticket 61373
 */
class Bug61373Test extends TestCase
{
    /**
     * @var ACLRoles
     */
    protected $role = null;

    /**
     * @var SugarBean
     */
    protected $bean = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->role = BeanFactory::newBean('ACLRoles');
        $this->role->name = 'bug61373role';
        $this->role->description = 'Temp Role';
        $this->role->save();

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);
    }

    protected function tearDown(): void
    {
        $this->role->mark_deleted($this->role->id);

        // Remove all of the modules from data provider
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testQuickSearchACLFields()
     */
    public static function dataProvider()
    {
        $createAccount = function () {
            return SugarTestAccountUtilities::createAccount(null, [
                'assigned_user_id' => null,
            ]);
        };

        return [
            'contacts-email-owner' => [
                'Contacts',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                [
                    'SugarTestContactUtilities',
                    'createContact',
                ],
            ],
            'accounts-email-owner' => [
                'Accounts',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                $createAccount,
            ],
            'leads-email-owner' => [
                'Leads',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                [
                    'SugarTestLeadUtilities',
                    'createLead',
                ],
            ],
            'contacts-email-default' => [
                'Contacts',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                [
                    'SugarTestContactUtilities',
                    'createContact',
                ],
            ],
            'accounts-email-default' => [
                'Accounts',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                $createAccount,
            ],
            'leads-email-default' => [
                'Leads',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                [
                    'SugarTestLeadUtilities',
                    'createLead',
                ],
            ],
            'contacts-description-owner' => [
                'Contacts',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                [
                    'SugarTestContactUtilities',
                    'createContact',
                ],
            ],
            'accounts-description-owner' => [
                'Accounts',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                $createAccount,
            ],
            'leads-description-owner' => [
                'Leads',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                [
                    'SugarTestLeadUtilities',
                    'createLead',
                ],
            ],
            'contacts-description-default' => [
                'Contacts',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                [
                    'SugarTestContactUtilities',
                    'createContact',
                ],
            ],
            'accounts-description-default' => [
                'Accounts',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                $createAccount,
            ],
            'leads-description-default' => [
                'Leads',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                [
                    'SugarTestLeadUtilities',
                    'createLead',
                ],
            ],
        ];
    }

    /**
     * Check if QuickSearch returns fields disabled with ACL
     *
     * @dataProvider dataProvider
     * @group 61373
     */
    public function testQuickSearchACLFields($module, $field, $acl, $value, $expected, callable $factory)
    {
        $this->bean = $factory();

        // Set create by to some different than current user
        $this->bean->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        // Set the field we are checking to some value
        $this->bean->$field = $value;
        $this->bean->save();

        // Set ACL for the field
        ACLField::setAccessControl($this->bean->module_name, $this->role->id, $field, $acl);

        // Load the ACLs
        ACLField::loadUserFields(
            $this->bean->module_name,
            $this->bean->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $quickSearchQuery = new QuickSearchQuery();

        $args = [
            'method' => 'query',
            'modules' => [
                0 => $module,
            ],
            'group' => 'or',
            'field_list' => [
                0 => $field,
            ],
            'conditions' => [
                0 => [
                    'name' => 'name',
                    'op' => 'like_custom',
                    'end' => '%',
                    'value' => $this->bean->name,
                ],
            ],
            'order' => 'name',
            'limit' => '30',
            'no_match_text' => 'No Match',
        ];

        // Do a QuickSearch query
        $results = $quickSearchQuery->query($args);

        $json = getJSONobj();
        $results = $json->decode($results);

        $this->assertEquals($expected, $results['fields'][0][$field], "$module->$field should be equal to $value. ACL level $acl");
    }
}
