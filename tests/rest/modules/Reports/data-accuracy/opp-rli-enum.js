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
const chakram = require('chakram');
const expect = require('chakram').expect;

describe('Reports.Enum', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {attributes: {name: 'accuracy_opp1', opportunity_type: 'New Business',
                lead_source: 'Cold Call', assigned_user_id: johnId}},
            {attributes: {name: 'accuracy_opp2', opportunity_type: 'Existing Business',
                lead_source: 'Existing Customer', assigned_user_id: johnId}},
            {attributes: {name: 'accuracy_opp3', opportunity_type: 'New Business',
                lead_source: 'Partner', assigned_user_id: johnId}},
            {attributes: {name: 'accuracy_opp4', opportunity_type: 'New Business',
                lead_source: 'Cold Call', assigned_user_id: johnId}},
            {attributes: {name: 'accuracy_opp5', opportunity_type: 'Existing Business',
                lead_source: 'Cold Call', assigned_user_id: johnId}}
        ];

        let opportunities = yield Fixtures.create(records, {module: 'Opportunities'});
        [this.opp1, this.opp2, this.opp3, this.opp4, this.opp5] = opportunities.Opportunities;

        let content = {
            'display_columns': [],
            'module': 'Opportunities',
            'group_defs': [
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum'},
                {'name': 'lead_source', 'label': 'Lead Source', 'table_key': 'self', 'type': 'enum'}
            ],
            'summary_columns': [
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'lead_source', 'label': 'Lead Source', 'table_key': 'self'},
                {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count', 'table_key': 'self'}
            ],
            'report_name': 'test_enum',
            'chart_type': 'vBarF',
            'do_round': 1,
            'chart_description': '',
            'numerical_chart_column': 'self:count',
            'numerical_chart_column_type': '',
            'assigned_user_id': johnId,
            'report_type': 'summary',
            'full_table_list': {
                'self': {
                    'value': 'Opportunities',
                    'module': 'Opportunities',
                    'label': 'Opportunities'
                },
                'Opportunities:assigned_user_link': {
                    'name': 'Opportunities  \u003E  Assigned to User',
                    'parent': 'self',
                    'link_def': {
                        'name': 'assigned_user_link',
                        'relationship_name': 'opportunities_assigned_user',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Assigned to User',
                        'module': 'Users',
                        'table_key': 'Opportunities:assigned_user_link'
                    },
                    'dependents': ['Filter.1_table_filter_row_2'],
                    'module': 'Users',
                    'label': 'Assigned to User'
                }
            },
            'filters_def': {
                'Filter_1': {
                    'operator': 'AND',
                    '0': {
                        'name': 'id',
                        'table_key': 'Opportunities:assigned_user_link',
                        'qualifier_name': 'is',
                        'input_name0': johnId,
                        'input_name1': 'Smith'
                    }
                }
            }
        };

        let report = {
            name: 'test_enum',
            module: 'Opportunities',
            report_type: 'summary',
            chart_type: 'vBarF',
            assigned_user_id: johnId,
            content: JSON.stringify(content)
        };

        let response = yield Agent.as('John').post('Reports', report);
        this.reportId = response.body.id;
    });

    after(function*() {
        yield Agent.as('John').delete('Reports/' + this.reportId);
        yield Fixtures.cleanup();
    });

    it('should return report data for given 2 grouped by enum fields', function*() {
        let filter = {
            group_filters: [
                {'self:opportunity_type': 'New Business'},
                {'self:lead_source': 'Cold Call'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records', {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(2);
            let opp1 = records.find((r) => r.id === this.opp1.id);
            let opp4 = records.find((r) => r.id === this.opp4.id);
            expect(opp1).to.exist;
            expect(opp4).to.exist;
        });
    });
});

