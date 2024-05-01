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
describe('Base.Layouts.PopupHelpLayout', function() {
    var app;
    var layout;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        app.data.declareModels();

        SugarTest.loadComponent('base', 'layout', 'popup-help');
        SugarTest.loadComponent('base', 'view', 'popup-info');

        meta = {
            components: [{
                view: {
                    type: 'popup-info',
                    name: 'popup-info',
                },
            }],
        };

        layout = SugarTest.createLayout('base', null, 'popup-help', meta);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();

        app = null;
        meta = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    describe('setPopupInfo', function() {
        it('should change the popupInfo', function() {
            const info = 'New Info';

            layout.setPopupInfo(info);

            const popupInfoController = layout.getComponent('popup-info');

            expect(popupInfoController._popupInfo).toEqual(info);
        });
    });
});
