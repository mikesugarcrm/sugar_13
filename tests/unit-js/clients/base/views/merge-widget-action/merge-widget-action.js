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
describe('Base.View.MergeWidgetAction', function() {
    var app;
    var sinonSandbox;
    var view;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'merge-widget', 'DocumentMerges');
        SugarTest.loadComponent('base', 'layout', 'help');

        SugarTest.loadComponent('base', 'view', 'merge-widget-header', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'merge-widget-list',  'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'helplet');
        SugarTest.loadComponent('base', 'view', 'merge-widget-action');

        SugarTest.loadHandlebarsTemplate('merge-widget-action', 'view', 'base');

        SugarTest.testMetadata.addLayoutDefinition('merge-widget', {
            'components': [
                {
                    view: 'merge-widget-header'
                },
                {
                    view: 'merge-widget-list',
                }
            ],
        }, 'DocumentMerges');

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', null, 'merge-widget-action');

        context = app.context.getContext();
        context.set({
            module: null,
            view: view
        });
        context.prepare();

    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sinonSandbox.restore();
        sinon.restore();

        view.dispose();

        app.cache.cutAll();
        app.view.reset();
    });

    describe('initialize', function() {
        var initMergeWidgetStub;

        beforeEach(function() {
            initMergeWidgetStub = sinonSandbox.stub(view, '_initializeWidgetLayout');
        });

        it('should set _isVisible to false', function() {
            view.initialize({context: context});
            expect(view._isVisible).toBe(false);
        });
    });

    describe('showWidget', function() {
        var toggleDocumentMergeWidgetStub;

        beforeEach(function() {
            toggleDocumentMergeWidgetStub = sinonSandbox.stub(view, 'toggleDocumentMergeWidget');
        });

        it('should show the widget', function() {
            view._isVisible = false;
            view._documentMergeWidgetLayout = app.view.createLayout({
                module: 'DocumentMerges',
                type: 'merge-widget',
                button: view.$el
            });
            view._documentMergeWidgetLayout.button = {
                popover: sinonSandbox.spy(),
            };

            var reloadStub = sinonSandbox.stub(view._documentMergeWidgetLayout, 'reload');

            view._initializeWidgetLayout();

            view.showWidget();
            expect(toggleDocumentMergeWidgetStub).toHaveBeenCalled();
        });
    });

    describe('_initializeWidgetLayout', function() {
        it('should create the document-merge layout', function() {
            view._documentMergeWidgetLayout = app.view.createLayout({
                module: 'DocumentMerges',
                type: 'merge-widget',
                button: view.$el
            });
            view._documentMergeWidgetLayout.button = {
                popover: sinonSandbox.spy(),
            };

            var reloadStub = sinonSandbox.stub(view._documentMergeWidgetLayout, 'reload');
            view._initializeWidgetLayout();
            expect(view._documentMergeWidgetLayout)
                .toEqual(jasmine.any(App.view.layouts.BaseDocumentMergesMergeWidgetLayout));
        });
    });
});
