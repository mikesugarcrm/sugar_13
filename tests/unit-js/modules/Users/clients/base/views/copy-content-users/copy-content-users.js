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
describe('Users.View.CopyContentUsers', function() {
    var app;
    var viewButtons;
    var layout;
    var viewUsers;
    var usersSelectField;
    var teamsSelectField;
    var rolesSelectField;
    var module = 'Users';
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);

        SugarTest.loadComponent('base', 'layout', 'copy-content', 'Users');

        SugarTest.loadComponent('base', 'view', 'copy-content-buttons', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-items', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-users', 'Users');

        SugarTest.testMetadata.addLayoutDefinition('copy-content', {
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
        }, null);

        SugarTest.loadHandlebarsTemplate('copy-content-buttons', 'view', 'base', null, 'Users');

        layout = SugarTest.createLayout('base', 'Users', 'copy-content', null, null, true);
        viewUsers = SugarTest.createView('base', 'Users', 'copy-content-users', null, null, true, layout);
        viewButtons = SugarTest.createView('base', 'Users', 'copy-content-buttons', null, null, true, layout);

        SugarTest.loadHandlebarsTemplate('hybrid-select', 'field', 'base', 'edit', 'Users');
        SugarTest.loadComponent('base', 'field', 'base');

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
    });

    afterEach(function() {
        sinon.restore();
        viewUsers.dispose();
        usersSelectField.dispose();
        rolesSelectField.dispose();
        teamsSelectField.dispose();
        layout.dispose();
        viewButtons = null;
    });

    describe('updateDestination', function() {
        beforeEach(function() {
            usersSelectField.items = [{id: 'userId1'}, {id: 'userId2'}];
            teamsSelectField.items = [{id: 'teamId1'}, {id: 'teamId2'}];
            rolesSelectField.items = [{id: 'roleId1'}, {id: 'roleId2'}];
        });
        it('should set the context with selected destination', function() {
            var getFieldUsersStub = sinon.stub(viewUsers, 'getField');

            getFieldUsersStub.withArgs('users_select').returns(usersSelectField);
            getFieldUsersStub.withArgs('teams_select').returns(teamsSelectField);
            getFieldUsersStub.withArgs('roles_select').returns(rolesSelectField);

            viewUsers.updateDestination();

            expect(viewUsers.context.get('destinationUsers')).toEqual(['userId1', 'userId2']);
            expect(viewUsers.context.get('destinationTeams')).toEqual(['teamId1', 'teamId2']);
            expect(viewUsers.context.get('destinationRoles')).toEqual(['roleId1', 'roleId2']);
        });
    });
});
