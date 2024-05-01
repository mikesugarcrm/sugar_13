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
describe('Base.Views.DashletFilterWidget', function() {
    var app;
    var view;
    var viewName = 'dashlet-filter-widget';
    var context;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        sinon.stub(app.controller, 'context').value(context);

        initOptions = {
            type: viewName,
            name: viewName,
            def: {
                view: viewName
            },
            module: 'Home',
            context: context,
            meta: {},
            filterData: {
                table_key: 'self',
                name: 'date_entered',
            },
            fullTableList: {
                'self': {
                    module: 'Accounts',
                },
            },
            reportId: 'test-report-id',
        };
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
        initOptions = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);
            view.initialize(initOptions);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(view._highlighted).toBeFalsy();
            expect(view._checked).toBeFalsy();
            expect(view._available).toBeTruthy();
            expect(view._targetModule).toEqual('Accounts');

            expect(view._filterData).toEqual({
                table_key: 'self',
                name: 'date_entered',
            });

            expect(view._fullTableList).toEqual({
                'self': {
                    module: 'Accounts',
                },
            });
        });
    });

    describe('getFilterData', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);
            view.initialize(initOptions);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will return filterData properly', function() {
            expect(view.getFilterData()).toEqual({
                table_key: 'self',
                name: 'date_entered',
            });
        });
    });

    describe('manageWidgetState', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);

            view.initialize(initOptions);
            view.manageWidgetState({
                field: {
                    type: 'datetime',
                },
            });
        });

        afterEach(function() {
            view.dispose();
        });

        it('will not alter states', function() {
            expect(view._highlighted).toBeFalsy();
            expect(view._checked).toBeFalsy();
            expect(view._available).toBeTruthy();
        });
    });

    describe('toggleHighlight', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);

            view.initialize(initOptions);
            view.toggleHighlight(true);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will be highlighted', function() {
            expect(view._highlighted).toEqual(true);
            expect(view._checked).toEqual(false);
            expect(view._available).toEqual(false);
        });
    });

    describe('toggleChecked()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);

            view.initialize(initOptions);
            view.toggleChecked(true);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will be checked', function() {
            expect(view._highlighted).toEqual(false);
            expect(view._checked).toEqual(true);
            expect(view._available).toEqual(false);
        });
    });

    describe('toggleAvailable', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);

            view.initialize(initOptions);
            view.toggleAvailable(true);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will be available', function() {
            expect(view._highlighted).toEqual(false);
            expect(view._checked).toEqual(false);
            expect(view._available).toEqual(true);
        });
    });

    describe('_getTargetModule', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                options: initOptions,
            }, context);

            view.initialize(initOptions);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will have the correct target module', function() {
            expect(view._getTargetModule()).toEqual('Accounts');
        });
    });
});
