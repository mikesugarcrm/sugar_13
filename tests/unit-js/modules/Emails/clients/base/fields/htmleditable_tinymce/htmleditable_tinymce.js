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

describe('Emails.Field.Htmleditable_tinymce', function() {
    var app;
    var field;
    var context;
    var model;
    var sandbox;
    var editor;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('htmleditable_tinymce', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('htmleditable_tinymce', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();

        const addFunction = (name, options) => {
            editor.buttons[name] = options;
        };

        editor = {
            buttons: {},
            ui: {
                registry: {
                    addMenuButton: addFunction,
                    addButton: addFunction,
                },
            },
            on: $.noop
        };
    });

    afterEach(function() {
        delete app.drawer;
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('default signature', function() {
        var signature;

        beforeEach(function() {
            signature = app.data.createBean('UserSignatures', {
                id: _.uniqueId(),
                signature_html: 'my signature'
            });
            sandbox.stub(app.user, 'getPreference').withArgs('signature_default').returns(signature);
        });

        it('should store the default signature as the current signature', function() {
            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });

            expect(context.get('current_signature')).toBe(signature);
        });

        it('should default the signature location as below the content', function() {
            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });

            expect(context.get('signature_location')).toBe('below');
        });

        describe('adding the default signature', function() {
            beforeEach(function() {
                model.set('description_html', '<p>Some content</p>');
            });

            it('should not add the signature', function() {
                sandbox.stub(model, 'isNew').returns(false);

                field = SugarTest.createField({
                    name: 'description_html',
                    type: 'htmleditable_tinymce',
                    viewName: 'edit',
                    module: 'Emails',
                    model: model,
                    context: context,
                    loadFromModule: true
                });

                expect(model.get('description_html')).toBe('<p>Some content</p>');
            });

            it('should add the signature above the existing content', function() {
                var expected = '<div></div><div class="signature">my signature</div><div></div><p>Some content</p>';

                context.set('signature_location', 'above');
                field = SugarTest.createField({
                    name: 'description_html',
                    type: 'htmleditable_tinymce',
                    viewName: 'edit',
                    module: 'Emails',
                    model: model,
                    context: context,
                    loadFromModule: true
                });

                expect(model.get('description_html')).toBe(expected);
            });

            it('should add the signature below the existing content', function() {
                var expected = '<p>Some content</p><div></div><div class="signature">my signature</div><div></div>';

                context.set('signature_location', 'below');
                field = SugarTest.createField({
                    name: 'description_html',
                    type: 'htmleditable_tinymce',
                    viewName: 'edit',
                    module: 'Emails',
                    model: model,
                    context: context,
                    loadFromModule: true
                });

                expect(model.get('description_html')).toBe(expected);
            });

            it('should add the signature at the cursor', function() {
                context.set('signature_location', 'cursor');
                field = SugarTest.createField({
                    name: 'description_html',
                    type: 'htmleditable_tinymce',
                    viewName: 'edit',
                    module: 'Emails',
                    model: model,
                    context: context,
                    loadFromModule: true
                });
                sandbox.stub(field, '_insertSignature');

                // Trigger it twice to prove that it will only happen once.
                context.trigger('tinymce:oninit');
                context.trigger('tinymce:oninit');

                expect(field._insertSignature).toHaveBeenCalledOnce();
                expect(field._insertSignature).toHaveBeenCalledWith(signature, 'cursor');
            });
        });
    });

    describe('replies', function() {
        var htmlEditor;
        var jquery;

        beforeEach(function() {
            jquery = window.$;

            model.set({
                description_html: 'my reply content',
                reply_to_id: _.uniqueId()
            });

            htmlEditor = {
                focus: sandbox.stub(),
                getBody: sandbox.stub().returns('<p>Some content</p>'),
                selection: {
                    select: sandbox.stub(),
                    collapse: sandbox.stub(),
                    setCursorLocation: sandbox.stub()
                },
                dom: {
                    select: sandbox.stub().returns(function(selector) {
                        return ['<p></p>'];
                    }),
                    uniqueId: sandbox.stub().returns('mce-0')
                }
            };
        });

        afterEach(function() {
            window.$ = jquery;
        });

        it('should focus the editor', function() {
            context.set({
                cursor_location: 'above'
            });

            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });
            field._htmleditor = htmlEditor;
            window.$ = sandbox.stub().returns(htmlEditor);

            // Trigger it twice to prove that it will only happen once.
            context.trigger('tinymce:oninit');
            context.trigger('tinymce:oninit');

            expect(htmlEditor.focus).toHaveBeenCalledOnce();
        });

        it('should focus the editor and set the cursor to the bottom of the editor', function() {
            context.set({
                cursor_location: 'below'
            });

            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });
            field._htmleditor = htmlEditor;
            sandbox.stub(field, '_insertNodeInEditor').returns('<p id="mce-0"></p>');

            // Trigger it twice to prove that it will only happen once.
            context.trigger('tinymce:oninit');
            context.trigger('tinymce:oninit');

            expect(htmlEditor.focus).toHaveBeenCalledOnce();
            expect(htmlEditor.selection.setCursorLocation).toHaveBeenCalledOnce();
            expect(htmlEditor.selection.setCursorLocation).toHaveBeenCalledWith('<p id="mce-0"></p>');
            expect(htmlEditor.selection.collapse).toHaveBeenCalledOnce();
            expect(htmlEditor.selection.collapse).toHaveBeenCalledWith(true);
        });

        it('should not focus the editor', function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });
            field._htmleditor = htmlEditor;
            window.$ = sandbox.stub().returns(htmlEditor);

            context.trigger('tinymce:oninit');

            expect(htmlEditor.focus).not.toHaveBeenCalled();
            expect(htmlEditor.getBody).not.toHaveBeenCalled();
            expect(htmlEditor.selection.select).not.toHaveBeenCalled();
            expect(htmlEditor.selection.collapse).not.toHaveBeenCalled();
        });
    });

    describe('rendering the field', function() {
        var $textarea;

        beforeEach(function() {
            $textarea = $('<iframe class="htmleditable" frameborder="0" height="0"></iframe>');
        });

        using(
            'preview',
            [
                [
                    // Content height is padded to 25px more than it is set to
                    // allow for scrollbar padding.
                    'should set iframe height when contentHeight is set',
                    200,
                    '200px'
                ],
                [
                    'should set iframe to max height when contentHeight is greater than 400',
                    550,
                    '400px'
                ]
            ],
            function(should, contentHeight, expectedCssHeight) {
                it(should, function() {
                    var htmlEditor;
                    var cssHeight;

                    field = SugarTest.createField(
                        'base',
                        'description_html',
                        'htmleditable_tinymce',
                        'preview',
                        {},
                        'Emails',
                        null,
                        null,
                        true
                    );

                    sandbox.stub(field, '_iframeHasBody').returns(true);
                    sandbox.stub(field, '_getHtmlEditableField').returns($textarea);
                    sandbox.stub(field, 'destroyTinyMCEEditor');
                    sandbox.stub(field, '_getContentHeight').returns(contentHeight);
                    sandbox.stub(field, '_setIframeBaseTarget');

                    field.render();
                    htmlEditor = field._getHtmlEditableField();
                    cssHeight = htmlEditor.css('height');

                    expect(cssHeight).toBe(expectedCssHeight);
                });
            }
        );

        it('should not change the iframe height when template is not preview', function() {
            var cssHeight;
            var htmlEditor;
            var preRenderHeight;

            field = SugarTest.createField(
                'base',
                'description_html',
                'htmleditable_tinymce',
                'detail',
                {},
                'Emails',
                null,
                null,
                true
            );

            sandbox.stub(field, '_iframeHasBody').returns(false);
            sandbox.stub(field, '_getHtmlEditableField').returns($textarea);
            sandbox.stub(field, 'destroyTinyMCEEditor');
            sandbox.stub(field, '_setIframeBaseTarget');

            preRenderHeight = $textarea.css('height');
            field.render();
            htmlEditor = field._getHtmlEditableField();
            cssHeight = htmlEditor.css('height');

            expect(cssHeight).toBe(preRenderHeight);
        });

        using('view names', [
            [
                'edit',
                0,
                'should not set the base target to "_blank" for the iFrame'
            ],
            [
                'detail',
                1,
                'should set the base target to "_blank" for the iFrame'
            ]
        ], function(viewName, callCount, should) {
            it(should, function() {
                field = SugarTest.createField(
                    'base',
                    'description_html',
                    'htmleditable_tinymce',
                    viewName,
                    {},
                    'Emails',
                    null,
                    null,
                    true
                );
                field.action = viewName;

                sandbox.stub(field, '_iframeHasBody').returns(false);
                sandbox.stub(field, '_getHtmlEditableField').returns($textarea);
                sandbox.stub(field, 'destroyTinyMCEEditor');
                sandbox.stub(field, '_setIframeBaseTarget');

                field.render();

                expect(field._setIframeBaseTarget.callCount).toBe(callCount);
            });
        });
    });

    describe('adding custom buttons', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });
            sandbox.stub(editor.ui.registry, 'addMenuButton');
            sandbox.stub(editor.ui.registry, 'addButton');
        });

        it('should add all of the buttons', function() {
            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton.callCount).toBe(2);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarattachment');
            expect(editor.ui.registry.addMenuButton.getCall(1).args[0]).toBe('sugarsignature');
            expect(editor.ui.registry.addButton.getCall(0).args[0]).toBe('sugartemplate');
        });

        it('should not add attachments buttons', function() {
            sandbox.stub(app.acl, 'hasAccess');
            app.acl.hasAccess.withArgs('create', 'Notes').returns(false);
            app.acl.hasAccess.withArgs('view', 'EmailTemplates').returns(true);

            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton.callCount).toBe(1);
            expect(editor.ui.registry.addButton.callCount).toBe(1);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarsignature');
            expect(editor.ui.registry.addButton.getCall(0).args[0]).toBe('sugartemplate');
        });

        it('should not add the attachment document button', function() {
            sandbox.stub(app.acl, 'hasAccess');
            app.acl.hasAccess.withArgs('create', 'Notes').returns(true);
            app.acl.hasAccess.withArgs('view', 'Documents').returns(false);
            app.acl.hasAccess.withArgs('view', 'EmailTemplates').returns(true);

            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton.callCount).toBe(2);
            expect(editor.ui.registry.addButton.callCount).toBe(1);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarattachment');
            expect(editor.ui.registry.addMenuButton.getCall(1).args[0]).toBe('sugarsignature');
            expect(editor.ui.registry.addButton.getCall(0).args[0]).toBe('sugartemplate');
        });

        it('should not add the email template button', function() {
            sandbox.stub(app.acl, 'hasAccess');
            app.acl.hasAccess.withArgs('create', 'Notes').returns(true);
            app.acl.hasAccess.withArgs('view', 'Documents').returns(true);
            app.acl.hasAccess.withArgs('view', 'EmailTemplates').returns(false);

            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton.callCount).toBe(2);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarattachment');
            expect(editor.ui.registry.addMenuButton.getCall(1).args[0]).toBe('sugarsignature');
        });
    });

    describe('clicking custom buttons', function() {
        let subButtons = [];

        beforeEach(function() {
            field = SugarTest.createField({
                name: 'description_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context,
                loadFromModule: true
            });
            field.addCustomButtons(editor);

            const setButtonCallback = (cButtons) => {
                subButtons = cButtons;
            };
            editor.buttons.sugarattachment.fetch(setButtonCallback);
        });

        describe('attachments buttons', function() {
            it('should trigger email_attachments:file on the view when the file button is clicked', function() {
                var spy = sandbox.spy();
                var button = subButtons[0];

                field.view.on('email_attachments:file', spy);
                button.onAction($.Event());

                expect(spy).toHaveBeenCalledOnce();
            });

            it('should should allow the user to select a document when the documents button is clicked', function() {
                var spy = sandbox.spy();
                var button = subButtons[1];
                var doc = {
                    id: _.uniqueId(),
                    value: 'Quote.pdf',
                    name: 'Quote.pdf'
                };

                app.drawer = {
                    open: function(def, onClose) {
                        onClose(doc);
                    }
                };

                field.view.on('email_attachments:document', spy);
                button.onAction($.Event());

                expect(spy).toHaveBeenCalledOnce();
                expect(spy.firstCall.args[0].get('id')).toBe(doc.id);
            });
        });

        describe('template button', function() {
            var button;
            var template;

            beforeEach(function() {
                button = editor.buttons.sugartemplate;
                app.drawer = {
                    open: function(def, onClose) {
                        onClose(template);
                    }
                };
            });

            it('should alert the user if the user does not have view permissions for EmailTemplates', function() {
                sandbox.spy(app.alert, 'show');

                template = {
                    id: _.uniqueId(),
                    value: 'Welcome to our portal'
                };

                button.onAction($.Event());

                expect(app.alert.show).toHaveBeenCalledOnce();
                expect(app.alert.show.firstCall.args[0]).toBe('no_access_error');
                expect(app.alert.show.firstCall.args[1].level).toBe('error');
            });

            describe('using the selected template', function() {
                beforeEach(function() {
                    template = {
                        id: _.uniqueId(),
                        value: 'Welcome to our portal',
                        subject: 'Welcome to our portal',
                        body_html: '<h1>Template Content</h1>',
                        body: 'Template Content',
                        text_only: false
                    };
                });

                describe('confirm with the user', function() {
                    beforeEach(function() {
                        sandbox.stub(field, '_applyTemplate');
                    });

                    describe('requires confirmation before replacing the content', function() {
                        using('content fields', [
                            [
                                'previous subject',
                                '',
                                ''
                            ],
                            [
                                '',
                                'previous html',
                                ''
                            ],
                            [
                                '',
                                '',
                                'previous text'
                            ]
                        ], function(subject, html, text) {
                            it('should apply the template', function() {
                                var call;

                                sandbox.stub(app.alert, 'show');
                                model.set('name', subject);
                                model.set('description_html', html);
                                model.set('description', text);

                                button.onAction($.Event());

                                expect(app.alert.show.firstCall.args[0]).toBe('delete_confirmation');

                                app.alert.show.firstCall.args[1].onConfirm($.Event());

                                expect(field._applyTemplate).toHaveBeenCalledOnce();

                                call = field._applyTemplate.firstCall;
                                expect(call.args[0].get('id')).toBe(template.id);
                                expect(call.args[0].get('subject')).toBe(template.subject);
                                expect(call.args[0].get('body_html')).toBe(template.body_html);
                                expect(call.args[0].get('body')).toBe(template.body);
                                expect(call.args[0].get('text_only')).toBe(template.text_only);
                                expect(call.args[0].get('value')).toBeUndefined();
                            });

                            it('should not apply the template', function() {
                                sandbox.stub(app.alert, 'show');
                                model.set('name', subject);
                                model.set('description_html', html);
                                model.set('description', text);

                                button.onAction($.Event());

                                expect(app.alert.show.firstCall.args[0]).toBe('delete_confirmation');

                                app.alert.show.firstCall.args[1].onCancel($.Event());

                                expect(field._applyTemplate).not.toHaveBeenCalled();
                            });
                        });
                    });

                    it('should not require confirmation', function() {
                        var call;

                        sandbox.stub(app.alert, 'show');

                        button.onAction($.Event());

                        expect(app.alert.show).not.toHaveBeenCalled();
                        expect(field._applyTemplate).toHaveBeenCalledOnce();

                        call = field._applyTemplate.firstCall;
                        expect(call.args[0].get('id')).toBe(template.id);
                        expect(call.args[0].get('subject')).toBe(template.subject);
                        expect(call.args[0].get('body_html')).toBe(template.body_html);
                        expect(call.args[0].get('body')).toBe(template.body);
                        expect(call.args[0].get('text_only')).toBe(template.text_only);
                        expect(call.args[0].get('value')).toBeUndefined();
                    });
                });

                describe('applying the template', function() {
                    var modelData = {
                        nonReply: {
                            name: 'original subject',
                            description_html: '<div><b>original</b> content</div>'
                        },
                        reply: {
                            name: 'RE: original subject',
                            description_html: '<div>My Content</div><div id="replycontent">My Reply Content</div>'
                        },
                        forward: {
                            name: 'FW: original subject',
                            description_html: '<div>My Content</div><div id="forwardcontent">My Forwarded Content</div>'
                        }
                    };

                    beforeEach(function() {
                        // Skip the confirmation. The user accepts.
                        sandbox.stub(app.alert, 'show').callsFake(function(name, options) {
                            options.onConfirm($.Event());
                        });
                    });

                    describe('Subject line for non-reply, reply, and forward', function() {
                        var dataProvider = [
                            {
                                message: 'should use the template subject for a non-reply',
                                name: modelData.nonReply.name,
                                description_html: modelData.nonReply.description_html,
                                expected: 'Welcome to our portal'
                            },
                            {
                                message: 'should use the template subject for a reply',
                                name: modelData.reply.name,
                                description_html: modelData.reply.description_html,
                                expected: modelData.reply.name
                            },
                            {
                                message: 'should use the template subject for a forward',
                                name: modelData.forward.name,
                                description_html: modelData.forward.description_html,
                                expected: modelData.forward.name
                            }
                        ];

                        _.each(dataProvider, function(data) {
                            it(data.message, function() {
                                model.set('name', data.name);
                                model.set('description_html', data.description_html);
                                button.onAction($.Event());

                                expect(model.get('name')).toBe(data.expected);
                            });
                        }, this);
                    });

                    describe('HTML body for non-reply, reply, and forward', function() {
                        var dataProvider = [
                            {
                                message: 'should use the html body for a non-reply',
                                name: modelData.nonReply.name,
                                description_html: modelData.nonReply.description_html,
                                expected: '<h1>Template Content</h1>'
                            },
                            {
                                message: 'should use the html body for a reply',
                                name: modelData.reply.name,
                                description_html: modelData.reply.description_html,
                                expected: '<h1>Template Content</h1>' +
                                    '<div></div><div id="replycontent">My Reply Content</div><div></div>'
                            },
                            {
                                message: 'should use the html body for a forward',
                                name: modelData.forward.name,
                                description_html: modelData.forward.description_html,
                                expected: '<h1>Template Content</h1>' +
                                    '<div></div><div id="forwardcontent">My Forwarded Content</div><div></div>'
                            }
                        ];

                        _.each(dataProvider, function(data) {
                            it(data.message, function() {
                                model.set('name', data.name);
                                model.set('description_html', data.description_html);
                                button.onAction($.Event());

                                expect(model.get('description_html')).toBe(data.expected);
                            });
                        }, this);
                    });

                    describe('Text body for non-reply, reply, and forward', function() {
                        var dataProvider = [
                            {
                                message: 'should use the text body for a non-reply',
                                name: modelData.nonReply.name,
                                description_html: modelData.nonReply.description_html,
                                expected: 'Template Content'
                            },
                            {
                                message: 'should use the text body for a reply',
                                name: modelData.reply.name,
                                description_html: modelData.reply.description_html,
                                expected: 'Template Content' +
                                    '<div></div><div id="replycontent">My Reply Content</div><div></div>'
                            },
                            {
                                message: 'should use the text body for a forward',
                                name: modelData.forward.name,
                                description_html: modelData.forward.description_html,
                                expected: 'Template Content' +
                                    '<div></div><div id="forwardcontent">My Forwarded Content</div><div></div>'
                            }
                        ];

                        _.each(dataProvider, function(data) {
                            it(data.message, function() {
                                model.set('name', data.name);
                                model.set('description_html', data.description_html);
                                template.text_only = true;
                                button.onAction($.Event());

                                expect(model.get('description_html')).toBe(data.expected);
                            });
                        }, this);
                    });

                    it('should trigger an event so the attachments field can apply template attachments', function() {
                        var call;

                        sandbox.spy(field.view, 'trigger');

                        button.onAction($.Event());

                        expect(field.view.trigger).toHaveBeenCalledOnce();

                        call = field.view.trigger.firstCall;
                        expect(call.args[0]).toBe('email_attachments:template');
                        expect(call.args[1].get('id')).toBe(template.id);
                        expect(call.args[1].get('subject')).toBe(template.subject);
                        expect(call.args[1].get('body_html')).toBe(template.body_html);
                        expect(call.args[1].get('body')).toBe(template.body);
                        expect(call.args[1].get('text_only')).toBe(template.text_only);
                        expect(call.args[1].get('value')).toBeUndefined();
                    });

                    describe('appending the signature', function() {
                        beforeEach(function() {
                            sandbox.stub(field, '_insertSignature');
                        });

                        it('should append the signature', function() {
                            var signature = app.data.createBean('UserSignatures', {
                                id: _.uniqueId(),
                                signature_html: 'my signature'
                            });

                            context.set('current_signature', signature);

                            button.onAction($.Event());

                            expect(field._insertSignature).toHaveBeenCalledWith(signature, 'below');
                        });

                        it('should not append the signature', function() {
                            context.unset('current_signature');

                            button.onAction($.Event());

                            expect(field._insertSignature).not.toHaveBeenCalled();
                        });
                    });
                });
            });
        });

        describe('signature button', function() {
            var button;
            var signatures;

            beforeEach(function() {
                signatures = app.data.createBeanCollection('UserSignatures');
                signatures.add([
                    app.data.createBean('UserSignatures', {
                        id: _.uniqueId(),
                        signature_html: 'This &lt;signature&gt; has HTML-style brackets'
                    }),
                    app.data.createBean('UserSignatures', {
                        id: _.uniqueId(),
                        signature_html: 'This signature has no HTML-style brackets'
                    })
                ]);

                sandbox.stub(field, '_getSignatures').callsFake(function() {
                    field._signatureBtn = {
                        settings: {
                            menu: []
                        }
                    };
                    field._getSignaturesSuccess(signatures);
                });

                button = editor.buttons.sugarsignature;
                button.onSetup({
                    setEnabled: () => {},
                });
            });

            describe('decoding signatures', function() {
                beforeEach(function() {
                    sandbox.stub(field, '_insertInEditor');
                });

                it('should convert &lt; and &gt; to < and >', function() {
                    field._signatureSubmenu[0].onAction($.Event());

                    expect(field._insertInEditor).toHaveBeenCalledWith(
                        '<div class="signature keep">This <signature> has HTML-style brackets</div>',
                        'cursor'
                    );
                });

                it('should leave a signature as is if &lt; and &gt; are not found', function() {
                    field._signatureSubmenu[1].onAction($.Event());

                    expect(field._insertInEditor).toHaveBeenCalledWith(
                        '<div class="signature keep">This signature has no HTML-style brackets</div>',
                        'cursor'
                    );
                });
            });

            describe('insert a signature', function() {
                beforeEach(function() {
                    model.set('description_html', '<p>Some content</p>');
                });

                it('should insert a signature at the cursor', function() {
                    var expected = '<p>Some </p>' +
                        '<div></div><div></div>' +
                        '<div class="signature">This signature has no HTML-style brackets</div>' +
                        '<div></div><div></div>' +
                        'content<p></p>';
                    var tinymce = {
                        insertContent: function(value) {
                            this.content = '<p>Some </p><div></div>' + value + '<div></div>content<p></p>';
                        },
                        getContent: function() {
                            return this.content;
                        }
                    };
                    tinymce.content = model.get('description_html');
                    field._htmleditor = tinymce;

                    field._signatureSubmenu[1].onAction($.Event());

                    expect(model.get('description_html')).toBe(expected);
                });

                it('should append a signature', function() {
                    var expected = '<p>Some content</p>' +
                        '<div></div>' +
                        '<div class="signature">This signature has no HTML-style brackets</div>' +
                        '<div></div>';

                    field._insertSignature(signatures.at(1), 'below');

                    expect(model.get('description_html')).toBe(expected);
                });

                it('should prepend a signature', function() {
                    var expected = '<div></div>' +
                        '<div class="signature">This signature has no HTML-style brackets</div>' +
                        '<div></div>' +
                        '<p>Some content</p>';

                    field._insertSignature(signatures.at(1), 'above');

                    expect(model.get('description_html')).toBe(expected);
                });

                it('should replace any existing signatures', function() {
                    var expected = '<p>Some content</p>' +
                        '<div></div><div></div><div></div>' +
                        '<div class="signature">This signature has no HTML-style brackets</div>' +
                        '<div></div>';

                    model.set('description_html', '<p>Some content</p>' +
                        '<div></div><div class="signature">original signature</div><div></div>');

                    field._insertSignature(signatures.at(1), 'below');

                    expect(model.get('description_html')).toBe(expected);
                });

                it('should store the new signature as the current signature', function() {
                    field._signatureSubmenu[1].onAction($.Event());

                    expect(context.get('current_signature')).toBe(signatures.at(1));
                });
            });
        });
    });
});
