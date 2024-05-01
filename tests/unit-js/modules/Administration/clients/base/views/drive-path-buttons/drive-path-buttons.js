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
describe('Administration.View.DrivePathButtons', function() {
    var app = SUGAR.App;
    var sinonSandbox;
    var view;
    var viewName = 'drive-path-buttons';
    var module = 'Administration';
    var meta;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'layout', 'drive-path-select', module);
        SugarTest.loadComponent('base', 'view', viewName, module);
        SugarTest.loadComponent('base', 'view', 'drive-path-select', module);

        SugarTest.loadHandlebarsTemplate('drive-path-buttons', 'view', 'base', null, module);

        SugarTest.testMetadata.addLayoutDefinition('drive-path-select', {
            'components': [
                {
                    'view': 'drive-path-buttons'
                },
                {
                    'view': 'drive-path-select'
                },
            ],
        });

        meta = {
            buttons: [
                {
                    'name': 'shared_button',
                    'type': 'button',
                    'label': 'LBL_SHARED_WITH_ME',
                },
                {
                    'name': 'cancel_button',
                    'type': 'button',
                    'label': 'LBL_CANCEL_BUTTON_LABEL',
                    'css_class': 'btn-invisible btn-link',
                },
                {
                    'name': 'cancel_button',
                    'type': 'button',
                    'label': 'LBL_CANCEL_BUTTON_LABEL',
                    'css_class': 'btn-invisible btn-link',
                },
            ]
        };

        app.drawer = {close: sinon.stub()};

        context = app.context.getContext();
        context.set({
            isRoot: false,
            pathModule: 'Accounts'
        });

        context.prepare();
        SugarTest.testMetadata.set();

        sinonSandbox = sinon.createSandbox();

        parentLayout = SugarTest.createLayout('base', module, 'drive-path-select', null, null, true);

        view = SugarTest.createView('base', module, viewName, meta, context, true, parentLayout);
        selectPathView = SugarTest.createView('base', module, 'drive-path-select', meta, null, true, parentLayout);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('check buttons', function() {
        beforeEach(function() {
            view.render();
        });

        it('should render shared button', function() {
            expect(view.$el.find('[name=shared_button]').length).toBe(1);
        });

        it('should render shared checkbox buttons', function() {
            expect(view.$el.find('input.sharedWithMe').length).toBe(1);
        });
    });

    describe('saveCurrentPath', function() {
        var apiMock;
        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');

            view.saveCurrentPath();
        });

        it('should save the path', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('closeDrawer', function() {
        beforeEach(function() {
            view.closeDrawer();
        });

        it('should close the drawer', function() {
            expect(app.drawer.close).toHaveBeenCalled();
        });
    });

    describe('toggleCheckbox', function() {
        var toggleSharedStub;

        beforeEach(function() {
            toggleSharedStub = sinonSandbox.stub(view, 'toggleShared');
            view.sharedWithMe = true;
            view.render();
            view.toggleCheckbox();
        });

        it('should toggle the checkboxhhh and call for sharedWithMe', function() {
            expect(toggleSharedStub).toHaveBeenCalled();
        });
    });
});
