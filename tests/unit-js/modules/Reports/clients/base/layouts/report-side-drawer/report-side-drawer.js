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
describe('Reports.Base.Layouts.ReportSideDrawer', function() {
    var app;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();
        app = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    it('initialize', function() {
        layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);

        expect(layout.enums).toEqual({});
    });

    it('refreshListChart', function() {
        layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);
        var stub = sinon.stub(layout, 'updateList').callsFake(function() {});

        expect(layout.context.get('dashConfig')).toBeUndefined();

        layout.context.trigger('report:side:drawer:list:refresh', 1, 2);
        expect(stub.calledOnce).toBe(true);

        expect(layout.context.get('dashConfig')).toBe(1);
        expect(layout.context.get('chartState')).toBe(2);
    });

    describe('loadData', function() {
        it('loadData -> enums', function() {
            layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);
            layout.context.set('enumsToFetch', ['test']);

            var stubLoadEnum = sinon.stub(layout, '_loadEnumOptions').callsFake(function(enumOptions) {});
            var stubUpdateList = sinon.stub(layout, 'updateList').callsFake(function() {});

            layout.loadData();

            expect(stubLoadEnum).toHaveBeenCalledOnce();
            expect(stubUpdateList).not.toHaveBeenCalledOnce();
        });

        it('loadData -> updateList', function() {
            layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);
            layout.context.set('enumsToFetch', []);

            var stubLoadEnum = sinon.stub(layout, '_loadEnumOptions').callsFake(function(enumOptions) {});
            var stubUpdateList = sinon.stub(layout, 'updateList').callsFake(function() {});

            layout.loadData();

            expect(stubLoadEnum).not.toHaveBeenCalledOnce();
            expect(stubUpdateList).toHaveBeenCalledOnce();
        });
    });

    describe('_loadEnumOptions', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);
            app = SugarTest.app;
        });

        afterEach(function() {
            app.api.enumOptions.restore();
        });

        it('loads enum options and updates enums', function() {
            sinon.stub(layout, 'updateList').callsFake(function() {});

            var enumsToFetch = [
                {table_key: 'accounts', name: 'industry'},
                {table_key: 'contacts', name: 'lead_source'},
            ];

            var reportDef = {
                full_table_list: {
                    accounts: {module: 'Accounts'},
                    contacts: {module: 'Contacts'},
                },
            };

            layout.context.set('reportData', reportDef);
            layout.enums = {};

            sinon.stub(app.api, 'enumOptions').callsFake(function(module, field, options) {
                if (options.success) {
                    if (module === 'Accounts' && field === 'industry') {
                        options.success('accounts:industry', {'1': 'Manufacturing', '2': 'Finance'});
                    } else if (module === 'Contacts' && field === 'lead_source') {
                        options.success('contacts:lead_source', {'3': 'Web', '4': 'Phone'});
                    }
                }
            });

            layout._loadEnumOptions(enumsToFetch, function() {
                expect(app.api.enumOptions).toHaveBeenCalledWith('Accounts', 'industry', {
                    success: sinon.match.func
                });
                expect(app.api.enumOptions).toHaveBeenCalledWith('Contacts', 'lead_source', {
                    success: sinon.match.func
                });

                expect(layout.enums).toEqual({
                    'accounts:industry': {'1': 'Manufacturing', '2': 'Finance'},
                    'contacts:lead_source': {'3': 'Web', '4': 'Phone'},
                });
            });
        });
    });

    describe('updateList', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Reports', 'report-side-drawer', {}, context, true);
            app = SugarTest.app;

            layout.context.set('chartModule', 'Accounts');
            layout.context.set('reportId', '12345');
            layout.context.set('reportData', {});
            layout.context.set('dashConfig', {});
            layout.enums = {};
            layout.context.set('useSavedFilters', false);
            layout.context.set('useCustomReportDef', false);
            layout.context.set('collection', app.data.createBeanCollection('Accounts'));
            layout.context.set('fields', ['field1', 'field2']);
            layout.context.set('mass_collection', null);
        });

        it('should update the list', function() {
            sinon.stub(app.api, 'buildURL').callsFake(function(module, action, params) { return 'mocked_url'; });
            sinon.stub(app.api, 'call').callsFake(function(method, url, data, callbacks) { callbacks.success({}); });
            sinon.stub(SUGAR.charts, 'buildFilter').callsFake(function(reportDef, params, enums) { return {};});

            var stubTrigger = sinon.stub(layout.context, 'trigger');

            layout.updateList();

            expect(stubTrigger).toHaveBeenCalledWith('refresh:count');
            expect(layout.context.get('collection').module).toBe('Accounts');
            expect(layout.context.get('collection').getOption('fields')).toEqual(['field1', 'field2']);

            app.api.buildURL.restore();
            app.api.call.restore();
        });
    });
});
