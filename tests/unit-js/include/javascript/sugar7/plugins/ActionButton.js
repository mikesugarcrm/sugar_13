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
describe('Plugins.ActionButton', function() {
    var app;
    var plugin;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('ActionButton');
        plugin = app.plugins.plugins.layout.ActionButton;
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app = null;
    });

    describe('plugin', function() {
        it('should insert action buttons in dashboard header dropdown', function() {
            sinon.stub(plugin, 'getActionButtonsRowActions').returns('action buttons');
            let stub = sinon.stub(plugin, 'insertInDashboardHeader');
            plugin.type = 'dashboard';
            plugin.insertActionButtonsRows();
            expect(stub).toHaveBeenCalledWith('action buttons');
        });
    });
});
