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
describe('Reports.Base.Views.ReportHeader', function() {
    var app;
    var view;
    var context;
    var layout;
    var sinonSandbox;
    var layoutName = 'record';
    var platform = 'base';
    var moduleName = 'Reports';
    var viewName = 'report-header';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent(platform, 'view', viewName, moduleName);

        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.routing.start();
        app.data.declareModels();

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName,
            model: app.data.createBean(moduleName),
            collection: app.data.createBeanCollection(),
        });

        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });

        sinonSandbox = sinon.createSandbox();

        sinon.stub(app.controller, 'context').value(context);

        initOptions = {
            type: viewName,
            name: viewName,
            def: {
                view: viewName
            },
            module: moduleName,
            context: context,
        };
    });

    afterEach(function() {
        sinonSandbox.restore();
        sinon.restore();
        app.router.stop();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        app.data.reset();
        layout.dispose();
        view.dispose();
        view = null;
        app = null;
    });

    describe('initialize()', function() {
        var localSinonSandbox;
        beforeEach(function() {
            localSinonSandbox = sinon.createSandbox();
            localSinonSandbox.stub(Backbone.history, 'getFragment').returns(moduleName);

            view = SugarTest.createView(platform, moduleName, viewName, {module: moduleName}, context, true);
        });

        afterEach(function() {
            localSinonSandbox.restore();
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(view._currentUrl).toEqual(moduleName);
        });
    });

    describe('Warning delete', function() {
        var sinonSandbox;
        var alertShowStub;
        var routerStub;

        beforeEach(function() {
            sinonSandbox = sinon.createSandbox();
            routerStub = sinonSandbox.stub(app.router, 'navigate');
            sinonSandbox.stub(Backbone.history, 'getFragment');
            alertShowStub = sinonSandbox.stub(app.alert, 'show');

            view = SugarTest.createView(platform, moduleName, viewName, {module: moduleName}, context, true);
        });

        afterEach(function() {
            sinonSandbox.restore();
            view.dispose();
        });

        it('Should return true if _modelToDelete is not defined', function() {
            sinonSandbox.stub(view, 'warnDelete');

            expect(view.beforeRouteDelete()).toBeTruthy();
        });

        it('Should return false if _modelToDelete is defined (to prevent routing to other views)', function() {
            sinonSandbox.stub(view, 'warnDelete');
            view._modelToDelete = new Backbone.Model();

            expect(view.beforeRouteDelete()).toBeFalsy();
        });

        it('Should redirect the user to the targetUrl', function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteDelete');

            sinonSandbox.stub(app.lang, 'getModuleName').returns('contacts');

            view._modelToDelete = new Backbone.Model();
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.deleteModel();

            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });
});
