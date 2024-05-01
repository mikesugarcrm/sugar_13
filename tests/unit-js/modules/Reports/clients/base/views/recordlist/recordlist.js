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
describe('Reports.Base.Views.RecordList', function() {
    var app;
    var view;
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean('Reports');
        model.set({id: 'report_id', name: 'report_name'});
        view = SugarTest.createView('base', 'Reports', 'recordlist', null, null, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
        app.cache.cutAll();
        app.view.reset();
        app = null;
    });

    describe('scheduleReport()', function() {
        beforeEach(function() {
            app.drawer = {
                open: sinon.stub()
            };
        });
        afterEach(function() {
            sinon.restore();
            delete app.drawer;
        });
        it('should open report schedule create view with pre-populated report', function() {
            var drawerOptions;
            view.scheduleReport(model, null);
            drawerOptions = _.first(app.drawer.open.lastCall.args);
            expect(drawerOptions.context.model.get('report_id')).toEqual(model.get('id'));
            expect(drawerOptions.context.model.get('report_name')).toEqual(model.get('name'));
        });
    });

    describe('viewSchedules()', function() {
        var sandbox;
        beforeEach(function() {
            sandbox = sinon.createSandbox();
            sandbox.stub(app.controller, 'loadView');
        });
        afterEach(function() {
            sandbox.restore();
        });
        it('should open report schedule list view with report filter', function() {
            var filterOptions;
            view.viewSchedules(model, null);
            expect(app.controller.loadView).toHaveBeenCalledOnce();
            filterOptions = app.controller.loadView.firstCall.args[0].filterOptions;
            expect(filterOptions.filter_populate.report_id[0]).toEqual(model.get('id'));
            expect(filterOptions.initial_filter_label).toEqual(model.get('name'));
        });
    });
});
