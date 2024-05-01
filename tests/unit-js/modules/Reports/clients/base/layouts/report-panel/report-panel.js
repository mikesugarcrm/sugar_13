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
describe('Reports.Base.Layouts.ReportPanel', function() {
    var app;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', 'Reports', 'report-panel', {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();
        app = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    describe('_initPanels', function() {
        it('should set this.panels appropriately', function() {
            var callStub = sinon.stub(app.api, 'call');
            var url = app.api.buildURL('Reports/panel', '123');

            layout.model.set('id', '123');

            layout._initPanels();

            expect(callStub.getCall(0).args[0]).toEqual('read');
            expect(callStub.getCall(0).args[1]).toEqual(url);

            callStub.restore();
        });
    });

    describe('handleSave', function() {
        it('should not update if user doesn\'t have access', function() {
            var stubHasAccessToModel = sinon.stub(app.acl, 'hasAccessToModel').returns(true);
            var callStub = sinon.stub(app.api, 'call');

            layout.model.set('id', '123');
            layout.handleSave();

            var url = app.api.buildURL('Reports/panel', '123');

            expect(url).toEqual('../../../rest/v10/Reports/panel/123');

            stubHasAccessToModel.restore();
            callStub.restore();
        });
    });
});
