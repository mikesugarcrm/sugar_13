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
describe('Quotes.View.ConfigSummary', function() {
    var app;
    var context;
    var options;
    var view;
    var quotesFieldsMeta;
    var quoteDataHeaderMeta;
    var layout;

    beforeEach(function() {
        var meta;
        var depFields;
        var relFields;

        app = SugarTest.app;
        context = app.context.getContext();

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('config-panel', 'view', 'base', 'help', 'Quotes');
        SugarTest.testMetadata.set();

        meta = {
            label: 'testLabel',
            panels: [
                {
                    fields: []
                }
            ]
        };

        options = {
            context: context,
            meta: meta
        };

        depFields = SugarTest.loadFixture('dependent-fields', '../tests/modules/Quotes/fixtures');
        relFields = SugarTest.loadFixture('related-fields', '../tests/modules/Quotes/fixtures');
        quotesFieldsMeta = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');

        context.set({
            dependentFields: depFields,
            quotesFields: quotesFieldsMeta,
            relatedFields: relFields,
            model: new Backbone.Model()
        });

        quoteDataHeaderMeta = {
            panels: [{
                name: 'panel_quote_data_grand_totals_header',
                label: 'LBL_QUOTE_DATA_GRAND_TOTALS_HEADER',
                fields: [{
                    name: 'deal_tot',
                    label: 'LBL_LIST_DEAL_TOT',
                    css_class: 'quote-totals-row-item',
                    related_fields: ['deal_tot_discount_percentage']
                }, {
                    name: 'new_sub',
                    label: 'LBL_NEW_SUB',
                    css_class: 'quote-totals-row-item'
                }, {
                    name: 'tax',
                    label: 'LBL_TAX_TOTAL',
                    css_class: 'quote-totals-row-item'
                }, {
                    name: 'shipping',
                    label: 'LBL_SHIPPING',
                    css_class: 'quote-totals-row-item'
                }, {
                    name: 'total',
                    label: 'LBL_LIST_GRAND_TOTAL',
                    css_class: 'quote-totals-row-item'
                }]
            }]
        };

        sinon.stub(app.metadata, 'getModule')
            .withArgs('Quotes', 'fields').returns(quotesFieldsMeta);

        sinon.stub(app.metadata, 'getView')
            .withArgs('Quotes', 'quote-data-grand-totals-header').returns(quoteDataHeaderMeta);

        SugarTest.loadComponent('base', 'view', 'config-panel');
        SugarTest.loadComponent('base', 'view', 'config-panel', 'Quotes');
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer-content');
        view = SugarTest.createView('base', 'Quotes', 'config-summary', meta, context, true, layout);
    });

    afterEach(function() {
        quotesFieldsMeta = null;
        quoteDataHeaderMeta = null;
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set eventViewName', function() {
            expect(view.eventViewName).toBe('summary_columns');
        });

        it('should set quotesFieldMeta', function() {
            expect(view.quotesFieldMeta).toBe(quotesFieldsMeta);
        });

        it('should set defaultFields', function() {
            expect(view.defaultFields).toEqual([{
                css_class: 'quote-totals-row-item',
                label: 'LBL_LIST_DEAL_TOT',
                labelModule: 'Quotes',
                name: 'deal_tot'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_NEW_SUB',
                labelModule: 'Quotes',
                name: 'new_sub'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_TAX_TOTAL',
                labelModule: 'Quotes',
                name: 'tax'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_SHIPPING',
                labelModule: 'Quotes',
                name: 'shipping'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_LIST_GRAND_TOTAL',
                labelModule: 'Quotes',
                name: 'total'
            }]);
        });

        it('should set listHeaderFields', function() {
            expect(view.listHeaderFields).toEqual([{
                css_class: 'quote-totals-row-item',
                label: 'LBL_LIST_DEAL_TOT',
                labelModule: 'Quotes',
                name: 'deal_tot',
                related_fields: ['deal_tot_discount_percentage']
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_NEW_SUB',
                labelModule: 'Quotes',
                name: 'new_sub'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_TAX_TOTAL',
                labelModule: 'Quotes',
                name: 'tax'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_SHIPPING',
                labelModule: 'Quotes',
                name: 'shipping'
            }, {
                css_class: 'quote-totals-row-item',
                label: 'LBL_LIST_GRAND_TOTAL',
                labelModule: 'Quotes',
                name: 'total'
            }]);
        });

        it('should set listDefaultFieldNameLabels', function() {
            expect(view.listDefaultFieldNameLabels).toBe('LBL_LIST_DEAL_TOT, LBL_NEW_SUB, LBL_TAX_TOTAL, ' +
                'LBL_SHIPPING, LBL_LIST_GRAND_TOTAL');
        });

        it('should set summary_columns on the model', function() {
            expect(view.model.get('summary_columns')).toBe(view.listHeaderFields);
        });
    });

    describe('_getEventViewName()', function() {
        it('should return summary_columns', function() {
            expect(view._getEventViewName()).toBe('summary_columns');
        });
    });

    describe('_getFieldLabelModule()', function() {
        it('should return labelModule', function() {
            var field = {
                labelModule: 'test'
            };
            expect(view._getFieldLabelModule(field)).toBe('test');
        });

        it('should return Quotes if no labelModule is present', function() {
            expect(view._getFieldLabelModule({})).toBe('Quotes');
        });
    });

    describe('_getPanelFields()', function() {
        var quotesFields;

        beforeEach(function() {
            quotesFields = {
                aaa: {
                    type: 'test'
                },
                bbb: {
                    type: 'collection'
                },
                ccc_id: {
                    type: 'test'
                }
            };
            view.context.set({
                quotesFields: quotesFields
            });
        });

        afterEach(function() {
            quotesFields = null;
        });

        it('should return quotesFields without collection field types', function() {
            expect(view._getPanelFields()).toEqual([{
                name: 'aaa',
                type: 'test'
            }]);
        });

        it('should return quotesFields without fields that have _id in the name', function() {
            expect(view._getPanelFields()).toEqual([{
                name: 'aaa',
                type: 'test'
            }]);
        });
    });

    describe('_getPanelFieldsModule()', function() {
        it('should return summary_columns', function() {
            expect(view._getPanelFieldsModule()).toBe('Quotes');
        });
    });

    describe('_onDependentFieldsChange()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'trigger');

            view._onDependentFieldsChange(view.context, view.context.get('dependentFields'));
        });

        it('should set the _related_fields', function() {
            expect(view.model.get(view.eventViewName + '_related_fields')).toEqual([
                'base_rate',
                'deal_tot',
                'deal_tot_usdollar',
                'shipping',
                'subtotal',
                'subtotal_usdollar',
                'tax'
            ]);
        });
    });

    describe('_onConfigFieldChange()', function() {
        var addColumnHeaderFieldStub;
        var removeColumnHeaderFieldStub;
        var testField;

        beforeEach(function() {
            addColumnHeaderFieldStub = sinon.stub();
            removeColumnHeaderFieldStub = sinon.stub();
            sinon.stub(view.context, 'trigger');
            sinon.spy(view.model, 'set');
            sinon.stub(view, '_getFieldLabelModule').callsFake(function() {
                return 'Products';
            });
            view.listHeaderView = {
                addColumnHeaderField: addColumnHeaderFieldStub,
                removeColumnHeaderField: removeColumnHeaderFieldStub,
                dispose: $.noop
            };

            testField = quotesFieldsMeta.total;
            testField.def = {
                relatedFields: [
                    'currency_id'
                ]
            };
        });

        afterEach(function() {
            addColumnHeaderFieldStub = null;
            removeColumnHeaderFieldStub = null;
            testField = null;
        });

        describe('when changing field wasVisible = false, isVisible = true', function() {
            it('should call addColumnHeaderField on listHeaderView when field has vname', function() {
                testField.vname = 'LBL_LINE_ITEM_TOTAL1';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addColumnHeaderFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.vname,
                    labelModule: 'Products'
                });
            });

            it('should call addColumnHeaderField on listHeaderView', function() {
                delete testField.vname;
                testField.label = 'LBL_LINE_ITEM_TOTAL2';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addColumnHeaderFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.label,
                    labelModule: 'Products'
                });
            });

            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(view.context.trigger).toHaveBeenCalledWith(
                    'config:summary_columns:currency_id:related:toggle',
                    testField,
                    true
                );
            });
        });

        describe('when changing field wasVisible = true, isVisible = false', function() {
            it('should call removeColumnHeaderField on listHeaderView', function() {
                view._onConfigFieldChange(testField, 'checked', 'unchecked');

                expect(removeColumnHeaderFieldStub).toHaveBeenCalledWith(testField);
            });

            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'checked', 'unchecked');

                expect(view.context.trigger).toHaveBeenCalled();
            });
        });

        describe('when changing field wasVisible = false, isVisible = false, isUnchecked = true', function() {
            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'filled', 'unchecked');

                expect(view.context.trigger).toHaveBeenCalledWith(
                    'config:summary_columns:currency_id:related:toggle',
                    testField,
                    false
                );
            });
        });
    });

    describe('render()', function() {
        var setColumnHeaderFieldsStub;
        var appendStub;

        beforeEach(function() {
            setColumnHeaderFieldsStub = sinon.stub();
            appendStub = sinon.stub();
            sinon.stub(app.view, 'createView').callsFake(function() {
                return {
                    setColumnHeaderFields: setColumnHeaderFieldsStub,
                    el: 'test',
                    dispose: $.noop
                };
            });
            sinon.stub(view, '$').callsFake(function() {
                return {
                    append: appendStub
                };
            });
            sinon.stub(view, '_render');

            view.render();
        });

        afterEach(function() {
            setColumnHeaderFieldsStub = null;
            appendStub = null;
        });

        it('should call $ to select .quote-summary-data-list-table element', function() {
            expect(view.$).toHaveBeenCalledWith('.quote-summary-data-list-table');
        });

        it('should append the listHeaderView element', function() {
            expect(appendStub).toHaveBeenCalledWith(view.listHeaderView.el);
        });

        it('should call setColumnHeaderFields with the listHeaderFields', function() {
            expect(setColumnHeaderFieldsStub).toHaveBeenCalledWith(view.listHeaderFields);
        });
    });

    describe('_customFieldDef()', function() {
        it('should set the def.eventViewName from this.eventViewName', function() {
            var def = {};
            view.eventViewName = 'test1';
            expect(view._customFieldDef(def)).toEqual({
                eventViewName: view.eventViewName
            });
        });
    });

    describe('onClickRestoreDefaultsBtn()', function() {
        var setColumnHeaderFieldsStub;

        beforeEach(function() {
            setColumnHeaderFieldsStub = sinon.stub();
            sinon.stub(view.context, 'trigger');
            view.listHeaderView = {
                setColumnHeaderFields: setColumnHeaderFieldsStub,
                el: 'test',
                dispose: $.noop
            };

            view.onClickRestoreDefaultsBtn({});
        });

        afterEach(function() {
            setColumnHeaderFieldsStub = null;
        });

        it('should call setColumnHeaderFields on the listHeaderView', function() {
            expect(setColumnHeaderFieldsStub).toHaveBeenCalledWith(view.defaultFields);
        });

        it('should trigger config:fields:<eventViewName>:reset on the context', function() {
            expect(view.context.trigger).toHaveBeenCalledWith('config:fields:' + view.eventViewName + ':reset');
        });
    });
});
