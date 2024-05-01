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
describe('Reports.Base.Views.ReportAdvancedFilters', function() {
    var app;
    var view;
    var context;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;

        let reportData = new Backbone.Model();

        reportData.set('filtersDef', {
            Filter_1: {
                operator: 'OR',
                0: {
                    type: 'text',
                },
                1: {
                    type: 'enuim',
                },
            },
        });

        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.set('reportData', reportData);
        context.prepare();
        sinonSandbox = sinon.createSandbox();

        sinon.stub(app.api, 'call').callsFake(function() {});

        view = SugarTest.createView('base', 'Reports', 'report-advanced-filters', {}, context, true);
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
        sinonSandbox.restore();
    });

    describe('_initProperties', function() {
        it('should set properties appropriately', function() {
            expect(view._flattenedFilters.length).toEqual(3);
        });
    });

    describe('_flattenFilters', function() {
        it('should properly make filters a one dimensional object', function() {
            view._flattenedFilters = {};

            view._flattenFilters(
                'TEST_ID',
                'TEST_TOOLTIP_ID',
                {
                    operator: 'OR',
                    0: {
                        type: 'text',
                    },
                    1: {
                        operator: 'AND',
                        0: {
                            type: 'text',
                        },
                        1: {
                            operator: 'enum',
                        },
                        2: {
                            operator: 'select',
                        },
                    },
                },
                view._flattenedFilters,
                0,
                0
            );

            expect(Object.keys(view._flattenedFilters).length).toEqual(6);
        });
    });
});
