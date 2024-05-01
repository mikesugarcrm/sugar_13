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
describe('View.Views.Base.FocuscabField', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'focuscab', 'focuscab', 'detail');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app.cache.cutAll();
        Handlebars.templates = {};
    });

    describe('isFocusDrawerEnabled', function() {
        it('should be disabled by default', function() {
            app.config.enableLinkToDrawer = false;
            expect(field.isFocusDrawerEnabled()).toEqual(false);
        });

        it('should be enabled if enabled from config', function() {
            app.config.enableLinkToDrawer = true;
            expect(field.isFocusDrawerEnabled()).toEqual(true);
        });
    });
});
