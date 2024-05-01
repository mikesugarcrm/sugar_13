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
describe('Quotes.Base.Views.QuoteDataListHeader', function() {
    var app;
    var view;
    var viewMeta;
    var metaPanels;
    var layout;
    var layoutDefs;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('MassCollection');

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

        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'Quotes', 'default', layoutDefs);
        layout.isCreateView = false;
        layout.moveMassCollectionItemsToNewGroup = function() {};

        metaPanels = [{
            fields: [
                'field1', 'field2', 'field3', 'field4'
            ]
        }];

        sinon.stub(app.metadata, 'getView').callsFake(function() {
            return {
                panels: metaPanels
            };
        });

        var context = app.context.getContext();
        var prodBundles = new Backbone.Collection();
        var model = app.data.createBean('Quotes', {
            bundles: prodBundles
        });
        context.prepare();
        context.set('model', model);

        view = SugarTest.createView('base', 'Quotes', 'quote-data-list-header', viewMeta, context, true, layout);
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        app.data.reset();
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
        metaPanels = null;
        viewMeta = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-list-header');
        });

        it('should set this.meta.panels', function() {
            expect(view.meta.panels).toEqual(metaPanels);
        });

        it('should set this._fields', function() {
            expect(view._fields).toEqual(_.flatten(_.pluck(metaPanels, 'fields')));
        });

        describe('when on create view', function() {
            var initMeta;
            beforeEach(function() {
                layout.isCreateView = true;
                initMeta = {
                    meta: viewMeta,
                    layout: layout
                };

                view.dispose();
                view.initialize(initMeta);
            });

            afterEach(function() {
                initMeta = null;
            });

            it('should clear out left column select buttons', function() {
                expect(view.leftColumns[0].fields.length).toBe(0);
            });
        });
    });

    describe('bindDataChange()', function() {
        var massCollection;

        beforeEach(function() {
            massCollection = new Backbone.Collection();
            view.massCollection = massCollection;
            sinon.spy(view.massCollection, 'on');
        });

        afterEach(function() {
            massCollection = null;
        });

        it('should set listener for mass collection add remove reset if it exists', function() {
            view.bindDataChange();

            expect(view.massCollection.on).toHaveBeenCalledWith('add remove reset');
        });
    });

    describe('_massCollectionChange()', function() {
        var propStub;

        beforeEach(function() {
            propStub = sinon.stub();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    length: 1,
                    prop: propStub
                };
            });
        });

        afterEach(function() {
            propStub = null;
        });

        it('should set group and mass delete fields enabled if massCollection has items', function() {
            view._massCollectionChange({}, {
                length: 0
            });

            expect(propStub).toHaveBeenCalledWith('checked', false);
        });
    });

    describe('_render()', function() {
        var massCollection;
        var quoteModel;
        var productModel;

        beforeEach(function() {
            quoteModel = app.data.createBean('Quotes', {
                id: 'quoteId1'
            });
            quoteModel.module = 'Quotes';

            productModel = app.data.createBean('Quotes', {
                id: 'productId1'
            });
            productModel.module = 'Products';

            massCollection = new Backbone.Collection();
            massCollection.add(quoteModel);
            massCollection.add(productModel);

            sinon.stub(view, '_super').callsFake(function() {});
            view.massCollection = massCollection;

            sinon.stub(view, '_checkMassActions').callsFake(function() {});
        });

        it('should remove Quotes module models', function() {
            view._render();

            expect(view.massCollection.models.length).toBe(1);
        });

        it('should call _checkMassActions', function() {
            view._render();

            expect(view._checkMassActions).toHaveBeenCalled();
        });
    });

    describe('checkAll()', function() {
        var evt;

        beforeEach(function() {
            sinon.stub(view.context, 'trigger').callsFake(function() {});
        });

        it('should trigger quotes:collections:all:checked when checked', function() {
            evt = {
                currentTarget: '<input data-check="all" type="checkbox" name="check" checked="checked">'
            };
            view.checkAll(evt);

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:collections:all:checked');
        });

        it('should trigger quotes:collections:not:all:checked when not checked', function() {
            evt = {
                currentTarget: '<input data-check="all" type="checkbox" name="check">'
            };
            view.checkAll(evt);

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:collections:not:all:checked');
        });
    });

    describe('_checkMassActions()', function() {
        var massActionsField;
        var groupButtonField;
        var massDeleteField;
        var quoteModel;
        var massCollection;

        beforeEach(function() {
            quoteModel = app.data.createBean('Quotes', {
                id: 'testId',
                _module: 'Quotes'
            });
            quoteModel.id = 'testId';
            massCollection = new Backbone.Collection(quoteModel);
            sinon.spy(massCollection, 'remove');
            view.massCollection = massCollection;

            massActionsField = {
                setDisabled: sinon.spy()
            };
            groupButtonField = {
                setDisabled: sinon.spy()
            };
            massDeleteField = {
                setDisabled: sinon.spy()
            };
            sinon.stub(view, 'getField')
                .withArgs('quote-data-mass-actions').returns(massActionsField)
                .withArgs('group_button').returns(groupButtonField)
                .withArgs('massdelete_button').returns(massDeleteField);
        });

        afterEach(function() {
            massCollection = null;
            quoteModel = null;
        });

        it('should remove any Quote models from massCollection', function() {
            view._checkMassActions();

            expect(massCollection.remove).toHaveBeenCalledWith(quoteModel, {silent: true});
        });

        describe('bundles are empty', function() {
            beforeEach(function() {
                sinon.stub(view, '_bundlesAreEmpty').callsFake(function() {
                    return true;
                });
            });

            it('should set mass actions field disabled if bundles are empty', function() {
                view._checkMassActions();

                expect(massActionsField.setDisabled).toHaveBeenCalledWith(true);
            });
        });

        describe('bundles have items', function() {
            beforeEach(function() {
                sinon.stub(view, '_bundlesAreEmpty').callsFake(function() {
                    return false;
                });
            });

            it('should set mass actions field enabled if bundles have items', function() {
                view._checkMassActions();

                expect(massActionsField.setDisabled).toHaveBeenCalledWith(false);
            });

            it('should set group and mass delete fields disabled if massCollection is empty', function() {
                view.massCollection.models = [];
                view._checkMassActions();

                expect(groupButtonField.setDisabled).toHaveBeenCalledWith(true);
                expect(massDeleteField.setDisabled).toHaveBeenCalledWith(true);
            });

            it('should set group and mass delete fields enabled if massCollection has items', function() {
                view.massCollection.add(app.data.createBean('Products', {
                    id: 'testProductId',
                    _module: 'Products'
                }));
                view._checkMassActions();

                expect(groupButtonField.setDisabled).toHaveBeenCalledWith(false);
                expect(massDeleteField.setDisabled).toHaveBeenCalledWith(false);
            });
        });
    });

    describe('_bundlesAreEmpty()', function() {
        var bundles;
        var bundle1;
        var bundle2;
        var pbItems;
        var pbItem;
        var result;

        it('should return true if all bundles are empty', function() {
            bundles = new Backbone.Collection();
            pbItems = new Backbone.Collection();
            bundle1 = new Backbone.Model({
                id: 'bundleId1',
                product_bundle_items: pbItems
            });

            bundles.add(bundle1);
            view.model.set('bundles', bundles);
            result = view._bundlesAreEmpty();
            expect(result).toBeTruthy();
        });

        it('should return false any bundle has an item in it', function() {
            bundles = new Backbone.Collection();
            bundle1 = new Backbone.Model({
                id: 'bundleId1',
                product_bundle_items: new Backbone.Collection()
            });
            pbItem = new Backbone.Model({
                id: 'pbItem1'
            });
            pbItems = new Backbone.Collection(pbItem);
            bundle2 = new Backbone.Model({
                id: 'bundleId2',
                product_bundle_items: pbItems
            });

            bundles.add(bundle1);
            bundles.add(bundle2);
            view.model.set('bundles', bundles);
            result = view._bundlesAreEmpty();
            expect(result).toBeFalsy();
        });
    });

    describe('_onCreateGroupBtnClicked()', function() {
        var massCollection;

        beforeEach(function() {
            massCollection = new Backbone.Collection();
            view.massCollection = massCollection;

            sinon.stub(view.context, 'on').callsFake(function() {});
            sinon.stub(view.context, 'trigger').callsFake(function() {});
            sinon.stub(app.alert, 'show').callsFake(function() {});
        });

        afterEach(function() {
            massCollection = null;
        });

        it('should trigger events on context if massCollection has items', function() {
            massCollection.add(app.data.createBean('Products'));
            view._onCreateGroupBtnClicked({});

            expect(view.context.on).toHaveBeenCalledWith('quotes:group:create:success');
            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create');
        });

        it('should display an alert if massCollection is empty', function() {
            view._onCreateGroupBtnClicked({});

            expect(app.alert.show).toHaveBeenCalledWith('quote_grouping_message');
        });
    });

    describe('_onNewGroupedItemsCreateSuccess()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'off').callsFake(function() {});
            sinon.stub(view.layout, 'moveMassCollectionItemsToNewGroup').callsFake(function() {});

            view._onNewGroupedItemsCreateSuccess({});
        });

        afterEach(function() {

        });

        it('should call context.off quotes:group:create:success', function() {
            expect(view.context.off).toHaveBeenCalledWith('quotes:group:create:success');
        });

        it('should call layout.moveMassCollectionItemsToNewGroup', function() {
            expect(view.layout.moveMassCollectionItemsToNewGroup).toHaveBeenCalled();
        });
    });

    describe('_onDeleteBtnClicked()', function() {
        var massCollection;
        var model;

        beforeEach(function() {
            model = app.data.createBean('Products', {
                id: 'modelId1'
            });
            massCollection = new Backbone.Collection({
                id: 'pbId1'
            });
            sinon.stub(app.alert, 'show').callsFake(function() {});
            sinon.stub(app.lang, 'get').callsFake(function() {});
            sinon.stub(view.context, 'trigger').callsFake(function() {});
        });

        describe('with massCollection items', function() {
            beforeEach(function() {
                massCollection.add(model);
                view.massCollection = massCollection;

                view._onDeleteBtnClicked({});
            });

            it('should call app.alert.show with confirm message', function() {
                expect(app.alert.show).toHaveBeenCalledWith('confirm_delete');
            });
        });

        describe('with empty massCollection', function() {
            beforeEach(function() {
                view._onDeleteBtnClicked({});
            });

            it('should call app.alert.show with error message', function() {
                expect(app.alert.show).toHaveBeenCalledWith('quote_grouping_message');
            });
        });
    });

    describe('_dispose()', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'off').callsFake(function() {});
            sinon.stub(view, '_super').callsFake(function() {});

            view._dispose();
        });

        it('should call context.off quotes:group:create:success', function() {
            expect(view.context.off).toHaveBeenCalledWith('quotes:group:create:success');
        });

        it('should call _super', function() {
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
