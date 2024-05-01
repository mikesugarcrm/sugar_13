
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
describe('Base.Users.CopyContentLocale', function() {
    var app;
    var module = 'Users';
    var context;
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'copy-user-settings', module);

        SugarTest.loadComponent('base', 'view', 'copy-content-locale', module);
        SugarTest.loadComponent('base', 'view', 'copy-content-users', module);
        SugarTest.loadComponent('base', 'view', 'copy-user-settings-buttons', module);

        SugarTest.loadHandlebarsTemplate('copy-content-locale', 'view', 'base', null, module);
        SugarTest.loadHandlebarsTemplate('copy-user-settings-buttons', 'view', 'base', null, module);
        SugarTest.loadHandlebarsTemplate('copy-content-users', 'view', 'base', null, module);

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

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        context = app.context.getContext();
        context.set({
            module: 'Users',
            destinationUsers: ['1'],
            destinationTeams: [],
            destinationRoles: []
        });
        context.prepare();

        layout = SugarTest.createLayout('base', module, 'copy-user-settings', null, context, true);
        view = SugarTest.createView('base', module, 'copy-content-locale', null, context, true, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should initialize the locale data for the current user', function() {
            expect(view.currentUserId).toBe(app.user.id);
        });
    });

    describe('render', function() {
        it('should call the renderDropdowns function', function() {
            var renderDropdownsStub = sinon.stub(view, 'renderDropdowns');
            view.render();

            expect(renderDropdownsStub).toHaveBeenCalled();
        });
    });

    describe('getData', function() {
        it('should get the locales data for the current user', function() {
            var apiCallStub = sinon.stub(app.api, 'call');
            var buildUrlStub = sinon.stub(app.api, 'buildURL');
            view.getData();

            expect(apiCallStub).toHaveBeenCalled();
            expect(buildUrlStub).toHaveBeenCalled();
        });
    });
});
