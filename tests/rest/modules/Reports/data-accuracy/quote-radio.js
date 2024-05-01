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

describe('Reports.DataAccuracy.Radio', function() {
    before(function*() {
        let module = 'Quotes';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {attributes: {
                name: 'DataAccuracyQuote1',
                quote_type: 'Quotes',
                quote_stage: 'Draft',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DataAccuracyQuote2',
                quote_type: 'Quotes',
                quote_stage: 'Negotiation',
                assigned_user_id: this.johnId
            }}
        ];

        let createdRecords = yield Fixtures.create(records, {module: module});
        [this.rec1, this.rec2] = createdRecords.Quotes;

        // Report filtered on records starting with 'DataAccuracy'
        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'quote_type',
                label: 'Quote Type',
                table_key: 'self',
                type: 'radioenum',
                force_label: 'Quote Type'
            }, {
                name: 'quote_stage',
                label: 'Quote Stage',
                table_key: 'self',
                type: 'enum',
                force_label: 'Quote Stage'
            }],
            summary_columns: [{
                name: 'quote_type',
                label: 'Quote Type',
                table_key: 'self'
            }, {
                name: 'quote_stage',
                label: 'Quote Stage',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                table_key: 'self',
                field_type: '',
                group_function: 'count'
            }],
            report_name: 'DataAccuracyTest-Radio-Enum1',
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
                    label: 'Quotes'
                },
                'Quotes:assigned_user_link': {
                    name: 'Quotes  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'quotes_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Quotes:assigned_user_link'
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
                        table_key: 'Quotes:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Radio-Enum1',
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

    it('should only show quotes where quote_type is Quote and quote_stage is Draft', function*() {
        let filter = {
            group_filters: [{'self:quote_type': ['Quotes']}, {'self:quote_stage': ['Draft']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let quote = records.find((r) => r.id === this.rec1.id);
            expect(quote).to.exist;
        });
    });
});

