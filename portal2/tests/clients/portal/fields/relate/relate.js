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

describe('Portal.Field.Relate', function() {
    describe('Should not add href on portal for Users module fields', function() {
        var app;
        var field;
        var parentView;
        var oldRouter;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'field', 'relate');
            SugarTest.loadComponent('portal', 'field', 'relate');

            app = SugarTest.app;
            field = SugarTest.createField('portal', 'testfile', 'relate', 'detail', {});
            parentView = app.view.createView({type: 'base'});
            oldRouter = app.router;
            oldUser = app.user;
            app.user = {get: function() {return '123456789';}};
            app.router = {buildRoute: function(module, id) {return module + '/' + id;}};
        });

        afterEach(function() {
            app.view.reset();
            app.router = oldRouter;
            app.user = oldUser;
            parentView.dispose();
            field.dispose();
        });

        using('portal related records', [
            {
                name: 'bob',
                id: '1234',
                module: 'Cases',
                vardefsLink: 'cases',
                expectedHref: '#Cases/1234'
            },
            {
                name: 'bob',
                id: '123456789',
                module: 'Contacts',
                vardefsLink: 'accounts',
                expectedHref: '#Contacts/123456789'
            },
            {
                name: 'bob',
                id: '1234',
                module: 'Users',
                vardefsLink: 'user',
                expectedHref: void 0
            },
            {
                name: 'bob',
                id: '1234',
                module: 'Employees',
                vardefsLink: 'user',
                expectedHref: void 0
            },
            {
                name: 'bob',
                id: '1234',
                module: 'Users',
                vardefsLink: 'user',
                expectedHref: void 0
            },
            {
                name: 'bob',
                id: '1234',
                module: 'Contacts',
                vardefsLink: 'accounts',
                expectedHref: void 0
            },
            {
                name: 'bob',
                id: '1234',
                module: 'Contacts',
                vardefsLink: 'accounts',
                link: false,
                expectedHref: void 0
            },
            {
                name: 'bob',
                module: 'Contacts',
                vardefsLink: 'accounts',
                id: void 0,
                expectedHref: void 0
            },
        ],
        function(data) {
            it('should not build the link for portal Users module fields', function() {
                var attrs = {};
                attrs[data.vardefsLink] = {
                    _acl: {
                        fields: [],
                        view: data.view_access,
                    },
                    name: data.name,
                    id: data.id
                };
                app.data.declareModel(data.module, app.metadata.getModule(data.module), 'portal');
                app.data.declareModel(data.module, app.metadata.getModule(data.module), 'base');

                var model = app.data.createBean(data.module, attrs);
                var field = app.view.createField({
                    viewDefs: {
                        link: data.link,
                        name: 'account_name',
                        type: 'relate',
                    },
                    def: {},
                    model: model,
                    module: data.module,
                    view: parentView
                });
                field.buildRoute(data.module, data.id);

                expect(field.href).toEqual(data.expectedHref);
            });
        });
    });
});
