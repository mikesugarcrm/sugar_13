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
describe('View.Views.Base.QuicksearchModuleList', function() {
    var viewName = 'quicksearch-modulelist',
        view, layout, app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();

        sinon.stub(SugarTest.app.acl, 'hasAccess').callsFake(function(action, module) {
            return module !== 'NoAccess';
        });
        sinon.stub(SugarTest.app.api, 'isAuthenticated').returns(true);

        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', null, viewName, null, null, null, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        view = null;
        app = null;
    });

    describe('populateModules', function() {
        var getModulesStub;
        beforeEach(function() {
            getModulesStub = sinon.stub(app.metadata, 'getModules');
        });

        it('Should show searchable modules only', function() {
            var fakeModuleList = {
                Accounts: {globalSearchEnabled: true},
                Contacts: {globalSearchEnabled: true},
                globalSearchDisabled: {globalSearchEnabled: false},
                globalSearchNotSet: {},
                NoAccess: {globalSearchEnabled: true}
            };
            getModulesStub.returns(fakeModuleList);

            sinon.stub(view, 'render');
            view.populateModules();
            expect(view.searchModuleFilter.get('Accounts')).toBeTruthy();
            expect(view.searchModuleFilter.get('Contacts')).toBeTruthy();
            expect(view.searchModuleFilter.get('globalSearchDisabled')).toBeFalsy();
            expect(view.searchModuleFilter.get('globalSearchNotSet')).toBeFalsy();
            expect(view.searchModuleFilter.get('NoAccess')).toBeFalsy();
        });

        using('different modules and orderings', [
            {
                //unsorted list
                given: {
                    'Accounts': {globalSearchEnabled: true},
                    'Calls': {globalSearchEnabled: true},
                    'Cases': {globalSearchEnabled: true},
                    'Bugs': {globalSearchEnabled: true}
                },
                expected: ['Accounts', 'Bugs', 'Calls', 'Cases']
            },
            {
                //sorted list
                given: {
                    'Campaigns': {globalSearchEnabled: true},
                    'Documents': {globalSearchEnabled: true},
                    'Emails': {globalSearchEnabled: true},
                    'Manufacturers': {globalSearchEnabled: true},
                    'Notes': {globalSearchEnabled: true}
                },
                expected: ['Campaigns', 'Documents', 'Emails', 'Manufacturers', 'Notes']
            }
        ], function(value) {
            it('should always be sorted alphabetically', function() {
                sinon.stub(view, 'render');
                getModulesStub.returns(value.given);
                view.populateModules();
                expect(_.pluck(_.pluck(view.searchModuleFilter.models, 'attributes'), 'id')).toEqual(value.expected);
            });
        });
    });
});
