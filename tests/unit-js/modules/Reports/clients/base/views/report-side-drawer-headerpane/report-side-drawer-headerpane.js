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
describe('Reports.Base.Views.ReportsSideDrawerHeaderpane', function() {
    var app;
    var view;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        view = SugarTest.createView('base', 'Reports', 'report-side-drawer-headerpane', {}, context, true);

        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        app = null;
        view.context = null;
        view.model = null;
        view = null;
    });

    it('refreshWidget', function() {
        var stubTrigger = sinon.stub(view.context, 'trigger');

        view.refreshWidget();

        expect(stubTrigger).toHaveBeenCalledWith('report:side:drawer:list:refresh');
        expect(stubTrigger).toHaveBeenCalledWith('saved:report:chart:refresh');
    });
});
