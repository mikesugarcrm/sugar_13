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
describe('Base.View.Themerollerpreview', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base','Cases', 'themerollerpreview');
    });

    afterEach(function() {
        app.view.reset();
        view = null;
    });

    describe('_fixRelativeUrls', function() {
        it('should replace the scoped urls', function() {
            var css = '@font-face {\n' +
                '  font-family: \'Open Sans\';\n' +
                '  font-style: normal;\n' +
                '  font-weight: 400;\n' +
                '  src: url(\'../../../../../styleguide/assets/fonts/opensans/opensans.woff2\') ' +
                'format(\'woff2\'), url(\'../../../../../styleguide/assets/fonts/opensans/opensans.woff\') ' +
                'format(\'woff\');\n' +
                '}';

            var fixed = '@font-face {\n' +
                '  font-family: \'Open Sans\';\n' +
                '  font-style: normal;\n' +
                '  font-weight: 400;\n' +
                '  src: url(\'../../styleguide/assets/fonts/opensans/opensans.woff2\') ' +
                'format(\'woff2\'), url(\'../../styleguide/assets/fonts/opensans/opensans.woff\') ' +
                'format(\'woff\');\n' +
                '}';
            expect(view._fixRelativeUrls(css)).toEqual(fixed);
        });
    });
});
