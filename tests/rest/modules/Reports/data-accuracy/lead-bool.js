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

describe('Reports.DataAccuracy.Bool', function() {
    before(function*() {
        let module = 'Leads';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {attributes: {
                last_name: 'DataAccuracyLead1',
                converted: 0,
                lead_source: 'Self Generated',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead2',
                converted: 0,
                lead_source: 'Cold Call',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead3',
                converted: 1,
                lead_source: 'Self Generated',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead4',
                converted: 1,
                lead_source: 'Cold Call',
                assigned_user_id: this.johnId
            }}
        ];

        let leads = yield Fixtures.create(records, {module: module});
        [this.lead1, this.lead2, this.lead3, this.lead4] = leads.Leads;

        // Report filtered on records starting with 'DataAccuracy'
        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'lead_source',
                label: 'Lead Source',
                table_key: 'self',
                type: 'enum',
                force_label: 'Lead Source'
            }, {
                name: 'converted',
                label: 'Converted',
                table_key: 'self',
                type: 'bool',
                force_label: 'Converted'
            }],
            summary_columns: [{
                name: 'lead_source',
                label: 'Lead Source',
                table_key: 'self'
            }, {
                name: 'converted',
                label: 'Converted',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                table_key: 'self',
                field_type: '',
                group_function: 'count'
            }],
            report_name: 'DataAccuracyTest-Bool-Enum1',
            do_round: 1,
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: 'Leads'
                },
                'Leads:assigned_user_link': {
                    name: 'Leads  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'leads_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Leads:assigned_user_link'
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
                        table_key: 'Leads:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Bool-Enum1',
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

    it('should only show converted, Cold Call leads', function*() {
        let filter = {
            group_filters: [{'self:converted': ['1']}, {'self:lead_source': ['Cold Call']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let lead = records.find((r) => r.id === this.lead4.id);
            expect(lead).to.exist;
        });
    });

    it('should only show not converted, Cold Call leads', function*() {
        let filter = {
            group_filters: [{'self:converted': ['0']}, {'self:lead_source': ['Cold Call']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let lead = records.find((r) => r.id === this.lead2.id);
            expect(lead).to.exist;
        });
    });

});

describe('Reports.DataAccuracy.Bool.Runtime', function() {
    before(function*() {
        let module = 'Leads';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {attributes: {
                last_name: 'DataAccuracyLead1',
                converted: 0,
                lead_source: 'Self Generated',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead2',
                converted: 0,
                lead_source: 'Cold Call',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead3',
                converted: 1,
                lead_source: 'Self Generated',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'DataAccuracyLead4',
                converted: 1,
                lead_source: 'Cold Call',
                assigned_user_id: this.johnId
            }}
        ];

        let leads = yield Fixtures.create(records, {module: module});
        [this.lead1, this.lead2, this.lead3, this.lead4] = leads.Leads;

        // Report filtered on records starting with 'DataAccuracy'
        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'lead_source',
                label: 'Lead Source',
                table_key: 'self',
                type: 'enum',
                force_label: 'Lead Source'
            }, {
                name: 'converted',
                label: 'Converted',
                table_key: 'self',
                type: 'bool',
                force_label: 'Converted'
            }],
            summary_columns: [{
                name: 'lead_source',
                label: 'Lead Source',
                table_key: 'self'
            }, {
                name: 'converted',
                label: 'Converted',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                table_key: 'self',
                field_type: '',
                group_function: 'count'
            }],
            report_name: 'DataAccuracyTest-Bool-Enum2',
            do_round: 1,
            numerical_chart_column: 'self:count',
            numerical_chart_column_type: '',
            assigned_user_id: this.johnId,
            report_type: 'summary',
            full_table_list: {
                self: {
                    value: module,
                    module: module,
                    label: 'Leads'
                },
                'Leads:assigned_user_link': {
                    name: 'Leads  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'leads_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Leads:assigned_user_link'
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
                        name: 'converted',
                        table_key: 'self',
                        qualifier_name: 'not_empty',
                        input_name0: 'not_empty',
                        input_name1: 'on'
                    },
                    1: {
                        name: 'lead_source',
                        table_key: 'self',
                        qualifier_name: 'is_not',
                        runtime: 1,
                        input_name0: ['Cold Call']
                    },
                    2: {
                        name: 'id',
                        table_key: 'Leads:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Bool-Enum2',
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

    it('should only show converted, non Cold Call leads', function*() {
        let filter = {
            group_filters: [{'self:converted': ['1']}, {'self:lead_source': ['Self Generated']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let lead = records.find((r) => r.id === this.lead3.id);
            expect(lead).to.exist;
        });
    });

    it('should only show not converted, non Cold Call leads', function*() {
        let filter = {
            group_filters: [{'self:converted': ['0']}, {'self:lead_source': ['Self Generated']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let lead = records.find((r) => r.id === this.lead1.id);
            expect(lead).to.exist;
        });
    });

});
