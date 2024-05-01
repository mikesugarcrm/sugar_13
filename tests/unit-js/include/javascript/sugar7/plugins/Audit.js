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
describe('Plugins.Audit', function() {
    var app;
    var view;
    var plugin;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('Audit');
        plugin = app.plugins.plugins.view.Audit;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'view', 'record');
        view = SugarTest.createView('base', 'Accounts', 'record');

        app.drawer = {open: sinon.stub()};
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        delete app.drawer;
        app = null;
    });

    describe('plugin', function() {
        it('should attach init event handler', function() {
            stub = sinon.stub(view, 'on');
            plugin.onAttach.apply(view);
            expect(stub).toHaveBeenCalledWith('init');
        });

        it('should open a drawer', function() {
            plugin.onAttach.apply(view);
            var context = new app.Context();
            sinon.stub(view.context, 'getChildContext').returns(context);
            view.auditClicked();
            expect(app.drawer.open).toHaveBeenCalledWith({
                layout: 'audit',
                context: context
            });
        });
    });
});
