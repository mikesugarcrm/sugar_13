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

describe('Reports.Chart', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {attributes: {industry: 'TEST', assigned_user_id: johnId}},
            {attributes: {industry: 'TEST', assigned_user_id: johnId}}
        ];

        yield Fixtures.create(records, {module: 'Accounts'});

        let content = {
            'display_columns': [],
            'module': 'Accounts',
            'group_defs': [{'name': 'industry', 'label': 'Industry', 'table_key': 'self', 'type': 'enum'}],
            'summary_columns': [
                 {'name': 'industry', 'label': 'Industry', 'table_key': 'self'},
                 {'name': 'count', 'label': 'Count', 'field_type': '', 'group_function': 'count', 'table_key': 'self'}
             ],
            'report_name': 'test',
            'chart_type': 'hBarF',
            'do_round': 1,
            'chart_description': '',
            'numerical_chart_column': 'self:count',
            'numerical_chart_column_type': '',
            'assigned_user_id': johnId,
            'report_type': 'summary',
            'full_table_list': {'self': {'value': 'Accounts', 'module': 'Accounts', 'label': 'Accounts'}},
            'filters_def': {
                'Filter_1': {
                    'operator': 'AND',
                    '0': {'name': 'industry', 'table_key': 'self', 'qualifier_name': 'one_of', 'input_name0': ['TEST']}
                }
            }
        };

        let report = {
            name: 'test',
            module: 'Accounts',
            report_type: 'summary',
            chart_type: 'hBarF',
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

    it('should return chart data for given report', function*() {
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/chart');
        expect(response).to.have.json('reportData', (reportData) => {
            expect(reportData.id).to.be.equal(this.reportId);
            expect(reportData.label).to.be.equal('test');
            expect(reportData.base_module).to.be.equal('Accounts');
        });
        expect(response).to.have.json('chartData', (chartData) => {
            expect(chartData.properties).to.have.length(1);
            expect(chartData.properties[0].type).to.be.equal('horizontal bar chart');
            expect(chartData.values).to.have.length(1);
            expect(chartData.values[0].label).to.have.length(1);
        });
    });
});
