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
describe("Subpanel List View", function() {
    var app, module, parentLayout, layout, view, sinonSandbox;

    beforeEach(function () {
        sinonSandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        module = 'Cases';
        layout = SugarTest.createLayout("base", module, "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", module, "list", null, null);
        layout.layout = parentLayout;
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        view = SugarTest.createView("base", module, 'subpanel-list', null, null, null, layout);
    });

    afterEach(function () {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        layout = null;
    });

    describe('Subpanel metadata intiialization', function() {
        it('should return most specific subpanel view metadata if found', function() {
            sinonSandbox.stub(view.options.context, "get").returns("Accounts");
            var expected = {a:1};
            var getViewStub = sinonSandbox.stub(app.metadata, 'getView').returns(expected);
            var actual = view._initializeMetadata(view.options);
            expect(actual).toEqual(expected);
            expect(getViewStub).toHaveBeenCalledThrice();
        });
    });

    describe('initialize', function() {
        var oldConfig;
        beforeEach(function() {
            oldConfig = app.config.maxSubpanelResult;
            app.config.maxSubpanelResult = 7;
        });
        afterEach(function(){
            app.config.maxSubpanelResult = oldConfig;
        });
        it('set the fetch limit on the context to app.config.maxSubpanelResult', function() {
            view = SugarTest.createView("base", 'Cases', 'subpanel-list', null, null, null, layout);
            expect(view.context.get('limit')).toEqual(app.config.maxSubpanelResult);
        });
    });

    describe("Warning unlink", function() {
        var sinonSandbox, alertShowStub, routerStub;
        beforeEach(function() {
            sinonSandbox = sinon.createSandbox();
            routerStub = sinonSandbox.stub(app.router, "navigate");
            sinonSandbox.stub(Backbone.history, "getFragment");
            alertShowStub = sinonSandbox.stub(app.alert, "show");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if _modelToUnlink is not defined", function() {
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalled();
        });
        it("should return true if _modelToUnlink is not defined", function() {
            sinonSandbox.stub(view, 'warnUnlink');
            expect(view.beforeRouteUnlink()).toBeTruthy();
        });
        it("should return false if _modelToUnlink is defined (to prevent routing to other views)", function() {
            sinonSandbox.stub(view, 'warnUnlink');
            view._modelToUnlink = new Backbone.Model();
            expect(view.beforeRouteUnlink()).toBeFalsy();
        });
        it("should redirect the user to the targetUrl", function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteUnlink');
            view._modelToUnlink = app.data.createBean(module);
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.unlinkModel();
            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });

    describe('_buildWidthKey', function() {
        var original;
        beforeEach(function() {
            original = app.user.lastState;
            sinonSandbox.spy(app.user.lastState, 'buildKey');
        });
        afterEach(function() {
            sinonSandbox.restore();
        });
        it('should build a unique key using app.user.lastState', function() {
            sinonSandbox.stub(view.context, 'get').returns('Accounts');
            view._thisListViewFieldSizesKey = 'test:key';
            var key = view._buildWidthKey();
            expect(app.user.lastState.buildKey).toHaveBeenCalledWith('test:key', 'Accounts');
            expect(key).toEqual('Accounts:test:key');
        });
    });
});
