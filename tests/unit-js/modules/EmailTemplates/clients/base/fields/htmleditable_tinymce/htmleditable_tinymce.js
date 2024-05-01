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

describe('EmailTemplates.Field.Htmleditable_tinymce', function() {
    var app;
    var field;
    var context;
    var model;
    var sandbox;
    var editor;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('htmleditable_tinymce', 'field',
            'base', 'detail');
        SugarTest.loadHandlebarsTemplate('htmleditable_tinymce', 'field',
            'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        context = app.context.getContext({module: 'EmailTemplates'});
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

    describe('adding custom buttons', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'body_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'EmailTemplates',
                model: model,
                context: context,
                loadFromModule: true
            });
            sandbox.stub(editor.ui.registry, 'addMenuButton');
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should add all of the buttons', function() {
            field.addCustomButtons(editor);
            expect(editor.ui.registry.addMenuButton.callCount).toBe(1);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarattachment');
        });

        it('should not add attachments buttons', function() {
            sandbox.stub(app.acl, 'hasAccess');
            app.acl.hasAccess.withArgs('create', 'Notes').returns(false);
            app.acl.hasAccess.withArgs('view', 'EmailTemplates').returns(true);

            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton).not.toHaveBeenCalled();
        });

        it('should not add the attachment document button', function() {
            sandbox.stub(app.acl, 'hasAccess');
            app.acl.hasAccess.withArgs('create', 'Notes').returns(true);
            app.acl.hasAccess.withArgs('view', 'Documents').returns(false);
            app.acl.hasAccess.withArgs('view', 'EmailTemplates').returns(true);

            field.addCustomButtons(editor);

            expect(editor.ui.registry.addMenuButton.callCount).toBe(1);
            expect(editor.ui.registry.addMenuButton.getCall(0).args[0]).toBe('sugarattachment');
        });
    });

    describe('clicking custom buttons', function() {
        let subButtons = [];

        beforeEach(function() {
            field = SugarTest.createField({
                name: 'body_html',
                type: 'htmleditable_tinymce',
                viewName: 'edit',
                module: 'EmailTemplates',
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
            it('should trigger email_attachments:file on the view when the file button is clicked',
                function() {
                    var spy = sandbox.spy();
                    var button = subButtons[0];

                    field.view.on('email_attachments:file', spy);
                    button.onAction($.Event());

                    expect(spy).toHaveBeenCalledOnce();
                });

            it('should should allow the user to select a document when the documents button is clicked',
                function() {
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
    });
});
