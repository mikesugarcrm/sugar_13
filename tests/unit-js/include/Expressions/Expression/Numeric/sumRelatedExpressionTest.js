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
describe('SumRelatedExpression Function', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model =  isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || new app.Context({
            url: 'someurl',
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent('View', {
            context: context,
            type: 'edit',
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    var initializeContexts = function() {
        getSLContext(model).initialize(meta.modules.Quotes.dependencies);
        getSLContext(model.get('bundles')).initialize(meta.modules.ProductBundles.dependencies);
        model.get('bundles').each(function(bundle) {
            var prods = bundle.getRelatedCollection('products');
            getSLContext(prods).initialize(meta.modules.Products.dependencies || []);
        });
    };

    var seedServer = function() {
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith(
            'POST',
            /.*\/rest\/v10\/ExpressionEngine\/quote_id_1\/related.*/, //new RegExp('.*/rest/v10/ExpressionEngine/.*'),
            [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify({
                    'product_bundles': {
                        'rollupSum': {
                            'new_sub': '4050.000000',
                            'new_sub_values': {'bundle_id_1': '4050.000000'}
                        },
                        'rollupCurrencySum': {
                            'new_sub': '4050.000000',
                            'new_sub_values': {'bundle_id_1': '4050.000000'}
                        }
                    }
                })
            ]
        );
        SugarTest.server.respondWith(
            'POST',
            /.*\/rest\/v10\/ExpressionEngine\/bundle_id_1\/related.*/, //new RegExp('.*/rest/v10/ExpressionEngine/.*'),
            [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify({
                    'products': {
                        'rollupCurrencySum': {
                            'deal_calc': '0.000000',
                            'deal_calc_values': {
                                'prod_tk_1000': '0.000000',
                                'prod_tk_m30': '0.000000'
                            },
                            'subtotal': '4050.000000',
                            'subtotal_values': {
                                'prod_tk_1000': '800.000000',
                                'prod_tk_m30': '3250.000000'
                            },
                        },
                        'rollupSum': {
                            'deal_calc': '0.000000',
                            'deal_calc_values': {
                                'prod_tk_1000': '0.000000',
                                'prod_tk_m30': '0.000000'
                            },
                            'subtotal': '4050.000000',
                            'subtotal_values': {
                                'prod_tk_1000': '800.000000',
                                'prod_tk_m30': '3250.000000'
                            },
                        }
                    }
                })
            ]
        );
    };

    beforeEach(function() {
        oldApp = App;
        App = App || SUGAR.App;
        sinonSandbox = sinon.createSandbox();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('nested-collections-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        sinon.stub(app.currency, 'convertAmount').callsFake(function(val, from, to) {
            from = !from || from == '-99' ? 1 : parseFloat(from);
            to = !to || to == '-99' ? 1 : parseFloat(to);

            return (parseFloat(val) / from) * to;
        });
        model = dm.createBean('Quotes', SugarTest.loadFixture('quote'));
        seedServer();
        initializeContexts();
    });

    afterEach(function() {
        App = oldApp;
        app.currency.convertAmount.restore();
        sinonSandbox.restore();
    });

    describe('Related Collection Handling', function() {
        it('should return the set of backing values for a rollup calculation', function() {
            SugarTest.server.respond();
            expect(model.get('bundles').length).toBe(1);
            model.get('bundles').add(dm.createBean('ProductBundles', {
                'id': 'pb_123',
                'name': 'test',
                'new_sub': 12.5
            }));
            expect(model.get('bundles').length).toBe(2);
            expect(parseFloat(model.get('new_sub'))).toBe(4062.5);
        });
    });
});
