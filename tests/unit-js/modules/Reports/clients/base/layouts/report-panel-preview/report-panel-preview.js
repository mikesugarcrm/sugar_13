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
describe('Reports.Base.Layouts.ReportPanelPreview', function() {
    var app;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', 'Reports', 'report-panel-preview', {}, context, true);
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

    describe('_initProperties', function() {
        it('should properly initialize properties', function() {
            layout.layout = {
                options: {
                    def: {
                        previewData: {
                            filtersData: 'filters-data',
                            chartData: 'chart-data',
                            tableData: 'table-data',
                            reportId: 'report-id',
                            reportType: 'summation',
                        }
                    }
                }
            };

            layout._initProperties();

            expect(layout._filtersData).toEqual('filters-data');
            expect(layout._chartData).toEqual('chart-data');
            expect(layout._tableData).toEqual('table-data');
            expect(layout._reportId).toEqual('report-id');

            layout.layout = undefined;
        });
    });

    describe('_initPanels', function() {
        it('should properly initialize panels', function() {
            var superStub = sinon.stub(layout, '_super');

            layout._initPanels();

            superStub.restore();

            expect(layout.model.dataFetched).toEqual(true);
        });
    });
});
