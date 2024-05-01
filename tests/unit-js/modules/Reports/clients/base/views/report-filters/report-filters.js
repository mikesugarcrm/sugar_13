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
describe('Reports.Base.Views.ReportFilters', function() {
    var app;
    var view;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        sinon.stub(app.api, 'call').callsFake(function() {});

        view = SugarTest.createView('base', 'Reports', 'report-filters', {}, context, true);
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

    describe('_beforeInit', function() {
        it('should set properties appropriately', function() {
            view._beforeInit();

            expect(view._runtimeFilters).toEqual([]);

            expect(view._runtimeFiltersDef).toEqual({});
        });
    });

    describe('_getValidFilters', function() {
        it('should properly return valid filters', function() {
            const validFilters = view._getValidFilters({
                test: [{label: 'Test'}],
                test2: [{label: 'Test2'}],
                operator: 'AND',
            });

            expect(validFilters).toEqual([[{label: 'Test'}], [{label: 'Test2'}]]);
        });
    });

    describe('_getRuntimeFilters', function() {
        it('should properly return runtime filters', function() {
            const runtimeFilters = view._getRuntimeFilters([
                {label: 'Test'},
                {label: 'Test2', runtime: 1},
            ], []);

            expect(runtimeFilters.length).toEqual(1);
        });
    });

    describe('dispose', function() {
        it('should properly dispose elements', function() {
            view.dispose(false);

            expect(view._runtimeFilters).toEqual([]);
        });
    });
});
