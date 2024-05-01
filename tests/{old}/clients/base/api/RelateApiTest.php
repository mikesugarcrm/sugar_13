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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @group ApiTests
 */
class RelateApiTest extends TestCase
{
    public $accounts = [];
    public $contacts = [];
    public $roles = [];
    public $opportunities = [];
    public $opp_contacts = [];

    /** @var  RelateApi */
    public $relateApi;

    private $db = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        // load up the unifiedSearchApi for good times ahead
        $this->relateApi = new RelateApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'RelateApi setUp Account';
        $account->save();
        $this->accounts[] = $account;

        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'RelateApi setUp';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contacts[] = $contact;

        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->load_relationship('contacts');
        $contact = SugarTestContactUtilities::createContact();
        $contact->opportunity_role = 'A';
        $contact->first_name = 'LeanCount setUp';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contacts[] = $contact;
        $opportunity->contacts->add($contact);
        $this->opp_contacts[] = $contact;

        $contact = SugarTestContactUtilities::createContact();
        $contact->opportunity_role = 'A';
        $contact->first_name = 'RelateApi setUp';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contacts[] = $contact;
        $opportunity->contacts->add($contact);
        $this->opp_contacts[] = $contact;

        $contact = SugarTestContactUtilities::createContact();
        $contact->opportunity_role = 'B';
        $contact->first_name = 'RelateApi setUp';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contacts[] = $contact;
        $opportunity->contacts->add($contact);
        $this->opp_contacts[] = $contact;

        $opportunity->save();
        $this->opportunities[] = $opportunity;

