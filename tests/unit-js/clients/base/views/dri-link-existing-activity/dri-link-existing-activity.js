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
describe('DRI_LinkExistingActivity View', function() {
    let app;
    let sinonSandbox;
    let view;
    let layout;
    let context;
    let viewName = 'dri-link-existing-activity';
    let moduleName = 'Tasks';
    let layoutName = 'list';

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadComponent('base', 'view', viewName);

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();

        layout = SugarTest.createLayout('base', moduleName, layoutName, {}, context);
        view = SugarTest.createView('base', moduleName, viewName, {module: moduleName}, context, null, layout);

        sinonSandbox.stub(view, '_super');
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.app.view.reset();
        app.data.reset();
        layout.dispose();
        view.dispose();
        view = null;
        layout = null;
    });

    describe('bindDataChange', function() {
        it('should add listeners and call view._super.bindDataChange', function() {
            sinonSandbox.stub(view, 'listenTo');
            view.bindDataChange();
            expect(view.listenTo).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('bindDataChange');
        });
    });

    describe('checkDuplicate', function() {
        let stageModel;
        let massCollectionModel;
        let model;
        let modelStub;

        beforeEach(function() {
            stageModel = app.data.createBean('DRI_SubWorkflows');
            let stageModelStub = sinonSandbox.stub(stageModel, 'get');
            stageModelStub.withArgs('name').returns('stage_name');
            stageModelStub.withArgs('activities').returns([
                {
                    name: 'test_activity1',
                },
                {
                    name: 'test_activity2',
                },
            ]);

            massCollectionModel = app.data.createBean(moduleName);
            let massCollectionStub = sinonSandbox.stub(massCollectionModel, 'get');
            massCollectionStub.withArgs('name').returns('activity1');
            massCollectionStub.withArgs('id').returns('101');

            let contextGetSub = sinonSandbox.stub(view.context, 'get');
            contextGetSub.withArgs('stageParent').returns(stageModel);
            contextGetSub.withArgs('mass_collection').returns([massCollectionModel]);

            model = app.data.createBean(moduleName);
            modelStub = sinonSandbox.stub(model, 'get');
            modelStub.withArgs('_module').returns(moduleName);

            sinonSandbox.stub(app.lang, 'get').returns('');
            sinonSandbox.stub(app.lang, 'getModuleName').returns(moduleName);
        });

        afterEach(function() {
            stageModel.dispose();
            massCollectionModel.dispose();
            model.dispose();
            sinonSandbox.restore();
        });

        it('should return false', function() {
            modelStub.withArgs('name').returns('activity_test');
            modelStub.withArgs('id').returns('001');
            model.attributes = [];

            let response = view.checkDuplicate(model, view.context);
            expect(response).toBe(false);
        });

        it('should call app.alert.show and return true', function() {
            sinonSandbox.stub(app.alert, 'show');
            modelStub.withArgs('name').returns('activity1');
            modelStub.withArgs('id').returns('102');
            model.attributes = [];

            let response = view.checkDuplicate(model, view.context);
            expect(app.alert.show).toHaveBeenCalled();
            expect(response).toBe(true);
        });
    });

    describe('_validateSelection', function() {
        let contextGetSub;

        beforeEach(function() {
            contextGetSub = sinonSandbox.stub(view.context, 'get');
            contextGetSub.withArgs('mass_collection').returns([
                {
                    name: 'test_activity1',
                },
                {
                    name: 'test_activity2',
                },
            ]);
            app.drawer = {
                close: sinonSandbox.stub(),
            };
            view.maxSelectedRecords = 20;
        });

        afterEach(function() {
            delete app.drawer;
        });

        it('should call _showMaxSelectedRecordsAlert ', function() {
            view._showMaxSelectedRecordsAlert = sinonSandbox.stub();
            view.maxSelectedRecords = 0;
            view._validateSelection();
            expect(view._showMaxSelectedRecordsAlert).toHaveBeenCalled();
        });

        it('should call app.drawer.close if the selection is valid', function() {
            sinonSandbox.stub(view, 'checkDuplicate').returns(false);
            view._validateSelection();
            expect(app.drawer.close).toHaveBeenCalled();
        });

        it('should not call app.drawer.close as the selection is not valid', function() {
            sinonSandbox.stub(view, 'checkDuplicate').returns(true);
            view._validateSelection();
            expect(app.drawer.close).not.toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        it('should call view.stopListening and view._super_dispose', function() {
            sinonSandbox.stub(view, 'stopListening');
            view._dispose();
            expect(view.stopListening).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
