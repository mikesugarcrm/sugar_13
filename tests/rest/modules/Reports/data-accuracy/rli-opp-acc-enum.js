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

const {Agent, Fixtures} = require('@sugarcrm/thorn');
const expect = require('chakram').expect;

describe('Reports.DataAccuracy.RLI-GB-AccName', function() {
    before(function*() {
        let module = 'RevenueLineItems';

        let userAttributes = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        let users = yield Fixtures.create(userAttributes, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let rliAttributes = [
            {attributes: {name: 'DataAccuracyRLI1', assigned_user_id: this.johnId, sales_stage: 'Prospecting'}},
            {attributes: {name: 'DataAccuracyRLI2', assigned_user_id: this.johnId, sales_stage: 'Qualification'}},
            {attributes: {name: 'DataAccuracyRLI3', assigned_user_id: this.johnId, sales_stage: 'Prospecting'}},
            {attributes: {name: 'DataAccuracyRLI4', assigned_user_id: this.johnId, sales_stage: 'Qualification'}},
        ];

        let rlis = yield Fixtures.create(rliAttributes, {module: module});
        [this.rli1, this.rli2, this.rli3, this.rli4] = rlis.RevenueLineItems;

        let oppAttributes = [
            {attributes: {name: 'DataAccuracyOpp1', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyOpp2', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyOpp3', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyOpp4', assigned_user_id: this.johnId}},
        ];

        let opportunities = yield Fixtures.create(oppAttributes, {module: 'Opportunities'});
        [this.opp1, this.opp2, this.opp3, this.opp4] = opportunities.Opportunities;

        yield Promise.all([
            Fixtures.link(this.rli1, 'opportunities', this.opp1),
            Fixtures.link(this.rli2, 'opportunities', this.opp2),
            Fixtures.link(this.rli3, 'opportunities', this.opp3),
            Fixtures.link(this.rli4, 'opportunities', this.opp4),
        ]);

        let accountAttributes = [
            {attributes: {name: 'DataAccuracyAccount1', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyAccount2', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyAccount3', assigned_user_id: this.johnId}},
            {attributes: {name: 'DataAccuracyAccount4', assigned_user_id: this.johnId}},
        ];

        let accounts = yield Fixtures.create(accountAttributes, {module: 'Accounts'});
        [this.account1, this.account2, this.account3, this.account4] = accounts.Accounts;

        yield Promise.all([
            Fixtures.link(this.opp1, 'accounts', this.account1),
            Fixtures.link(this.opp2, 'accounts', this.account2),
            Fixtures.link(this.opp3, 'accounts', this.account3),
            Fixtures.link(this.opp4, 'accounts', this.account4),
        ]);

        let content = {
            display_columns: [],
            module: module,
            group_defs: [
                {
                    'name': 'name',
                    'label': 'Name',
                    'table_key': 'RevenueLineItems:opportunities:accounts',
                    'type': 'name'
                },
                {
                    'name': 'sales_stage',
                    'label': 'Sales Stage',
                    'table_key': 'self',
                    'type': 'enum'
                }
            ],
            summary_columns: [
                {'name': 'name','label': 'Name','table_key': 'RevenueLineItems:opportunities:accounts'},
                {'name': 'sales_stage','label': 'Sales Stage','table_key': 'self'},
                {'name': 'count','label': 'Count','field_type': '','group_function': 'count','table_key': 'self'}
            ],
            report_name: 'Related enum1',
            do_round: 1,
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                'self': {
                    'value': 'RevenueLineItems',
                    'module': 'RevenueLineItems',
                    'label': 'RevenueLineItems',
                    'dependents': []
                },
                'RevenueLineItems:opportunities': {
                    'name': 'Revenue Line Items  \u003E  Opportunities ',
                    'parent': 'self',
                    'link_def': {
                        'name': 'opportunities',
                        'relationship_name': 'opportunities_revenuelineitems',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Opportunity Name',
                        'module': 'Opportunities',
                        'table_key': 'RevenueLineItems:opportunities'
                    },
                    'dependents': ['group_by_row_3','display_summaries_row_group_by_row_3'],
                    'module': 'Opportunities',
                    'label': 'Opportunity Name'
                },
                'RevenueLineItems:opportunities:accounts': {
                    'name': 'Revenue Line Items  \u003E  Opportunities  \u003E  Accounts',
                    'parent': 'RevenueLineItems:opportunities',
                    'link_def': {
                        'name': 'accounts',
                        'relationship_name': 'accounts_opportunities',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Account Name',
                        'module': 'Accounts',
                        'table_key': 'RevenueLineItems:opportunities:accounts'
                    },
                    'dependents': ['group_by_row_3','display_summaries_row_group_by_row_3'],
                    'module': 'Accounts',
                    'label': 'Account Name'
                },
                'RevenueLineItems:assigned_user_link': {
                    name: 'Revenue Line Items  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'revenuelineitems_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'RevenueLineItems:assigned_user_link'
                    },
                    dependents: ['Filter.1_table_filter_row_1'],
                    module: 'Users',
                    label: 'Assigned to User'
                }
            },
            filters_def: {
                Filter_1: {
                    operator: 'AND',
                    0: {
                        name: 'id',
                        table_key: 'RevenueLineItems:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'Related enum1',
            module: module,
            report_type: 'summary',
            assigned_user_id: this.johnId,
            content: JSON.stringify(content)
        };

        let response = yield Agent.as('John').post('Reports', report);
        this.reportId = response.body.id;
    });

    after(function*() {
        yield Agent.as('John').delete('Reports/' + this.reportId);
        yield Fixtures.cleanup();
    });

    it('should RLIs with Opps with Acccount1 and Prospecting', function*() {
        let filter = {
            group_filters: [
                {'RevenueLineItems:opportunities:accounts:name': 'DataAccuracyAccount1'},
                {'self:sales_stage': 'Prospecting'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let rli1 = records.find((r) => r.id === this.rli1.id);

            expect(rli1).to.exist;
        });
    });

    it('should RLIs with Opps with Acccount2 and Qualification', function*() {
        let filter = {
            group_filters: [
                {'RevenueLineItems:opportunities:accounts:name': 'DataAccuracyAccount2'},
                {'self:sales_stage': 'Qualification'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let rli2 = records.find((r) => r.id === this.rli2.id);

            expect(rli2).to.exist;
        });
    });
});
