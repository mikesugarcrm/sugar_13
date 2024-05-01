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

class FieldTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarBean::clearLoadedDef('Case');
        SugarBean::clearLoadedDef('Contact');
        SugarBean::clearLoadedDef('Account');
    }

    /**
     * Test OrderBy field build should not apply team security
     *
     * @throws SugarQueryException
     */
    public function testGetJoinOrderBy()
    {
        $orderBy = $this->getMockBuilder('SugarQuery_Builder_Field_OrderBy')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMockForAbstractClass();

        $orderBy->query = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getJoinTableAlias',
                    'join',
                ]
            )
            ->getMock();

        $orderBy->query->method('getJoinTableAlias')->willReturn('joinName');

        $join = $this->getMockBuilder('SugarQuery_Builder_Join')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $join->query = $orderBy->query;
        $orderBy->query->join['joinName'] = $join;

        // mock $orderBy->query->join() to check if 'team_security' is set to false
        // because $orderBy is an instance of SugarQuery_Builder_Field_OrderBy
        $orderBy->query->expects($this->once())
            ->method('join')->with(
                'accounts',
                [
                    'joinType' => 'LEFT',
                    'team_security' => false,
                ]
            )
            ->willReturn($join);

        $orderBy->def['link'] = 'accounts';
        $orderBy->def['source'] = 'non-db';
        $orderBy->def['type'] = 'related';
        $alias = $orderBy->getJoin();
    }

    public function testGetJoinRecursion()
    {
        $contact = BeanFactory::newBean('Contacts');

        // create field definition which refers itself as id_name and doesn't have link attribute
        $contact->field_defs['account_name']['id_name'] = 'account_name';
        $contact->field_defs['account_name']['link'] = null;

        $query = new SugarQuery();
        $query->from($contact);
        $field = new SugarQuery_Builder_Field('account_name', $query);
        $alias = $field->getJoin();

        $this->assertFalse($alias, 'Field with invalid vardefs should not produce JOIN');
    }

    public function testGetJoinRelatedFieldWithoutLink()
    {
        // Create account
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome account';
        $account->save();

        SugarTestAccountUtilities::setCreatedAccount([$account->id]);

        // Create case
        $cases = BeanFactory::newBean('Cases');
        $cases->name = 'Awesome contact!';
        $cases->account_id = $account->id;
        $cases->save();

        SugarTestCaseUtilities::setCreatedCase([$cases->id]);

        // Set link field to null
        $cases->field_defs['account_name']['link'] = null;

        $query = new SugarQuery();
        $query->select('account_name');
        $query->from($cases, ['team_security' => false]);
        $query->where()->in('account_id', [$account->id]);
        $result = $query->execute();

        $this->assertNotEmpty($result, 'Account should be selected');

        // mark account as deleted and try to select again
        $account->mark_deleted($account->id);

        $queryDeleted = new SugarQuery();
        $queryDeleted->from($cases, ['team_security' => false]);
        $queryDeleted->where()->in('account_id', [$account->id]);
        $result = $queryDeleted->execute();
        $this->assertEmpty($result, 'Deleted account should not be selected');
    }

    public function testGetRelatedFullNameFieldWithoutLink()
    {
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead(null, [
            'reports_to_id' => $contact->id,
        ]);

        $query = new SugarQuery();
        $query->select('id', 'report_to_name');
        $query->from($lead, [
            'team_security' => false,
        ]);
        $query->where()->equals('id', $lead->id);
        $result = $query->execute();

        $this->assertCount(1, $result);

        $row = array_shift($result);
        $this->assertEquals($lead->id, $row['id']);
        $this->assertEquals($contact->first_name, $row['rel_report_to_name_first_name']);
        $this->assertEquals($contact->last_name, $row['rel_report_to_name_last_name']);
    }

    public function testGetFieldDef()
    {
        $account = BeanFactory::newBean('Accounts');
        // create custom field defs
        $account->field_defs['my_field_c'] = [
            'labelValue' => 'my field',
            'full_text_search' => [
                'boost' => '0',
                'enabled' => false,
            ],
            'enforced' => '',
            'dependency' => '',
            'required' => false,
            'source' => 'custom_fields',
            'name' => 'my_field_c',
            'vname' => 'LBL_MY_FIELD',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
            'id' => 'Accountsmy_field_c',
            'custom_module' => 'Accounts',
        ];
        $sq = new SugarQuery();
        $sq->select(['my_field']);
        $sq->from($account);
        $field = new SugarQuery_Builder_Field('my_field', $sq);
        $def = $field->getFieldDef();
        $this->assertNotFalse($def, "can't find field def for custom field");
    }
}
