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
describe('Reports.Base.Views.ReportsReportTablePaginationView', function() {
    var app;
    var view;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        view = SugarTest.createView('base', 'Reports', 'report-table-pagination', {}, context, true);

        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        app = null;
        view.context = null;
        view.model = null;
        view = null;
    });

    describe('initialize', function() {
        var options;
        beforeEach(function() {
            options = {};

            view.initialize(options);
        });

        it('should call view._super method', function() {
            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });
    });

    describe('togglePaginationWidget', function() {
        it('should properly hide/show pagination buttons', function() {
            view.togglePaginationWidget(true);

            expect(view.$el.hasClass('hide')).toEqual(false);
        });
    });
});
