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
describe('Emails.BaseEmailComposeView', function() {
    var app;
    var view;
    var context;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({
            module: 'Emails',
            create: true
        });
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();

        model.off();
        view.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('initializing the view', function() {
        it('should be a draft if the model is new', function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);

            expect(model.get('state')).toBe('Draft');
        });

        using('states', ['Draft', 'Archived'], function(state) {
            it('should use the existing state if the model is not new', function() {
                model.set('id', _.uniqueId());
                model.set('state', state);

                view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);

                expect(model.get('state')).toBe(state);
            });
        });
    });

    describe('setting the page title', function() {
        it('should set the title for composing an email', function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);
            sandbox.stub(app.lang, 'get').returnsArg(0);
            sandbox.stub(view, 'setTitle');

            view.render();

            expect(view.setTitle).toHaveBeenCalledWith('LBL_COMPOSE_MODULE_NAME_SINGULAR');
        });
    });

    describe('email accounts are not configured', function() {
        var sendButton;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);
            sendButton = {
                def: {},
                setDisabled: sandbox.spy()
            };
        });

        it('should disable the send button', function() {
            var error = {
                status: 403,
                code: 'not_authorized',
                message: 'You are not authorized to perform this action.'
            };

            sandbox.stub(view, 'getField').withArgs('send_button').returns(sendButton);

            view.trigger('email_not_configured', error);

            expect(sendButton.setDisabled).toHaveBeenCalledOnce();
            expect(view._userHasConfiguration).toBe(false);
        });

        it('should not allow the send button to be enabled', function() {
            view._userHasConfiguration = false;
            view.buttons.send_button = sendButton;

            view.toggleButtons(true);

            expect(sendButton.setDisabled).toHaveBeenCalledTwice();
            expect(sendButton.setDisabled.firstCall.args[0]).toBe(false);
            expect(sendButton.setDisabled.lastCall.args[0]).toBe(true);
        });
    });

    describe('recipient fields', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);
        });

        it('should toggle action buttons while loading all recipients', function() {
            sandbox.spy(view, 'toggleButtons');

            view.trigger('loading_collection_field', 'to_collection');
            view.trigger('loading_collection_field', 'cc_collection');
            view.trigger('loading_collection_field', 'bcc_collection');

            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.alwaysCalledWithExactly(false)).toBe(true);

            view.trigger('loaded_collection_field', 'to_collection');

            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'cc_collection');

            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'bcc_collection');

            expect(view.toggleButtons.callCount).toBe(4);
            expect(view.toggleButtons.lastCall.args[0]).toBe(true);
        });

        using('recipient fields', ['to_collection', 'cc_collection', 'bcc_collection'], function(fieldName) {
            it('should render the recipients fieldset when a recipients field changes', function() {
                var fieldset = {
                    render: sandbox.spy()
                };

                sandbox.stub(view, 'getField').withArgs('recipients').returns(fieldset);
                model.trigger('change:' + fieldName);

                expect(fieldset.render).toHaveBeenCalled();
            });
        });
    });

    describe('managing attachments', function() {
        var sendButton;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);

            sendButton = {
                def: {},
                setDisabled: sandbox.spy()
            };
            sandbox.stub(view, 'getField').withArgs('send_button').returns(sendButton);
        });

        it('should disable the send button when the attachments are too large', function() {
            model.trigger('attachments_collection:over_max_total_bytes');

            expect(sendButton.setDisabled).toHaveBeenCalledWith(true);
        });

        using(
            'under limit',
            [
                [
                    'should enable the button when the user has an email configuration to use',
                    true,
                    false
                ],
                [
                    'should keep the button disabled when the user does not have an email configuration to use',
                    false,
                    true
                ]
            ],
            function(should, hasConfiguration, disableButton) {
                it(should, function() {
                    view._userHasConfiguration = hasConfiguration;
                    model.trigger('attachments_collection:under_max_total_bytes');

                    expect(sendButton.setDisabled).toHaveBeenCalledWith(disableButton);
                });
            }
        );
    });

    describe('sending an email', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);

            sandbox.stub(view, 'save');
            sandbox.spy(view, 'enableButtons');
            sandbox.spy(view, 'disableButtons');
            sandbox.stub(app.alert, 'show');
        });

        using(
            'required fields',
            [
                [
                    'to_collection',
                    'to',
                    'foo',
                    'bar'
                ],
                [
                    'cc_collection',
                    'cc',
                    'foo',
                    'bar'
                ],
                [
                    'bcc_collection',
                    'bcc',
                    'foo',
                    'bar'
                ]
            ],
            function(collection, link, subject, html) {
                it('should send the email when the required fields are populated', function() {
                    var parentId = _.uniqueId();

                    model.set(collection, app.data.createBean('EmailParticipants', {
                        _link: link,
                        parent: {
                            _acl: {},
                            type: 'Contacts',
                            id: parentId,
                            name: 'Ray Mason'
                        },
                        parent_type: 'Contacts',
                        parent_id: parentId,
                        parent_name: 'Ray Mason',
                        email_address_id: _.uniqueId(),
                        email_address: 'rmason@example.com',
                        invalid_email: false,
                        opt_out: false
                    }));
                    model.set('name', subject);
                    model.set('description_html', html);

                    view.send();

                    expect(view.save).toHaveBeenCalledOnce();
                    expect(app.alert.show).not.toHaveBeenCalled();
                    expect(model.get('state')).toBe('Ready');
                    expect(view.disableButtons).toHaveBeenCalledOnce();
                    // The CreateView#save flow will enable buttons.
                    expect(view.enableButtons).not.toHaveBeenCalled();
                });
            }
        );

        it('should show an error to the user when there are no recipients', function() {
            var spy = sandbox.spy();

            model.on('error:validation:to_collection', spy);
            model.set('name', 'foo');
            model.set('description_html', 'bar');

            view.send();

            expect(view.save).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledOnce();
            expect(app.alert.show.firstCall.args[0]).toBe('send_error');
            expect(spy).toHaveBeenCalledOnce();
            expect(model.get('state')).toBe('Draft');
            expect(view.disableButtons).toHaveBeenCalledOnce();
            expect(view.enableButtons).toHaveBeenCalledOnce();
        });

        using(
            'fields requiring confirmation',
            [
                // No content.
                {},
                // The body is empty.
                {
                    name: 'foo'
                },
                // The subject is empty.
                {
                    description_html: 'bar'
                },
                // The body only contains HTML tags, so it is empty.
                {
                    name: 'Hello there',
                    description_html: '<div><span><b></b></span></div>'
                },
                // Content has variables but the related to field is not set.
                {
                    name: 'Hi $contact_name',
                    description_html: 'How are you?',
                    description: ''
                },
                // Content has variables but the related to field is not set.
                {
                    name: 'Hello there',
                    description_html: 'Hi, $account_name, how are you?',
                    description: ''
                },
                // Content has variables but the related to field is not set.
                {
                    name: 'Read this!',
                    description_html: '<b>What do you think?</b>',
                    description: '$contact_name, What do you think?'
                },
                // Content has variables but the related to field is not set.
                {
                    name: 'Hi $contact_name',
                    description_html: 'Hi, $account_name, how are you?',
                    description: '$contact_name, What do you think?'
                }
            ],
            function(attributes) {
                it('should confirm with the user before sending', function() {
                    var spy = sandbox.spy();
                    var parentId = _.uniqueId();

                    view.on('error:validation:to_collection', spy);
                    model.set(attributes);
                    model.set('to_collection', app.data.createBean('EmailParticipants', {
                        _link: 'to',
                        parent: {
                            _acl: {},
                            type: 'Contacts',
                            id: parentId,
                            name: 'Jason Withers'
                        },
                        parent_type: 'Contacts',
                        parent_id: parentId,
                        parent_name: 'Jason Withers',
                        email_address_id: _.uniqueId(),
                        email_address: 'jwithers@example.com',
                        invalid_email: false,
                        opt_out: false
                    }));

                    view.send();

                    expect(view.save).not.toHaveBeenCalled();
                    expect(app.alert.show).toHaveBeenCalledOnce();
                    expect(app.alert.show.firstCall.args[0]).toBe('send_confirmation');
                    expect(spy).not.toHaveBeenCalledOnce();
                    expect(model.get('state')).toBe('Draft');
                    expect(view.disableButtons).toHaveBeenCalledOnce();
                    // The CreateView#save flow will enable buttons if the user
                    // confirms.
                    expect(view.enableButtons).not.toHaveBeenCalled();
                });
            }
        );

        it('should enable buttons after canceling instead of confirming', function() {
            var parentId = _.uniqueId();

            model.set({
                name: 'foo'
            });
            model.set('to_collection', app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Sam Nalon'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Sam Nalon',
                email_address_id: _.uniqueId(),
                email_address: 'snalon@example.com',
                invalid_email: false,
                opt_out: false
            }));

            view.send();
            app.alert.show.firstCall.args[1].onCancel($.Event());

            expect(view.save).not.toHaveBeenCalled();
            expect(model.get('state')).toBe('Draft');
            expect(view.disableButtons).toHaveBeenCalledOnce();
            expect(view.enableButtons).toHaveBeenCalledOnce();
        });

        using(
            'confirmation is not required',
            [
                {
                    name: 'Read this!',
                    description_html: '<b>What do you think?</b>',
                    description: 'What do you think?'
                },
                {
                    name: 'Hi $contact_name',
                    description_html: 'How are you?',
                    description: '',
                    parent_type: 'Contacts',
                    parent_id: _.uniqueId()
                },
                {
                    name: 'Hello there',
                    description_html: 'Hi, $account_name, how are you?',
                    description: '',
                    parent_type: 'Contacts',
                    parent_id: _.uniqueId()
                },
                {
                    name: 'Read this!',
                    description_html: '<b>What do you think?</b>',
                    description: '$contact_name, What do you think?',
                    parent_type: 'Contacts',
                    parent_id: _.uniqueId()
                },
                {
                    name: 'Hi $contact_name',
                    description_html: 'Hi, $account_name, how are you?',
                    description: '$contact_name, What do you think?',
                    parent_type: 'Contacts',
                    parent_id: _.uniqueId()
                }
            ],
            function(attributes) {
                it('should send the email', function() {
                    var parentId = _.uniqueId();

                    model.set(attributes);
                    model.set('to_collection', app.data.createBean('EmailParticipants', {
                        _link: 'to',
                        parent: {
                            _acl: {},
                            type: 'Contacts',
                            id: parentId,
                            name: 'Heather Dunn'
                        },
                        parent_type: 'Contacts',
                        parent_id: parentId,
                        parent_name: 'Heather Dunn',
                        email_address_id: _.uniqueId(),
                        email_address: 'hdunn@example.com',
                        invalid_email: false,
                        opt_out: false
                    }));

                    view.send();

                    expect(view.save).toHaveBeenCalledOnce();
                    expect(app.alert.show).not.toHaveBeenCalled();
                    expect(model.get('state')).toBe('Ready');
                    expect(view.disableButtons).toHaveBeenCalledOnce();
                    // The CreateView#save flow will enable buttons.
                    expect(view.enableButtons).not.toHaveBeenCalled();
                });
            }
        );

        it('should send the email when the send button is clicked', function() {
            sandbox.stub(view, 'send');
            context.trigger('button:send_button:click');

            expect(view.send).toHaveBeenCalledOnce();
        });
    });

    describe('saving an email', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', 'Emails', 'compose-email', null, context, true);
            sandbox.stub(app.lang, 'get').returnsArg(0);
        });

        it('should build a message stating that the draft was saved', function() {
            var actual;

            model.set('state', 'Draft');
            actual = view.buildSuccessMessage();

            expect(actual).toBe('LBL_DRAFT_SAVED');
            expect(app.lang.get.firstCall.args[1]).toBe(view.module);
        });

        it('should build a message stating that the email was sent', function() {
            var actual;

            model.set('state', 'Ready');
            actual = view.buildSuccessMessage();

            expect(actual).toBe('LBL_EMAIL_SENT');
            expect(app.lang.get.firstCall.args[1]).toBe(view.module);
        });

        it('should use hasUnsavedChanges from the create view', function() {
            sandbox.stub(model, 'isNew').returns(true);
            sandbox.stub(view, '_super').withArgs('hasUnsavedChanges');

            view.hasUnsavedChanges();

            expect(view._super).toHaveBeenCalled();
        });

        it('should use hasUnsavedChanges from the record view', function() {
            SugarTest.loadComponent('base', 'view', 'record', 'Emails');
            sandbox.stub(model, 'isNew').returns(false);
            sandbox.stub(app.view.views.BaseEmailsRecordView.prototype, 'hasUnsavedChanges');

            view.hasUnsavedChanges();

            expect(app.view.views.BaseEmailsRecordView.prototype.hasUnsavedChanges).toHaveBeenCalled();
        });
    });
});
