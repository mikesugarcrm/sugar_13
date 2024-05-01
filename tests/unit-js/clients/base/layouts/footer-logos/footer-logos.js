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
describe("BaseFooterLogosLayout", function() {
    var layout;
    var app;
    var sandbox;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'footer-logos');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Users', 'footer-logos', {});
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('render', function() {

        it('should load the logo url when re-rendering the layout', function() {

            layout.$el.html('<span data-metadata="logo">Footer fixture</span>');

            sandbox.stub(app.metadata, 'getLogoUrl').callsFake(function() {
                return 'my_logo.jpg';
            });

            layout.render();

            expect(layout.$('[data-metadata="logo"]').attr('src')).toBe('my_logo.jpg');
        });
    });
});
