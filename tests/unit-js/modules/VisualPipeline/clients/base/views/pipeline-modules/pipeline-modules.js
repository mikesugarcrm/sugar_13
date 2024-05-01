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
describe('VisualPipeline.View.PipelineModuleView', function() {
    var app;
    var view;
    var layout;
    var context;
    var ctxModel;
    var meta;
    var parentLayout;

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('VisualPipeline');
        context.set('model', ctxModel);

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'VisualPipeline', 'config-drawer', {},  context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'VisualPipeline', 'pipeline-modules', {}, context, true, layout);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var options;
        var offStub;
        beforeEach(function() {
            offStub = sinon.stub();
            options = {
                context: context
            };
            sinon.spy(app.data, 'createBeanCollection');
            sinon.stub(view.context, 'get').callsFake(function() {
                return 'test';
            });
            sinon.stub(view, '_super').callsFake(function() {});
        });

        it('should call view._super method', function() {
            view.layout = {
                collection: {
                    name: 'collectionTest',
                    off: offStub
                },
                off: offStub
            };
            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call view.context.get method with allowedModules and assign it to view.allowedModules', function() {
            view.layout = {
                collection: {
                    name: 'collectionTest',
                    off: offStub
                },
                off: offStub
            };
            view.initialize(options);

            expect(view.context.get).toHaveBeenCalledWith('allowedModules');
            expect(view.allowedModules).toEqual('test');
        });

        describe('when view.layout.collection is defined', function() {
            it('should not call app.data.createBeanCollection and assign it to view.collection', function() {
                var offStub = sinon.stub();
                view.collection = {};
                view.layout = {
                    collection: {
                        name: 'collectionTest',
                        off: offStub
                    },
                    off: offStub
                };
                view.initialize(options);

                expect(app.data.createBeanCollection).not.toHaveBeenCalled();
                expect(view.collection.name).toEqual('collectionTest');
            });
        });

        describe('when view.layout.collection is not defined', function() {
            it('should call app.data.createBeanCollection and assign it to view.collection', function() {
                view.layout = {
                    off: offStub
                };
                view.initialize(options);

                expect(app.data.createBeanCollection).toHaveBeenCalledWith(view.module);
            });
        });
    });
});

