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
describe('Quotes.Base.Plugins.QuotesLineNumHelper', function() {
    var app;
    var component;
    var view;
    var model;
    var context;
    var quoteFields;
    var bundleFields;
    var productFields;
    var viewMeta;
    var metaPanels;
    var parentContext;
    var lineNumFieldDef;
    var layout;
    var layoutContext;
    var layoutGroupId;
    var layoutModel;
    var layoutInitOptions;
    var layoutParentContext;

    beforeEach(function() {
        app = SugarTest.app;

        quoteFields = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        bundleFields = SugarTest.loadFixture('product-bundle-fields', '../tests/modules/ProductBundles/fixtures');
        productFields = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');

        SugarTest.loadPlugin('MassCollection');
        SugarTest.loadFile('../modules/Quotes/clients/base/plugins', 'QuotesLineNumHelper', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.testMetadata.init();
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.testMetadata.updateModuleMetadata('ProductBundles', {
            fields: bundleFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Products', {
            fields: productFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Quotes', {
            fields: quoteFields
        });

        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        viewMeta = {
            selection: {
                type: 'multi',
                actions: [
                    {
                        name: 'edit_row_button',
                        type: 'button'
                    },
                    {
                        name: 'delete_row_button',
                        type: 'button'
                    }
                ]
            }
        };
        lineNumFieldDef = {
            name: 'line_num',
            label: 'LBL_LINE_NUMBER',
            widthClass: 'cell-small',
            css_class: 'line_num text-center',
            type: 'int',
            readonly: true
        };
        metaPanels = [{
            fields: [{
                name: 'quantity',
                label: 'LBL_QUANTITY',
                widthClass: 'cell-xsmall',
                css_class: 'quantity',
                type: 'float'
            }, lineNumFieldDef, {
                name: 'product_template_name',
                label: 'LBL_ITEM_NAME',
                widthClass: 'cell-large',
                type: 'quote-data-relate',
                required: true
            }]
        }];
        sinon.stub(app.metadata, 'getView').callsFake(function() {
            return {
                panels: metaPanels
            };
        });

        layoutGroupId = 'layoutGroupId1';
        layoutModel = new Backbone.Model({
            id: layoutGroupId,
            default_group: false,
            product_bundle_items: new Backbone.Collection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ])
        });

        layoutParentContext = app.context.getContext({module: 'Quotes'});
        layoutParentContext.prepare(true);

        layoutContext = app.context.getContext();
        layoutContext.set({
            module: 'ProductBundles',
            model: layoutModel
        });
        layoutContext.parent = layoutParentContext;

        layoutInitOptions = {
            model: layoutModel
        };

        layout = SugarTest.createLayout('base', 'ProductBundles', 'quote-data-group',
            null, layoutContext, true, layoutInitOptions);

        sinon.stub(layout, '_super').callsFake(function() {});

        context = app.context.getContext({
            module: 'Quotes'
        });
        parentContext = app.context.getContext({
            module: 'ProductBundles'
        });
        model = app.data.createBean('Quotes', {show_line_nums: false});
        model.module = 'Quotes';
        context.set({
            model: model,
            collection: new Backbone.Collection()
        });
        context.parent = parentContext;
        sinon.stub(context, 'on').callsFake(function() {});
        sinon.stub(context, 'off').callsFake(function() {});

        view = SugarTest.createView('base', 'Quotes', 'quote-data-list-header', viewMeta, context, true, layout, true);
    });

    afterEach(function() {
        // remove the plugin
        delete app.plugins.plugins['view']['QuotesLineNumHelper'];
        sinon.restore();
        if (component) {
            component.dispose();
            component = null;
        }
        app.cache.cutAll();
        view.dispose();
        view = null;
        app = null;
    });

    describe('onAttach init', function() {
        describe('when module is Quotes', function() {
            it('should call context.on', function() {
                expect(view.context.on).toHaveBeenCalledWith('quotes:show_line_nums:changed');
            });

            it('should get the line_num field defs from _fields', function() {
                expect(view.lineNumFieldDef).toEqual(lineNumFieldDef);
            });

            it('should remove the line_num field defs from _fields', function() {
                expect(view._fields).toEqual([{
                    name: 'quantity',
                    label: 'LBL_QUANTITY',
                    labelModule: 'Quotes',
                    widthClass: 'cell-xsmall',
                    css_class: 'quantity',
                    type: 'float'
                }, {
                    name: 'product_template_name',
                    label: 'LBL_ITEM_NAME',
                    labelModule: 'Quotes',
                    widthClass: 'cell-large',
                    type: 'quote-data-relate',
                    required: true
                }]);
            });

            it('should set hasLineNumField to false', function() {
                expect(view.hasLineNumField).toBeFalsy();
            });
        });

        describe('when module is ProductBundles', function() {
            var viewModel;
            beforeEach(function() {
                view.dispose();
                context.parent = parentContext;
                viewModel = app.data.createBean('ProductBundles');
                viewModel.module = 'ProductBundles';
                context.set('model', viewModel);
                view.model = viewModel;
                view.initialize({
                    model: viewModel,
                    context: context,
                    meta: viewMeta
                });

                sinon.stub(view.context.parent, 'on').callsFake(function() {});
                view.trigger('init');
            });

            it('should call context.parent.on', function() {
                expect(view.context.parent.on).toHaveBeenCalledWith('quotes:show_line_nums:changed');
            });
        });
    });

    describe('onDetach', function() {
        it('should call context.off when module is Quotes', function() {
            app.plugins.detach(view, 'view');
            expect(view.context.off).toHaveBeenCalledWith('quotes:show_line_nums:changed');
        });

        it('should call context.off when module is ProductBundles', function() {
            view.model.module = 'ProductBundles';
            sinon.stub(view.context.parent, 'off').callsFake(function() {});
            app.plugins.detach(view, 'view');
            expect(view.context.parent.off).toHaveBeenCalledWith('quotes:show_line_nums:changed');
        });
    });

    describe('onShowLineNumsChanged()', function() {
        beforeEach(function() {
            sinon.stub(view, '_addLineNumFieldDef').callsFake(function() {});
            sinon.stub(view, '_addLineNumToModel').callsFake(function() {});
            sinon.stub(view, '_removeLineNumFieldDef').callsFake(function() {});
            sinon.stub(view, '_removeLineNumFromModel').callsFake(function() {});
            sinon.stub(view, 'render').callsFake(function() {});
        });

        describe('showLineNums is true', function() {
            describe('hasLineNumField is false', function() {
                beforeEach(function() {
                    view.hasLineNumField = false;
                });

                describe('when module is Quotes', function() {
                    beforeEach(function() {
                        view.onShowLineNumsChanged(true);
                    });

                    it('should call _addLineNumFieldDef()', function() {
                        expect(view._addLineNumFieldDef).toHaveBeenCalled();
                    });

                    it('should not call _addLineNumToModel()', function() {
                        expect(view._addLineNumToModel).not.toHaveBeenCalled();
                    });

                    it('should call render', function() {
                        expect(view.render).toHaveBeenCalled();
                    });
                });

                describe('when module is ProductBundles', function() {
                    beforeEach(function() {
                        view.model.module = 'ProductBundles';
                        view.model.set('id', 'viewId1');
                    });

                    it('should call _addLineNumFieldDef()', function() {
                        view.onShowLineNumsChanged(true);

                        expect(view._addLineNumFieldDef).toHaveBeenCalled();
                    });

                    describe('when bundle is empty', function() {
                        beforeEach(function() {
                            view.onShowLineNumsChanged(true);
                        });

                        it('should call _addLineNumToModel()', function() {
                            expect(view._addLineNumToModel).not.toHaveBeenCalled();
                        });
                    });

                    describe('when bundle is not empty', function() {
                        var bundle1;
                        beforeEach(function() {
                            bundle1 = app.data.createBean('Products', {
                                id: 'productId1'
                            });
                            view.collection.add(bundle1);

                            view.onShowLineNumsChanged(true);
                        });

                        it('should call _addLineNumToModel()', function() {
                            expect(view._addLineNumToModel).toHaveBeenCalledWith(view.model.cid);
                        });
                    });

                    it('should call render', function() {
                        view.onShowLineNumsChanged(true);

                        expect(view.render).toHaveBeenCalled();
                    });
                });
            });
        });

        describe('showLineNums is false', function() {
            describe('hasLineNumField is true', function() {
                beforeEach(function() {
                    view.hasLineNumField = true;
                });

                describe('when module is Quotes', function() {
                    beforeEach(function() {
                        view.onShowLineNumsChanged(false);
                    });

                    it('should call _removeLineNumFieldDef()', function() {
                        expect(view._removeLineNumFieldDef).toHaveBeenCalled();
                    });

                    it('should not call _removeLineNumFromModel()', function() {
                        expect(view._removeLineNumFromModel).not.toHaveBeenCalled();
                    });

                    it('should call render', function() {
                        expect(view.render).toHaveBeenCalled();
                    });
                });

                describe('when module is ProductBundles', function() {
                    beforeEach(function() {
                        view.model.module = 'ProductBundles';
                        view.model.set('id', 'viewId1');
                    });

                    it('should call _removeLineNumFieldDef()', function() {
                        view.onShowLineNumsChanged(false);

                        expect(view._removeLineNumFieldDef).toHaveBeenCalled();
                    });

                    describe('when bundle is empty', function() {
                        beforeEach(function() {
                            view.onShowLineNumsChanged(false);
                        });

                        it('should call _removeLineNumFromModel()', function() {
                            expect(view._removeLineNumFromModel).not.toHaveBeenCalled();
                        });
                    });

                    describe('when bundle is not empty', function() {
                        var bundle1;
                        beforeEach(function() {
                            bundle1 = app.data.createBean('Products', {
                                id: 'productId1'
                            });
                            view.collection.add(bundle1);

                            view.onShowLineNumsChanged(false);
                        });

                        it('should call _removeLineNumFromModel()', function() {
                            expect(view._removeLineNumFromModel).toHaveBeenCalledWith(view.model.cid);
                        });
                    });

                    it('should call render', function() {
                        view.onShowLineNumsChanged(false);

                        expect(view.render).toHaveBeenCalled();
                    });
                });
            });
        });
    });

    describe('getGroupLineNumCount()', function() {
        var lineNumGroupObj;
        var groupId;
        var result;
        beforeEach(function() {
            groupId = 'testGroup1';
            lineNumGroupObj = {
                ct: 3
            };
            view.lineNumGroupIdMap = {};
            view.lineNumGroupIdMap.testGroup1 = lineNumGroupObj;
        });

        it('should return the lineNumGroupIdMap object ', function() {
            result = view.getGroupLineNumCount(groupId);

            expect(result).toEqual(lineNumGroupObj);
        });
    });

    describe('resetGroupLineNumbers()', function() {
        beforeEach(function() {
            sinon.stub(view, '_checkAddGroupToMap').callsFake(function() {});
        });

        it('should do nothing if showLineNums is false', function() {
            view.showLineNums = false;
            view.resetGroupLineNumbers();

            expect(view._checkAddGroupToMap).not.toHaveBeenCalled();
        });

        describe('resetting line_num', function() {
            var model1;
            var model2;
            var model3;
            var lineNumGroupObj;
            var groupId;

            beforeEach(function() {
                view.showLineNums = true;

                groupId = 'testGroup1';
                lineNumGroupObj = {
                    ct: 4
                };
                view.lineNumGroupIdMap = {};
                view.lineNumGroupIdMap.testGroup1 = lineNumGroupObj;

                model1 = app.data.createBean('Products', {
                    id: 'idModel1',
                    line_num: 1
                });
                model2 = app.data.createBean('Products', {
                    id: 'idModel2',
                    line_num: 2
                });
                model3 = app.data.createBean('Products', {
                    id: 'idModel3',
                    line_num: 3
                });

                view.collection.add(model1);
                view.collection.add(model2);
                view.collection.add(model3);
            });

            it('should re-number models starting from 1', function() {
                view.collection.remove(model1);
                view.resetGroupLineNumbers(groupId, view.collection);

                expect(model2.get('line_num')).toBe(1);
                expect(model3.get('line_num')).toBe(2);
                expect(view.lineNumGroupIdMap.testGroup1.ct).toBe(3);
            });

            it('should re-number models starting from 1 removing middle model', function() {
                view.collection.remove(model2);
                view.resetGroupLineNumbers(groupId, view.collection);

                expect(model1.get('line_num')).toBe(1);
                expect(model3.get('line_num')).toBe(2);
                expect(view.lineNumGroupIdMap.testGroup1.ct).toBe(3);
            });
        });
    });

    describe('_checkAddGroupToMap()', function() {
        it('should create a new object if groupId does not exist', function() {
            view.lineNumGroupIdMap = {};
            view._checkAddGroupToMap('test1');

            expect(view.lineNumGroupIdMap.test1).toBeDefined();
            expect(view.lineNumGroupIdMap.test1.ct).toBe(1);
        });
    });

    describe('_checkRemoveGroupFromMap()', function() {
        it('should do nothing if groupId ct is >= 1', function() {
            view.lineNumGroupIdMap = {
                test1: {
                    ct: 1
                }
            };
            view._checkRemoveGroupFromMap('test1');

            expect(view.lineNumGroupIdMap.test1).toBeDefined();
            expect(view.lineNumGroupIdMap.test1.ct).toBe(1);
        });

        it('should remove the group if groupId ct is < 1', function() {
            view.lineNumGroupIdMap = {
                test1: {
                    ct: 0
                }
            };
            view._checkRemoveGroupFromMap('test1');

            expect(view.lineNumGroupIdMap.test1).toBeUndefined();
        });
    });

    describe('_addLineNumFieldDef()', function() {
        beforeEach(function() {
            view._addLineNumFieldDef();
        });

        it('should add the line_num field to _fields', function() {
            expect(view._fields).toEqual([{
                name: 'line_num',
                label: 'LBL_LINE_NUMBER',
                labelModule: 'Quotes',
                widthClass: 'cell-small',
                css_class: 'line_num text-center',
                type: 'int',
                readonly: true
            }, {
                name: 'quantity',
                label: 'LBL_QUANTITY',
                labelModule: 'Quotes',
                widthClass: 'cell-xsmall',
                css_class: 'quantity',
                type: 'float'
            }, {
                name: 'product_template_name',
                label: 'LBL_ITEM_NAME',
                labelModule: 'Quotes',
                widthClass: 'cell-large',
                type: 'quote-data-relate',
                required: true
            }]);
        });

        it('should set hasLineNumField to true', function() {
            expect(view.hasLineNumField).toBeTruthy();
        });
    });

    describe('_removeLineNumFieldDef()', function() {
        beforeEach(function() {
            view._fields = [{
                name: 'line_num',
                label: 'LBL_LINE_NUMBER',
                widthClass: 'cell-small',
                css_class: 'line_num text-center',
                type: 'int',
                readonly: true
            }, {
                name: 'quantity',
                label: 'LBL_QUANTITY',
                widthClass: 'cell-xsmall',
                css_class: 'quantity',
                type: 'float'
            }, {
                name: 'product_template_name',
                label: 'LBL_ITEM_NAME',
                widthClass: 'cell-large',
                type: 'quote-data-relate',
                required: true
            }];
            view._removeLineNumFieldDef();
        });

        it('should add the line_num field to _fields', function() {
            expect(view._fields).toEqual([{
                name: 'quantity',
                label: 'LBL_QUANTITY',
                widthClass: 'cell-xsmall',
                css_class: 'quantity',
                type: 'float'
            }, {
                name: 'product_template_name',
                label: 'LBL_ITEM_NAME',
                widthClass: 'cell-large',
                type: 'quote-data-relate',
                required: true
            }]);
        });

        it('should set hasLineNumField to true', function() {
            expect(view.hasLineNumField).toBeFalsy();
        });
    });

    describe('_addLineNumToModel()', function() {
        var model1;
        var model2;

        beforeEach(function() {
            model1 = app.data.createBean('Products', {
                id: 'idModel1',
                line_num: -1
            });
            model2 = app.data.createBean('Products', {
                id: 'idModel2',
                line_num: -2
            });
            view.collection.add(model1);
            view.collection.add(model2);
            sinon.spy(view, '_checkAddGroupToMap');

            view._addLineNumToModel('test1', view.collection);
        });

        it('should call _checkAddGroupToMap', function() {
            expect(view._checkAddGroupToMap).toHaveBeenCalledWith('test1');
        });

        it('should add line_num to models', function() {
            expect(model1.get('line_num')).toBe(1);
            expect(model2.get('line_num')).toBe(2);
        });
    });

    describe('_removeLineNumFromModel()', function() {
        var model1;
        var model2;

        beforeEach(function() {
            model1 = app.data.createBean('Products', {
                id: 'idModel1',
                line_num: 1
            });
            model2 = app.data.createBean('Products', {
                id: 'idModel2',
                line_num: 2
            });

            view.collection.add(model1);
            view.collection.add(model2);
            sinon.spy(view, '_checkRemoveGroupFromMap');

            view.lineNumGroupIdMap = {};
            view.lineNumGroupIdMap.test1 = {
                ct: 3
            };

            view._removeLineNumFromModel('test1', view.collection);
        });

        it('should call _checkAddGroupToMap', function() {
            expect(view._checkRemoveGroupFromMap).toHaveBeenCalledWith('test1');
        });

        it('should add line_num to models', function() {
            expect(model1.get('line_num')).toBeUndefined();
            expect(model2.get('line_num')).toBeUndefined();
        });
    });
});
