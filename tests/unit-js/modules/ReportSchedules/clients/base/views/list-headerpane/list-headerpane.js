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
describe('ReportSchedules.Base.Views.ListHeaderpane', function() {
    var app;
    var view;
    var context;
    var moduleName = 'ReportSchedules';
    var viewName = 'list-headerpane';

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = {
            open: sinon.stub()
        };
        context = app.context.getContext();
        context.set({
            currentFilterId: 'by_report',
            filterOptions: {
                initial_filter_label: 'My Report Name',
                initial_filter: 'by_report',
                filter_populate: {
                    report_id: ['My Report Id']
                }
            }
        });
        context.prepare();
        view = SugarTest.createView('base', moduleName, viewName, null, context, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;
        app = null;
    });

    describe('create()', function() {
        it('should pass current report to create view', function() {
            var drawerOptions;
            view.create();
            drawerOptions = _.first(app.drawer.open.lastCall.args);
            expect(drawerOptions.context.model.get('report_id')).toEqual('My Report Id');
            expect(drawerOptions.context.model.get('report_name')).toEqual('My Report Name');
        });
    });
});
