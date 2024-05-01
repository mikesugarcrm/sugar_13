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

describe('Reports.DataAccuracy.Date.Opp', function() {
    before(function*() {
        let module = 'Opportunities';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        this.now = new Date().valueOf();

        let records = [
            // close date 2017-08, type New Business
            {
                attributes: {
                    name: this.now + '_Opp1',
                    opportunity_type: 'New Business',
                    date_closed: '2017-08-31',
                    date_closed_timestamp: 1504162800,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    revenuelineitems: {
                        create: [{
                            deleted: false,
                            id: this.now + '_RLI1',
                            assigned_user_id: this.johnId,
                            name: this.now + '_RLI1',
                            date_closed: '2017-08-31',
                            date_closed_timestamp: 1504162800,
                            likely_case: '100.000000'
                        }]
                    }
                }
            },
            // close date 2017-08, type Existing Business
            {
                attributes: {
                    name: this.now + '_Opp2',
                    opportunity_type: 'Existing Business',
                    date_closed: '2017-08-31',
                    date_closed_timestamp: 1504162800,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    revenuelineitems: {
                        create: [{
                            deleted: false,
                            id: this.now + '_RLI2',
                            assigned_user_id: this.johnId,
                            name: this.now + '_RLI2',
                            date_closed: '2017-08-31',
                            date_closed_timestamp: 1504162800,
                            likely_case: '100.000000'
                        }]
                    }
                }
            },
            // close date 2017-09, type New Business
            {
                attributes: {
                    name: this.now + '_Opp3',
                    opportunity_type: 'New Business',
                    date_closed: '2017-09-01',
                    date_closed_timestamp: 1504249200,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    revenuelineitems: {
                        create: [{
                            deleted: false,
                            id: this.now + '_RLI3',
                            assigned_user_id: this.johnId,
                            name: this.now + '_RLI3',
                            date_closed: '2017-09-01',
                            date_closed_timestamp: 1504249200,
                            likely_case: '100.000000'
                        }]
                    }
                }
            }
        ];
        let createdRecords = yield Fixtures.create(records, {module: module});
        [this.opp1, this.opp2, this.opp3] = createdRecords.Opportunities;

        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'date_closed',
                label: 'Month: Expected Close Date',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self',
                type: 'date'
            }, {
                name: 'opportunity_type',
                label: 'Type',
                table_key: 'self',
                type: 'enum'
            }],
            summary_columns: [{
                name: 'date_closed',
                label: 'Month: Expected Close Date',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self',
                type: 'date'
            }, {
                name: 'opportunity_type',
                label: 'Type',
                table_key: 'self',
                type: 'enum'
            }, {
                name: 'count',
                label: 'Count',
                table_key: 'self',
                field_type: '',
                group_function: 'count'
            }],
            report_name: 'DataAccuracyTest-Date-Enum1',
            do_round: 1,
            chart_description: '',
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: module
                },
                'Opportunities:assigned_user_link': {
                    name: 'Opportunities  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'opportunities_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Opportunities:assigned_user_link'
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
                        table_key: 'Opportunities:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Date-Enum1',
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

    it('should only show opps where type is New Business and close month is 2017-08', function*() {
        let filter = {
            group_filters: [
                {'self:date_closed': ['2017-08-01', '2017-08-31', 'month']},
                 {'self:opportunity_type': ['New Business']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let record = records.find((r) => r.id === this.opp1.id);
            expect(record).to.exist;
        });
    });
});

describe('Reports.DataAccuracy.Date.RLI', function() {
    before(function*() {
        let module = 'RevenueLineItems';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        this.now = new Date().valueOf();

        let records = [
            {attributes:
                {
                    name: this.now + '_RLI1',
                    date_closed: '2017-08-31',
                    date_closed_timestamp: 1504162800,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    product_type: 'New Business',
                    likely_case: '100.000000'
                }
            },
            {attributes:
                {
                    name: this.now + '_RLI2',
                    opportunity_type: 'Existing Business',
                    date_closed: '2017-09-01',
                    date_closed_timestamp: 1504249200,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    product_type: 'Existing Business',
                    likely_case: '100.000000'
                }
            },
            {attributes:
                {
                    name: this.now + '_RLI3',
                    opportunity_type: 'New Business',
                    date_closed: '2017-09-01',
                    date_closed_timestamp: 1504249200,
                    deleted: false,
                    assigned_user_id: this.johnId,
                    product_type: 'New Business',
                    likely_case: '100.000000'
                }
            }
        ];
        let createdRecords = yield Fixtures.create(records, {module: module});
        [this.rli1, this.rli2, this.rli3] = createdRecords.RevenueLineItems;

        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'date_closed',
                label: 'Month: Expected Close Date',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self',
                type: 'date'
            }, {
                name: 'product_type',
                label: 'Type',
                table_key: 'self',
                type: 'enum'
            }],
            summary_columns: [{
                name: 'date_closed',
                label: 'Month: Expected Close Date',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self',
                type: 'date'
            }, {
                name: 'product_type',
                label: 'Type',
                table_key: 'self',
                type: 'enum'
            }, {
                name: 'likely_case',
                label: 'AVG: Likely',
                table_key: 'self',
                field_type: 'currency',
                group_function: 'avg'
            }],
            report_name: 'DataAccuracyTest-Date-Enum2',
            do_round: 1,
            chart_description: '',
            numerical_chart_column: 'self:likely_case:avg',
            numerical_chart_column_type: 'currency',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: module
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
                        name: 'date_closed',
                        table_key: 'self',
                        qualifier_name: 'after',
                        input_name0: '2017-08-31',
                        input_name1: 'on'
                    },
                    1: {
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
            name: 'DataAccuracyTest-Date-Enum2',
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

    it('should only show opps where type is New Business and close date in 2017-09', function*() {
        let filter = {
            group_filters: [
                {'self:date_closed': ['2017-09-01', '2017-09-30', 'month']},
                {'self:product_type': ['New Business']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let record = records.find((r) => r.id === this.rli3.id);
            expect(record).to.exist;
        });
    });
});
