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
describe('Base.Views.DashletFilterMode', function() {
    var app;
    var view;
    var viewName = 'dashlet-filter-mode';
    var context;
    var model;
    var filterGroups;
    var activeFilterGroupId;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', 'dashlet-filter-widget');

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        model = app.data.createBean('Accounts');
        model.set({
            filtersDef: {
                    0: {
                        name: 'date_closed',
                        table_key: 'self',
                        qualifier_name: 'tp_next_7_days',
                        input_name0: 'tp_next_7_days',
                        input_name1: 'on',
                        runtime: 1,
                    },
                    1: {
                        name: 'date_entered',
                        table_key: 'self',
                        qualifier_name: 'tp_next_14_days',
                        input_name0: 'tp_next_14_days',
                        input_name1: 'on',
                    },
                    operator: 'AND',
                },
        });

        context.set({
            module: 'Accounts',
            model: model,
        });

        sinon.stub(app.controller, 'context').value(context);

        filterGroups = {
            'de64443c-4f21-4125-b3a8-f204d3334a7a': {
                'label': 'New Filter',
                'fieldType': 'datetime',
                'fields': [
                    {
                        'dashletId': '7fa55b8c-afad-42a4-97ee-140f757269cd',
                        'reportId': 'ff5d5d0e-7905-11e9-a6cc-f218983a1c3e',
                        'fieldName': 'date_due',
                        'tableKey': 'self',
                    },
                    {
                        'dashletId': 'd8d6bd15-5f0e-4cfc-bbec-fb8e62801c89',
                        'reportId': 'efc189ce-7905-11e9-8d92-f218983a1c3e',
                        'fieldName': 'date_entered',
                        'tableKey': 'self',
                    },
                ],
                'filterDef': {
                    'operator': 'AND',
                },
            },
            'ge64443c-4f21-4125-b3a8-f204d3334a7a': {
                'label': 'New Filter',
                'fieldType': 'datetime',
                'fields': [
                    {
                        'dashletId': 'd8d6bd15-5f0e-4cfc-bbec-fb8e62801c89',
                        'reportId': 'efc189ce-7905-11e9-8d92-f218983a1c3e',
                        'fieldName': 'date_closed',
                        'tableKey': 'self',
                    },
                ],
                'filterDef': {
                    'operator': 'AND',
                },
            },
        };
        activeFilterGroupId = 'ge64443c-4f21-4125-b3a8-f204d3334a7a';
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
        model = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(view._widgets).toEqual([]);
            expect(view._runtimeFilters.length).toEqual(1);
            expect(view._noFilters).toEqual(false);
        });
    });

    describe('_getValidFilters', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will return only valid filters', function() {
            const allFilters = view.model.get('filtersDef');
            const validFilters = view._getValidFilters(allFilters);

            expect(Object.keys(allFilters).length).toEqual(3);
            expect(validFilters.length).toEqual(2);
        });
    });

    describe('_getRuntimeFilters', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will return only valid runtime filters', function() {
            const allFilters = view.model.get('filtersDef');
            const validFilters = view._getValidFilters(allFilters);
            const runtimeFilters = view._getRuntimeFilters(validFilters, []);

            expect(Object.keys(allFilters).length).toEqual(3);
            expect(validFilters.length).toEqual(2);
            expect(runtimeFilters.length).toEqual(1);
        });
    });

    describe('show', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will create widgets', function() {
            expect(view._widgets.length).toEqual(0);
            view.show();
            expect(view._widgets.length).toEqual(1);
        });
    });

    describe('hide', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will destroy widgets', function() {
            expect(view._widgets.length).toEqual(0);
            view.show();
            expect(view._widgets.length).toEqual(1);
            view.hide();
            expect(view._widgets.length).toEqual(0);
        });
    });

    describe('manageGroupWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set the correct state for each widget', function() {
            view.show();

            view.layout = {
                options: {
                    dashletMetaId: 'd8d6bd15-5f0e-4cfc-bbec-fb8e62801c89',
                },
            };

            view.manageGroupWidgets(filterGroups, activeFilterGroupId);

            delete view.layout;

            for (let index = 0; index < view._widgets.length; index++) {
                const _widget = view._widgets[index];
                expect(_widget._highlighted).toEqual(true);
                expect(_widget._checked).toEqual(false);
                expect(_widget._available).toEqual(false);
            }
        });
    });

    describe('resetWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set widgets as available', function() {
            view.show();
            view.resetWidgets();

            for (let index = 0; index < view._widgets.length; index++) {
                const _widget = view._widgets[index];

                expect(_widget._available).toEqual(true);
            }
        });
    });

    describe('highlightWidgets()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set widgets as available', function() {
            view.show();

            view.layout = {
                options: {
                    dashletMetaId: 'd8d6bd15-5f0e-4cfc-bbec-fb8e62801c89',
                },
            };

            view.highlightWidgets(filterGroups, activeFilterGroupId);

            delete view.layout;

            for (let index = 0; index < view._widgets.length; index++) {
                const _widget = view._widgets[index];
                expect(_widget._highlighted).toEqual(true);
            }
        });
    });

    describe('addCheckmarksToWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set widgets as checked', function() {
            view.show();
            view.addCheckmarksToWidgets(filterGroups, activeFilterGroupId);

            for (let index = 0; index < view._widgets.length; index++) {
                const _widget = view._widgets[index];
                expect(_widget._checked).toEqual(false);
            }
        });
    });

    describe('disableWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will disable all widgets', function() {
            view.show();
            view.disableWidgets(filterGroups, activeFilterGroupId);

            for (let index = 0; index < view._widgets.length; index++) {
                const _widget = view._widgets[index];
                expect(_widget._checked).toEqual(false);
                expect(_widget._highlighted).toEqual(false);
                expect(_widget._available).toEqual(true);
            }
        });
    });

    describe('_createWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will create widgets', function() {
            expect(view._widgets.length).toEqual(0);
            view._createWidgets();
            expect(view._widgets.length).toEqual(1);
        });
    });

    describe('_disposeWidgets', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, {
                model: model,
            }, context);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will destroy widgets', function() {
            expect(view._widgets.length).toEqual(0);
            view._createWidgets();
            expect(view._widgets.length).toEqual(1);
            view._disposeWidgets();
            expect(view._widgets.length).toEqual(0);
        });
    });
});
