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

describe('Reports.DataAccuracy.Varchar1', function() {
    before(function*() {
        let module = 'Accounts';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {attributes: {
                name: 'DataAccuracyAccount1',
                billing_address_street: '123 Anywhere Street',
                billing_address_city: 'San Jose',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                date_entered: '2017-08-11T13:01:45-0700',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DataAccuracyAccount2',
                billing_address_street: '456 Anywhere Street',
                billing_address_city: 'San Jose',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                date_entered: '2017-08-11T13:01:45-0700',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DataAccuracyAccount3',
                billing_address_street: '123 Anywhere Street',
                billing_address_city: 'Campbell',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                date_entered: '2017-08-11T13:01:45-0700',
                assigned_user_id: this.johnId
            }}
        ];

        records = yield Fixtures.create(records, {module: module});
        this.accounts = records.Accounts;

        // Report filtered on records starting with 'DataAccuracy'
        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'billing_address_city',
                label: 'Billing City',
                table_key: 'self',
                type: 'varchar'
            }, {
                name: 'date_entered',
                label: 'Month: Date Created',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self',
                type: 'datetime'
            }],
            summary_columns: [{
                name: 'billing_address_city',
                label: 'Billing City',
                table_key: 'self'
            }, {
                name: 'date_entered',
                label: 'Month: Date Created',
                column_function: 'month',
                qualifier: 'month',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                field_type: '',
                group_function: 'count',
                table_key: 'self'
            }],
            report_name: 'DataAccuracyTest-Account-Varchar1',
            do_round: 1,
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: 'Accounts'
                },
                'Accounts:assigned_user_link': {
                    name: 'Accounts  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'accounts_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Accounts:assigned_user_link'
                    },
                    dependents: ['Filter.1_table_filter_row_2'],
                    module: 'Users',
                    label: 'Assigned to User'
                }
            },
            filters_def: {
                Filter_1: {
                    operator: 'AND',
                    0: {
                        name: 'id',
                        table_key: 'Accounts:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Account-Varchar1',
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

    it('should only show accounts with specified billing city and created month', function*() {
        let filter = {
            group_filters: [
                {'self:billing_address_city': ['Campbell']},
                {'self:date_entered': ['2017-08-01', '2017-08-31']}
            ]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let account = records.find((r) => r.id === this.accounts[2].id);
            expect(account).to.exist;
        });
    });
});

describe('Reports.DataAccuracy.Varchar2', function() {
    before(function*() {
        let module = 'Accounts';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {attributes: {
                name: 'DataAccuracyAccount1',
                billing_address_street: '123 Anywhere Street',
                billing_address_city: 'San Jose',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                account_type: 'Investor',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DataAccuracyAccount2',
                billing_address_street: '456 Anywhere Street',
                billing_address_city: 'San Jose',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                account_type: 'Partner',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DataAccuracyAccount3',
                billing_address_street: '123 Anywhere Street',
                billing_address_city: 'Campbell',
                billing_address_state: 'CA',
                billing_address_postalcode: '23087',
                billing_address_country: 'USA',
                account_type: 'Partner',
                assigned_user_id: this.johnId
            }}
        ];

        records = yield Fixtures.create(records, {module: module});
        this.accounts = records.Accounts;

        // Report filtered on records starting with 'DataAccuracy'
        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'billing_address_city',
                label: 'Billing City',
                table_key: 'self',
                type: 'varchar'
            }, {
                name: 'account_type',
                label: 'Type',
                table_key: 'self',
                type: 'enum'
            }],
            summary_columns: [{
                name: 'billing_address_city',
                label: 'Billing City',
                table_key: 'self'
            }, {
                name: 'account_type',
                label: 'Type',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                field_type: '',
                group_function: 'count',
                table_key: 'self'
            }],
            report_name: 'DataAccuracyTest-Account-Varchar2',
            do_round: 1,
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: 'Accounts'
                },
                'Accounts:assigned_user_link': {
                    name: 'Accounts  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'accounts_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Accounts:assigned_user_link'
                    },
                    dependents: ['Filter.1_table_filter_row_2'],
                    module: 'Users',
                    label: 'Assigned to User'
                }
            },
            filters_def: {
                Filter_1: {
                    operator: 'AND',
                    0: {
                        name: 'billing_address_city',
                        table_key: 'self',
                        qualifier_name: 'starts_with',
                        input_name0: 'S',
                        input_name1: 'on'
                    },
                    1: {
                        name: 'billing_address_country',
                        table_key: 'self',
                        qualifier_name: 'starts_with',
                        input_name0: 'USA',
                        input_name1: 'on'
                    },
                    2: {
                        name: 'id',
                        table_key: 'Accounts:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Account-Varchar2',
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

    it('should only show accounts with specified billing city, country and type', function*() {
        let filter = {
            group_filters: [
                {'self:billing_address_city': ['San Jose']},
                {'self:account_type': ['Investor']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let account = records.find((r) => r.id === this.accounts[0].id);
            expect(account).to.exist;
        });
    });
});
