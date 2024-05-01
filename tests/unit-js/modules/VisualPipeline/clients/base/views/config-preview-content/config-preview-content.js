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
describe('VisualPipeline.View.ConfigPreviewContentView', function() {
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

        view = SugarTest.createView('base', 'VisualPipeline', 'config-preview-content', {}, context, true, layout);

    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'render').callsFake(function() {});
            sinon.stub(view, 'setupTabChange').callsFake(function() {});
            sinon.stub(view.model, 'on').callsFake(function() {});
            sinon.stub(view.context, 'on').callsFake(function() {});
            sinon.stub(view.collection, 'on').callsFake(function() {});
            view.bindDataChange();
        });

        it('should call view.model.on with change', function() {

            expect(view.model.on).toHaveBeenCalledWith('change');
        });

        it('should call view.context.on with change', function() {

            expect(view.context.on).toHaveBeenCalledWith('pipeline:config:tabs-initialized');
        });

        it('should call view.collection.on with change', function() {

            expect(view.collection.on).toHaveBeenCalledWith('change');
        });
    });

    describe('setupTabChange', function() {
        var contentStub;
        var onStub;
        beforeEach(function() {
            sinon.stub(view, 'render').callsFake(function() {});
            onStub = sinon.stub(jQuery.fn, 'on').callsFake(function() {});
            contentStub = sinon.stub().withArgs('#tabs select.module-selection').returns({
                name: 'change',
                on: onStub,
                val: function() {
                    return 'Leads';
                }
            });
            sinon.stub(view, 'closestComponent').callsFake(function() {
                return {
                    layout: {
                        name: '#tabs select.module-selection',
                        $: contentStub
                    }

                };
            });

            view.setupTabChange();
        });

        it('should call closestComponent method', function() {

            expect(view.closestComponent).toHaveBeenCalledWith('side-pane');
        });

        it('should call content.$ method', function() {

            expect(contentStub).toHaveBeenCalledWith('#tabs select.module-selection');
        });

        it('should call $(el).on method', function() {

            expect(onStub).toHaveBeenCalledWith('change');
        });
    });

    describe('removeTabChangeEvents', function() {
        var contentStub;
        var offStub;
        beforeEach(function() {
            offStub = sinon.stub(jQuery.fn, 'off').callsFake(function() {});
            contentStub = sinon.stub().withArgs('#tab li.tab').returns({
                name: 'click',
                on: offStub
            });
            sinon.stub(view, 'closestComponent').callsFake(function() {
                return {
                    layout: {
                        name: '#tab li.tab',
                        $: contentStub
                    }
                };
            });

            view.removeTabChangeEvents();
        });

        it('should call closestComponent method', function() {

            expect(view.closestComponent).toHaveBeenCalledWith('side-pane');
        });

        it('should call content.$ method', function() {

            expect(contentStub).toHaveBeenCalledWith('#tabs li.tab');
        });

        it('should call $(el).off method', function() {

            expect(offStub).toHaveBeenCalledWith('click');
        });
    });

    describe('render', function() {
        var contentStub;
        let valStub;

        beforeEach(function() {
            valStub = sinon.stub().returns('Cases');
            contentStub = sinon.stub().withArgs('#tabs select.module-selection').returns({
                name: 'change',
                val: valStub
            });
            sinon.stub(view, 'closestComponent').callsFake(function() {
                return {
                    layout: {
                        name: '#tab .ui-tabs-active',
                        $: contentStub
                    }
                };
            });
        });

        it('should call view.closestComponent method', function() {
            view.render();

            expect(view.closestComponent).toHaveBeenCalledWith('side-pane');
        });

        it('should call content.$().attr() method', function() {
            view.render();

            expect(contentStub).toHaveBeenCalledWith('#tabs select.module-selection');
            expect(valStub).toHaveBeenCalled();
        });

        it('should call view.collections.models[0].get method', function() {
            view.collection = {
                models: [{
                    get: sinon.stub()
                }],
                off: sinon.stub()
            };
            view.render();

            expect(view.collection.models[0].get).toHaveBeenCalledWith('enabled_module');
            expect(valStub).toHaveBeenCalled();
        });

        describe('when current model is defined', function() {
            var getStub;
            beforeEach(function() {
                sinon.stub(view, 'getFieldLabel').withArgs('Cases').returns('LBL_NAME')
                    .withArgs('name').returns('LBL_NAME')
                    .withArgs('account_name').returns('LBL_ACCOUNT_NAME');
            });

            it('should assign current model to view.currentModel', function() {
                getStub = sinon.stub().withArgs('enabled_module').returns('Cases');
                view.collection = {
                    models: [{
                        name: 'Cases',
                        get: getStub
                    }],
                    off: sinon.stub()
                };
                view.currentModel = view.collection.models[0];
                view.render();

                expect(view.currentModel).toEqual({
                    name: 'Cases',
                    get: getStub
                });
            });

            it('should call currentModel.get and assign its value to view.previewModel', function() {
                view.collection = {
                    models: [{
                        name: 'Cases',
                        get: sinon.stub().withArgs('enabled_module').returns('Cases')
                    }],
                    off: sinon.stub()
                };
                view.currentModel = view.collection.models[0];
                view.render();

                expect(view.currentModel.get).toHaveBeenCalledWith('enabled_module');
                expect(view.previewModel.moduleName).toEqual('Cases');
            });

            it('should populate view.previousModel.tile_body_fields', function() {
                view.collection = {
                    models: [{
                        name: 'Cases',
                        get: function(arg) {return arg === 'enabled_module' ? 'Cases' : ['account_name'];}
                    }],
                    off: sinon.stub()
                };
                view.currentModel = view.collection.models[0];
                view.render();

                expect(view.previewModel.tile_body_fields).toEqual(['LBL_ACCOUNT_NAME']);
            });

            it('should call view._super with render', function() {
                view.collection = {
                    models: [{
                        name: 'Cases',
                        get: sinon.stub().withArgs('enabled_module').returns('Cases')
                    }],
                    off: sinon.stub()
                };
                view.currentModel = view.collection.models[0];
                sinon.stub(view, '_super').callsFake(function() {});
                view.render();

                expect(view._super).toHaveBeenCalledWith('render');
            });
        });

        describe('when current model is undefined', function() {
            it('should not call view._super method', function() {
                view.currentModel = undefined;
                view.render();

                expect(view._super).not.toHaveBeenCalled();
            });
        });
    });

    describe('getFieldLabel', function() {
        var fieldName;
        var res;
        it('should call app.metadat.getModule method', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return ['account_name'];
            });
            fieldName = 'account_name';
            view.previewModel = {
                moduleName: 'VisualPipeline'
            };
            view.getFieldLabel(fieldName);

            expect(app.metadata.getModule).toHaveBeenCalledWith('VisualPipeline', 'fields');
        });

        describe('when field.name equals fieldName and field is an object', function() {
            it('should return a non-empty label', function() {
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return [{
                        name: 'testName',
                        vname: 'testLabel'
                    }];
                });
                fieldName = 'testName';
                view.previewModel = {
                    moduleName: 'VisualPipeline'
                };
                res = view.getFieldLabel(fieldName);

                expect(res).toEqual('testLabel');
            });
        });

        describe('when field.name is not equal to fieldName or field is not an object', function() {
            it('should return a empty label', function() {
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return ['test'];
                });
                fieldName = 'testName';
                view.previewModel = {
                    moduleName: 'VisualPipeline'
                };
                res = view.getFieldLabel(fieldName);

                expect(res).toEqual('');
            });

            it('should return a empty label', function() {
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return [{
                        name: 'testName',
                        vname: 'testLabel'
                    }];
                });
                fieldName = 'testNameNot';
                view.previewModel = {
                    moduleName: 'VisualPipeline'
                };
                res = view.getFieldLabel(fieldName);

                expect(res).toEqual('');
            });
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.stub(view, 'removeTabChangeEvents').callsFake(function() {});
            sinon.stub(view, '_super').callsFake(function() {});
            view._dispose();
        });

        it('should call view.removeTabChangeEvents method', function() {

            expect(view.removeTabChangeEvents).toHaveBeenCalled();
        });

        it('should call view._super method with _dispose', function() {

            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});

