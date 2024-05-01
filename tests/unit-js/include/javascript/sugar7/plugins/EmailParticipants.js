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
describe('Plugins.EmailParticipants', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'to_collection',
            type: 'email-recipients',
            viewName: 'detail',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        model.off();
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('preparing a model to be added to the collection', function() {
        var bean;

        beforeEach(function() {
            var parentId = _.uniqueId();
            var emailAddressId = _.uniqueId();

            bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: emailAddressId,
                email_address: 'hrhodes@example.com',
                email_addresses: {
                    email_address: 'hrhodes@example.com',
                    id: emailAddressId,
                    _erased_fields: []
                }
            });
        });

        it('should define the data that is needed for Select2', function() {
            var result;

            result = field.prepareModel(bean);

            // The model is modified, but it is returned so that `prepareModel`
            // can be used as the map callback.
            expect(result).toBe(bean);
            expect(result.nameIsErased).toBe(false);
            expect(bean.nameIsErased).toBe(false);
            expect(result.emailIsErased).toBe(false);
            expect(bean.emailIsErased).toBe(false);
            expect(result.locked).toBe(false);
            expect(bean.locked).toBe(false);
            // Derived from `app.utils.isValidEmailAddress`.
            expect(result.invalid).toBe(false);
            expect(bean.invalid).toBe(false);
        });

        it('should lock the selection', function() {
            field.def.readonly = true;

            field.prepareModel(bean);

            expect(bean.locked).toBe(true);
        });

        using(
            'attributes to set',
            [
                // No change. Has parent and email address. invalid_email is not set.
                {},
                // Has parent but no email address. invalid_email is not set.
                {
                    email_address_id: '',
                    email_address: ''
                },
                // Has parent but no email address. invalid_email is not set.
                {
                    email_address_id: ''
                },
                // Has parent and email address. The server gave us invalid_email.
                {
                    invalid_email: false
                }
            ],
            function(attrs) {
                it('should mark the participant as valid', function() {
                    // Update the attributes so the model has the data we need
                    // for this test case.
                    bean.set(attrs);

                    field.prepareModel(bean);

                    expect(bean.invalid).toBe(false);
                });
            }
        );

        using(
            'attributes to set',
            [
                // Invalid email address.
                {
                    email_address: 'foo'
                },
                // No parent and invalid email address.
                {
                    parent: {},
                    email_address: 'foo'
                },
                // No parent and no email address.
                {
                    parent: {},
                    email_address_id: '',
                    email_address: ''
                },
                // No parent and no email address.
                {
                    parent: {},
                    email_address_id: ''
                },
                // The server gave us invalid_email.
                {
                    invalid_email: true
                },
                // The email address was erased.
                {
                    email_address: '',
                    email_addresses: {
                        email_address: '',
                        id: _.uniqueId(),
                        _erased_fields: [
                            'email_address',
                            'email_address_caps'
                        ]
                    }
                }
            ],
            function(attrs) {
                it('should mark the participant as invalid', function() {
                    // Update the attributes so the model has the data we need
                    // for this test case.
                    bean.set(attrs);

                    field.prepareModel(bean);

                    expect(bean.invalid).toBe(true);
                });
            }
        );

        it('should define the url to the model', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(true);

            field.prepareModel(bean);

            expect(bean.href).toBe('#Contacts/' + bean.get('parent_id'));
        });

        it('should not define the url to the model if the user does not have access', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(false);

            field.prepareModel(bean);

            expect(bean.href).toBeUndefined();
        });

        it('should not define the url to the model if the parent record does not exist', function() {
            bean.unset('parent');

            field.prepareModel(bean);

            expect(bean.href).toBeUndefined();
        });

        it('should indicate that the name has been erased', function() {
            // Erase the name.
            bean.set('parent_name', '');
            sandbox.stub(app.utils, 'isNameErased').returns(true);

            field.prepareModel(bean);

            expect(bean.nameIsErased).toBe(true);
            // None of the other properties are affected.
            expect(bean.emailIsErased).toBe(false);
            expect(bean.locked).toBe(false);
            expect(bean.invalid).toBe(false);
            expect(bean.href).toBe('#Contacts/' + bean.get('parent_id'));
        });

        it('should indicate that the email address has been erased', function() {
            var link = bean.get('email_addresses');

            // Erase the email address.
            bean.set('email_address', '');
            link.email_address = '';
            link._erased_fields = [
                'email_address',
                'email_address_caps'
            ];

            field.prepareModel(bean);

            expect(bean.emailIsErased).toBe(true);
            // An erased email address is invalid.
            expect(bean.invalid).toBe(true);
            // None of the other properties are affected.
            expect(bean.nameIsErased).toBe(false);
            expect(bean.locked).toBe(false);
            expect(bean.href).toBe('#Contacts/' + bean.get('parent_id'));
        });
    });

    describe('searching for participants', function() {
        var options;

        beforeEach(function() {
            SugarTest.seedFakeServer();
            options = field.getSelect2Options();
        });

        afterEach(function() {
            SugarTest.server.restore();
        });

        it('should search for participants that match the query', function() {
            var data = {
                next_offset: -1,
                records: [{
                    id: _.uniqueId(),
                    _module: 'Contacts',
                    name: 'Haley Rhodes',
                    email: 'hrhodes@example.com',
                    _acl: {},
                    _erased_fields: []
                }]
            };
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?q=haley&max_num=10&erased_fields=true/;
            var query = {
                term: 'haley',
                callback: function(response) {
                    expect(response.more).toBe(false);
                    expect(response.results.length).toBe(1);
                    expect(response.results[0].get('_link')).toBe('to');
                    expect(response.results[0].get('parent_type')).toBe('Contacts');
                    expect(response.results[0].get('parent_id')).toBe(data.records[0].id);
                    expect(response.results[0].get('parent_name')).toBe(data.records[0].name);
                    expect(response.results[0].get('email_address_id')).toBeUndefined();
                    expect(response.results[0].get('email_address')).toBe(data.records[0].email);
                    expect(response.results[0].get('parent').type).toBe('Contacts');
                    expect(response.results[0].get('parent').id).toBe(data.records[0].id);
                    expect(response.results[0].get('parent').name).toBe(data.records[0].name);
                    expect(response.results[0].get('parent')._acl).toEqual({});
                    expect(response.results[0].get('parent')._erased_fields).toEqual([]);
                }
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(data)
            ];

            SugarTest.server.respondWith('GET', url, response);
            options.query(query);
            SugarTest.server.respond();
        });

        it('should return no results on error', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?q=haley&max_num=10&erased_fields=true/;
            var query = {
                term: 'haley',
                callback: function(data) {
                    expect(data.more).toBe(false);
                    expect(data.results.length).toBe(0);
                }
            };
            var response = [
                500,
                {'Content-Type': 'application/json'},
                JSON.stringify({error: 'fatal_error', error_description: 'Your request failed.'})
            ];

            SugarTest.server.respondWith('GET', url, response);
            options.query(query);
            SugarTest.server.respond();
        });

        it('should not create a choice when matches were found', function() {
            var term = 'test@example.com';
            var data = [{
                id: _.uniqueId(),
                _module: 'Contacts',
                name: 'Yolanda Grace',
                email: term
            }];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });

        it('should create a choice when the term is a valid email address', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            expect(actual.get('invalid_email')).toBeUndefined();
            expect(actual.get('opt_out')).toBeUndefined();
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            // Act as if the user has selected the choice in order to see that
            // the event is triggered.
            field.model.get(field.name).add(actual);
            sandbox.spy(field.model, 'trigger');

            SugarTest.server.respond();

            // Data is patched and the choice is now seen as valid.
            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.get('invalid_email')).toBe(false);
            expect(actual.get('opt_out')).toBe(false);
            expect(actual.invalid).toBe(false);
            expect(field.model.trigger).toHaveBeenCalledOnce();
            expect(field.model.trigger.args[0][0]).toBe('change:' + field.name);
            expect(field.model.trigger.args[0][1]).toBe(field.model);
            expect(field.model.trigger.args[0][2]).toBe(field.model.get(field.name));
        });

        it('should not trigger the event if the choice has already been selected', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            expect(actual.get('invalid_email')).toBeUndefined();
            expect(actual.get('opt_out')).toBeUndefined();
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            // Act as if the user has not selected the choice in order to see
            // that the event is not triggered.
            sandbox.spy(field.model, 'trigger');

            SugarTest.server.respond();

            // Data is patched and the choice is now seen as valid.
            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.get('invalid_email')).toBe(false);
            expect(actual.get('opt_out')).toBe(false);
            expect(actual.invalid).toBe(false);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should be marked invalid when the request responds with an invalid email address', function() {
            var term = 'test@.example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: true,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            expect(actual.get('invalid_email')).toBeUndefined();
            expect(actual.get('opt_out')).toBeUndefined();
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Data is patched but the email is still seen as invalid.
            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.get('invalid_email')).toBe(true);
            expect(actual.get('opt_out')).toBe(false);
            expect(actual.invalid).toBe(true);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not be marked invalid when the request responds with an opted out email address', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: true
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            expect(actual.get('invalid_email')).toBeUndefined();
            expect(actual.get('opt_out')).toBeUndefined();
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Data is patched and the choice is now seen as valid.
            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.get('invalid_email')).toBe(false);
            expect(actual.get('opt_out')).toBe(true);
            expect(actual.invalid).toBe(false);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not patch the new choice when the request fails', function() {
            var term = 'test@example.com';
            var response = [
                500,
                {'Content-Type': 'application/json'},
                JSON.stringify({
                    error: 'fatal_error',
                    error_message: 'Your request failed to complete.'
                })
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Nothing changed.
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.invalid).toBe(true);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not create a choice when the term is an invalid email address', function() {
            var term = 'test';
            var data = [];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });
    });

    describe('validation', function() {
        var bean;

        beforeEach(function() {
            var parentId = _.uniqueId();

            bean = app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: _.uniqueId(),
                email_address: 'hrhodes',
                invalid_email: true,
                opt_out: false
            });
        });

        it('should invalidate the field', function() {
            var cbSpy = sandbox.spy();
            var eventSpy = sandbox.spy();

            model.on('error:validation:' + field.name, eventSpy);

            field.prepareModel(bean);
            model.get(field.name).add(bean);

            runs(function() {
                model.doValidate(null, cbSpy);
            });

            waitsFor(function() {
                return cbSpy.called;
            });

            runs(function() {
                expect(cbSpy).toHaveBeenCalledWith(false);
                expect(eventSpy).toHaveBeenCalledOnce();
                expect(eventSpy.firstCall.args[0][field.type]).toBe(true);
            });
        });
    });

    describe('getting the link name associated with the collection field', function() {
        it('should return the name when the link is a string', function() {
            expect(field.getLinkName()).toBe('to');
        });

        it('should return the name property when the link is an object', function() {
            field.def.links[0] = {
                name: 'to',
                field_map: {}
            };

            expect(field.getLinkName()).toBe('to');
        });
    });
});
