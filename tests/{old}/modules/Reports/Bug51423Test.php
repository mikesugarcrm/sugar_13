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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

/**
 * Bug 51423:
 *  Related data is not properly populated in Reports
 * @ticket 51423
 * @author arymarchik@sugarcrm.com
 */
class Bug51423Test extends TestCase
{
    /**
     * @var array Request for creating/deleting related field for Accounts module
     */
    private $req = [
        'to_pdf' => 'true',
        'sugar_body_only' => '1',
        'module' => 'ModuleBuilder',
        'new_dropdown' => '',
        'view_module' => 'Accounts',
        'is_update' => 'true',
        'type' => 'relate',
        'name' => 'relate_contacts',
        'labelValue' => 'relate contacts',
        'label' => 'LBL_RELATE_CONTACTS',
        'help' => '',
        'comments' => '',
        'ext2' => 'Contacts',
        'ext3' => '',
        'dependency' => '',
        'dependency_display' => '',
        'reportableCheckbox' => '1',
        'reportable' => '1',
        'importable' => 'true',
        'duplicate_merge' => '0',
    ];

    private $account_1;

    private $account_2;

    private $contact_1;

    private $contact_2;

    /**
     * @var bool
     */
    protected $origin_isCacheReset;

    protected function setUp(): void
    {
        $this->origin_isCacheReset = SugarCache::$isCacheReset;
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = $user;

        $this->req['action'] = 'saveField';
        $request = InputValidation::create($this->req, []);
        $mb = new ModuleBuilderController($request);
        $mb->action_saveField();

        $this->contact_1 = SugarTestContactUtilities::createContact();
        $this->contact_1->last_name = 'Contact #1';
        $this->contact_1->team_id = 1;
        $this->contact_1->save();

        $this->contact_2 = SugarTestContactUtilities::createContact();
        $this->contact_2->last_name = 'Contact #2';
        $this->contact_2->team_id = 1;
        $this->contact_2->save();

        $this->account_1 = SugarTestAccountUtilities::createAccount();
        $this->account_1->name = 'Account #1';
        $this->account_1->contact_id_c = $this->contact_1->id;
        $this->account_1->team_id = 1;
        $this->account_1->relate_contacts_c = $this->contact_1->last_name;
        $this->account_1->save();

        $this->account_2 = SugarTestAccountUtilities::createAccount();
        $this->account_2->name = 'Account #2';
        $this->account_2->contact_id_c = $this->contact_2->id;
        $this->account_2->relate_contacts_c = $this->contact_2->last_name;
        $this->account_2->parent_id = $this->account_1->id;
        $this->account_2->team_id = 1;
        $this->account_2->save();
    }

    protected function tearDown(): void
    {
        $this->req['action'] = 'DeleteField';
        $this->req['name'] = 'relate_contacts_c';
        $request = InputValidation::create($this->req, []);
        $mb = new ModuleBuilderController($request);
        $mb->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarCache::$isCacheReset = $this->origin_isCacheReset;
        SugarTestHelper::tearDown();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Testing related fields in the report
     * @group 51423
     */
    public function testReportsRelatedField()
    {
        /**
         * Report defs for generating the report
         */
        $rep_defs = [
            'display_columns' => [
                0 => [
                    'name' => 'name',
                    'label' => 'Name',
                    'table_key' => 'self',
                ],
                1 => [
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts',
                    'table_key' => 'self',
                ],
                2 => [
                    'name' => 'name',
                    'label' => 'Name1',
                    'table_key' => 'Accounts:member_of',
                ],
                3 => [
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts1',
                    'table_key' => 'Accounts:member_of',
                ],
            ],
            'module' => 'Accounts',
            'group_defs' => [],
            'summary_columns' => [],
            'report_name' => 'report #1',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Accounts',
                    'module' => 'Accounts',
                    'label' => 'Accounts',
                ],
                'Accounts:member_of' => [
                    'name' => 'Accounts  >  Member of',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'member_of',
                        'relationship_name' => 'member_accounts',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Member of',
                        'module' => 'Accounts',
                        'table_key' => 'Accounts:member_of',
                    ],
                    'dependents' => [
                        0 => 'display_cols_row_3',
                        1 => 'display_cols_row_4',
                        2 => 'display_cols_row_3',
                        3 => 'display_cols_row_4',
                    ],
                    'module' => 'Accounts',
                    'label' => 'Member of',
                    'optional' => true,
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    0 => [
                        'name' => 'name',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                    ],
                ],
            ],
        ];
        $rep_defs['filters_def']['Filter_1']['0']['input_name0'] = $this->account_2->id;
        $rep_defs['filters_def']['Filter_1']['0']['input_name1'] = $this->account_2->name;
        $json = getJSONobj();
        $tmp = $json->encode($rep_defs);
        $report = new Report($tmp);
        $report->run_query();
        while (($row = $report->get_next_row()) != 0) {
            $this->assertMatchesRegularExpression('/.*' . preg_quote($this->account_2->name) . '.*/', $row['cells']['0']);
            $this->assertMatchesRegularExpression('/.*' . preg_quote($this->contact_2->last_name) . '.*/', $row['cells']['1']);
            $this->assertMatchesRegularExpression('/.*' . preg_quote($this->account_1->name) . '.*/', $row['cells']['2']);
            $this->assertMatchesRegularExpression('/.*' . preg_quote($this->contact_1->last_name) . '.*/', $row['cells']['3']);
        }
    }
}
