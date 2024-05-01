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
describe('Base.View.ReportDashletFilterView', function() {
    var app;
    var context;
    var meta;
    var view;
    var viewName = 'report-dashlet-filter';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', viewName);

        context = new app.Context();
        context.set({
            model: new Backbone.Model({
                label: 'testLabel',
                sortOrder: 'asc',
                reportType: 'detailed_summary',
                displayColumns: ['status', 'priority'],
                filtersDef: {
                    'Filter_1': {
                        name: 'test',
                        type: 'test',
                        runtime: 1,
                    },
                },
            }),
            module: 'Home',
        });

        meta = {
            config: false
        };

        view = SugarTest.createView('base', null, viewName, {}, context, true);

        app.drawer = {
            $: function() {
                return {
                    empty: sinon.stub(),
                    append: sinon.stub(),
                };
            },
        };
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();

        app = null;
        view = null;
    });

    describe('_initProperties()', function() {
        it('should properly setup default properties', function() {
            view._initProperties();

            expect(view._runtimeFilters).toEqual({});
            expect(view._filtersDef).toEqual(false);
        });
    });

    describe('_getRuntimeFilters()', function() {
        it('should properly return only the runtime filters', function() {
            const filters = view.model.get('filtersDef');
            const runtimeFilters = view._getRuntimeFilters(view._getValidFilters(filters), []);

            expect(runtimeFilters).toEqual([{
                name: 'test',
                type: 'test',
                runtime: 1,
            }]);
        });
    });

    describe('_tryBuildFilters()', function() {
        it('should properly build filter controllers', function() {
            expect(Object.keys(view._runtimeFilters).length).toEqual(0);

            view._tryBuildFilters({});

            expect(Object.keys(view._runtimeFilters).length).toEqual(1);
        });
    });

    describe('_disposeFilters()', function() {
        it('should properly dispose the filter controllers', function() {
            view._tryBuildFilters({});
            expect(Object.keys(view._runtimeFilters).length).toEqual(1);

            view._disposeFilters();
            expect(Object.keys(view._runtimeFilters).length).toEqual(0);
        });
    });
});
