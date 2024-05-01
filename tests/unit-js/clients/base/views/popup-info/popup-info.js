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
describe('View.Views.Base.Views.PopupInfoView', function() {
    var app;
    var view;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', null, 'popup-help');
        view = SugarTest.createView('base', null, 'popup-info', {}, context, true, layout);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();

        app = null;
        view.context = null;
        view.model = null;
        view = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    describe('_initProperties', function() {
        it('should set properties appropriately', function() {
            layout.options = {
                popupBody: 'Popup Info',
            };
            view._initProperties();

            expect(view._popupInfo).toEqual(layout.options.popupBody);
        });
    });

    describe('setPopupInfo', function() {
        it('should change the popupInfo', function() {
            const info = 'New Info';

            view.setPopupInfo(info);

            expect(view._popupInfo).toEqual(info);
        });
    });
});
