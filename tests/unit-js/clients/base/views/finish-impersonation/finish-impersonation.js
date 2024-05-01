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
describe('Finish Impersonation', function() {

    var app;
    var view;
    var sinonSandbox;
    var menuMeta;
    var close;
    beforeEach(function() {
        var context;
        var meta;
        app = SugarTest.app;
        app.config.tenant = 'srn:dev:iam:na:1234567890:tenant';
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        context = app.context.getContext();

        meta = {
            comeBackUrl: 'http://cloud.console.url?tid=1234567890'
        };
        view = SugarTest.createView('base','Accounts', 'finish-impersonation', meta, context);
        sinonSandbox = sinon.createSandbox();
        sinonSandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks) {
            if (callbacks.complete) {
                callbacks.complete();
            }
        });

        close = sinonSandbox.stub(window, 'close');
    });
    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        Handlebars.templates = {};
        view.dispose();
        view = null;
        menuMeta = null;
    });

    it('should close window when opener still exists', function() {
        window.opener = {'not': 'empty'};

        view.finishImpersonation();

        expect(close).toHaveBeenCalled();
    });

    it('should restore origin sesssion', function() {
        window.opener = {'not': 'empty'};

        app.cache.set('ImpersonationFor', 'admin-user-id');
        app.cache.set('AuthAccessToken', 'user-access-token');
        app.cache.set('AuthRefreshToken', 'user-refresh-token');
        app.cache.set('OriginAuthAccessToken', 'admin-access-token');
        app.cache.set('OriginAuthRefreshToken', 'admin-refresh-token');

        view.finishImpersonation();

        expect(app.cache.has('ImpersonationFor')).toBeFalsy();
        expect(app.cache.has('OriginAuthAccessToken')).toBeFalsy();
        expect(app.cache.has('OriginAuthRefreshToken')).toBeFalsy();
        expect(app.cache.get('AuthAccessToken')).toEqual('admin-access-token');
        expect(app.cache.get('AuthRefreshToken')).toEqual('admin-refresh-token');
    });

});
