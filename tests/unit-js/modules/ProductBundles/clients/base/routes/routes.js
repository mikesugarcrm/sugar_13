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
describe('ProductBundles.Routes', function() {
    var app;
    var buildKeyStub;
    var getStub;
    var setStub;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);
        // FIXME: SC-4677, load additionalComponents in tests
        // "Before Route Show Wizard Check" dependency
        buildKeyStub = sinon.stub(app.user.lastState, 'buildKey');
        getStub = sinon.stub(app.user.lastState, 'get');
        setStub = sinon.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../modules/ProductBundles/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
    });

    describe('Routes', function() {
        var mockKey = 'foo:key';
        var oldIsSynced;

        beforeEach(function() {
            oldIsSynced = app.isSynced;
            app.isSynced = true;
            sinon.stub(app.router, 'redirect');

            sinon.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.stub(app.api, 'isAuthenticated').returns(true);
            sinon.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
        });

        it('should redirect to Quotes records view on ProductBundles list', function() {
            app.router.navigate('ProductBundles', {trigger: true});

            expect(app.router.redirect).toHaveBeenCalledWith('#Quotes');
        });

        it('should redirect to Quotes records view on ProductBundles create', function() {
            app.router.navigate('ProductBundles/create', {trigger: true});

            expect(app.router.redirect).toHaveBeenCalledWith('#Quotes');
        });

        it('should redirect to Quotes records view on ProductBundles record', function() {
            app.router.navigate('ProductBundles/test-hash', {trigger: true});

            expect(app.router.redirect).toHaveBeenCalledWith('#Quotes');
        });
    });
});
