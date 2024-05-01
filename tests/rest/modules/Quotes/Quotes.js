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
const expect = chakram.expect;

describe('Quotes', function() {
    before(function*() {
        this.timeout(30000);
        this.adminUser = Agent.as(Agent.ADMIN);

        let Account = {
            module: 'Accounts',
            attributes: {
                name: 'Acc1',
                account_type: 'Analyst',
                assigned_user_id: this.adminUser.id
            }
        };

        this.records = yield Fixtures.create([Account]);

    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should be able to save a quote with a QLI', function*() {
        let response = yield this.adminUser.post('Quotes',
            {
                'deleted': false,
                'taxrate_value': 0,
                'show_line_nums': true,
                'shipping': '0',
                'deal_tot_discount_percentage': 0,
                'tax': '0.000000',
                'currency_id': '-99',
                'assigned_user_id': '1',
                'product_bundles': {
                    'create': [{
                        '_module': 'ProductBundles',
                        '_action': 'create',
                        '_link': 'product_bundles',
                        'default_group': true,
                        'currency_id': '-99',
                        'product_bundle_notes': [],
                        'position': 0,
                        'products': {
                            'create': [{
                                'deleted': false,
                                'subtotal': '0.000000',
                                'total_amount': '0.000000',
                                'discount_price': '0',
                                'discount_amount': '0',
                                'discount_amount_signed': '0',
                                'discount_select': true,
                                'status': '',
                                'tax_class': 'Taxable',
                                'quantity': 1,
                                'currency_id': '-99',
                                '_module': 'Products',
                                '_link': 'products',
                                'position': 0,
                                'base_rate': '1.000000',
                                'line_num': 1,
                                'discount_rate_percent': 0,
                                'discount_amount_usdollar': '0.000000',
                                'deal_calc': '0.000000',
                                'deal_calc_usdollar': '0.000000',
                                'discount_usdollar': '0.000000',
                                'product_template_id': '',
                                'product_template_name': 'aaa',
                                'name': 'qli'
                            }]
                        },
                        'deleted': false,
                        'deal_tot': '0.000000',
                        '_products-rel_exp_values': {
                            'rollupSum': {
                                'deal_calc': '0.000000',
                                'subtotal': '0.000000',
                                'deal_calc_values': {'c883': '0.000000'},
                                'subtotal_values': {'c883': '0.000000'}
                            },
                            'rollupConditionalSum': {
                                'total_amount_values': {'c883': '0.000000'},
                                'total_amount': '0.000000'
                            }
                        },
                        'subtotal': '0.000000',
                        'new_sub': '0.000000',
                        'total': '0.000000',
                        'taxable_subtotal': '0.000000'
                    }]
                },
                'subtotal': '0.000000',
                'deal_tot': '0.000000',
                'new_sub': '0.000000',
                'taxable_subtotal': '0.000000',
                'total': '0.000000',
                'base_rate': '1.000000',
                'subtotal_usdollar': '0.000000',
                'shipping_usdollar': '0.000000',
                'deal_tot_usdollar': '0.000000',
                'new_sub_usdollar': '0.000000',
                'tax_usdollar': '0.000000',
                'total_usdollar': '0.000000',
                'quote_stage': 'Draft',
                'payment_terms': '',
                'order_stage': 'Pending',
                'team_name': [{
                    'id': '1',
                    'display_name': 'Global',
                    'name': 'Global',
                    'name_2': '',
                    'primary': true,
                    'selected': false
                }],
                'name': 'qqq',
                'date_quote_expected_closed': '2017-06-16',
                'billing_account_id': this.records.Accounts[0].id,
                'shipping_account_id': this.records.Accounts[0].id,
                'billing_address_street': '',
                'billing_address_city': '',
                'billing_address_state': '',
                'billing_address_postalcode': '',
                'billing_address_country': '',
                'shipping_address_street': '',
                'shipping_address_city': '',
                'shipping_address_state': '',
                'shipping_address_postalcode': '',
                'shipping_address_country': ''
            }
        );
        expect(response).to.have.status(200);
    });

    it('should be able to save a quote with more than 30 QLIs', function*() {
        let productArray = [];

        for (var i = 0; i <= 30; i++) {
            productArray.push(
                {
                    'deleted': false,
                    'subtotal': '0.000000',
                    'total_amount': '0.000000',
                    'discount_price': '0',
                    'discount_amount': '0',
                    'discount_amount_signed': '0',
                    'discount_select': true,
                    'status': '',
                    'tax_class': 'Taxable',
                    'quantity': 1,
                    'currency_id': '-99',
                    '_module': 'Products',
                    '_link': 'products',
                    'position': 0,
                    'base_rate': '1.000000',
                    'line_num': 1,
                    'discount_rate_percent': 0,
                    'discount_amount_usdollar': '0.000000',
                    'deal_calc': '0.000000',
                    'deal_calc_usdollar': '0.000000',
                    'discount_usdollar': '0.000000',
                    'product_template_id': '',
                    'product_template_name': 'aaa',
                    'name': 'qli' + i
                }
            );
        }

        let response = yield this.adminUser.post('Quotes',
            {
                'deleted': false,
                'taxrate_value': 0,
                'show_line_nums': true,
                'shipping': '0',
                'deal_tot_discount_percentage': 0,
                'tax': '0.000000',
                'currency_id': '-99',
                'assigned_user_id': '1',
                'product_bundles': {
                    'create': [{
                        '_module': 'ProductBundles',
                        '_action': 'create',
                        '_link': 'product_bundles',
                        'default_group': true,
                        'currency_id': '-99',
                        'product_bundle_notes': [],
                        'position': 0,
                        'products': {
                            'create': productArray
                        },
                        'deleted': false,
                        'deal_tot': '0.000000',
                        '_products-rel_exp_values': {
                            'rollupSum': {
                                'deal_calc': '0.000000',
                                'subtotal': '0.000000',
                                'deal_calc_values': {'c883': '0.000000'},
                                'subtotal_values': {'c883': '0.000000'}
                            },
                            'rollupConditionalSum': {
                                'total_amount_values': {'c883': '0.000000'},
                                'total_amount': '0.000000'
                            }
                        },
                        'subtotal': '0.000000',
                        'new_sub': '0.000000',
                        'total': '0.000000',
                        'taxable_subtotal': '0.000000'
                    }]
                },
                'subtotal': '0.000000',
                'deal_tot': '0.000000',
                'new_sub': '0.000000',
                'taxable_subtotal': '0.000000',
                'total': '0.000000',
                'base_rate': '1.000000',
                'subtotal_usdollar': '0.000000',
                'shipping_usdollar': '0.000000',
                'deal_tot_usdollar': '0.000000',
                'new_sub_usdollar': '0.000000',
                'tax_usdollar': '0.000000',
                'total_usdollar': '0.000000',
                'quote_stage': 'Draft',
                'payment_terms': '',
                'order_stage': 'Pending',
                'team_name': [{
                    'id': '1',
                    'display_name': 'Global',
                    'name': 'Global',
                    'name_2': '',
                    'primary': true,
                    'selected': false
                }],
                'name': 'qqq',
                'date_quote_expected_closed': '2017-06-16',
                'billing_account_id': this.records.Accounts[0].id,
                'shipping_account_id': this.records.Accounts[0].id,
                'billing_address_street': '',
                'billing_address_city': '',
                'billing_address_state': '',
                'billing_address_postalcode': '',
                'billing_address_country': '',
                'shipping_address_street': '',
                'shipping_address_city': '',
                'shipping_address_state': '',
                'shipping_address_postalcode': '',
                'shipping_address_country': ''
            }
        );
        expect(response).to.have.status(200);
    });
});
