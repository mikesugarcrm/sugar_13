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
describe('Base.Layout.AdministrationPortalThemeMegaMenu', function() {
    var app;
    var layout;
    var layoutName = 'portaltheme-megamenu';
    var module = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', layoutName, module);
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', module, layoutName, {}, null, true);
    });

    afterEach(function(){
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        sinon.restore();
    });

    describe('_getPortalHelpButton', function() {
        it('should apply label to help button based on getter util return value', function() {
            sinon.stub(layout, '_getHelpButtonLabel').returns('test label');
            var actual = layout._getPortalHelpButton();
            var expected = [{
                type: 'button',
                name: 'help_button',
                css_class: 'btn-primary',
                label: 'test label',
                events: {
                    click: 'button:help_button:click'
                }
            }];
            expect(layout._getHelpButtonLabel).toHaveBeenCalledOnce();
            expect(actual).toEqual(expected);
        });
    });

    describe('_getHelpButtonLabel', function() {
        it('should call app.lang.get with expected arguments', function() {
            sinon.stub(app.lang, 'get');
            layout._getHelpButtonLabel();
            expect(app.lang.get).toHaveBeenCalledWith(
                'LBL_PORTALTHEME_NEW_CASE_BUTTON_TEXT_DEFAULT',
                module
            );
        });
    });
});
