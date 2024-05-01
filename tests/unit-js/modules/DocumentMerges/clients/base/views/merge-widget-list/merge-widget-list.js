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
describe('Base.View.MergeWidgetList', function() {
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

        SugarTest.loadHandlebarsTemplate('merge-widget-list', 'view', 'base', null, 'DocumentMerges');

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

        context = app.context.getContext();
        context.set({
            module: null,
            layout: parentLayout
        });
        context.prepare();

        view = SugarTest.createView('base', 'DocumentMerges', 'merge-widget-list', null, context, true, parentLayout);
    });

    afterEach(function() {
        parentLayout.dispose();
        view.dispose();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sinonSandbox.restore();
    });

    describe('render', function() {
        it('should display merges', function() {
            view.merges = [{
                name: 'Merge 1',
                parent_id: '123',
                parent_name: 'Parent Record',
                parent_type: 'Accounts'
            }];
            view.render();
            expect(view.$el.find('.merge-row').length).toBe(1);
        });
    });

    describe('loadData', function() {
        it('should load merge data', function() {
            view.merges = [{
                name: 'Merge 1',
                parent_id: '123',
                parent_name: 'Parent Record',
                parent_type: 'Accounts'
            }];
            view.render();
            expect(view.$el.find('.merge-row').length).toBe(1);
        });
    });
});
