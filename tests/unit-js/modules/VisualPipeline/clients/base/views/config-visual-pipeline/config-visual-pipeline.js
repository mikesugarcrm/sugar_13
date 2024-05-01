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
describe('VisualPipeline.View.ConfigVisualPipelineView', function() {
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
        context.set('collection', app.data.createBeanCollection('VisualPipeline'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'VisualPipeline', 'config-drawer', {},  context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'VisualPipeline', 'config-visual-pipeline', {}, context, true, layout);
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var options;
        beforeEach(function() {
            options = {
                context: context
            };
            sinon.stub(view, 'customizeMetaFields').callsFake(function() {});
            view.initialize(options);
        });

        it('should call view._super method', function() {

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call view.customizeMetaFields method', function() {

            expect(view.customizeMetaFields).toHaveBeenCalled();
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'render').callsFake(function() {});
            sinon.stub(view.collection, 'on').callsFake(function() {});
            sinon.stub(view, 'listenTo').callsFake(function() {});
            view.bindDataChange();
        });

        it('should call view.context.on with change', function() {

            expect(view.collection.on).toHaveBeenCalledWith('add remove reset');
        });
        it('should call view.listenTo with change', function() {

            expect(view.listenTo).toHaveBeenCalledWith(view.context, 'pipeline:config:set-active-module');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'get').callsFake(function() {});
            sinon.stub(view, '$').callsFake(function() {
                return {
                    tabs: sinon.stub()
                };
            });
            sinon.stub(view.context, 'trigger').callsFake(function() {});
            view.render();
        });

        it('should call view._super with render', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call view.context.trigger method with tabs-initialized', function() {

            expect(view.context.trigger).toHaveBeenCalledWith('pipeline:config:tabs-initialized');
        });
    });

    describe('customizeMetaFields', function() {
        describe('when field.twoColumns is true', function() {
            describe('when twoColumns has length of 2', function() {
                beforeEach(function() {
                    view.meta = {
                        panels: [{
                            fields: [
                                {
                                    twoColumns: true,
                                    name: 'test1'
                                },
                                {
                                    twoColumns: true,
                                    name: 'test2'
                                }
                            ]
                        }],
                        customizedFields: []
                    };
                    view.customizeMetaFields();
                });
                it('should push twoColumns to customizeFields', function() {

                    expect(view.meta.customizedFields).toEqual([
                        [{
                            twoColumns: true,
                            name: 'test1'
                        },
                        {
                            twoColumns: true,
                            name: 'test2'
                        }]
                    ]);
                });
            });
            describe('when twoColumns does not have length of 2', function() {
                beforeEach(function() {
                    view.meta = {
                        panels: [{
                            fields: [
                                {
                                    twoColumns: true,
                                    name: 'test1'
                                }
                            ]
                        }],
                        customizedFields: []
                    };
                    view.customizeMetaFields();
                });
                it('should not push twoColumns to customizeFields', function() {

                    expect(view.meta.customizedFields).toEqual([]);
                });
            });
        });
        describe('when field.twoColumns is not true', function() {
            beforeEach(function() {
                view.meta = {
                    panels: [{
                        fields: [
                            {
                                name: 'test1'
                            },
                            {
                                name: 'test2'
                            }
                        ]
                    }],
                    customizedFields: []
                };
                view.customizeMetaFields();
            });

            it('should push fields to customizeFields', function() {

                expect(view.meta.customizedFields).toEqual([
                    [{
                        name: 'test1'
                    }],
                    [{
                        name: 'test2'
                    }]
                ]);
            });
        });
    });

    describe('_setupActiveModule', function() {
        it('should call render function', function() {
            sinon.stub(view, 'render');
            view._setupActiveModule('Cases');
            expect(view.render).toHaveBeenCalled();
        });
    });
});

