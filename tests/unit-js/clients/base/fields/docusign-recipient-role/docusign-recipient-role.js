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
describe('Base.Field.DocusignRecipientRoleField', function() {
    var app;
    var field;
    var model;
    var module = 'Accounts';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        var templateDetails = {
            roles: [
                {
                    name: 'role1',
                },
                {
                    name: 'role2',
                },
            ]
        };
        var fieldContext = new app.Context();
        fieldContext.set('templateDetails', templateDetails);
        fieldContext.set('storedRoles', {});

        field = SugarTest.createField(
            'base',
            'docusign-recipient-role',
            'docusign-recipient-role',
            'detail',
            {},
            module,
            model,
            fieldContext,
            false
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
        sinon.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('should set the options', function() {
            sinon.stub(field, 'listenTo');

            var templateDetails = {
                roles: [
                    {
                        name: 'role1',
                    },
                    {
                        name: 'role2',
                    },
                ]
            };
            var fieldContext = new app.Context();
            fieldContext.set('templateDetails', templateDetails);
            fieldContext.set('storedRoles', {});
            field.view = {
                collection: {}
            };
            field.initialize({
                def: {},
                context: fieldContext,
                view: field.view
            });
            expect(field.options.def.options.role1).toEqual('role1');
        });
    });
});
