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
describe('ProductBundles.Base.Layouts.QuoteDataGroup', function() {
    var app;
    var layout;
    var layoutModel;
    var layoutContext;
    var layoutGroupId;
    var initializeOptions;
    var parentContext;
    var parentContextModel;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        parentContext = app.context.getContext({module: 'Quotes'});
        parentContext.prepare(true);

        parentContextModel = app.data.createBean('Quotes', {
            id: 'test1'
        });
        parentContext.set('model', parentContextModel);
        sinon.spy(parentContextModel, 'on');
        sinon.spy(parentContextModel, 'off');

        layoutModel = new Backbone.Model({
            id: layoutGroupId,
            default_group: false,
            product_bundle_items: new Backbone.Collection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ]),
        });
        layoutModel.dispose = function() {};
        layoutGroupId = layoutModel.cid;

        layoutContext = app.context.getContext();
        layoutContext.set({
            module: 'ProductBundles',
            model: layoutModel
        });

        layoutContext.parent = parentContext;

        sinon.stub(app.metadata, 'getView').callsFake(function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        initializeOptions = {
            model: layoutModel
        };

        layout = SugarTest.createLayout('base', 'ProductBundles', 'quote-data-group', null,
            layoutContext, true, initializeOptions);

        sinon.stub(layout, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        parentContextModel = null;
    });

    describe('initialize()', function() {
        var lastCall;

        it('should have className', function() {
            expect(layout.className).toBe('quote-data-group');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('tbody');
        });

        it('should set the groupId from the model ID', function() {
            expect(layout.groupId).toBe(layoutGroupId);
        });

        it('should set this.collection to be the product_bundle_items', function() {
            expect(layout.collection).toBe(layoutModel.get('product_bundle_items'));
        });

        it('should add the comparator function to this.collections', function() {
            expect(layout.collection.comparator).toBeDefined();
        });

        it('should call app.metadata.getView with first param Products module', function() {
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[0]).toBe('Products');
        });

        it('should call app.metadata.getView with second param quote-data-group-list', function() {
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[1]).toBe('quote-data-group-list');
        });

        it('should set listColSpan if metadata exists', function() {
            expect(layout.listColSpan).toBe(4);
        });
    });

    describe('bindDataChange()', function() {
        it('will subscribe the model to listen for a change on product_bundle_items and call render', function() {
            sinon.stub(layout.model, 'on');
            layout.bindDataChange();
            expect(layout.model.on).toHaveBeenCalledWith('change:product_bundle_items', layout.render, layout);
        });

        it('should add listener for parent context model change:currency_id', function() {
            layout.bindDataChange();

            expect(parentContextModel.on).toHaveBeenCalledWith('change:currency_id');
        });
    });

    describe('_render()', function() {
        var $elAttrSpy;
        var $elDataSpy;
        var $attrSpy;
        var $dataSpy;
        var $oldEl;

        beforeEach(function() {
            $elAttrSpy = sinon.spy();
            $elDataSpy = sinon.spy();
            $attrSpy = sinon.spy();
            $dataSpy = sinon.spy();

            $oldEl = layout.$el;
            layout.$el = {
                attr: $elAttrSpy,
                data: $elDataSpy
            };

            sinon.stub(layout, '$').callsFake(function() {
                return {
                    attr: $attrSpy,
                    data: $dataSpy
                };
            });

            layout._render();
        });

        afterEach(function() {
            $elAttrSpy = null;
            $attrSpy = null;
            delete layout.$el.attr;
            layout.$el = $oldEl;
            $oldEl = null;
        });

        it('should call super _render', function() {
            expect(layout._super).toHaveBeenCalledWith('_render');
        });

        it('should call $el.attr, $el.data, and set the data-group-id to the groupId', function() {
            expect($elAttrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
            expect($elDataSpy).toHaveBeenCalledWith('group-id', layoutGroupId);
        });

        it('should call this.$ to get the table rows', function() {
            expect(layout.$).toHaveBeenCalledWith('tr.quote-data-group-list');
        });

        it('should call $.attr and set the data-group-id to the groupId', function() {
            expect($attrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
            expect($dataSpy).toHaveBeenCalledWith('group-id', layoutGroupId);
        });
    });

    describe('addRowModel()', function() {
        var rowModel;
        var rowModel2;
        beforeEach(function() {
            layout.quoteDataGroupList = {
                toggledModels: {}
            };

            rowModel = new Backbone.Model({
                id: 'rowModelId1',
                position: 0
            });
            rowModel.cid = 'rowModelId1';
            rowModel2 = new Backbone.Model({
                id: 'rowModelId2',
                position: 0
            });
            rowModel2.cid = 'rowModelId2';
            sinon.spy(layout.collection, 'add');

            layout.collection.reset();
        });

        afterEach(function() {
            rowModel = null;
        });

        it('should add the model to the collection', function() {
            layout.addRowModel(rowModel, false);

            expect(layout.collection.length).toBe(1);
        });

        it('should add model to list component toggledModels if in edit', function() {
            layout.addRowModel(rowModel, true);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toEqual(rowModel);
        });

        it('should not add model to list component toggledModels if not in edit', function() {
            layout.addRowModel(rowModel, false);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toBeUndefined();
        });

        it('should add row model at the position value on the model', function() {
            layout.addRowModel(rowModel, false);
            rowModel.set('position', 1);
            layout.addRowModel(rowModel2, false);

            expect(layout.collection.models[0].get('id')).toBe('rowModelId2');
            expect(layout.collection.models[1].get('id')).toBe('rowModelId1');
        });
    });

    describe('removeRowModel()', function() {
        var rowModel;

        beforeEach(function() {
            rowModel = new Backbone.Model({
                id: 'rowModelId1'
            });
            layout.quoteDataGroupList = {
                toggledModels: {
                    rowModelId1: rowModel
                }
            };
            layout.collection.reset(rowModel);
        });

        afterEach(function() {
            rowModel = null;
        });

        it('should remove the model from the collection', function() {
            layout.removeRowModel(rowModel, false);

            expect(layout.collection.length).toBe(0);
        });

        it('should remove model from list toggledModels if in edit', function() {
            layout.removeRowModel(rowModel, true);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toBeUndefined();
        });
    });

    describe('switchModel()', function() {
        var newModel;
        var newCollection;
        var qli1;
        var qli2;

        beforeEach(function() {
            // Create the new Product Bundle model
            newModel = app.data.createBean('ProductBundles', {
                default_group: true
            });
            newModel.cid = 'c12345';

            // Simulate a few QLIs being a part of the product bundle
            qli1 = app.data.createBean('QuotedLineItems', {
                position: 0
            });
            qli2 = app.data.createBean('QuotedLineItems', {
                position: 1
            });
            newCollection = app.data.createBeanCollection('QuotedLineItems', [qli2, qli1]);
            newModel.set('product_bundle_items', newCollection);

            // Mock the inner views
            layout.quoteDataGroupList = {
                switchModel: function() {}
            };
            sinon.stub(layout.quoteDataGroupList, 'switchModel');

            layout.quoteDataGroupHeader = {
                switchModel: function() {}
            };
            sinon.stub(layout.quoteDataGroupHeader, 'switchModel');

            layout.quoteDataGroupFooter = {
                switchModel: function() {}
            };
            sinon.stub(layout.quoteDataGroupFooter, 'switchModel');
        });

        it('should update the model CID', function() {
            layout.switchModel(newModel);
            expect(layout.model.cid).toEqual('c12345');
        });

        it('should sort the new collection correctly', function() {
            layout.switchModel(newModel);
            expect(layout.collection.models[0].get('position')).toEqual(0);
            expect(layout.collection.models[1].get('position')).toEqual(1);
        });

        describe('when switching the model on the inner views', function() {
            it('should only switch the inner list view model if this is the default group', function() {
                layout.switchModel(newModel);
                expect(layout.quoteDataGroupList.switchModel).toHaveBeenCalled();
                expect(layout.quoteDataGroupHeader.switchModel).not.toHaveBeenCalled();
                expect(layout.quoteDataGroupFooter.switchModel).not.toHaveBeenCalled();
            });

            it('should switch all the inner view models if this is not the default group', function() {
                newModel.set('default_group', false);
                layout.switchModel(newModel);
                expect(layout.quoteDataGroupList.switchModel).toHaveBeenCalled();
                expect(layout.quoteDataGroupHeader.switchModel).toHaveBeenCalled();
                expect(layout.quoteDataGroupFooter.switchModel).toHaveBeenCalled();
            });
        });
    });

    describe('addComponent()', function() {
        var components;

        beforeEach(function() {
            components = [{
                name: 'quote-data-group-list'
            }, {
                name: 'other-component'
            }];
        });

        afterEach(function() {
            components = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            _.each(components, function(comp) {
                layout.addComponent(comp, {});
            }, this);

            expect(layout.quoteDataGroupList).toEqual(components[0]);
        });
    });

    describe('removeComponent()', function() {
        var components;

        beforeEach(function() {
            components = [{
                name: 'quote-data-group-list'
            }, {
                name: 'other-component'
            }];
        });

        afterEach(function() {
            components = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            _.each(components, function(comp) {
                layout.removeComponent(comp, {});
            }, this);

            expect(layout.quoteDataGroupList).toBeNull();
        });
    });

    describe('_dispose()', function() {
        var quoteDataGroupList;

        beforeEach(function() {
            quoteDataGroupList = {
                name: 'quote-data-group-list'
            };
            layout.quoteDataGroupList = quoteDataGroupList;
            layout._dispose();
        });

        afterEach(function() {
            quoteDataGroupList = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            expect(layout.quoteDataGroupList).toBeNull();
        });

        it('should remove listener for parent context model change:currency_id', function() {
            expect(parentContextModel.off).toHaveBeenCalledWith('change:currency_id');
        });
    });
});
