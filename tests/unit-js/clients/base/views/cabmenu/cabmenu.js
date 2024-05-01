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
describe('Base.View.Cabmenu', function() {
    var view;
    var app;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadHandlebarsTemplate('cabmenu', 'view', 'cabmenu');
        view = SugarTest.createView('base', 'cabmenu', 'cabmenu');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should set cabButtons', function() {
        var buttons = [
            {
                type: 'dashletaction'
            }
        ];

        sinon.stub(view, 'getCabMeta').returns([
            {
                type: 'focuscab'
            },
            {
                type: 'cab_actiondropdown',
                buttons: buttons
            }
        ]);

        view.initCabMenu();
        expect(view.cabButtons).toEqual(buttons);
    });
});
