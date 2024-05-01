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
describe('controller', function() {
    it('should not call logout if app status equals offline and not authenticated', function() {
        var app = SugarTest.app;
        var params = {
                module: 'Contacts',
                layout: 'list'
            };
        app.config.appStatus = 'offline';
        var logoutSpy = sinon.spy(app, 'logout');
        var ajaxPrevention = sinon.stub(app.api, 'call');
        var triggerBeforeStub = sinon.stub(app, 'triggerBefore');

        app.controller.loadView(params);

        expect(logoutSpy).not.toHaveBeenCalled();
        app.config.appStatus = 'online';
        ajaxPrevention.restore();
        logoutSpy.restore();
        triggerBeforeStub.restore();
    });
});