describe('Reports.Enum.Runtime', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            /* RevenueLineItems */
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli100', sales_stage: 'Prospecting',
                likely_case: '100.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli200', sales_stage: 'Prospecting',
                likely_case: '200.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli300', sales_stage: 'Prospecting',
                likely_case: '300.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli400', sales_stage: 'Prospecting',
                likely_case: '400.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli500', sales_stage: 'Prospecting',
                likely_case: '500.0', assigned_user_id: johnId}},

            /* Opportunities */
            {module: 'Opportunities', attributes: {name: 'accuracy_opp100', opportunity_type: 'Existing Business',
                lead_source: 'Existing Customer', assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp200', opportunity_type: 'New Business',
                lead_source: 'Existing Customer', assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp300', opportunity_type: 'Existing Business',
                lead_source: 'Cold Call', assigned_user_id: johnId}}
        ];
        this.records = yield Fixtures.create(records);
        [this.opp100, this.opp200, this.opp300] = this.records.Opportunities;

        yield Promise.all([
            Fixtures.link(this.records.RevenueLineItems[0], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[1], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[2], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[3], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[4], 'opportunities', this.records.Opportunities[2])
        ]);

        let content = {
            'display_columns': [],
            'module': 'Opportunities',
            'group_defs': [
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum'},
                {'name': 'lead_source', 'label': 'Lead Source', 'table_key': 'self', 'type': 'enum'}
            ],
            'summary_columns': [
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'lead_source', 'label': 'Lead Source', 'table_key': 'self'},
                {
                    'name': 'amount',
                    'label': 'SUM: Likely',
                    'field_type': 'currency',
                    'group_function': 'sum',
                    'table_key': 'self'
                }
            ],
            'report_name': 'test_enum1',
            'chart_type': 'vBarF',
            'do_round': 1,
            'chart_description': '',
            'numerical_chart_column': 'self:amount:sum',
            'numerical_chart_column_type': 'currency',
            'assigned_user_id': johnId,
            'report_type': 'summary',
            'full_table_list': {
                'self': {
                    'value': 'Opportunities',
                    'module': 'Opportunities',
                    'label': 'Opportunities'
                },
                'Opportunities:assigned_user_link': {
                    'name': 'Opportunities  \u003E  Assigned to User',
                    'parent': 'self',
                    'link_def': {
                        'name': 'assigned_user_link',
                        'relationship_name': 'opportunities_assigned_user',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Assigned to User',
                        'module': 'Users',
                        'table_key': 'Opportunities:assigned_user_link'
                    },
                    'dependents': ['Filter.1_table_filter_row_2'],
                    'module': 'Users',
                    'label': 'Assigned to User'
                }
            },
            'filters_def': {
                'Filter_1': {
                    'operator': 'AND',
                    '0': {
                        'name': 'opportunity_type', 'table_key': 'self', 'qualifier_name': 'not_empty', 'runtime': 1,
                        'input_name0': 'not_empty', 'input_name1': 'on'
                    },
                    '1': {
                        'name': 'lead_source',
                        'table_key': 'self',
                        'qualifier_name': 'is_not',
                        'input_name0': ['Cold Call']
                    },
                    '2': {
                        'name': 'id',
                        'table_key': 'Opportunities:assigned_user_link',
                        'qualifier_name': 'is',
                        'input_name0': johnId,
                        'input_name1': 'Smith'
                    }
                }
            }
        };
        let report = {
            name: 'test_enum1',
            module: 'Opportunities',
            report_type: 'summary',
            chart_type: 'vBarF',
            assigned_user_id: johnId,
            content: JSON.stringify(content)
        };

        let response = yield Agent.as('John').post('Reports', report);
        this.reportId = response.body.id;
    });

    after(function*() {
        yield Agent.as('John').delete('Reports/' + this.reportId);
        yield Fixtures.cleanup();
    });

    it('should return report data for given 2 grouped by enum fields - runtime enabled', function*() {
        let filter = {
            group_filters: [
                {'self:opportunity_type': 'Existing Business'},
                {'self:lead_source': 'Existing Customer'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records', {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let opp100 = records.find((r) => r.id === this.opp100.id);
            expect(opp100).to.exist;
        });
    });
});
