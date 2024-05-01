
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
describe('Base.Users.CopyUserSettingsButtons', function() {
    var app;
    var module = 'Users';
    var view;
    var layout;
    var context;
    var settingsView;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'copy-user-settings', module);

        SugarTest.loadComponent('base', 'view', 'copy-content-locale', module);
        SugarTest.loadComponent('base', 'view', 'copy-content-users', module);
        SugarTest.loadComponent('base', 'view', 'copy-user-settings-buttons', module);
        SugarTest.loadComponent('base', 'view', 'copy-content-buttons', module);

        SugarTest.testMetadata.addLayoutDefinition('copy-user-settings', {
            'components': [
                {
                    view: 'copy-user-settings-buttons'
                },
                {
                    view: 'copy-content-locale',
                },
                {
                    view: 'copy-content-users',
                },
            ],
        }, null);

        SugarTest.loadHandlebarsTemplate('copy-user-settings-buttons', 'view', 'base', null, module);
        SugarTest.loadHandlebarsTemplate('copy-content-buttons', 'view', 'base', null, module);
        SugarTest.loadHandlebarsTemplate('copy-content-locale', 'view', 'base', null, module);

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        context = app.context.getContext();
        context.set({
            module: 'Users',
            destinationUsers: ['1'],
            destinationTeams: ['team1', 'team2'],
            destinationRoles: ['role1', 'role2']
        });
        context.prepare();

        layout = SugarTest.createLayout('base', module, 'copy-user-settings', null, context, true);
        view = SugarTest.createView('base', module, 'copy-user-settings-buttons', null, context, true, layout);
        settingsView = SugarTest.createView('base', module, 'copy-content-locale', null, context, true, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
        settingsView.dispose();
        settingsView = null;
    });

    describe('copy', function() {
        it('should call the callCommand method', function() {
            var callCommandStub = sinon.stub(view, 'callCommand');
            var getComponentStub = sinon.stub(view.layout, 'getComponent');

            getComponentStub
                .withArgs('copy-content-locale')
                .returns(settingsView);

            view.copy();

            expect(view.context.get('destinationUsers')).toEqual(['1']);
            expect(view.context.get('destinationTeams')).toEqual(['team1', 'team2']);
            expect(view.context.get('destinationRoles')).toEqual(['role1', 'role2']);
            expect(callCommandStub).toHaveBeenCalled();
        });
    });
});
