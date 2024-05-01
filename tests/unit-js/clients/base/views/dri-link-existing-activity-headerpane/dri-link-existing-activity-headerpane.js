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
describe('DRI_LinkExistingActivityHeaderPane View', function() {
    let app;
    let sinonSandbox;
    let view;
    let layout;
    let context;
    let viewName = 'dri-link-existing-activity-headerpane';
    let moduleName = 'Accounts';
    let layoutName = 'record';

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadComponent('base', 'view', viewName);

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();

        layout = SugarTest.createLayout('base', moduleName, 'dashboard', {}, context);
        view = SugarTest.createView('base', moduleName, viewName, {}, context, null, layout);

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
            sinonSandbox.stub(view, 'listenToOnce');
            sinonSandbox.stub(view, 'listenTo');
            view.bindDataChange();
            expect(view.listenToOnce).toHaveBeenCalled();
            expect(view.listenTo).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('bindDataChange');
        });
    });

    describe('_formatTitle', function() {
        it('should call app.lang.get and return formatted title', function() {
            sinonSandbox.stub(app.lang, 'get').returns('test');
            let title = 'title';
            let response = view._formatTitle(title);
            expect(app.lang.get).toHaveBeenCalledWith('LBL_MODULE_NAME', moduleName);
            expect(app.lang.get).toHaveBeenCalledWith(title, moduleName, {module: 'test'});
            expect(response).toBe('test');
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
