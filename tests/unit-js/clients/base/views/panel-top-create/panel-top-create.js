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
describe('Base.View.PanelTopCreate', function() {
    var app, view, context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        view = SugarTest.createView('base', 'RevenueLineItems', 'panel-top-create', null, context);
        sinon.stub(view, '_super').callsFake(function() {});
    });
    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should set collapsed to false', function() {
            view.initialize();
            expect(view.context.get('collapsed')).toBe(false);
        });
    });
});
