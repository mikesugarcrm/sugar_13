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
 * @coversDefaultClass RelateRecordApi
 */
class RelateRecordApiUpdateTest extends TestCase
{
    private $user1;
    private $user2;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @coversDefaultClass ::updateRelatedLink
     */
    public function testRelateFieldUpdated()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->assigned_user_id = $this->user1->id;
        $contact->save();

        $contact->retrieve($contact->id);
        $this->assertEquals($contact->assigned_user_name, $this->user1->name);

        $account = SugarTestAccountUtilities::createAccount();
        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->updateRelatedLink($service, [
            'module' => $account->module_name,
            'record' => $account->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
            'assigned_user_id' => $this->user2->id,
        ]);

        $this->assertEquals($this->user2->name, $response['related_record']['assigned_user_name']);
    }

    public function testOppContactsAttributesUpdate()
    {
        $contact = SugarTestContactUtilities::createContact();

        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $contact_role = 'Executive Sponsor';
        $api->updateRelatedLink($service, [
            'module' => $opportunity->module_name,
            'record' => $opportunity->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
            'opportunity_role' => $contact_role,
        ]);

        $query = 'SELECT contact_role 
                    FROM opportunities_contacts 
                    WHERE contact_id = ' . $contact->db->quoted($contact->id) . ' AND 
                    opportunity_id = ' . $opportunity->db->quoted($opportunity->id);
        $result = $contact->db->fetchOne($query);

        $this->assertEquals($contact_role, $result['contact_role']);
    }

    public function testACLRolesActionsAttributesUpdate()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $role = SugarTestACLUtilities::createRole(
            'Test ' . time(),
            ['Accounts'],
            ['access', 'create', 'view', 'list', 'edit', 'import', 'export', 'massupdate']
        );
        $actions = ACLRole::getRoleActions($role->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $access_override = 122;
        $api->updateRelatedLink($service, [
            'module' => $role->getModuleName(),
            'record' => $role->id,
            'link_name' => 'actions',
            'remote_id' => $actions['Accounts']['module']['access']['id'],
            'access_override' => $access_override,
        ]);

        $actions = ACLRole::getRoleActions($role->id);
        $this->assertEquals($access_override, $actions['Accounts']['module']['access']['access_override']);
    }
}
