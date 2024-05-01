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
describe('Users.View.CopyContentButtons', function() {
    var app;
    var view;
    var layout;
    var viewItems;
    var viewUsers;
    var usersSelectField;
    var teamsSelectField;
    var rolesSelectField;
    var dashboardsSelectField;
    var filtersSelectField;
    var module = 'Users';
    var model;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        app = SugarTest.app;
        model = app.data.createBean(module);

        SugarTest.loadComponent('base', 'layout', 'copy-content', 'Users');

        SugarTest.loadComponent('base', 'view', 'copy-content-buttons', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-items', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-users', 'Users');

        SugarTest.loadComponent('base', 'field', 'hybrid-select', 'Users');
        SugarTest.loadComponent('base', 'field', 'module-enum');

        SugarTest.loadHandlebarsTemplate('hybrid-select', 'field', 'base', 'edit', 'Users');
        SugarTest.loadComponent('base', 'field', 'base');

        SugarTest.testMetadata.addLayoutDefinition('copy-content', {
            'css-class': 'w-max',
            'components': [
                {
                    view: 'copy-content-buttons'
                },
                {
                    view: 'copy-content-items',
                },
                {
                    view: 'copy-content-users',
                },
            ],
        }, 'Users');

        SugarTest.loadHandlebarsTemplate('copy-content-buttons', 'view', 'base', null, 'Users');

        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', 'Users', 'copy-content', null, null, true);

        var itemsViewMeta = {
            'fields': {
                'dashboards_module_select': {
                    'name': 'dashboards_module_select',
                    'type': 'module-enum',
                    'view_template': 'edit',
                },
                'dashboards_select': {
                    'name': 'dashboards_select',
                    'type': 'hybrid-select',
                    'select_module': 'Dashboards',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_DASHBOARDS',
                },
                'filters_select': {
                    'name': 'filters_select',
                    'type': 'hybrid-select',
                    'select_module': 'Filters',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_FILTERS',
                },
            }
        };
        viewItems = SugarTest.createView('base', 'Users', 'copy-content-items', itemsViewMeta, null, true, layout);
        viewItems.section = 'user_prefs';

        var viewUsersMeta = {
            'fields': {
                'users_select': {
                    'name': 'users_select',
                    'type': 'hybrid-select',
                    'select_module': 'Users',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_DESTINATION_USERS',
                },
                'teams_select': {
                    'name': 'teams_select',
                    'type': 'hybrid-select',
                    'select_module': 'Teams',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_DESTINATION_TEAMS',
                },
                'roles_select': {
                    'name': 'roles_select',
                    'type': 'hybrid-select',
                    'select_module': 'ACLRoles',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_DESTINATION_ROLES',
                },
            }
        };
        viewUsers = SugarTest.createView('base', 'Users', 'copy-content-users', viewUsersMeta, null, true, layout);

        var buttonsViewMeta = {
            'buttons': [
                {
                    'name': 'cancel_button',
                    'type': 'button',
                    'label': 'LBL_CANCEL_BUTTON_LABEL',
                    'css_class': 'btn-link',
                },
                {
                    'name': 'clear_button',
                    'type': 'button',
                    'label': 'LBL_CLEAR',
                    'css_class': 'btn',
                },
                {
                    'name': 'copy_button',
                    'type': 'button',
                    'label': 'LBL_COPY',
                    'css_class': 'btn btn-primary',
                },
            ],
        };

        view = SugarTest.createView('base', 'Users', 'copy-content-buttons', buttonsViewMeta, null, true, layout);

        usersSelectField = SugarTest.createField(
            'base',
            'users_select',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        teamsSelectField = SugarTest.createField(
            'base',
            'teams_select',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        rolesSelectField = SugarTest.createField(
            'base',
            'roles_select',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        dashboardsSelectField = SugarTest.createField(
            'base',
            'dashboards_select',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        filtersSelectField = SugarTest.createField(
            'base',
            'filters_select',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        viewItems.dispose();
        viewUsers.dispose();
        usersSelectField.dispose();
        rolesSelectField.dispose();
        teamsSelectField.dispose();
        dashboardsSelectField.dispose();
        filtersSelectField.dispose();
        layout.dispose();
        view = null;
    });

    describe('copy', function() {
        it('should make the api call to copy contents', function() {
            var getComponentStub = sinon.stub(view.layout, 'getComponent');
            var getFieldItemsStub = sinon.stub(viewItems, 'getField');
            var getFieldUsersStub = sinon.stub(viewUsers, 'getField');

            getComponentStub
                .withArgs('copy-content-items')
                .returns(viewItems);

            getComponentStub
                .withArgs('copy-content-users')
                .returns(viewUsers);

            getFieldUsersStub.withArgs('users_select').returns(usersSelectField);
            getFieldUsersStub.withArgs('teams_select').returns(teamsSelectField);
            getFieldUsersStub.withArgs('roles_select').returns(rolesSelectField);

            getFieldItemsStub.withArgs('dashboards_select').returns(dashboardsSelectField);
            getFieldItemsStub.withArgs('filters_select').returns(filtersSelectField);

            var callCommandStub = sinon.stub(view, 'callCommand');
            view.layout.getComponent('copy-content-items').section = 'user_prefs';
            view.copy();
            expect(callCommandStub).toHaveBeenCalled();
        });
    });

    describe('cancel', function() {
        beforeEach(function() {
            app.routing.start();
        });

        it('should redirect us to Users list view', function() {
            var navigateStub = sinon.stub(app.router, 'navigate');
            view.cancel();
            expect(navigateStub).toHaveBeenCalled();
            expect(navigateStub.lastCall.args[0]).toEqual('#Users');
            expect(navigateStub.lastCall.args[1]).toEqual({trigger: true});
        });
    });

    describe('clear', function() {
        it('should render the layout', function() {
            var renderStub = sinon.stub(view.layout, 'render');
            view.clear();
            expect(renderStub).toHaveBeenCalled();
        });
    });
});
