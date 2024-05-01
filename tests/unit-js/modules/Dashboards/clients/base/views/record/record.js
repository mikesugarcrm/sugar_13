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

describe('Dashboards.Base.View.Record', function() {
    var app;
    var view;
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record', 'Dashboards');
        view = SugarTest.createView('base', 'Dashboards', 'record');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sandbox.restore();
        view.dispose();
        view.context = null;
        view = null;
    });

    describe('getDeleteMessages', function() {
        var appLangGetStub;

        beforeEach(function() {
            appLangGetStub = sandbox.stub(app.lang, 'get');
        });

        it('should use translation from the model\'s dashboard_module', function() {
            view.model.set({name: 'to be translated', dashboard_module: 'Accounts'});
            view.moduleName = 'Dashboards';

            appLangGetStub.withArgs('to be translated', 'Accounts').returns('translated');
            appLangGetStub.withArgs('LBL_DELETE_DASHBOARD_CONFIRM', 'Dashboards', {
                name: 'translated'
            }).returns('translated confirmation');
            appLangGetStub.withArgs('LBL_DELETE_DASHBOARD_SUCCESS', 'Dashboards', {
                name: 'translated'
            }).returns('translated success');

            var messages = view.getDeleteMessages();

            expect(messages.confirmation).toEqual('translated confirmation');
            expect(messages.success).toEqual('translated success');
        });
    });
});
