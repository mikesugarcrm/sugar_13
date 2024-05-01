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
describe('Reports.Base.Views.ReportPanelToolbar', function() {
    var app;
    var view;
    var context;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('report-panel-toolbar', 'view', 'base', 'report-panel-toolbar', 'Reports');
        SugarTest.loadComponent('base', 'view', 'report-panel-toolbar', 'Reports');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        app = SugarTest.app;

        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', 'Reports', 'base', {});

        view = SugarTest.createView('base', 'Reports', 'report-panel-toolbar', {}, context, true, layout, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        app = null;
        view.context = null;
        view.model = null;
        view = null;
    });

    describe('render', function() {
        it('should properly render detailed_summary toolbar', function() {
            view.model.set('report_type', 'detailed_summary');
            view.layout.type = 'report-table';
            view.render();
            expect(view.$('.toggleGrooups').length).toEqual(1);
        });
        it('should properly render summary toolbar', function() {
            view.model.set('report_type', 'summary');
            view.layout.type = 'report-table';
            view.render();
            expect(view.$('.reports-collection-count').length).toEqual(0);
        });
    });
});
