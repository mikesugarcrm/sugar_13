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
describe('Base.Layout.HistoryDefault', function() {
    var layout, app, def;

    beforeEach(function() {
        app = SugarTest.app;
        def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, 'history-default', def, null);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('right pane', function() {

        it('should be always collapsed when the layout is created', function() {
            expect(layout.isSidePaneVisible()).toBe(false);
            layout.toggleSidePane();
            expect(layout.isSidePaneVisible()).toBe(true);

            var newLayout = SugarTest.createLayout('base', null, 'history-default', def, null);
            expect(newLayout.isSidePaneVisible()).toBe(false);
            newLayout.dispose();
        });
    });

});
