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

describe('PortalActivityView', function() {
    var app;
    var viewName = 'activity';
    var moduleName = 'Notes';
    var view;
    var modelId = 'new_model_id';
    var drawer;

    beforeEach(function() {
        app = SugarTest.app;
        context = new app.Context();
        context.set({
            module: moduleName
        });
        context.prepare();
        SugarTest.loadComponent('portal', 'view', viewName);
        view = SugarTest.createView('portal', 'Cases', viewName, {}, context);
        app.data.declareModels();
        model = app.data.createBean(moduleName, {id: modelId});
        view.createLinkModel = sinon.stub().returns(model);
        drawer = app.drawer;
        app.drawer = {
            open: sinon.stub()
        };
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        sinon.restore();
        app.drawer = drawer;
    });

    describe('openNoteDrawer', function() {
        it('should open drawer with linked bean', function() {
            view.openNoteDrawer();
            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.drawer.open.lastCall.args[0].layout).toEqual('create');
            expect(app.drawer.open.lastCall.args[0].context.create).toBeTruthy();
            expect(app.drawer.open.lastCall.args[0].context.module).toEqual(moduleName);
            expect(app.drawer.open.lastCall.args[0].context.model.get('id')).toEqual(modelId);
        });
    });
});
