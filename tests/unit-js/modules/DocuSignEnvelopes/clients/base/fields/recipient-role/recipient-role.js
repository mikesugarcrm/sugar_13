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
describe('Base.DocuSignEnvelopes.RecipientRoleField', function() {
    var app;
    var field;
    var model;
    var module = 'DocuSignEnvelopes';

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

        field = SugarTest.createField(
            'base',
            'recipient-role',
            'recipient-role',
            'detail',
            {},
            module,
            model,
            fieldContext,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        it('should set the options', function() {
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
            field.initialize({
                def: {},
                context: fieldContext
            });
            expect(field.options.def.options.role1).toEqual('role1');
        });
    });
});
