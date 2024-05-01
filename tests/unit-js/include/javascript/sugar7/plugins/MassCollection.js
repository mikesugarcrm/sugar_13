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
describe('MassCollection plugin:', function() {

    var app, layout, view, data, massCollection, collection, context;
    var moduleName = 'Accounts',
        viewName = 'multi-selection-list',
        layoutName = 'multi-selection-list';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();
        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });
        var meta = {
            selection: {
                type: 'multi'
            }
        };
        // This initializes the MassCollection plugin thanks to the 'init' event.
        view = SugarTest.createView('base', moduleName, viewName, meta, context, null, layout);
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        layout = null;
        view = null;
        data = null;
        sinon.restore();
    });

    describe('Initialize:', function() {
        it('should create the mass collection', function() {
            expect(view.massCollection).toBeDefined();
        });

        it('should not initialize on non-multi selection views', function() {
            view.dispose();
            view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);

            expect(view.massCollection).not.toBeDefined();
        });
    });

    describe('CreateMassCollection:', function() {
        it('should set the mass collection in the context', function() {
            view.context.attributes.mass_collection = null;
            view.createMassCollection();
            expect(view.context.get('mass_collection')).toBeDefined();
        });
    });

    describe('addModel:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}]);
            view.collection.add([{id: 1}, {id: 2}]);
        });

        it('should add the model to the mass collection', function() {
            view.addModel({id: 3});
            var addedModel = massCollection.get('3');

            expect(addedModel).toBeDefined();
        });

        it('should trigger the "all:checked" event on the massCollection when adding the last model', function() {
            sinon.spy(massCollection, 'trigger');
            view.addModel({id: 2});

            expect(massCollection.trigger).toHaveBeenCalledWith('all:checked');
        });
    });

    describe('addAllModels:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
            view.collection.add([{id: 4}, {id: 5}]);
        });

        it('should add all models of the current collection to the massCollection', function() {
            view.addAllModels();
            expect(_.intersection(view.collection.models, massCollection.models)).toEqual(view.collection.models);
        });

        it('should reset the massCollection to match the view collection', function() {
            // Boolean set to `false` indicating the mass collection is tied to the collection.
            view.independentMassCollection = false;
            view.addAllModels();
            expect(_.isEqual(massCollection.models, view.collection.models)).toBe(true);
        });
    });

    describe('removeModel:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
            view.collection.add([{id: 1}, {id: 2}]);
        });

        it('should remove the model from the mass collection', function() {
            view.removeModel({id: 1});
            var model1 = massCollection.get('1');
            var model2 = massCollection.get('2');
            var model3 = massCollection.get('3');

            expect(model1).toBeUndefined();
            expect(model2).toBeDefined();
            expect(model3).toBeDefined();
        });

        it('should trigger the event "not:all:checked" on the massCollection', function() {
            sinon.spy(massCollection, 'trigger');
            view.removeModel({id: 1});

            expect(massCollection.trigger).toHaveBeenCalledWith('not:all:checked');
        });
    });

    describe('removeAllModels:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
            view.collection.add([{id: 2}, {id: 3}]);
        });

        using('independentMassCollection boolean', [true, false], function(independentMassCollection) {
            it('should remove models from the mass collection', function() {
                view.independentMassCollection = independentMassCollection;
                view.removeAllModels();
                var removedModel1 = massCollection.get('2');
                var removedModel2 = massCollection.get('3');
                if (independentMassCollection) {
                    expect(removedModel1).toBeFalsy();
                    expect(removedModel2).toBeFalsy();
                    expect(massCollection.length).toBeGreaterThan(0);
                } else {
                    expect(massCollection.length).toBe(0);
                }
            });

        });
    });

    describe('clearMassCollection:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
        });

        it('should clear the mass collection', function() {
            var resetSpy = sinon.spy(massCollection, 'reset');
            view.clearMassCollection();

            expect(resetSpy).toHaveBeenCalled();
            expect(_.isEmpty(massCollection.models)).toBe(true);
        });

        it('should trigger the "not:all:checked" event on the massCollection', function() {
            sinon.stub(massCollection, 'trigger');
            view.clearMassCollection();

            expect(massCollection.trigger).toHaveBeenCalledWith('not:all:checked');
        });
    });

    //Events
    describe('getting an "mass_collection:add" event', function() {
        it('should call addModel method', function() {
            sinon.stub(view, 'addModel');
            view.trigger('init');
            view.context.trigger('mass_collection:add', {});
            expect(view.addModel).toHaveBeenCalled();
        });
    });

    describe('getting an "mass_collection:add:all" event', function() {
        it('should call addAllModels method', function() {
            sinon.stub(view, 'addAllModels');
            view.trigger('init');
            view.context.trigger('mass_collection:add:all', {});
            expect(view.addAllModels).toHaveBeenCalled();
        });
    });

    describe('getting an "mass_collection:remove" event', function() {
        it('should call removeModel method', function() {
            sinon.stub(view, 'removeModel');
            view.trigger('init');
            view.context.trigger('mass_collection:remove', {});
            expect(view.removeModel).toHaveBeenCalled();
        });
    });

    describe('getting an "mass_collection:remove:all" event', function() {
        it('should call removeAllModels method', function() {
            sinon.stub(view, 'removeAllModels');
            view.trigger('init');
            view.context.trigger('mass_collection:remove:all', {});
            expect(view.removeAllModels).toHaveBeenCalled();
        });
    });

    describe('getting an "mass_collection:clear" event', function() {
        it('should call clearMassCollection method', function() {
            sinon.stub(view, 'clearMassCollection');
            view.trigger('init');
            view.context.trigger('mass_collection:clear');
            expect(view.clearMassCollection).toHaveBeenCalled();
        });
    });
});
