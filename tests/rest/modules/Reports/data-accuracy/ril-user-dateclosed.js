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

describe('Reports.DataAccuracy.RLI-AssignedUser-DateClosed', function() {
    before(function*() {
        let module = 'RevenueLineItems';

        let userAttributes = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}},
            {attributes: {user_name: 'Jane', last_name: 'Doe', status: 'Active'}},

        ];

        let users = yield Fixtures.create(userAttributes, {module: 'Users'});

        this.johnId = users.Users[0].id;
        this.johnUserName = users.Users[0].user_name;
        this.janeId = users.Users[1].id;
        this.janeUserName = users.Users[1].user_name;

        let rliAttributes = [
            {attributes: {name: 'DataAccuracyRLI1', assigned_user_id: this.johnId, date_closed: '2018-01-01'}},
            {attributes: {name: 'DataAccuracyRLI2', assigned_user_id: this.johnId, date_closed: '2018-01-15'}},
            {attributes: {name: 'DataAccuracyRLI3', assigned_user_id: this.janeId, date_closed: '2018-02-01'}},
            {attributes: {name: 'DataAccuracyRLI4', assigned_user_id: this.janeId, date_closed: '2018-02-15'}},
        ];

        let rlis = yield Fixtures.create(rliAttributes, {module: module});
        [this.rli1, this.rli2, this.rli3, this.rli4] = rlis.RevenueLineItems;

        let content = {
            display_columns: [],
            module: module,
            group_defs: [
                {
                    'name': 'user_name',
                    'label': 'User Name',
                    'table_key': 'RevenueLineItems:assigned_user_link',
                    'type': 'username',
                    'force_label': 'User Name'
                },
                {
                    'name': 'date_closed',
                    'label': 'Month: Expected Close Date',
                    'column_function': 'month',
                    'qualifier': 'month',
                    'table_key': 'self',
                    'type': 'date',
                    'force_label': 'Month: Expected Close Date'
                }
            ],
            summary_columns: [
                {
                    'name': 'user_name',
                    'label': 'User Name',
                    'table_key': 'RevenueLineItems:assigned_user_link'
                },
                {
                    'name': 'date_closed',
                    'label': 'Month: Expected Close Date',
                    'column_function': 'month',
                    'qualifier': 'month',
                    'table_key': 'self'
                },
                {
                    'name': 'likely_case',
                    'label': 'MAX: Likely',
                    'field_type': 'currency',
                    'group_function': 'max',
                    'table_key': 'self'
                }
            ],
            report_name: 'DataAccuracyTest-Relate-Date2',
            do_round: 1,
            numerical_chart_column: 'self:likely_case:max',
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
                    'dependents': [
                        'Filter.1_table_filter_row_1',
                        'group_by_row_1',
                        'display_summaries_row_group_by_row_1'
                    ],
                    'module': 'Users',
                    'label': 'Assigned to User'
                }
            },
            filters_def: {
                'Filter_1': {
                    'operator': 'AND',
                    '0': {
                        'name': 'user_name',
                        'table_key': 'RevenueLineItems:assigned_user_link',
                        'qualifier_name': 'is_not',
                        'runtime': 1,
                        'input_name0': ['seed_jim_id']
                    },
                    '1': {
                        'name': 'date_closed',
                        'table_key': 'self',
                        'qualifier_name': 'between_dates',
                        'input_name0': '2018-01-01',
                        'input_name1': '2018-12-31'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Relate-Date2',
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

    it('should RLIs assigned user John and date closed in Jan 2018', function*() {
        let filter = {
            group_filters: [
                {'RevenueLineItems:assigned_user_link:user_name': this.johnUserName},
                {'self:date_closed': ['2018-01-01', '2018-01-31', 'month']}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(2);
            let rli1 = records.find((r) => r.id === this.rli1.id);
            let rli2 = records.find((r) => r.id === this.rli2.id);

            expect(rli1).to.exist;
            expect(rli2).to.exist;
        });
    });

    it('should RLIs assigned user Jane and date closed in Feb 2018', function*() {
        let filter = {
            group_filters: [
                {'RevenueLineItems:assigned_user_link:user_name': this.janeUserName},
                {'self:date_closed': ['2018-02-01', '2018-02-31', 'month']}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(2);
            let rli3 = records.find((r) => r.id === this.rli3.id);
            let rli4 = records.find((r) => r.id === this.rli4.id);

            expect(rli3).to.exist;
            expect(rli4).to.exist;
        });
    });
});
