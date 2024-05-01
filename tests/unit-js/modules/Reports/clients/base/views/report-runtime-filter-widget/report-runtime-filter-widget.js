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
describe('Reports.Base.Views.ReportRuntimeFiltersWidget', function() {
    var app;
    var view;
    var context;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();
        sinonSandbox = sinon.createSandbox();

        sinon.stub(app.api, 'call').callsFake(function() {});

        view = SugarTest.createView('base', 'Reports', 'report-runtime-filter-widget', {}, context, true);
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

    describe('_beforeInit', function() {
        it('should set properties appropriately', function() {
            expect('runtime-filter-id').toEqual('runtime-filter-id');
            view._beforeInit({
                reportData: app.data.createBean('', {
                    fullTableList: {
                        self: {
                            module: 'Accounts',
                        },
                    },
                    users: {
                        a: 'a',
                        b: 'b',
                    },
                    operators: {},
                }),
                filterData: {
                    testFilter: true,
                    table_key: 'self',
                },
                runtimeFilterId: 'runtime-filter-id',
            });

            expect(view._filterData).toEqual({
                testFilter: true,
                table_key: 'self',
            });

            expect(view._runtimeFilterId).toEqual('runtime-filter-id');
            expect(view._users).toEqual({
                a: 'a',
                b: 'b',
            });
        });
    });

    describe('_initProperties', function() {
        it('should set properties appropriately', function() {
            sinonSandbox.stub(app.metadata, 'getField').callsFake(function() {
                return {type: 'name'};
            });

            view._beforeInit({
                reportData: app.data.createBean('', {
                    fullTableList: {
                        self: {
                            module: 'Accounts',
                        },
                    },
                    users: {
                        a: 'a',
                        b: 'b',
                    },
                    operators: {
                        name: {
                            'is': 'LBL_IS',
                        },
                    },
                }),
                filterData: {
                    qualifier_name: '',
                    testFilter: true,
                    table_key: 'self',
                    name: 'name',
                },
                runtimeFilterId: 'runtime-filter-id',
            });

            view._initProperties();

            expect(view._targetModule).toEqual('Accounts');

            expect(view._operators).toEqual({
                'is': 'LBL_IS',
            });
        });
    });

    describe('_updateFilterInput', function() {
        it('should set properties appropriately', function() {
            view._filterData = {
                qualifier_name: 'between',
                input_name0: 1,
                input_name1: 12,
            };

            view._targetField = {
                type: 'datetimecombo',
            };

            view._updateFilterInput();

            expect(view._inputType).toEqual('text-between');
            expect(view._inputValue).toEqual(1);
            expect(view._inputValue1).toEqual(12);
        });
    });
});
