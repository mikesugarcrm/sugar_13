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
describe('Quotes.View.ConfigFooter', function() {
    var app;
    var context;
    var options;
    var view;
    var quotesFieldsMeta;
    var quotesFooterMeta;
    var layout;

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

        quotesFieldsMeta = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        quotesFooterMeta = {
            panels: [{
                name: 'panel_quote_data_grand_totals_footer',
                label: 'LBL_QUOTE_DATA_GRAND_TOTALS_FOOTER',
                fields: [{
                    name: 'new_sub',
                    type: 'currency',
                    vname: 'LBL_NEW_SUB'
                }, {
                    name: 'tax',
                    type: 'currency',
                    related_fields: [
                        'taxrate_value'
                    ],
                    vname: 'LBL_TAX'
                }, {
                    name: 'shipping',
                    type: 'quote-footer-currency',
                    css_class: 'quote-footer-currency',
                    default: '0.00',
                    label: 'LBL_SHIPPING'
                }, {
                    name: 'total',
                    label: 'LBL_LIST_GRAND_TOTAL',
                    type: 'currency',
                    css_class: 'grand-total'
                }]
            }]
        };

        sinon.stub(app.metadata, 'getModule')
            .withArgs('Quotes', 'fields').returns(quotesFieldsMeta);

        sinon.stub(app.metadata, 'getView')
            .withArgs('Quotes', 'quote-data-grand-totals-footer').returns(quotesFooterMeta);

        SugarTest.loadComponent('base', 'view', 'config-panel');
        SugarTest.loadComponent('base', 'view', 'config-panel', 'Quotes');
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer-content');
        view = SugarTest.createView('base', 'Quotes', 'config-footer', meta, context, true, layout);
        view.eventViewName = 'config-footer';
    });

    afterEach(function() {
        quotesFieldsMeta = null;
        quotesFooterMeta = null;
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(view, 'getPanelFieldNamesList');
        });

        it('should call getPanelFieldNamesList', function() {
            expect(view.listDefaultFieldNameLabels).toBe('LBL_NEW_SUB, LBL_TAX, LBL_SHIPPING, LBL_LIST_GRAND_TOTAL');
        });
    });

    describe('_getEventViewName()', function() {
        it('should return footer_rows', function() {
            expect(view._getEventViewName()).toBe('footer_rows');
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
        it('should only return currency fields', function() {
            view.context.set('quotesFields', {
                aaa: {
                    type: 'currency'
                },
                bbb: {
                    type: 'text'
                }
            });

            expect(view._getPanelFields()).toEqual([{
                name: 'aaa',
                type: 'currency'
            }]);
        });
    });

    describe('_getPanelFieldsModule()', function() {
        it('should return Quotes', function() {
            expect(view._getPanelFieldsModule()).toBe('Quotes');
        });
    });

    describe('onConfigPanelShow()', function() {
        it('should return Quotes', function() {
            view.dependentFields = [{
                test: '123'
            }];
            view.panelFields = [{
                test: '456'
            }];
            sinon.stub(view.context, 'trigger');
            view.onConfigPanelShow();

            expect(view.context.trigger).toHaveBeenCalledWith(
                'config:fields:change',
                view.eventViewName,
                view.panelFields
            );
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

    describe('_onConfigFieldChange()', function() {
        var addFooterRowFieldStub;
        var removeFooterRowFieldStub;
        var testField;

        beforeEach(function() {
            addFooterRowFieldStub = sinon.stub();
            removeFooterRowFieldStub = sinon.stub();
            sinon.stub(view.context, 'trigger');
            sinon.spy(view.model, 'set');
            sinon.stub(view, '_getFieldLabelModule').callsFake(function() {
                return 'Quotes';
            });
            view.footerRowsView = {
                addFooterRowField: addFooterRowFieldStub,
                removeFooterRowField: removeFooterRowFieldStub,
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
            addFooterRowFieldStub = null;
            removeFooterRowFieldStub = null;
            testField = null;
        });

        describe('when changing field wasVisible = false, isVisible = true', function() {
            it('should call addFooterRowField on footerRowsView when field has vname', function() {
                testField.vname = 'LBL_LINE_ITEM_TOTAL1';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addFooterRowFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.vname,
                    labelModule: 'Quotes'
                });
            });

            it('should call addFooterRowField on footerRowsView', function() {
                delete testField.vname;
                testField.label = 'LBL_LINE_ITEM_TOTAL2';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addFooterRowFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.label,
                    labelModule: 'Quotes'
                });
            });

            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(view.context.trigger).toHaveBeenCalledWith(
                    'config:config-footer:currency_id:related:toggle',
                    testField,
                    true
                );
            });
        });

        describe('when changing field wasVisible = true, isVisible = false', function() {
            it('should call removeFooterRowField on footerRowsView', function() {
                view._onConfigFieldChange(testField, 'checked', 'unchecked');

                expect(removeFooterRowFieldStub).toHaveBeenCalledWith(testField);
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
                    'config:config-footer:currency_id:related:toggle',
                    testField,
                    false
                );
            });
        });
    });

    describe('render()', function() {
        var setFooterRowFieldsStub;
        var appendStub;

        beforeEach(function() {
            setFooterRowFieldsStub = sinon.stub();
            appendStub = sinon.stub();
            sinon.stub(app.view, 'createView').callsFake(function() {
                return {
                    setFooterRowFields: setFooterRowFieldsStub,
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
            setFooterRowFieldsStub = null;
            appendStub = null;
        });

        it('should call $ to select .quote-footer-rows element', function() {
            expect(view.$).toHaveBeenCalledWith('.quote-footer-rows');
        });

        it('should append the footerRowsView element', function() {
            expect(appendStub).toHaveBeenCalledWith(view.footerRowsView.el);
        });

        it('should call setFooterRowFields with the footerRowFields', function() {
            expect(setFooterRowFieldsStub).toHaveBeenCalledWith(view.footerRowFields);
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

    describe('onClickRestoreDefaultsBtn()', function() {
        var setFooterRowFieldsStub;

        beforeEach(function() {
            setFooterRowFieldsStub = sinon.stub();
            sinon.stub(view.context, 'trigger');
            view.footerRowsView = {
                setFooterRowFields: setFooterRowFieldsStub,
                el: 'test',
                dispose: $.noop
            };

            view.onClickRestoreDefaultsBtn({});
        });

        afterEach(function() {
            setFooterRowFieldsStub = null;
        });

        it('should call setFooterRowFields on the footerRowsView', function() {
            expect(setFooterRowFieldsStub).toHaveBeenCalledWith(view.defaultFields);
        });

        it('should trigger config:fields:<eventViewName>:reset on the context', function() {
            expect(view.context.trigger).toHaveBeenCalledWith('config:fields:' + view.eventViewName + ':reset');
        });
    });
});
