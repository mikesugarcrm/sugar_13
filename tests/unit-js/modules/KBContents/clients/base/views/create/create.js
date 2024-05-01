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

describe('modules.KBContents.clients.base.view.CreateView', function() {
    var app;
    var moduleName = 'KBContents';
    var viewName = 'create';
    var sinonSandbox;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBNotify',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadFile(
            '../include/javascript/sugar7/plugins',
            'Tinymce',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        view = SugarTest.createView('base', moduleName, viewName, null, null, true);
    });

    afterEach(function() {
        delete app.plugins.plugins.view.KBNotify;
        delete app.plugins.plugins.view.Tinymce;
        delete app.drawer;
        sinon.restore();
        sinonSandbox.restore();
        view.dispose();
        app.view.reset();
    });

    describe('initialize', function() {
        it('should add the required plugins', function() {
            sinon.stub(view, '_super');
            view.plugins = _.without(view.plugins, 'KBContent', 'KBNotify', 'Tinymce');

            view.initialize({});

            expect(view.plugins).toContain('KBContent');
            expect(view.plugins).toContain('KBNotify');
            expect(view.plugins).toContain('Tinymce');
            expect(view._super).toHaveBeenCalledWith('initialize', [{}]);
        });
    });

    it('Success created callback should trigger kb:collection:updated', function() {
        var callbackStub = sinonSandbox.stub();
        var eventCallbackStub = sinonSandbox.stub();
        view.on('kb:collection:updated', eventCallbackStub);

        var options = {'success': callbackStub};
        var customSaveOptions = view.getCustomSaveOptions(options);
        customSaveOptions.success();

        expect(callbackStub).toHaveBeenCalled();
        expect(eventCallbackStub).toHaveBeenCalled();
    });

    describe('setupEditorResize method present in Tinymce plugin', function() {
        beforeEach(function() {
            view.render();
            sinonSandbox.stub(view, 'resizeEditor');
        });

        it('should resize the editor when tinymce is initialized', function() {
            view.context.trigger('tinymce:oninit');

            expect(view.resizeEditor).toHaveBeenCalledOnce();
        });

        it('should resize the editor when toggling to show/hide hidden panel', function() {
            view.trigger('more-less:toggled');

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
