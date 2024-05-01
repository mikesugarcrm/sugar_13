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

describe('Reports.RecordCount', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {attributes: {name: 'RecordCountAccount1', industry: 'Banking', assigned_user_id: johnId}},
            {attributes: {name: 'RecordCountAccount2', industry: 'Banking', assigned_user_id: johnId}},
            {attributes: {name: 'RecordCountAccount3', industry: 'Engineering', assigned_user_id: johnId}}
        ];

        records = yield Fixtures.create(records, {module: 'Accounts'});

        this.accounts = records.Accounts;

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
                        input_name0: 'RecordCount',
                        input_name1: 'on'
                    }
                }
            },
            chart_type: 'none'
        };

        let report = {
            name: 'test',
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

    it('should return record count for a filtered list', function*() {
        let filter = {
            group_filters: [{'self:industry': ['Banking']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/record_count', {qs: filter});
        expect(response).to.have.status(200);
        expect(response.body.record_count).to.equal(2);
    });

    it('should exclude deleted records', function*() {
        yield Agent.as('John').delete('Accounts/' + this.accounts[0].id);
        let filter = {
            group_filters: [{'self:industry': ['Banking']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/record_count', {qs: filter});
        expect(response).to.have.status(200);
        expect(response.body.record_count).to.equal(1);
    });
});
