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
describe('Base.View.MergeWidgetHeader', function() {
    var app;
    var sinonSandbox;
    var view;
    var context;
    var parentLayout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'merge-widget', 'DocumentMerges');
        SugarTest.loadComponent('base', 'layout', 'help');

        SugarTest.loadComponent('base', 'view', 'merge-widget-header', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'merge-widget-list', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'helplet');
        SugarTest.loadComponent('base', 'view', 'merge-widget-action');

        SugarTest.loadHandlebarsTemplate('merge-widget-header', 'view', 'base', null, 'DocumentMerges');

        SugarTest.testMetadata.addLayoutDefinition('merge-widget', {
            'components': [
                {
                    view: 'merge-widget-header'
                },
                {
                    view: 'merge-widget-list',
                }
            ],
        }, null);

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        parentLayout = SugarTest.createLayout('base', 'DocumentMerges', 'merge-widget', null, null, true);

        view = SugarTest.createView('base', 'DocumentMerges', 'merge-widget-header', null, null, true, parentLayout);

        context = app.context.getContext();
        context.set({
            module: null,
            view: view
        });
        context.prepare();

    });

    afterEach(function() {
        parentLayout.dispose();
        view.dispose();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sinonSandbox.restore();
    });

    describe('render', function() {
        it('should contain the close button', function() {
            view.render();
            expect(view.$el.find('.sicon.sicon-close.pull-right.feedback-close.document-merge').length).toBe(1);
        });
    });
});
