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
describe('Quotes.View.ConfigPanel', function() {
    var app;
    var context;
    var options;
    var view;
    var quotesFieldsMeta;
    var productsFieldsMeta;
    var productQuoteDataGroupListMeta;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('config-panel', 'view', 'base', 'help', 'Quotes');
        SugarTest.testMetadata.set();

        var meta = {
            label: 'testLabel',
            panels: [{
                fields: []
            }]
        };

        options = {
            context: context,
            meta: meta
        };

        productsFieldsMeta = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');
        quotesFieldsMeta = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');

        productQuoteDataGroupListMeta = {
            panels: [{
                name: 'products_quote_data_group_list',
                label: 'LBL_PRODUCTS_QUOTE_DATA_LIST',
                fields: [{
                    name: 'line_num',
                    label: null,
                    type: 'line-num',
                    readonly: true
                }, {
                    name: 'quantity',
                    label: 'LBL_QUANTITY',
                    type: 'float'
                }, {
                    name: 'product_template_name',
                    label: 'LBL_ITEM_NAME',
                    type: 'quote-data-relate',
                    required: true
                }, {
                    name: 'mft_part_num',
                    label: 'LBL_MFT_PART_NUM',
                    type: 'base'
                }, {
                    name: 'discount_price',
                    label: 'LBL_DISCOUNT_PRICE',
                    type: 'currency',
                    convertToBase: true,
                    showTransactionalAmount: true,
                    related_fields: [
                        'discount_price',
                        'currency_id',
                        'base_rate'
                    ]
                }, {
                    name: 'discount',
                    label: 'LBL_DISCOUNT_AMOUNT',
                    type: 'fieldset',
                    fields: [{
                        name: 'discount_amount',
                        label: 'LBL_DISCOUNT_AMOUNT',
                        type: 'discount',
                        convertToBase: true,
                        showTransactionalAmount: true
                    }, {
                        name: 'discount_select',
                        type: 'discount-select',
                        no_default_actions: true,
                        buttons: [{
                            name: 'select_discount_amount_button',
                            label: 'LBL_DISCOUNT_AMOUNT',
                            type: 'rowaction',
                            event: 'button:discount_select_change:click'
                        }, {
                            name: 'select_discount_percent_button',
                            label: 'LBL_DISCOUNT_PERCENT',
                            type: 'rowaction',
                            event: 'button:discount_select_change:click'
                        }]
                    }]
                }, {
                    name: 'total_amount',
                    label: 'LBL_LINE_ITEM_TOTAL',
                    type: 'currency',
                    showTransactionalAmount: true,
                    related_fields: [
                        'total_amount',
                        'currency_id',
                        'base_rate'
                    ]
                }]
            }]
        };

        sinon.stub(app.metadata, 'getModule')
            .withArgs('Products', 'fields').returns(productsFieldsMeta)
            .withArgs('Quotes', 'fields').returns(quotesFieldsMeta);

        sinon.stub(app.metadata, 'getView')
            .withArgs('Products', 'quote-data-group-list').returns(productQuoteDataGroupListMeta);

        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Quotes', 'config-panel', meta, context, true);
        view.eventViewName = 'config-columns';
    });

    afterEach(function() {
        quotesFieldsMeta = null;
        productQuoteDataGroupListMeta = null;
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.spy(view, '_getEventViewName');
            sinon.stub(view, 'getPanelFieldNamesList');
            sinon.stub(app.template, 'getView').returns(function() {});
        });

        it('should set eventViewName', function() {
            view.initialize(options);

            expect(view.eventViewName).toBe('config_panel');
        });

        it('should call _getEventViewName', function() {
            view.initialize(options);

            expect(view._getEventViewName).toHaveBeenCalled();
        });

        it('should call getPanelFieldNamesList', function() {
            view.initialize(options);

            expect(view.getPanelFieldNamesList).toHaveBeenCalled();
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'once');
            sinon.stub(view.context, 'on');

            view.bindDataChange();
        });

        it('should call once on context for change:dependentFields', function() {
            expect(view.context.once).toHaveBeenCalledWith('change:dependentFields');
        });

        it('should call on on context for change:dependentFields', function() {
            expect(view.context.on).toHaveBeenCalledWith('config:' + view.eventViewName + ':field:change');
        });
    });

    describe('_getEventViewName()', function() {
        it('should return config_panel', function() {
            expect(view._getEventViewName()).toBe('config_panel');
        });
    });

    describe('_onDependentFieldsChange()', function() {
        var fieldDeps;

        beforeEach(function() {
            fieldDeps = {
                test: 'hey'
            };
            view.context.set({
                dependentFields: fieldDeps,
                relatedFields: fieldDeps
            });
        });

        afterEach(function() {
            fieldDeps = null;
        });

        it('should set dependentFields', function() {
            expect(view.dependentFields).toEqual(fieldDeps);
        });

        it('should set relatedFields', function() {
            expect(view.relatedFields).toEqual(fieldDeps);
        });
    });

    describe('getPanelFieldNamesList()', function() {
        it('should set panelFieldNameList', function() {
            view.getPanelFieldNamesList();

            expect(view.panelFieldNameList).toEqual([]);
        });
    });

    describe('_buildPanelFieldsList()', function() {
        it('should return the expected list of fields', function() {
            var expected = [{
                name: 'name',
                label: 'LBL_QUOTE_NAME',
                type: 'tristate-checkbox',
                labelModule: 'Quotes',
                locked: undefined,
                related: undefined
            }, {
                name: 'quantity',
                label: 'LBL_QUANTITY',
                type: 'tristate-checkbox',
                labelModule: 'Quotes',
                locked: undefined,
                related: undefined
            }];
            sinon.stub(view, '_getPanelFields').callsFake(function() {
                return [{
                    name: 'name',
                    label: 'LBL_QUOTE_NAME'
                }, {
                    name: 'quantity',
                    label: 'LBL_QUANTITY'
                }];
            });
            var results = view._buildPanelFieldsList('Quotes', ['name', 'quantity']);

            expect(results).toEqual(expected);
        });
    });

    describe('_customFieldDef()', function() {
        it('should return the def passed in', function() {
            var def = {
                test: 'hey'
            };
            var results = view._customFieldDef(def);

            expect(results).toBe(def);
        });
    });

    describe('_customFieldsSorting()', function() {
        it('should return the arr passed in', function() {
            var arr = [{
                name: 'bbb'
            }, {
                name: 'aaa'
            }];
            var results = view._customFieldsSorting(arr);

            expect(results).toEqual([{
                name: 'aaa'
            }, {
                name: 'bbb'
            }]);
        });
    });

    describe('_customFieldsProcessing()', function() {
        it('should return the arr passed in', function() {
            var arr = ['test', 'hey'];
            var results = view._customFieldsProcessing(arr);

            expect(results).toBe(arr);
        });
    });
});
