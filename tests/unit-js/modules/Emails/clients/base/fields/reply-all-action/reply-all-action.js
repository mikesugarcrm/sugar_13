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
describe('Emails.Field.ReplyAllAction', function() {
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
        SugarTest.loadComponent('base', 'field', 'reply-action', 'Emails');
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
                    opt_out: true,
                    _erased_fields: []
                },
                email_address_id: bhunterEmailAddressId,
                email_address: 'bhunter@example.com',
                invalid_email: false,
                opt_out: true
            })
        ]);
        model.set('cc_collection', [
            createParticipant('cc', 'wbibby@example.com', 'Contacts', 'Wally Bibby')
        ]);
        model.set('bcc_collection', [
            createParticipant('bcc', 'rwithers@example.com', 'Contacts', 'Rhonda Withers')
        ]);

        field = SugarTest.createField({
            name: 'reply_all_action',
            type: 'reply-all-action',
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

    describe('email options', function() {
        it('should add the sender and To recipients to the To field and CC recipients to the CC field', function() {
            var sender = model.get('from_collection').first();

            model.trigger('change', model);

            expect(field.emailOptions.to.length).toBe(4);
            expect(field.emailOptions.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(field.emailOptions.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(field.emailOptions.to[0].email.get('invalid_email')).toBe(sender.get('invalid_email'));
            expect(field.emailOptions.to[0].email.get('opt_out')).toBe(sender.get('opt_out'));
            expect(field.emailOptions.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(field.emailOptions.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(field.emailOptions.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(field.emailOptions.to[1].email.get('id'))
                .toBe(model.get('to_collection').at(0).get('email_address_id'));
            expect(field.emailOptions.to[1].email.get('email_address'))
                .toBe(model.get('to_collection').at(0).get('email_address'));
            expect(field.emailOptions.to[1].email.get('invalid_email'))
                .toBe(model.get('to_collection').at(0).get('invalid_email'));
            expect(field.emailOptions.to[1].email.get('opt_out')).toBe(model.get('to_collection').at(0).get('opt_out'));
            expect(field.emailOptions.to[1].bean.module).toBe(model.get('to_collection').at(0).get('parent_type'));
            expect(field.emailOptions.to[1].bean.get('id')).toBe(model.get('to_collection').at(0).get('parent_id'));
            expect(field.emailOptions.to[1].bean.get('name')).toBe(model.get('to_collection').at(0).get('parent_name'));
            expect(field.emailOptions.to[2].email.get('id'))
                .toBe(model.get('to_collection').at(1).get('email_address_id'));
            expect(field.emailOptions.to[2].email.get('email_address'))
                .toBe(model.get('to_collection').at(1).get('email_address'));
            expect(field.emailOptions.to[2].email.get('invalid_email'))
                .toBe(model.get('to_collection').at(1).get('invalid_email'));
            expect(field.emailOptions.to[2].email.get('opt_out')).toBe(model.get('to_collection').at(1).get('opt_out'));
            expect(field.emailOptions.to[2].bean.module).toBe(model.get('to_collection').at(1).get('parent_type'));
            expect(field.emailOptions.to[2].bean.get('id')).toBe(model.get('to_collection').at(1).get('parent_id'));
            expect(field.emailOptions.to[2].bean.get('name')).toBe(model.get('to_collection').at(1).get('parent_name'));
            expect(field.emailOptions.to[3].email.get('id'))
                .toBe(model.get('to_collection').at(2).get('email_address_id'));
            expect(field.emailOptions.to[3].email.get('email_address'))
                .toBe(model.get('to_collection').at(2).get('email_address'));
            expect(field.emailOptions.to[3].email.get('invalid_email'))
                .toBe(model.get('to_collection').at(2).get('invalid_email'));
            expect(field.emailOptions.to[3].email.get('opt_out')).toBe(model.get('to_collection').at(2).get('opt_out'));
            expect(field.emailOptions.to[3].bean).toBeUndefined();
            expect(field.emailOptions.cc.length).toBe(1);
            expect(field.emailOptions.cc[0].email.get('id'))
                .toBe(model.get('cc_collection').at(0).get('email_address_id'));
            expect(field.emailOptions.cc[0].email.get('email_address'))
                .toBe(model.get('cc_collection').at(0).get('email_address'));
            expect(field.emailOptions.cc[0].email.get('invalid_email'))
                .toBe(model.get('cc_collection').at(0).get('invalid_email'));
            expect(field.emailOptions.cc[0].email.get('opt_out')).toBe(model.get('cc_collection').at(0).get('opt_out'));
            expect(field.emailOptions.cc[0].bean.module).toBe(model.get('cc_collection').at(0).get('parent_type'));
            expect(field.emailOptions.cc[0].bean.get('id')).toBe(model.get('cc_collection').at(0).get('parent_id'));
            expect(field.emailOptions.cc[0].bean.get('name')).toBe(model.get('cc_collection').at(0).get('parent_name'));
        });

        describe('building the reply content', function() {
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
        });
    });
});
