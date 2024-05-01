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

describe('Reports.Records', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {attributes: {name: '_Test_Account1', industry: 'Banking', assigned_user_id: johnId}},
            {attributes: {name: '_Test_Account2', industry: 'Banking', assigned_user_id: johnId}},
            {attributes: {name: '_Test_Account3', industry: 'Apparel', assigned_user_id: johnId}}
        ];

        let createdRecords = yield Fixtures.create(records, {module: 'Accounts'});
        [this.account1, this.account2, this.account3] = createdRecords.Accounts;

        let content = {
            display_columns: [{name: 'name', label: 'Name', table_key: 'self'}],
            module: 'Accounts',
            group_defs: [{'table_key': 'self', 'name': 'industry', 'type': 'enum'}],
            summary_columns: [],
            report_name: 'test',
            do_round: 1,
            numerical_chart_column: '',
            numerical_chart_column_type: '',
            assigned_user_id: johnId,
            report_type: 'tabular',
            full_table_list: {self: {value: 'Accounts', module: 'Accounts', label: 'Accounts'}},
            filters_def: {
                Filter_1: {
                    operator: 'AND',
                    0: {
                        name: 'name',
                        table_key: 'self',
                        qualifier_name: 'starts_with',
                        input_name0: '_Test_Account',
                        input_name1: 'on'
                    }
                }
            },
            chart_type: 'none'
        };

        let report = {
            name: 'test',
            module: 'Accounts',
            report_type: 'tabular',
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

    it('should return filtered records whose field value matches given value', function*() {
        let filter = 'group_filters%5B0%5D%5Bindustry%5D=Banking';
        let url = 'Reports/' + this.reportId + '/records?view=list&fields=industry&' + filter;
        let response = yield Agent.as('John').get(url);
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(2);
            expect(records[0].industry).to.be.equal('Banking');
            expect(records[1].industry).to.be.equal('Banking');
        });
    });

    it('should paginate records', function*() {
        let filter = 'group_filters%5B0%5D%5Bindustry%5D=Banking';

        let url = 'Reports/' + this.reportId + '/records?view=list&fields=industry&offset=0&max_num=1&' + filter;
        let response = yield Agent.as('John').get(url);
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].industry).to.be.equal('Banking');
        });
    });

    it('should show previously favorited records', function*() {
        yield Agent.as('John').put('Accounts/' + this.account3.id + '/favorite');
        let filter = {
            group_filters: [{industry: 'Apparel'}],
            view: 'list',
            fields: 'my_favorite'
        };
        let url = 'Reports/' + this.reportId + '/records';
        let response = yield Agent.as('John').get(url, {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].id).to.be.equal(this.account3.id);
            expect(records[0].my_favorite).to.be.equal(true);
        });
    });
});
