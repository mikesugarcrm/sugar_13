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
describe('Emails.Field.ForwardAction', function() {
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

    function eraseEmailAddress(participant) {
        var link = participant.get('email_addresses');

        participant.set('email_address', '');
        link.email_address = '';
        link._erased_fields = [
            'email_address',
            'email_address_caps'
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

        SugarTest.loadHandlebarsTemplate('forward-action', 'field', 'base', 'forward-header-html', 'Emails');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();
        app.routing.start();

        // Used by formatDate in the forward header template.
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
            name: 'forward_button',
            type: 'forward-action',
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

        it('should not prepopulate the email with case data', function() {
            expect(field.emailOptions.skip_prepopulate_with_case).toBe(true);
        });

        using('subjects', [
            {
                original: '',
                expected: 'FW: '
            },
            {
                original: undefined,
                expected: 'FW: '
            },
            {
                original: 'My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'Re: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'FW: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'FWD: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'RE: re: Re: rE: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'FW: fw: Fw: fW: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'FWD: fwd: Fwd: fWD: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'RE: FWD: re: fwd: Re: Fwd: rE: fwD: fw: FW: fW: My Subject',
                expected: 'FW: My Subject'
            },
            {
                original: 'My Re: Subject',
                expected: 'FW: My Re: Subject'
            },
            {
                original: 'My FW: Subject',
                expected: 'FW: My FW: Subject'
            },
            {
                original: 'My FWD: Subject',
                expected: 'FW: My FWD: Subject'
            },
            {
                original: 'My Subject Re:',
                expected: 'FW: My Subject Re:'
            },
            {
                original: 'My Subject FW:',
                expected: 'FW: My Subject FW:'
            },
            {
                original: 'My Subject FWD:',
                expected: 'FW: My Subject FWD:'
            }
        ], function(data) {
            it('should add the forward subject', function() {
                model.set('name', data.original);
                expect(field.emailOptions.name).toBe(data.expected);
            });
        });

        describe('building the forward content', function() {
            it('should add the plain text version of email body', function() {
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

            it('should not add the plain text version of email body', function() {
                sandbox.stub(field, 'useSugarEmailClient').returns(true);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBeUndefined();
            });

            using('content id\'s', ['forwardcontent', 'replycontent'], function(id) {
                it('should add the email HTML body', function() {
                    var body = 'And this is my <b>HTML</b> content.<br /><br />' +
                        '<div class="signature">My signature</div><br />' +
                        '<div id="' + id + '">Some forward content</div>';
                    var expected = '<div></div><div id="forwardcontent">\n' +
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
                        '<div>Some forward content</div>' +
                        '</div>';

                    model.set('description_html', body);

                    expect(field.emailOptions.description_html).toBe(expected);
                });
            });

            it('should not include Date in the email HTML body', function() {
                var expected = '<div></div><div id="forwardcontent">\n' +
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

            it('should not include CC in the email HTML body', function() {
                var expected = '<div></div><div id="forwardcontent">\n' +
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

            it('should use "Value erased" for erased names and email addresses', function() {
                var from = model.get('from_collection');
                var to = model.get('to_collection');
                var cc = model.get('cc_collection');
                var bcc = model.get('bcc_collection');
                var expected = '\n-----\n' +
                    'From: Value erased <Value erased>\n' +
                    'Date: 03/27/2012 01:48\n' +
                    'To: Value erased <Value erased>, Value erased <Value erased>, Value erased\n' +
                    'Cc: Value erased <Value erased>\n' +
                    'Subject: My Subject\n\n' +
                    'Here is my plain-text content.';

                // The sender has an email address and name.
                eraseName(from.at(0));
                eraseEmailAddress(from.at(0));

                // The recipient has an email address and name.
                eraseName(to.at(0));
                eraseEmailAddress(to.at(0));

                // The recipient has an email address and name.
                eraseName(to.at(1));
                eraseEmailAddress(to.at(1));

                // The recipient has only an email address.
                eraseEmailAddress(to.at(2));

                // The recipient has an email address and name.
                eraseName(cc.at(0));
                eraseEmailAddress(cc.at(0));

                // The recipient has an email address and name.
                eraseName(bcc.at(0));
                eraseEmailAddress(bcc.at(0));

                sandbox.stub(field, 'useSugarEmailClient').returns(false);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBe(expected);
            });
        });

        it('should add the attachments', function() {
            var attachments = model.get('attachments_collection');
            var options = field.emailOptions.attachments;

            expect(options.length).toBe(3);
            expect(options[0].id).toBeUndefined();
            expect(options[0].name).toBe(attachments.at(0).get('filename'));
            expect(options[0].filename).toBe(attachments.at(0).get('filename'));
            expect(options[0].file_mime_type).toBe(attachments.at(0).get('file_mime_type'));
            expect(options[0].file_size).toBe(attachments.at(0).get('file_size'));
            expect(options[0].file_ext).toBe(attachments.at(0).get('file_ext'));
            expect(options[0].upload_id).toBe(attachments.at(0).get('id'));
            expect(options[1].id).toBeUndefined();
            expect(options[1].name).toBe(attachments.at(1).get('filename'));
            expect(options[1].filename).toBe(attachments.at(1).get('filename'));
            expect(options[1].file_mime_type).toBe(attachments.at(1).get('file_mime_type'));
            expect(options[1].file_size).toBe(attachments.at(1).get('file_size'));
            expect(options[1].file_ext).toBe(attachments.at(1).get('file_ext'));
            expect(options[1].upload_id).toBe(attachments.at(1).get('upload_id'));
            expect(options[2].id).toBeUndefined();
            expect(options[2].name).toBe(attachments.at(2).get('name'));
            expect(options[2].filename).toBe(attachments.at(2).get('name'));
            expect(options[2].file_mime_type).toBe(attachments.at(2).get('file_mime_type'));
            expect(options[2].file_size).toBe(attachments.at(2).get('file_size'));
            expect(options[2].file_ext).toBe(attachments.at(2).get('file_ext'));
            expect(options[2].upload_id).toBe(attachments.at(2).get('id'));
        });

        describe('adding the related record', function() {
            it('should add the parent', function() {
                expect(field.emailOptions.related.module).toBe(model.get('parent').type);
                expect(field.emailOptions.related.get('id')).toBe(model.get('parent').id);
                expect(field.emailOptions.related.get('name')).toBe(model.get('parent').name);
            });

            it('should add using the parent fields', function() {
                var parent = app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Cedric Harper'
                });

                model.unset('parent');
                model.set({
                    parent_type: parent.module,
                    parent_id: parent.get('id'),
                    parent_name: parent.get('name')
                });

                expect(field.emailOptions.related.module).toBe(parent.module);
                expect(field.emailOptions.related.get('id')).toBe(parent.get('id'));
                expect(field.emailOptions.related.get('name')).toBe(parent.get('name'));
            });
        });

        it('should add the selected teams', function() {
            expect(field.emailOptions.team_name).toEqual([
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
            ]);
        });
    });
});
