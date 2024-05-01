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
describe('Emails.Views.Create', function() {
    var app;
    var view;
    var layout;
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
        SugarTest.loadComponent('base', 'layout', 'create');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        SugarTest.loadFile(
            '../include/javascript/sugar7/plugins',
            'Tinymce',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        context = app.context.getContext({
            module: 'Emails',
            create: true
        });
        context.prepare(true);
        model = context.get('model');

        layout = SugarTest.createLayout('base', 'Emails', 'create', {}, null, false);
        view = SugarTest.createView('base', 'Emails', 'create', null, context, true, layout, true);

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        delete app.plugins.plugins.view.Tinymce;
        sinon.restore();
        sandbox.restore();

        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('initialize', function() {
        it('should add the TinyMCE to the plugins', function() {
            sinon.stub(view, '_super');
            view.plugins = _.without(view.plugins, 'Tinymce');

            view.initialize({});

            expect(view.plugins).toContain('Tinymce');
            expect(view._super).toHaveBeenCalledWith('initialize', [{}]);
        });
    });

    describe('setting the page title', function() {
        it('should set the title for creating an archived email', function() {
            sandbox.stub(app.lang, 'get').returnsArg(0);
            sandbox.stub(view, 'setTitle');

            view.render();

            expect(view.setTitle).toHaveBeenCalledWith('LNK_NEW_ARCHIVE_EMAIL');
        });
    });

    describe('managing attachments', function() {
        describe('hiding or showing the attachments field', function() {
            var field;
            var spyAddClass;
            var spyRemoveClass;

            beforeEach(function() {
                var $el;

                spyAddClass = sandbox.spy();
                spyRemoveClass = sandbox.spy();

                $el = {
                    closest: function() {
                        return {
                            addClass: spyAddClass,
                            removeClass: spyRemoveClass
                        };
                    }
                };

                field = {
                    getFieldElement: function() {
                        return $el;
                    },
                    isEmpty: $.noop
                };

                sandbox.stub(view, 'getField').withArgs('attachments_collection').returns(field);
            });

            describe('rendering the view', function() {
                it('should show the attachments field', function() {
                    // There are attachments.
                    sandbox.stub(field, 'isEmpty').returns(false);

                    view.render();

                    expect(spyAddClass).toHaveBeenCalledWith('single');
                    expect(spyRemoveClass).toHaveBeenCalledWith('hidden');
                });

                it('should hide the attachments field', function() {
                    // There are no attachments.
                    sandbox.stub(field, 'isEmpty').returns(true);

                    view.render();

                    expect(spyAddClass).toHaveBeenCalledWith('hidden');
                    expect(spyRemoveClass).toHaveBeenCalledWith('single');
                });
            });

            describe('responding to changes to the attachments', function() {
                it('should show the attachments field', function() {
                    // There are attachments.
                    sandbox.stub(field, 'isEmpty').returns(false);

                    model.trigger('change:attachments_collection');

                    expect(spyAddClass).toHaveBeenCalledWith('single');
                    expect(spyRemoveClass).toHaveBeenCalledWith('hidden');
                });

                it('should hide the attachments field', function() {
                    // THere are no attachments.
                    sandbox.stub(field, 'isEmpty').returns(true);

                    model.trigger('change:attachments_collection');

                    expect(spyAddClass).toHaveBeenCalledWith('hidden');
                    expect(spyRemoveClass).toHaveBeenCalledWith('single');
                });
            });
        });

        describe('alerting the user when the attachments are too large', function() {
            var saveButton;

            beforeEach(function() {
                saveButton = {
                    setDisabled: sandbox.spy()
                };
                sandbox.stub(view, 'getField').withArgs('save_button').returns(saveButton);
            });

            describe('attachments are over the limit', function() {
                it('should disable the save button', function() {
                    model.trigger('attachments_collection:over_max_total_bytes');

                    expect(saveButton.setDisabled).toHaveBeenCalledWith(true);
                });

                it('should alert the user', function() {
                    sandbox.stub(app.alert, 'show');

                    model.trigger('attachments_collection:over_max_total_bytes');

                    expect(app.alert.show).toHaveBeenCalledWith('email-attachment-status');
                });
            });

            describe('attachments are under the limit', function() {
                it('should enable the save button', function() {
                    model.trigger('attachments_collection:under_max_total_bytes');

                    expect(saveButton.setDisabled).toHaveBeenCalledWith(false);
                });

                it('should hide any open alerts', function() {
                    sandbox.stub(app.alert, 'dismiss');

                    model.trigger('attachments_collection:under_max_total_bytes');

                    expect(app.alert.dismiss).toHaveBeenCalledWith('email-attachment-status');
                });
            });
        });
    });

    describe('saving an email', function() {
        it('should build a message stating that the email was archived', function() {
            var actual;

            sandbox.stub(app.lang, 'get').returnsArg(0);

            actual = view.buildSuccessMessage();

            expect(actual).toBe('LBL_EMAIL_ARCHIVED');
            expect(app.lang.get.firstCall.args[1]).toBe(view.module);
        });

        it('should set the view parameter to the name of the view', function() {
            var options = view.getCustomSaveOptions({});
            expect(options.params.view).toBe(view.name);
        });
    });

    describe('setupEditorResize method present in Tinymce plugin', function() {
        beforeEach(function() {
            view.render();
            sandbox.stub(view, 'resizeEditor');
        });

        it('should resize the editor when tinymce is initialized', function() {
            view.context.trigger('tinymce:oninit');

            expect(view.resizeEditor).toHaveBeenCalledOnce();
        });

        it('should resize the editor when toggling to show/hide hidden panel', function() {
            view.trigger('more-less:toggled');

            expect(view.resizeEditor).toHaveBeenCalledOnce();
        });

        it('should resize the editor when toggling email-recipients', function() {
            view.trigger('email-recipients:toggled');

            expect(view.resizeEditor).toHaveBeenCalledOnce();
        });
    });

    describe('_resizeEditor method present in Tinymce plugin', function() {
        var layout;
        var $layout;
        var $editor;
        var otherHeight = 50;

        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Emails', 'create', {}, null, false);

            var mockHtml = '<div><div class="drawer active"><div class="main-pane span8">' +
                '<div class="headerpane"></div>' +
                '<div class="record">' +
                '<div class="record-edit-link" data-type="htmleditable_tinymce"></div>' +
                '<div class="mce-stack-layout">' +
                '<div class="mce-stack-layout-item">' +
                '<iframe frameborder="0"></iframe>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="show-hide-toggle"></div>' +
                '</div></div></div>';

            var layoutHeight = view.MIN_TINYMCE_EDITOR_HEIGHT + 500;
            var editorHeight = layoutHeight - otherHeight - view.TINYMCE_EDITOR_RESIZE_PADDING;

            view.$el = $(mockHtml);
            view.layout = layout;
            $layout = view.$('.main-pane');
            view.layout.$el = $layout;
            $layout.height(layoutHeight);
            $editor = view.$('.mce-stack-layout .mce-stack-layout-item iframe');
            $editor.height(editorHeight);

            view.$('.headerpane').height(otherHeight);
            view.$('.record').height(editorHeight);
            view.$('.show-hide-toggle').height(otherHeight);
            view.trigger('render');
        });

        it('should increase the height of the editor when layout height increases', function() {
            var layoutHeightBefore = $layout.height();
            var editorHeightBefore = $editor.height();

            //increase layout height by 100 pixels
            $layout.height(layoutHeightBefore + 100);

            $(window).trigger('resize');

            //editor should be increased to fill the space
            expect($editor.height()).toEqual(editorHeightBefore + 100);
        });

        it('should decrease the height of the editor when layout height decreases', function() {
            var layoutHeightBefore = $layout.height();
            var editorHeightBefore = $editor.height();

            //decrease layout height by 100 pixels
            $layout.height(layoutHeightBefore - 100);

            $(window).trigger('resize');
            //editor should be decreased to account for decreased layout height
            expect($editor.height()).toEqual(editorHeightBefore - 100);
        });

        it('should ensure that editor maintains minimum height when layout shrinks beyond that', function() {
            //decrease layout height to 50 pixels below min editor height
            $layout.height(view.MIN_TINYMCE_EDITOR_HEIGHT - 50);

            $(window).trigger('resize');
            //editor should maintain min height
            expect($editor.height()).toEqual(view.MIN_TINYMCE_EDITOR_HEIGHT);
        });

        it('should resize editor to fill empty drawer space but with a padding to prevent scrolling', function() {
            var editorHeightBefore = $editor.height();
            var editorHeightPlusPadding = editorHeightBefore + view.TINYMCE_EDITOR_RESIZE_PADDING;

            //add the resize padding on
            $editor.height(editorHeightPlusPadding);
            view.$('.record').height(editorHeightPlusPadding);

            //padding should be added back
            $(window).trigger('resize');
            expect($editor.height()).toEqual(editorHeightBefore);
        });
    });
});
