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
describe('Emails.Field.ReplyAction', function() {
    var app;
    var field;
    var model;
    var sandbox;
    var context;

    function createParticipant(link, email, parentType, parentName) {
        var parentId = _.uniqueId();
        var emailAddressId = _.uniqueId();

        return app.data.createBean('EmailParticipants', {
            _link: link,
            id: _.uniqueId(),
            parent: {
                _acl: {},
                _erased_fields: [],
                type: parentType,
                id: parentId,
                name: parentName
            },
            parent_type: parentType,
            parent_id: parentId,
            parent_name: parentName,
            email_addresses: {
                email_address: email,
                id: emailAddressId,
                invalid_email: false,
                opt_out: false,
                _erased_fields: []
            },
            email_address_id: emailAddressId,
            email_address: email,
            invalid_email: false,
            opt_out: false
        });
    }

    function eraseName(participant) {
        var parent = participant.get('parent');

        participant.set('parent_name', '');
        parent.name = '';
        parent._erased_fields = [
            'first_name',
            'last_name'
        ];
    }

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        var bhunterEmailAddressId = _.uniqueId();
        var parent;

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('reply-action', 'field', 'base', 'reply-header-html', 'Emails');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.loadComponent('base', 'field', 'forward-action', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();
        app.routing.start();

        // Used by formatDate in the reply header template.
        app.user.setPreference('datepref', 'm/d/Y');
        app.user.setPreference('timepref', 'H:i');

        parent = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: 'Eric Johns'
        });

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');
        model.set({
            id: _.uniqueId(),
            name: 'My Subject',
            // Create a datetime string that will work in any timezone.
            date_sent: app.date('2012-03-27 01:48').format(),
            description: 'this is the plain-text body',
            description_html: '<p>this is the <b>html</b> body</p>',
            parent: {
                _acl: {},
                type: parent.module,
                id: parent.get('id'),
                name: parent.get('name')
            },
            parent_type: parent.module,
            parent_id: parent.get('id'),
            parent_name: parent.get('name'),
            team_name: [
                {
                    id: 'West',
                    name: 'West',
                    primary: false
                },
                {
                    id: 'East',
                    name: 'East',
                    primary: true
                }
            ]
        });
        model.set('from_collection', [
            createParticipant('from', 'rturner@example.com', 'Contacts', 'Ralph Turner')
        ]);
        model.set('to_collection', [
            createParticipant('to', 'gearl@example.com', 'Contacts', 'Georgia Earl'),
            createParticipant('to', 'nholman@example.com', 'Contacts', 'Nancy Holman'),
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                email_addresses: {
                    email_address: 'bhunter@example.com',
                    id: bhunterEmailAddressId,
                    invalid_email: false,
                    opt_out: false,
                    _erased_fields: []
                },
                email_address_id: bhunterEmailAddressId,
                email_address: 'bhunter@example.com',
                invalid_email: false,
                opt_out: false
            })
        ]);
        model.set('cc_collection', [
            createParticipant('cc', 'wbibby@example.com', 'Contacts', 'Wally Bibby')
        ]);
        model.set('bcc_collection', [
            createParticipant('bcc', 'rwithers@example.com', 'Contacts', 'Rhonda Withers')
        ]);
        model.set('attachments_collection', [
            app.data.createBean('Notes', {
                _link: 'attachments',
                id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 448406,
                file_ext: 'pdf'
            }),
            app.data.createBean('Notes', {
                _link: 'attachments',
                id: _.uniqueId(),
                name: 'terms.pdf',
                filename: 'terms.pdf',
                file_mime_type: 'application/pdf',
                file_size: 8699354,
                file_ext: 'pdf',
                upload_id: _.uniqueId()
            }),
            app.data.createBean('Notes', {
                _link: 'attachments',
                id: _.uniqueId(),
                name: 'banner.jpg',
                file_mime_type: 'image/jpg',
                file_size: 21876,
                file_ext: 'jpg'
            })
        ]);

        field = SugarTest.createField({
            name: 'reply_action',
            type: 'reply-action',
            viewName: 'record',
            module: 'Emails',
            loadFromModule: true,
            model: model,
            context: context
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    it('should use the emailaction templates', function() {
        expect(field.type).toBe('emailaction');
    });

    describe('email options', function() {
        it('should set the email signature location to above', function() {
            expect(field.emailOptions.signature_location).toBe('above');
        });

        it('should add the reply_to_id', function() {
            expect(field.emailOptions.reply_to_id).toBe(model.get('id'));
        });

        describe('adding the sender as a recipient', function() {
            var sender;

            beforeEach(function() {
                sender = model.get('from_collection').first();
            });

            it('should add the sender to the To field', function() {
                expect(field.emailOptions.to.length).toBe(1);
                expect(field.emailOptions.to[0].email.get('id')).toBe(sender.get('email_address_id'));
                expect(field.emailOptions.to[0].email.get('email_address')).toBe(sender.get('email_address'));
                expect(field.emailOptions.to[0].email.get('invalid_email')).toBe(sender.get('invalid_email'));
                expect(field.emailOptions.to[0].email.get('opt_out')).toBe(sender.get('opt_out'));
                expect(field.emailOptions.to[0].bean.module).toBe(sender.get('parent_type'));
                expect(field.emailOptions.to[0].bean.get('id')).toBe(sender.get('parent_id'));
                expect(field.emailOptions.to[0].bean.get('name')).toBe(sender.get('parent_name'));
                expect(field.emailOptions.cc).toBeUndefined();
            });

            it('should add the sender with an erased name to the To field', function() {
                eraseName(sender);
                field.model.trigger('change', field.model);

                expect(field.emailOptions.to.length).toBe(1);
                expect(field.emailOptions.to[0].email.get('id')).toBe(sender.get('email_address_id'));
                expect(field.emailOptions.to[0].email.get('email_address')).toBe(sender.get('email_address'));
                expect(field.emailOptions.to[0].email.get('invalid_email')).toBe(sender.get('invalid_email'));
                expect(field.emailOptions.to[0].email.get('opt_out')).toBe(sender.get('opt_out'));
                expect(field.emailOptions.to[0].bean.module).toBe(sender.get('parent_type'));
                expect(field.emailOptions.to[0].bean.get('id')).toBe(sender.get('parent_id'));
                expect(field.emailOptions.to[0].bean.get('name')).toBe('');
                expect(field.emailOptions.to[0].bean.get('_acl')).toEqual({});
                expect(field.emailOptions.to[0].bean.get('_erased_fields')).toEqual([
                    'first_name',
                    'last_name'
                ]);
            });
        });

        using('subjects', [
            {
                original: '',
                expected: 'Re: '
            },
            {
                original: undefined,
                expected: 'Re: '
            },
            {
                original: 'My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'Re: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'FW: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'FWD: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'RE: re: Re: rE: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'FW: fw: Fw: fW: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'FWD: fwd: Fwd: fWD: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'RE: FWD: re: fwd: Re: Fwd: rE: fwD: fw: FW: fW: My Subject',
                expected: 'Re: My Subject'
            },
            {
                original: 'My Re: Subject',
                expected: 'Re: My Re: Subject'
            },
            {
                original: 'My FW: Subject',
                expected: 'Re: My FW: Subject'
            },
            {
                original: 'My FWD: Subject',
                expected: 'Re: My FWD: Subject'
            },
            {
                original: 'My Subject Re:',
                expected: 'Re: My Subject Re:'
            },
            {
                original: 'My Subject FW:',
                expected: 'Re: My Subject FW:'
            },
            {
                original: 'My Subject FWD:',
                expected: 'Re: My Subject FWD:'
            }
        ], function(data) {
            it('should add the reply subject', function() {
                model.set('name', data.original);
                expect(field.emailOptions.name).toBe(data.expected);
            });
        });

        describe('building the reply content', function() {
            it('should add the email body', function() {
                var expected = '\n-----\n' +
                    'From: Ralph Turner <rturner@example.com>\n' +
                    'Date: 03/27/2012 01:48\n' +
                    'To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n' +
                    'Cc: Wally Bibby <wbibby@example.com>\n' +
                    'Subject: My Subject\n\n' +
                    'Here is my plain-text content.';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not add the email body', function() {
                sandbox.stub(field, 'useSugarEmailClient').returns(true);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBeUndefined();
            });

            using('content id\'s', ['forwardcontent', 'replycontent'], function(id) {
                it('should add the email HTML body', function() {
                    var body = 'And this is my <b>HTML</b> content.<br /><br />' +
                        '<div class="signature">My signature</div><br />' +
                        '<div id="' + id + '">Some reply content</div>';
                    var expected = '<div></div><div id="replycontent">\n' +
                        '<hr>\n' +
                        '<p>\n' +
                        '    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n' +
                        '    <strong>Date:</strong> 03/27/2012 01:48<br/>\n' +
                        '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                        'Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n' +
                        '    <strong>Cc:</strong> Wally Bibby &lt;wbibby@example.com&gt;<br/>\n' +
                        '    <strong>Subject:</strong> My Subject<br/>\n' +
                        '</p>\n' +
                        'And this is my <b>HTML</b> content.<br /><br />' +
                        '<div>My signature</div><br />' +
                        '<div>Some reply content</div>' +
                        '</div>';

                    model.set('description_html', body);

                    expect(field.emailOptions.description_html).toBe(expected);
                });
            });

            it('should not include Date in the email body', function() {
                var expected = '\n-----\n' +
                    'From: Ralph Turner <rturner@example.com>\n' +
                    'To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n' +
                    'Cc: Wally Bibby <wbibby@example.com>\n' +
                    'Subject: My Subject\n\n' +
                    'this is the plain-text body';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);

                model.unset('date_sent');

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not include Date in the email HTML body', function() {
                var expected = '<div></div><div id="replycontent">\n' +
                    '<hr>\n' +
                    '<p>\n' +
                    '    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n' +
                    // The template includes whitespace, but it won't be rendered in the client.
                    '    \n' +
                    '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                    'Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n' +
                    '    <strong>Cc:</strong> Wally Bibby &lt;wbibby@example.com&gt;<br/>\n' +
                    '    <strong>Subject:</strong> My Subject<br/>\n' +
                    '</p>\n' +
                    '<p>this is the <b>html</b> body</p>' +
                    '</div>';

                model.unset('date_sent');

                expect(field.emailOptions.description_html).toBe(expected);
            });

            it('should not include CC in the email body', function() {
                var expected = '\n-----\n' +
                    'From: Ralph Turner <rturner@example.com>\n' +
                    'Date: 03/27/2012 01:48\n' +
                    'To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n' +
                    'Subject: My Subject\n\n' +
                    'this is the plain-text body';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);
                model.get('cc_collection').reset([]);

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not include CC in the email HTML body', function() {
                var expected = '<div></div><div id="replycontent">\n' +
                    '<hr>\n' +
                    '<p>\n' +
                    '    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n' +
                    '    <strong>Date:</strong> 03/27/2012 01:48<br/>\n' +
                    '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                    'Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n' +
                    // The template includes whitespace, but it won't be rendered in the client.
                    '    \n' +
                    '    <strong>Subject:</strong> My Subject<br/>\n' +
                    '</p>\n' +
                    '<p>this is the <b>html</b> body</p>' +
                    '</div>';

                model.get('cc_collection').reset([]);

                expect(field.emailOptions.description_html).toBe(expected);
            });
        });

        it('should not add the attachments', function() {
            expect(field.emailOptions.attachments).toBeUndefined();
        });
    });
});
