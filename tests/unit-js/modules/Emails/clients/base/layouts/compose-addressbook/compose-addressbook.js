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
describe('Emails.Base.Layout.ComposeAddressbook', function() {
    var app;
    var layout;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', 'Emails', 'compose-addressbook', null, null, true);

        sandbox = sinon.createSandbox();
        SugarTest.seedFakeServer();
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.server.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        layout.dispose();
    });

    describe('sync uses Mail/recipients/find', function() {
        var data;
        var onSuccess;
        var options;
        var response;

        beforeEach(function() {
            onSuccess = sandbox.spy();
            data = {
                next_offset: -1,
                records: [{
                    id: _.uniqueId(),
                    _module: 'Contacts',
                    _acl: {},
                    _erased_fields: [],
                    name: 'Haley Rhodes',
                    email: 'hrhodes@example.com',
                    email_address_id: _.uniqueId(),
                    opt_out: false
                }]
            };
            response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(data)
            ];
            options = {
                success: onSuccess
            };
        });

        it('should map the response', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find.*/;

            SugarTest.server.respondWith('GET', url, response);

            layout.collection.sync('read', layout.collection, options);
            SugarTest.server.respond();

            expect(onSuccess).toHaveBeenCalledOnce();
            expect(onSuccess.getCall(0).args[0][0]).toEqual({
                _module: 'Contacts',
                _acl: {},
                _erased_fields: [],
                id: data.records[0].id,
                name: 'Haley Rhodes',
                email: [{
                    email_address: 'hrhodes@example.com',
                    email_address_id: data.records[0].email_address_id,
                    opt_out: false,
                    primary_address: true
                }]
            });
        });

        it('should search for emails in all allowed modules when options.module_list is empty', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?.*module_list=all.*/;

            SugarTest.server.respondWith('GET', url, response);

            layout.collection.sync('read', layout.collection, options);
            SugarTest.server.respond();

            // The success callback would only be called if the URL includes
            // the correct module_list value.
            expect(onSuccess).toHaveBeenCalledOnce();
        });

        it('should remove all modules that are not allowed', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?.*module_list=Accounts%2CContacts.*/;

            options.module_list = ['Home','Contacts','TargetList','Calls','Accounts'];
            SugarTest.server.respondWith('GET', url, response);

            layout.collection.sync('read', layout.collection, options);
            SugarTest.server.respond();

            // The success callback would only be called if the URL includes
            // the correct module_list value.
            expect(onSuccess).toHaveBeenCalledOnce();
        });

        it('should add the erased_fields parameter', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?.*erased_fields=true.*/;

            SugarTest.server.respondWith('GET', url, response);

            layout.collection.sync('read', layout.collection, options);
            SugarTest.server.respond();

            // The success callback would only be called if the URL includes
            // the erased_fields parameter.
            expect(onSuccess).toHaveBeenCalledOnce();
        });
    });
});
