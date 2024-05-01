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
describe('EmailClientLaunch Plugin', function() {
    var app;
    var field;
    var setUseSugarClient;
    var userPrefs;
    var sandbox;
    var event;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.data.declareModel('EmailParticipants');
        app.data.declareModel('EmailAddresses');

        field = SugarTest.createField('base', 'label', 'label');
        field.plugins = ['EmailClientLaunch'];
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.app.plugins.attach(field, 'field');

        sandbox = sinon.createSandbox();
        sandbox.stub(app.utils, 'openEmailCreateDrawer');
        userPrefs = app.user.get('preferences');
        event = {
            stopPropagation: sinon.stub()
        };
    });

    afterEach(function() {
        event = null;
        sinon.restore();
        sandbox.restore();
        field.dispose();
        app.user.set('preferences', userPrefs);
    });

    setUseSugarClient = function(useSugarClient) {
        app.user.set('preferences', {email_client_preference: {type: useSugarClient ? 'sugar' : 'mailto'}});
    };

    describe('Launch Email Client', function() {
        beforeEach(function() {
            sandbox.stub(app.controller.context, 'reloadData');
        });

        it('should launch the Sugar Email Client if user profile says internal', function() {
            setUseSugarClient(true);
            field.launchEmailClient(event);
            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should not launch the Sugar Email Client if user profile says external', function() {
            setUseSugarClient(false);
            field.launchEmailClient(event);
            expect(app.utils.openEmailCreateDrawer).not.toHaveBeenCalled();
        });

        it('should clean to, cc, and bcc recipient lists before launching Sugar Email Client', function() {
            sandbox.spy(field, '_retrieveValidRecipients');

            field.launchSugarEmailClient({
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar1@baz.com'
                    })
                }],
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar2@baz.com'
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar3@baz.com'
                    })
                }]
            });
            expect(field._retrieveValidRecipients.callCount).toBe(3);
        });

        describe('after closing the create drawer', function() {
            var model;

            beforeEach(function() {
                model = app.data.createBean('Emails');
                setUseSugarClient(true);
                sandbox.spy(app.controller.context, 'trigger');
                sandbox.spy(field, 'trigger');
            });

            it('should reload the data if the module is Emails and it is a list view', function() {
                app.controller.context.set({
                    module: 'Emails',
                    layout: 'records'
                }, {silent: true});
                app.utils.openEmailCreateDrawer.callsArgWith(2, field.context, model);

                field.launchEmailClient(event);

                expect(field.trigger).toHaveBeenCalledWith('emailclient:close');
                expect(app.controller.context.reloadData).toHaveBeenCalledOnce();
                expect(app.controller.context.trigger).not.toHaveBeenCalledWith('panel-top:refresh');
            });

            it('should trigger panel-top:refresh events on the context if the module is not Emails', function() {
                app.controller.context.set({
                    module: 'Contacts',
                    layout: 'records'
                }, {silent: true});
                app.utils.openEmailCreateDrawer.callsArgWith(2, field.context, model);

                field.launchEmailClient(event);

                expect(field.trigger).toHaveBeenCalledWith('emailclient:close');
                expect(app.controller.context.reloadData).not.toHaveBeenCalled();
                expect(app.controller.context.trigger.callCount).toBe(3);
                expect(app.controller.context.trigger.getCall(0).args[0]).toEqual('panel-top:refresh');
                expect(app.controller.context.trigger.getCall(0).args[1]).toEqual('emails');
                expect(app.controller.context.trigger.getCall(1).args[0]).toEqual('panel-top:refresh');
                expect(app.controller.context.trigger.getCall(1).args[1]).toEqual('archived_emails');
                expect(app.controller.context.trigger.getCall(2).args[0]).toEqual('panel-top:refresh');
                expect(app.controller.context.trigger.getCall(2).args[1]).toEqual('contacts_activities_1_emails');
            });

            it('should trigger panel-top:refresh events on the context if it is not a list view', function() {
                app.controller.context.set({
                    module: 'Emails',
                    layout: 'record'
                }, {silent: true});
                app.utils.openEmailCreateDrawer.callsArgWith(2, field.context, model);
                // Pretend that Emails has a link to itself.
                sandbox.stub(app.utils, 'getLinksBetweenModules').returns([{
                    name: 'emails'
                }]);

                field.launchEmailClient(event);

                expect(field.trigger).toHaveBeenCalledWith('emailclient:close');
                expect(app.controller.context.reloadData).not.toHaveBeenCalled();
                expect(app.controller.context.trigger).toHaveBeenCalledOnce();
                expect(app.controller.context.trigger.getCall(0).args[0]).toEqual('panel-top:refresh');
                expect(app.controller.context.trigger.getCall(0).args[1]).toEqual('emails');
            });

            it('should not reload any data if the drawer is canceled', function() {
                app.controller.context.set({
                    module: 'Emails',
                    layout: 'records'
                }, {silent: true});
                // No model is passed.
                app.utils.openEmailCreateDrawer.callsArgWith(2, field.context);

                field.launchEmailClient(event);

                expect(field.trigger).not.toHaveBeenCalledWith('emailclient:close');
                expect(app.controller.context.reloadData).not.toHaveBeenCalled();
                expect(app.controller.context.trigger).not.toHaveBeenCalledWith('panel-top:refresh');
            });
        });
    });

    describe('Retrieve Valid Recipients', function() {
        it('should not return bean recipients that do not have a valid email address', function() {
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo1@bar.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo2@bar.com',
                primary_address: true,
                invalid_email: true,
                opt_out: true
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(0);
        });

        it('should return bean recipients that have a valid email address', function() {
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo1@bar.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo2@bar.com',
                primary_address: false,
                invalid_email: false,
                opt_out: true
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo3@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBeUndefined();
            expect(validRecipients[0].get('email_address')).toBeUndefined();
        });

        it('should use the specified email address', function() {
            var email = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'abc@bar.com'
            });
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('abc@bar.com');
        });

        using('invalid email values', [true, false, undefined, null, ''], function(value) {
            it('should include the invalid_email attribute for an email address', function() {
                var email = app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'abc@bar.com',
                    invalid_email: value
                });
                var recipients = [{
                    email: email
                }];
                var validRecipients = field._retrieveValidRecipients(recipients);

                expect(validRecipients[0].get('invalid_email')).toBe(value);
            });
        });

        using('opt-out values', [true, false, undefined, null, ''], function(value) {
            it('should include the opt_out attribute for an email address', function() {
                var email = app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'abc@bar.com',
                    opt_out: value
                });
                var recipients = [{
                    email: email
                }];
                var validRecipients = field._retrieveValidRecipients(recipients);

                expect(validRecipients[0].get('opt_out')).toBe(value);
            });
        });

        it('should not return an email address if it does not have id', function() {
            var email = app.data.createBean('EmailAddresses', {
                email_address: 'abc@bar.com'
            });
            var recipients = [{
                email: email
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(0);
        });

        it('should not use the specified email address if it does not have id', function() {
            var email = app.data.createBean('EmailAddresses', {
                email_address: 'abc@bar.com'
            });
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBeUndefined();
            expect(validRecipients[0].get('email_address')).toBeUndefined();
        });

        it('should not set the parent fields when a bean is not provided', function() {
            var email = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'abc@bar.com'
            });
            var recipients = [{
                email: email
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toBeUndefined();
            expect(validRecipients[0].get('parent_type')).toBeUndefined();
            expect(validRecipients[0].get('parent_id')).toBeUndefined();
            expect(validRecipients[0].get('parent_name')).toBeUndefined();
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('abc@bar.com');
        });

        it('should have an empty parent name when the name is erased', function() {
            var email = app.data.createBean('EmailAddresses', {
                _acl: {},
                _erased_fields: [],
                id: _.uniqueId(),
                email_address: 'test@example.com',
                invalid_email: false,
                opt_out: false
            });
            var bean = app.data.createBean('Contacts', {
                _acl: {},
                _erased_fields: [
                    'first_name',
                    'last_name'
                ],
                id: _.uniqueId(),
                name: '',
                first_name: '',
                last_name: ''
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toEqual({
                _acl: {},
                _erased_fields: [
                    'first_name',
                    'last_name'
                ],
                type: 'Contacts',
                id: bean.get('id'),
                name: '',
                first_name: '',
                last_name: ''
            });
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('');
            expect(validRecipients[0].get('email_addresses')).toEqual({
                _acl: {},
                _erased_fields: [],
                id: email.get('id'),
                email_address: 'test@example.com',
                invalid_email: false,
                opt_out: false
            });
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('test@example.com');
            expect(validRecipients[0].get('invalid_email')).toBe(false);
            expect(validRecipients[0].get('opt_out')).toBe(false);
        });

        it('should have an empty email address when the email address is erased', function() {
            var email = app.data.createBean('EmailAddresses', {
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: _.uniqueId(),
                email_address: '',
                invalid_email: false,
                opt_out: false
            });
            var recipients = [{
                email: email
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toBeUndefined();
            expect(validRecipients[0].get('parent_type')).toBeUndefined();
            expect(validRecipients[0].get('parent_id')).toBeUndefined();
            expect(validRecipients[0].get('parent_name')).toBeUndefined();
            expect(validRecipients[0].get('email_addresses')).toEqual({
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: email.get('id'),
                email_address: '',
                invalid_email: false,
                opt_out: false
            });
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('');
            expect(validRecipients[0].get('invalid_email')).toBe(false);
            expect(validRecipients[0].get('opt_out')).toBe(false);
        });

        it('should not have an email address when the email address is erased', function() {
            var email = app.data.createBean('EmailAddresses', {
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: _.uniqueId(),
                email_address: '',
                invalid_email: false,
                opt_out: false
            });
            var bean = app.data.createBean('Contacts', {
                _acl: {},
                _erased_fields: [],
                id: _.uniqueId(),
                name: 'Phil Thomas',
                first_name: 'Phil',
                last_name: 'Thomas'
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toEqual({
                _acl: {},
                _erased_fields: [],
                type: 'Contacts',
                id: bean.get('id'),
                name: 'Phil Thomas',
                first_name: 'Phil',
                last_name: 'Thomas'
            });
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_addresses')).toBeUndefined();
            expect(validRecipients[0].get('email_address_id')).toBeUndefined();
            expect(validRecipients[0].get('email_address')).toBeUndefined();
            expect(validRecipients[0].get('invalid_email')).toBeUndefined();
            expect(validRecipients[0].get('opt_out')).toBeUndefined();
        });

        it('should have an empty parent name and email address when the name an email address are erased', function() {
            var email = app.data.createBean('EmailAddresses', {
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: _.uniqueId(),
                email_address: '',
                invalid_email: false,
                opt_out: false
            });
            var bean = app.data.createBean('Contacts', {
                _acl: {},
                _erased_fields: [
                    'first_name',
                    'last_name'
                ],
                id: _.uniqueId(),
                name: '',
                first_name: '',
                last_name: ''
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toEqual({
                _acl: {},
                _erased_fields: [
                    'first_name',
                    'last_name'
                ],
                type: 'Contacts',
                id: bean.get('id'),
                name: '',
                first_name: '',
                last_name: ''
            });
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('');
            expect(validRecipients[0].get('email_addresses')).toEqual({
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: email.get('id'),
                email_address: '',
                invalid_email: false,
                opt_out: false
            });
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('');
            expect(validRecipients[0].get('invalid_email')).toBe(false);
            expect(validRecipients[0].get('opt_out')).toBe(false);
        });
    });

    describe('Should Add Email Options', function() {
        it('should not specify related on email options if model specified has no module', function() {
            field.addEmailOptions({related: {foo: 'bar'}});
            expect(field.emailOptions.related).toBeUndefined();
        });

        it('should overlay email options with new values, not replace the whole set', function() {
            field.emailOptions = {foo: 'bar', bar: 'foo'};
            field.addEmailOptions({bar: 'yes', baz: 'no'});
            expect(field.emailOptions).toEqual({
                foo: 'bar',
                bar: 'yes',
                baz: 'no'
            });
        });

        it('should compact email options to remove key-value pairs where the value is undefined', function() {
            field.emailOptions = {
                foo: 'bar',
                biz: 'baz',
                yak: 'yuk'
            };
            field.addEmailOptions({
                // "fizz" should be added.
                fizz: 'buzz',
                // "foo" should be removed.
                foo: undefined,
                // The existing value for "biz" should be replaced.
                biz: null,
                qux: '',
                // "qwerty" should not be added at all.
                qwerty: undefined,
                // The existing value for "yak" should be replaced.
                yak: 'yook'
            });

            expect(_.keys(field.emailOptions).sort()).toEqual(['biz', 'fizz', 'qux', 'yak']);
            expect(field.emailOptions).toEqual({
                biz: null,
                fizz: 'buzz',
                qux: '',
                yak: 'yook'
            });
        });
    });

    describe('Build mailto: Url', function() {
        it('should return an empty mailto if no options passed', function() {
            var url = field._buildMailToURL({});
            expect(url).toBe('mailto:');
        });

        it('should return mailto with only to addresses', function() {
            var options = {
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo1@bar.com'
                    })
                }, {
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo2@bar.com'
                    })
                }]
            };
            var url = field._buildMailToURL(options);
            expect(url).toEqual('mailto:foo1@bar.com,foo2@bar.com');
        });

        it('should return a mailto without any email addresses if they are empty', function() {
            var options = {
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }],
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }]
            };
            var url = field._buildMailToURL(options);

            expect(url).toEqual('mailto:');
        });

        it('should return a mailto without any email addresses if they have been erased', function() {
            var options = {
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [
                            'email_address',
                            'email_address_caps'
                        ],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }],
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [
                            'email_address',
                            'email_address_caps'
                        ],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        _acl: {},
                        _erased_fields: [
                            'email_address',
                            'email_address_caps'
                        ],
                        id: _.uniqueId(),
                        email_address: '',
                        invalid_email: false,
                        opt_out: false
                    })
                }]
            };
            var url = field._buildMailToURL(options);

            expect(url).toEqual('mailto:');
        });

        it('should return mailto with cc and bcc addresses in querystring', function() {
            var url = field._buildMailToURL({
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo1@bar.com'
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo2@bar.com'
                    })
                }]
            });
            var expectedParams = {
                cc: 'foo1@bar.com',
                bcc: 'foo2@bar.com'
            };
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });

        it('should return mailto with subject and text body in querystring', function() {
            var expectedParams = {
                subject: 'Foo',
                body: 'Bar!'
            };
            var url = field._buildMailToURL({
                name: expectedParams.subject,
                description: expectedParams.body,
                description_html: '<b>' + expectedParams.body + '</b>'
            });
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });
    });

    describe('Format Recipients To String', function() {
        using(
            'strings for email',
            [{
                expected: '',
                recipient: {}
            }, {
                expected: '',
                recipient: {
                    email: ''
                }
            }, {
                expected: 'foo@bar.com',
                recipient: {
                    email: 'foo@bar.com'
                }
            }],
            function(data) {
                it('should format the email addresses in a string', function() {
                    var actual = field._formatRecipientsToString(data.recipient);

                    expect(actual).toBe(data.expected);
                });
            }
        );

        it('should not return the email address when there is not one', function() {
            var recipient = {
                email: app.data.createBean('EmailAddresses')
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        using(
            'email addresses',
            [{
                expected: 'foo@bar.com',
                email: {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                }
            }, {
                expected: 'foo@bar.com',
                email: {
                    email_address: 'foo@bar.com'
                }
            }],
            function(data) {
                it('should return the email address when there is one', function() {
                    var recipient = {
                        email: app.data.createBean('EmailAddresses', data.email)
                    };
                    var actual = field._formatRecipientsToString(recipient);

                    expect(actual).toBe(data.expected);
                });
            }
        );

        it('should not return the email address when the bean is of the wrong module', function() {
            var recipient = {
                email: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        it('should return the primary address of the bean', function() {
            var recipient = {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should return the primary address of the bean when the email address does not have one', function() {
            var recipient = {
                email: app.data.createBean('EmailAddresses'),
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should not return the primary address of the bean if it is invalid', function() {
            var recipient = {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: false,
                        invalid_email: true,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        it('should return the email address over the primary address of the bean', function() {
            var recipient = {
                email: 'foo@bar.com',
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'biz@baz.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should return more than one email address', function() {
            var recipients = [{
                email: app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                })
            }, {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'biz@baz.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            }, {
                email: app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'qux@qar.com'
                })
            }];
            var actual = field._formatRecipientsToString(recipients);

            expect(actual).toBe('foo@bar.com,biz@baz.com,qux@qar.com');
        });
    });

    describe('Retrieving Email Options', function() {
        it('should return empty object if no options on link or controller', function() {
            var actual;
            var $link = $('<a href="#">Foo!</a>');

            field.emailOptions = undefined;
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({});
        });

        it('should return options from controller combined with options from link', function() {
            var actual;
            var $link = $('<a href="#">Foo!</a>');
            var to = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'foo@bar.com'
            });
            var cc = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'foo2@bar.com'
            });

            $link.data({
                to: [{
                    email: to
                }],
                subject: 'Bar!!!'
            });
            field.emailOptions = {
                cc: [{
                    email: cc
                }],
                subject: 'Bar'
            };
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({
                to: [{
                    email: to
                }],
                cc: [{
                    email: cc
                }],
                subject: 'Bar!!!'
            });
        });

        it('should set opt_out from data attribute in email link', function() {
            var actual;
            var $link = $('<a href="#" data-email-to="foo@bar.com" data-email-opt-out="true">Foo!</a>');
            var emailField = SugarTest.createField('base', 'email', 'email', 'detail');

            actual = emailField._retrieveEmailOptions($link);
            expect(actual.to[0].email.get('opt_out')).toBe(true);
        });
    });

    describe('Setting email links on attach', function() {
        it('should set href to mailto link on render if client is external', function() {
            setUseSugarClient(false);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('mailto:');
        });

        it('should set href to void link on render if client is internal', function() {
            setUseSugarClient(true);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('javascript:void(0)');
        });
    });

    describe('updating email options on changes to the model', function() {
        var contact;
        var email;

        beforeEach(function() {
            contact = app.data.createBean('Contacts', {id: _.uniqueId()});
            email = app.data.createBean('Emails', {id: _.uniqueId()});

            sandbox.spy(field, 'render');
            sandbox.spy(field, 'addEmailOptions');
            field.emailOptionTo = sandbox.spy();
            field.emailOptionCc = sandbox.spy();
            field.emailOptionBcc = sandbox.spy();
            field.emailOptionSubject = sandbox.spy();
            field.emailOptionDescription = sandbox.spy();
            field.emailOptionDescriptionHtml = sandbox.spy();
            field.emailOptionAttachments = sandbox.spy();
            field.emailOptionRelated = sandbox.spy();
            field.emailOptionTeams = sandbox.spy();
        });

        it('should update email options when the field is initialized', function() {
            // Set the context's model and initialize the field.
            field.context.set('model', contact);
            field.trigger('init');

            expect(field.emailOptionTo).toHaveBeenCalledWith(contact);
            expect(field.emailOptionCc).toHaveBeenCalledWith(contact);
            expect(field.emailOptionBcc).toHaveBeenCalledWith(contact);
            expect(field.emailOptionSubject).toHaveBeenCalledWith(contact);
            expect(field.emailOptionDescription).toHaveBeenCalledWith(contact);
            expect(field.emailOptionDescriptionHtml).toHaveBeenCalledWith(contact);
            expect(field.emailOptionAttachments).toHaveBeenCalledWith(contact);
            expect(field.emailOptionRelated).toHaveBeenCalledWith(contact);
            expect(field.emailOptionTeams).toHaveBeenCalledWith(contact);
            expect(field.addEmailOptions).toHaveBeenCalled();
            expect(field.render).not.toHaveBeenCalled();
        });

        using(
            'model change events',
            [
                'change',
                'change:from_collection',
                'change:to_collection',
                'change:cc_collection',
                'change:bcc_collection'
            ],
            function(event) {
                it('should update email options when the model changes', function() {
                    // Set the context's model and initialize the field.
                    field.context.set('model', contact);
                    field.trigger('init');

                    // Change the model.
                    contact.trigger(event);

                    expect(field.emailOptionTo).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTo).toHaveBeenCalledTwice();
                    expect(field.emailOptionCc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionCc).toHaveBeenCalledTwice();
                    expect(field.emailOptionBcc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionBcc).toHaveBeenCalledTwice();
                    expect(field.emailOptionSubject).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionSubject).toHaveBeenCalledTwice();
                    expect(field.emailOptionDescription).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescription).toHaveBeenCalledTwice();
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledTwice();
                    expect(field.emailOptionAttachments).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionAttachments).toHaveBeenCalledTwice();
                    expect(field.emailOptionRelated).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionRelated).toHaveBeenCalledTwice();
                    expect(field.emailOptionTeams).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTeams).toHaveBeenCalledTwice();
                    expect(field.addEmailOptions).toHaveBeenCalledTwice();
                    expect(field.render).toHaveBeenCalledOnce();
                });

                it('should update email options when the parent model changes', function() {
                    // Set the context's model.
                    field.context.set('model', email);

                    // Create a new context for the parent context.
                    field.context.parent = new app.Bean();
                    field.context.parent.set('model', contact);

                    // Initialize the field.
                    field.trigger('init');

                    // Change the parent model.
                    contact.trigger(event);

                    expect(field.emailOptionTo).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTo).toHaveBeenCalledTwice();
                    expect(field.emailOptionCc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionCc).toHaveBeenCalledTwice();
                    expect(field.emailOptionBcc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionBcc).toHaveBeenCalledTwice();
                    expect(field.emailOptionSubject).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionSubject).toHaveBeenCalledTwice();
                    expect(field.emailOptionDescription).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescription).toHaveBeenCalledTwice();
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledTwice();
                    expect(field.emailOptionAttachments).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionAttachments).toHaveBeenCalledTwice();
                    expect(field.emailOptionRelated).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionRelated).toHaveBeenCalledTwice();
                    expect(field.emailOptionTeams).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTeams).toHaveBeenCalledTwice();
                    expect(field.addEmailOptions).toHaveBeenCalledTwice();
                    expect(field.render).toHaveBeenCalledOnce();
                });

                it('should not update email options when the model changes', function() {
                    // Set the context's model.
                    field.context.set('model', email);

                    // Create a new context for the parent context.
                    field.context.parent = new app.Bean();
                    field.context.parent.set('model', contact);

                    // Initialize the field.
                    field.trigger('init');

                    // Change the model.
                    email.trigger(event);

                    expect(field.emailOptionTo).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTo).toHaveBeenCalledOnce();
                    expect(field.emailOptionCc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionCc).toHaveBeenCalledOnce();
                    expect(field.emailOptionBcc).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionBcc).toHaveBeenCalledOnce();
                    expect(field.emailOptionSubject).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionSubject).toHaveBeenCalledOnce();
                    expect(field.emailOptionDescription).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescription).toHaveBeenCalledOnce();
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionDescriptionHtml).toHaveBeenCalledOnce();
                    expect(field.emailOptionAttachments).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionAttachments).toHaveBeenCalledOnce();
                    expect(field.emailOptionRelated).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionRelated).toHaveBeenCalledOnce();
                    expect(field.emailOptionTeams).toHaveBeenCalledWith(contact);
                    expect(field.emailOptionTeams).toHaveBeenCalledOnce();
                    expect(field.addEmailOptions).toHaveBeenCalledOnce();
                    expect(field.render).not.toHaveBeenCalled();
                });
            }
        );
    });
});
