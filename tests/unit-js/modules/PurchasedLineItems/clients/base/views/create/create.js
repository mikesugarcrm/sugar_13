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
describe('PurchasedLineItems.Base.View.Create', function() {
    var app;
    var view;
    var options;

    beforeEach(function() {
        app = SugarTest.app;

        options = {
            def: {view: 'create'},
            module: 'PurchasedLineItems',
            name: 'create',
        };

        SugarTest.loadFile('../modules/PurchasedLineItems/clients/base/plugins',
            'PurchaseAndServiceChangeHandler', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        view = SugarTest.createView('base', 'PurchasedLineItems', 'create',
            {}, null, true);
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.stub();
            view.model = {
                fields: {
                    service_start_date: {name: 'service_start_date'},
                    service: {name: 'service'},
                    product_type_name: {name: 'product_type_name'},
                    category_name: {name: 'category_name'}
                },
                setDefault: stub,
                get: function() { return true; },
                off: stub
            };

            sinon.stub(view, 'handleServiceChange');
            sinon.stub(view, 'bindDataChange');
            sinon.stub(view, 'handlePurchaseChange');
        });

        afterEach(function() {
            stub = null;
            view.model = null;
        });

        it('should add PurchaseAndServiceChangeHandler to plugins', function() {
            view.plugins = [];
            view.initialize(options);

            expect(view.plugins).toEqual(['PurchaseAndServiceChangeHandler']);
        });

        it('should call _super initialize method', function() {
            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize');
        });

        describe('when the create view is opened from Purchase module subpanel', function() {
            var parent;
            beforeEach(function() {
                view.context = {
                    parent: {
                        get: function() {
                            return 'Purchases';
                        },
                    },
                    off: sinon.stub()
                };
            });

            it('should call handlePurchaseChange', function() {
                sinon.stub(view.model, 'get')
                    .withArgs('purchase_name').returns('testName')
                    .withArgs('product_template_id').returns('testId');
                view.initialize(options);

                expect(view.handlePurchaseChange).toHaveBeenCalledWith();
            });

            it('should not call handlePurchaseChange when purchase_name is empty', function() {
                sinon.stub(view.model, 'get')
                    .withArgs('purchase_name').returns('')
                    .withArgs('product_template_id').returns('testId');
                view.initialize(options);

                expect(view.handlePurchaseChange).not.toHaveBeenCalledWith();
            });

            it('should not call handlePurchaseChange when product_template_id is empty', function() {
                sinon.stub(view.model, 'get')
                    .withArgs('purchase_name').returns('testName')
                    .withArgs('product_template_id').returns('');
                view.initialize(options);

                expect(view.handlePurchaseChange).not.toHaveBeenCalledWith();
            });
        });

        it('should call view.model.setDefault with service_duration_value', function() {
            view.initialize(options);

            expect(view.model.setDefault).toHaveBeenCalledWith('service_duration_value', 1);
        });

        it('should set display_default for service_start_date', function() {
            view.initialize(options);

            expect(view.model.fields.service_start_date).toEqual({
                name: 'service_start_date',
                display_default: 'now',
            });
        });

        it('should call handleServiceChange method', function() {
            view.initialize(options);

            expect(view.handleServiceChange).toHaveBeenCalledWith();
        });

        it('should call bindDataChange method', function() {
            view.initialize(options);

            expect(view.bindDataChange).toHaveBeenCalledWith();
        });
    });
})
