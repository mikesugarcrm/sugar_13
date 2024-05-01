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
describe('PortalCreateView', function() {
    var app;
    var moduleName = 'Contacts';
    var viewName = 'create';
    var sinonSandbox;
    var view;
    var context;
    var drawer;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            'template': 'record',
            'buttons': [
                {
                    'name': 'cancel_button',
                    'type': 'button',
                    'label': 'LBL_CANCEL_BUTTON_LABEL',
                    'css_class': 'btn-invisible btn-link'
                },
                {
                    'name': 'restore_button',
                    'type': 'button',
                    'label': 'LBL_RESTORE',
                    'css_class': 'hide btn-invisible btn-link',
                    'showOn': 'select'
                },
                {
                    'name': 'save_button',
                    'type': 'rowaction',
                    'label': 'LBL_SAVE_BUTTON_LABEL',
                    'primary': true
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        app.data.declareModels();

        sinonSandbox = sinon.createSandbox();

        drawer = app.drawer;
        app.drawer = {
            close: function() {
            }
        };

        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView('portal', moduleName, viewName, null, context);
        sinonSandbox.stub(view, 'addToLayoutComponents');
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinonSandbox.restore();
        app.drawer = drawer;
    });

    describe('renderDupeCheckList', function() {
        it('should not set dupelisttype to dupecheck-list-edit', function() {
            view.renderDupeCheckList();
            expect(view.context.get('dupelisttype')).toBeUndefined();
        });
    });
});