describe('Reports.DataAccuracy.Radio.Runtime', function() {
    before(function*() {
        let module = 'Quotes';

        let users = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        users = yield Fixtures.create(users, {module: 'Users'});

        this.johnId = users.Users[0].id;

        let records = [
            {
                module: module,
                attributes: {
                    'deleted': false,
                    'quote_type': 'Quotes',
                    'quote_stage': 'Draft',
                    'deal_tot_discount_percentage': 5,
                    'currency_id': '-99',
                    'assigned_user_id': this.johnId,
                    'product_bundles': {
                        'create': [{
                            '_module': 'ProductBundles',
                            '_action': 'create',
                            '_link': 'product_bundles',
                            'default_group': true,
                            'currency_id': '-99',
                            'products': {
                                'create': [{
                                    'deleted': false,
                                    'discount_price': '150.000000',
                                    'discount_amount': '5',
                                    'discount_amount_signed': '5',
                                    'discount_amount_usdollar': '5.000000',
                                    'discount_select': true,
                                    'name': 'DataAccuracyQLI1'
                                }]
                            },
                            'deleted': false,
                            'deal_tot': '0.000000',
                            'subtotal': '150.000000',
                        }]
                    },
                    'name': 'DataAccuracyQuote1',
                    'assigned_user_id': this.johnId
                }
            },
            {
                module: module,
                attributes: {
                    'deleted': false,
                    'quote_type': 'Orders',
                    'quote_stage': 'Draft',
                    'deal_tot_discount_percentage': 5,
                    'currency_id': '-99',
                    'assigned_user_id': this.johnId,
                    'product_bundles': {
                        'create': [{
                            '_module': 'ProductBundles',
                            '_action': 'create',
                            '_link': 'product_bundles',
                            'default_group': true,
                            'currency_id': '-99',
                            'products': {
                                'create': [{
                                    'deleted': false,
                                    'discount_price': '150.000000',
                                    'discount_amount': '5',
                                    'discount_amount_signed': '5',
                                    'discount_amount_usdollar': '5.000000',
                                    'discount_select': true,
                                    'name': 'DataAccuracyQLI2'
                                }]
                            },
                            'deleted': false,
                            'deal_tot': '0.000000',
                            'subtotal': '150.000000',
                        }]
                    },
                    'name': 'DataAccuracyQuote2',
                    'assigned_user_id': this.johnId
                }
            },
            {
                module: module,
                attributes: {
                    'deleted': false,
                    'quote_type': 'Quotes',
                    'quote_stage': 'Draft',
                    'deal_tot_discount_percentage': 10,
                    'currency_id': '-99',
                    'assigned_user_id': this.johnId,
                    'product_bundles': {
                        'create': [{
                            '_module': 'ProductBundles',
                            '_action': 'create',
                            '_link': 'product_bundles',
                            'default_group': true,
                            'currency_id': '-99',
                            'products': {
                                'create': [{
                                    'deleted': false,
                                    'discount_price': '150.000000',
                                    'discount_amount': '10',
                                    'discount_amount_signed': '10',
                                    'discount_amount_usdollar': '10.000000',
                                    'discount_select': true,
                                    'name': 'DataAccuracyQLI3'
                                }]
                            },
                            'deleted': false,
                            'deal_tot': '0.000000',
                            'subtotal': '150.000000',
                        }]
                    },
                    'name': 'DataAccuracyQuote3',
                    'assigned_user_id': this.johnId
                }
            },
        ];

        let createdRecords = yield Fixtures.create(records, {module: module});
        [this.rec1, this.rec2, this.rec3] = createdRecords.Quotes;

        let content = {
            display_columns: [],
            module: module,
            group_defs: [{
                name: 'quote_type',
                label: 'Quote Type',
                table_key: 'self',
                type: 'radioenum',
                force_label: 'Quote Type'
            }, {
                name: 'quote_stage',
                label: 'Quote Stage',
                table_key: 'self',
                type: 'enum',
                force_label: 'Quote Stage'
            }],
            summary_columns: [{
                name: 'quote_type',
                label: 'Quote Type',
                table_key: 'self'
            }, {
                name: 'quote_stage',
                label: 'Quote Stage',
                table_key: 'self'
            }, {
                name: 'count',
                label: 'Count',
                table_key: 'self',
                field_type: '',
                group_function: 'count'
            }],
            report_name: 'DataAccuracyTest-Radio-Enum1',
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
                    label: 'Quotes'
                },
                'Quotes:assigned_user_link': {
                    name: 'Quotes  \u003E  Assigned to User',
                    parent: 'self',
                    link_def: {
                        name: 'assigned_user_link',
                        relationship_name: 'quotes_assigned_user',
                        bean_is_lhs: false,
                        link_type: 'one',
                        label: 'Assigned to User',
                        module: 'Users',
                        table_key: 'Quotes:assigned_user_link'
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
                        name: 'quote_type',
                        table_key: 'self',
                        qualifier_name: 'is_not',
                        runtime: 1,
                        input_name0: [
                            'Orders'
                        ]
                    },
                    1: {
                        name: 'deal_tot_discount_percentage',
                        table_key: 'self',
                        qualifier_name: 'less',
                        input_name0: '10',
                        input_name1: 'on'
                    },
                    2: {
                        name: 'id',
                        table_key: 'Quotes:assigned_user_link',
                        qualifier_name: 'is',
                        input_name0: this.johnId,
                        input_name1: 'Smith'
                    }
                }
            },
            chart_type: 'vBarF'
        };

        let report = {
            name: 'DataAccuracyTest-Radio-Enum1',
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

    it('should only show quotes where quote_type is Quote and quote_stage is Draft', function*() {
        let filter = {
            group_filters: [{'self:quote_type': ['Quotes']}, {'self:quote_stage': ['Draft']}]
        };
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records',  {qs: filter});
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            let quote = records.find((r) => r.id === this.rec1.id);
            expect(quote).to.exist;
        });
    });
});