        $db = DBManagerFactory::getInstance();
        $this->db = $db;
    }

    protected function tearDown(): void
    {
        $GLOBALS['current_user']->is_admin = 1;
        // delete the bunch of accounts crated
        foreach ($this->accounts as $account) {
            $account->mark_deleted($account->id);
        }
        foreach ($this->contacts as $contact) {
            $contact->mark_deleted($contact->id);
        }

        foreach ($this->roles as $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    // test set favorite
    public function testRelateRecordViewNone()
    {
        $modules = ['Contacts'];
        $this->roles[] = $role = $this->createRole(
            'UNIT TEST ' . create_guid(),
            $modules,
            ['access', 'edit', 'list', 'export']
        );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);

        $result = $this->relateApi->filterRelated(
            new RelateApiServiceMockUp(),
            [
                'module' => 'Accounts',
                'record' => $this->accounts[0]->id,
                'link_name' => 'contacts',
                'fields' => [],
            ]
        );

        $this->assertNotEmpty($result['records'], 'Records were empty');
        $this->assertEquals($result['records'][0]['id'], $this->contacts[0]->id, 'ID Does not match');
    }

    /**
     * Test asserts result of filterRelatedCount
     */
    public function testRelateCountViewNone()
    {
        $modules = ['Contacts'];
        $this->roles[] = $role = $this->createRole(
            'UNIT TEST ' . create_guid(),
            $modules,
            ['access', 'edit', 'list', 'export']
        );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);

        $reply = $this->relateApi->filterRelatedCount(
            new RelateApiServiceMockUp(),
            [
                'module' => 'Accounts',
                'record' => $this->accounts[0]->id,
                'link_name' => 'contacts',
                'fields' => [],
            ]
        );
        $this->assertArrayHasKey('record_count', $reply);
        $this->assertSame(1, $reply['record_count']);
    }

    protected function createRole($name, $allowedModules, $allowedActions, $ownerActions = [])
    {
        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $GLOBALS['db']->commit();

        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed modules
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                foreach ($actions['module'] as $actionName => $action) {
                    if (in_array($actionName, $allowedActions) && in_array($actionName, $ownerActions)) {
                        $aclAllow = ACL_ALLOW_OWNER;
                    } elseif (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }

                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }
        }
        return $role;
    }

    public function testFilteringOnARelationship()
    {
        $account_id = $this->accounts[0]->id;
        $contact_id = $this->contacts[0]->id;
        $serviceMock = new RelateApiServiceMockUp();
        $reply = $this->relateApi->filterRelated(
            $serviceMock,
            [
                'module' => 'Accounts',
                'record' => $account_id,
                'link_name' => 'contacts',
                'filter' => [['first_name' => ['$starts' => 'RelateApi']]],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals(1, safeCount($reply['records']));
        $this->assertEquals($contact_id, $reply['records'][0]['id']);
    }

    /**
     * Test asserts result of filterRelatedCount
     */
    public function testCountFilteringOnARelationship()
    {
        $account_id = $this->accounts[0]->id;
        $serviceMock = new RelateApiServiceMockUp();
        $reply = $this->relateApi->filterRelatedCount(
            $serviceMock,
            [
                'module' => 'Accounts',
                'record' => $account_id,
                'link_name' => 'contacts',
                'filter' => [['first_name' => ['$starts' => 'RelateApi']]],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );
        $this->assertArrayHasKey('record_count', $reply);
        $this->assertSame(1, $reply['record_count']);
    }

    /**
     * Test sorting on a field with rname_link property defined in vardefs, eg, opportunity_role in Contacts module
     */
    public function testOrderByRelationshipField()
    {
        $opp_id = $this->opportunities[0]->id;
        $contact_id = $this->contacts[3]->id;
        $serviceMock = new RelateApiServiceMockUp();
        $reply = $this->relateApi->filterRelated(
            $serviceMock,
            [
                'module' => 'Opportunities',
                'record' => $opp_id,
                'link_name' => 'contacts',
                'fields' => 'id, name, opportunity_role',
                'order_by' => 'opportunity_role:DESC',
            ]
        );

        $this->assertEquals(3, safeCount($reply['records']), 'Should return three records');
        $this->assertEquals($contact_id, $reply['records'][0]['id'], 'Should be in desc order');
    }


    /**
     * Test subpanel list should not contain duplicate records.
     */
    public function testFilterRelated()
    {
        $fieldParams = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
                'len' => 36,
            ],
            'pd_id' => [
                'name' => 'pd_id',
                'type' => 'id',
                'len' => 36,
            ],
            'bean_id' => [
                'name' => 'bean_id',
                'type' => 'id',
                'len' => 36,
            ],
            'bean_module' => [
                'name' => 'bean_module',
                'type' => 'varchar',
                'len' => 100,
            ],
        ];

        // we have three contacts in $this->opp_contacts
        foreach ($this->opp_contacts as $contact) {
            $i = 0;
            // insert twice to locked_field_bean_rel for each Contact
            while ($i++ < 2) {
                $this->db->insertParams('locked_field_bean_rel', $fieldParams, [
                    'id' => Uuid::uuid1(),
                    'pd_id' => Uuid::uuid1(),
                    'bean_id' => $contact->id,
                    'bean_module' => 'Contacts',
                ]);
            }
        }

        $args = [
            'module' => 'Opportunities',
            'record' => $this->opportunities[0]->id,
            'view' => 'subpanel',
            'link_name' => 'contacts',
            'fields' => 'id, name',
            'max_num' => 2,
            'erased_fields' => true,
        ];
        $serviceMock = new RelateApiServiceMockUp();
        $reply = $this->relateApi->filterRelated(
            $serviceMock,
            $args
        );
        $this->assertArrayHasKey('records', $reply);
        $this->assertEquals(2, safeCount($reply['records']));

        // test offset
        $args['offset'] = 2;
        $reply = $this->relateApi->filterRelated(
            $serviceMock,
            $args
        );
        $this->assertArrayHasKey('records', $reply);
        $this->assertEquals(1, safeCount($reply['records']));
    }

    /**
     * Related records should be accessible for record owner
     *
     * @dataProvider aclProvider
     */
    public function testFetchRelatedRecordsByOwner(array $acl)
    {
        global $current_user;

        [$lead, $email] = $this->setUpArchivedEmails($current_user);
        $records = $this->getRelatedEmails($lead, $acl);
        $this->assertCount(1, $records, 'There should be exactly one record');
        $record = array_shift($records);
        $this->assertEquals($email->id, $record['id']);
    }

    /**
     * Related records should not be accessible for a non-owner
     *
     * @dataProvider aclProvider
     */
    public function testFetchRelatedRecordsByNonOwner($acl, $exception)
    {
        $owner = SugarTestUserUtilities::createAnonymousUser();

        [$lead] = $this->setUpArchivedEmails($owner);

        $this->expectException($exception);
        $this->getRelatedEmails($lead, $acl);
    }

    /**
     * Test asserts result of filterRelatedLeanCount
     * @dataProvider leanCountProvider
     */
    public function testRelatedLeanCount($args, $recordCount, $hasMore)
    {
        $args['record'] = $this->opportunities[0]->id;
        $serviceMock = new RelateApiServiceMockUp();
        $reply = $this->relateApi->filterRelatedLeanCount(
            $serviceMock,
            $args
        );
        $this->assertArrayHasKey('record_count', $reply);
        $this->assertArrayHasKey('has_more', $reply);
        $this->assertSame($recordCount, $reply['record_count']);
        $this->assertSame($hasMore, $reply['has_more']);
    }

    /**
     * Test asserts result of filterRelatedLeanCount
     * @dataProvider leanCountExProvider
     */
    public function testRelatedLeanCountException($args)
    {
        $args['record'] = $this->opportunities[0]->id;
        $serviceMock = new RelateApiServiceMockUp();

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->relateApi->filterRelatedLeanCount(
            $serviceMock,
            $args
        );
    }

    private function setUpArchivedEmails(User $owner)
    {
        $lead = SugarTestLeadUtilities::createLead();
        $lead->assigned_user_id = $owner->id;
        $lead->save();

        // remove the lead from cache since it doesn't consider ACL
        BeanFactory::unregisterBean($lead);

        $email = SugarTestEmailUtilities::createEmail();
        $email->load_relationship('leads');
        $email->leads->add($lead);

        return [$lead, $email];
    }

    private function getRelatedEmails(Lead $lead, array $acl)
    {
        global $current_user;

        ACLAction::setACLData($current_user->id, $lead->module_dir, [
            'module' => array_merge([
                'access' => ['aclaccess' => ACL_ALLOW_ENABLED],
            ], $acl),
        ]);

        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $response = $this->relateApi->filterRelated($serviceBase, [
            'fields' => 'id',
            'link_name' => 'archived_emails',
            'module' => $lead->module_dir,
            'record' => $lead->id,
        ]);
        $this->assertArrayHasKey('records', $response);

        return $response['records'];
    }

    public static function aclProvider()
    {
        require_once 'modules/ACLActions/actiondefs.php';

        return [
            // not having permission to view the parent record should cause SugarApiExceptionNotFound
            [
                [
                    'view' => ['aclaccess' => ACL_ALLOW_OWNER],
                ],
                SugarApiExceptionNotFound::class,
            ],
        ];
    }

    public static function leanCountProvider()
    {
        return [
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'filter' => [
                        ['first_name' => ['$starts' => 'RelateApi']],
                    ],
                    'max_num' => 2,
                ],
                2,
                false,

            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'filter' => [
                        ['first_name' => ['$starts' => 'RelateApi']],
                    ],
                    'max_num' => 1,
                ],
                1,
                true,
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => 2,
                ],
                2,
                true,
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => 3,
                ],
                3,
                false,
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => 4,
                ],
                3,
                false,
            ],
        ];
    }

    public static function leanCountExProvider()
    {
        return [
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'filter' => [
                        ['first_name' => ['$starts' => 'RelateApi']],
                    ],
                ],
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => '-1',
                ],
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => 'foo',
                ],
            ],
            [
                [
                    'module' => 'Opportunities',
                    'link_name' => 'contacts',
                    'max_num' => '',
                ],
            ],
        ];
    }
}

class RelateApiServiceMockUp extends RestService
{
    public function __construct()
    {
        $this->user = $GLOBALS['current_user'];
    }

    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
