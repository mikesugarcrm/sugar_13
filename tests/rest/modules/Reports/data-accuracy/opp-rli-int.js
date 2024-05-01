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

describe('Reports.Int', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            /* RevenueLineItems */
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli100', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli200', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli300', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli400', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli500', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli600', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli700', assigned_user_id: johnId}},

            /* Opportunities */
            {module: 'Opportunities', attributes: {name: 'accuracy_opp100', opportunity_type: 'Existing Business',
                assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp200', opportunity_type: 'New Business',
                assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp300', opportunity_type: 'Existing Business',
                assigned_user_id: johnId}}
        ];
        this.records = yield Fixtures.create(records);
        [this.opp100, this.opp200, this.opp300] = this.records.Opportunities;

        yield Promise.all([
            Fixtures.link(this.records.RevenueLineItems[0], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[1], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[2], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[3], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[4], 'opportunities', this.records.Opportunities[2]),
            Fixtures.link(this.records.RevenueLineItems[5], 'opportunities', this.records.Opportunities[2]),
            Fixtures.link(this.records.RevenueLineItems[6], 'opportunities', this.records.Opportunities[2])
        ]);

        let content = {
            'display_columns': [],
            'module': 'Opportunities',
            'group_defs': [
                {'name': 'total_revenue_line_items', 'label': '# of Total Revenue Line Items',
                    'table_key': 'self', 'type': 'int'},
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum'}
            ],
            'summary_columns': [
                {'name': 'total_revenue_line_items', 'label': '# of Total Revenue Line Items', 'table_key': 'self'},
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count', 'table_key': 'self'}
            ],
            'report_name': 'test_int',
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
            name: 'test_int',
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

    it('should return report data for given grouped by int field', function*() {
        let filter = {
            group_filters: [
                {'self:total_revenue_line_items': '2'},
                {'self:opportunity_type': 'Existing Business'}
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

describe('Reports.Int.Runtime', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            /* RevenueLineItems */
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli100', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli200', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli300', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli400', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli500', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli600', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli700', assigned_user_id: johnId}},

            /* Opportunities */
            {module: 'Opportunities', attributes: {name: 'accuracy_opp100', opportunity_type: 'Existing Business',
                assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp200', opportunity_type: 'New Business',
                assigned_user_id: johnId}},
            {module: 'Opportunities', attributes: {name: 'accuracy_opp300', opportunity_type: 'Existing Business',
                assigned_user_id: johnId}}
        ];
        this.records = yield Fixtures.create(records);
        [this.opp100, this.opp200, this.opp300] = this.records.Opportunities;

        yield Promise.all([
            Fixtures.link(this.records.RevenueLineItems[0], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[1], 'opportunities', this.records.Opportunities[0]),
            Fixtures.link(this.records.RevenueLineItems[2], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[3], 'opportunities', this.records.Opportunities[1]),
            Fixtures.link(this.records.RevenueLineItems[4], 'opportunities', this.records.Opportunities[2]),
            Fixtures.link(this.records.RevenueLineItems[5], 'opportunities', this.records.Opportunities[2]),
            Fixtures.link(this.records.RevenueLineItems[6], 'opportunities', this.records.Opportunities[2])
        ]);

        let content = {
            'display_columns': [],
            'module': 'Opportunities',
            'group_defs': [
                {'name': 'total_revenue_line_items', 'label': '# of Total Revenue Line Items',
                    'table_key': 'self', 'type': 'int'},
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum'}
            ],
            'summary_columns': [
                {'name': 'total_revenue_line_items', 'label': '# of Total Revenue Line Items',
                    'table_key': 'self'},
                {'name': 'opportunity_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count',
                    'table_key': 'self'}
            ],
            'report_name': 'test_int1',
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
                        'name': 'total_revenue_line_items',
                        'table_key': 'self',
                        'qualifier_name': 'greater',
                        'runtime': 1,
                        'input_name0': '1',
                        'input_name1': 'on'
                    },
                    '1': {
                        'name': 'opportunity_type',
                        'table_key': 'self',
                        'qualifier_name': 'not_empty',
                        'input_name0': 'not_empty',
                        'input_name1': 'on'
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
            name: 'test_int1',
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

    it('should return report data for given grouped by int field - runtime enabled', function*() {
        let filter = {
            group_filters: [
                {'self:total_revenue_line_items': '3'},
                {'self:opportunity_type': 'Existing Business'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records', {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let opp300 = records.find((r) => r.id === this.opp300.id);
            expect(opp300).to.exist;
        });
    });
});
