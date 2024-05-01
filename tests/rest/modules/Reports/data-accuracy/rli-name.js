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

describe('Reports.Name', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli100', product_type: 'Existing Business',
                likely_case: '100.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli200', product_type: 'New Business',
                likely_case: '200.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli300', product_type: 'New Business',
                likely_case: '300.0', assigned_user_id: johnId}}
        ];

        let rli = yield Fixtures.create(records, {module: 'RevenueLineItems'});
        [this.rli1, this.rli2, this.rli3] = rli.RevenueLineItems;

        let content = {
            'display_columns': [],
            'module': 'RevenueLineItems',
            'group_defs': [
                {'name': 'name', 'label': 'Revenue Line Item', 'table_key': 'self', 'type': 'name',
                    'force_label': 'Revenue Line Item'},
                {'name': 'product_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum', 'force_label': 'Type'}
            ],
            'summary_columns': [
                {'name': 'name', 'label': 'Revenue Line Item', 'table_key': 'self'},
                {'name': 'product_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count', 'table_key': 'self'}
            ],
            'report_name': 'test_name',
            'chart_type': 'vBarF',
            'do_round': 1,
            'chart_description': '',
            'numerical_chart_column': 'self:count',
            'numerical_chart_column_type': '',
            'assigned_user_id': johnId,
            'report_type': 'summary',
            'full_table_list': {
                'self': {
                    'value': 'RevenueLineItems',
                    'module': 'RevenueLineItems',
                    'label': 'RevenueLineItems'
                },
                'RevenueLineItems:assigned_user_link': {
                    'name': 'Revenue Line Items  \u003E  Assigned to User',
                    'parent': 'self',
                    'link_def': {
                        'name': 'assigned_user_link',
                        'relationship_name': 'revenuelineitems_assigned_user',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Assigned to User',
                        'module': 'Users',
                        'table_key': 'RevenueLineItems:assigned_user_link'
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
                        'name': 'name',
                        'table_key': 'self',
                        'qualifier_name': 'starts_with',
                        'input_name0': 'accuracy_rli',
                        'input_name1': 'on'
                    },
                    '1': {
                        'name': 'id',
                        'table_key': 'RevenueLineItems:assigned_user_link',
                        'qualifier_name': 'is',
                        'input_name0': johnId,
                        'input_name1': 'Smith'
                    }
                }
            }
        };

        let report = {
            name: 'test_name',
            module: 'RevenueLineItems',
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

    it('should return report data for given grouped by name field', function*() {
        let filter = {
            group_filters: [
                {'self:name': 'accuracy_rli200'},
                {'self:product_type': 'New Business'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records?', {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let rli2 = records.find((r) => r.id === this.rli2.id);
            expect(rli2).to.exist;
        });
    });
});

describe('Reports.Name.Runtime', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli100', product_type: 'Existing Business',
                likely_case: '100.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_200', product_type: 'New Business',
                likely_case: '200.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli300', product_type: 'New Business',
                likely_case: '300.0', assigned_user_id: johnId}},
            {module: 'RevenueLineItems', attributes: {name: 'accuracy_rli400', product_type: '',
                likely_case: '400.0', assigned_user_id: johnId}}
        ];

        let rli = yield Fixtures.create(records, {module: 'RevenueLineItems'});
        [this.rli1, this.rli2, this.rli3, this.rli4] = rli.RevenueLineItems;

        let content = {
            'display_columns': [],
            'module': 'RevenueLineItems',
            'group_defs': [
                {'name': 'name', 'label': 'Revenue Line Item', 'table_key': 'self', 'type': 'name',
                    'force_label': 'Revenue Line Item'},
                {'name': 'product_type', 'label': 'Type', 'table_key': 'self', 'type': 'enum', 'force_label': 'Type'}
            ],
            'summary_columns': [
                {'name': 'name', 'label': 'Revenue Line Item', 'table_key': 'self'},
                {'name': 'product_type', 'label': 'Type', 'table_key': 'self'},
                {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count', 'table_key': 'self'}
            ],
            'report_name': 'test_name1',
            'chart_type': 'vBarF',
            'do_round': 1,
            'chart_description': '',
            'numerical_chart_column': 'self:count',
            'numerical_chart_column_type': '',
            'assigned_user_id': johnId,
            'report_type': 'summary',
            'full_table_list': {
                'self': {
                    'value': 'RevenueLineItems',
                    'module': 'RevenueLineItems',
                    'label': 'RevenueLineItems'
                },
                'RevenueLineItems:assigned_user_link': {
                    'name': 'Revenue Line Items  \u003E  Assigned to User',
                    'parent': 'self',
                    'link_def': {
                        'name': 'assigned_user_link',
                        'relationship_name': 'revenuelineitems_assigned_user',
                        'bean_is_lhs': false,
                        'link_type': 'one',
                        'label': 'Assigned to User',
                        'module': 'Users',
                        'table_key': 'RevenueLineItems:assigned_user_link'
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
                        'name': 'name',
                        'table_key': 'self',
                        'qualifier_name': 'starts_with',
                        'runtime': 1,
                        'input_name0': 'accuracy_rli',
                        'input_name1': 'on'
                    },
                    '1': {
                        'name': 'product_type',
                        'table_key': 'self',
                        'qualifier_name': 'not_empty',
                        'input_name0': 'not_empty',
                        'input_name1': 'on'
                    },
                    '2': {
                        'name': 'id',
                        'table_key': 'RevenueLineItems:assigned_user_link',
                        'qualifier_name': 'is',
                        'input_name0': johnId,
                        'input_name1': 'Smith'
                    }
                }
            }
        };

        let report = {
            name: 'test_name1',
            module: 'RevenueLineItems',
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

    it('should return report data for given grouped by name field - runtime enabled', function*() {
        let filter = {
            group_filters: [
                {'self:name': 'accuracy_rli300'},
                {'self:product_type': 'New Business'}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records?', {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let rli3 = records.find((r) => r.id === this.rli3.id);
            expect(rli3).to.exist;
        });
    });
});
