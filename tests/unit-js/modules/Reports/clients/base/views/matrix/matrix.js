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
describe('Reports.Base.Views.ReportsMatrixView', function() {
    var app;
    var view;
    var context;
    var sinonSandbox;
    var model;
    var module = 'Reports';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('matrix', 'view', 'base', 'matrix', 'Reports');
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set('fields', []);
        model = app.data.createBean(module);
        context.set('model', model);
        context.set('module', module);

        context.set('previewMode', true);
        context.set('previewData', {
            tableData: {
                'header': [
                    [
                        'Quarter: Expected Close Date',
                        'Type',
                        'Grand Total'
                    ],
                    [
                        '',
                        'Existing Business',
                        'New Business'
                    ]
                ],
                'legend': [
                    'SUM: Best',
                    'AVG: Best'
                ],
                'layoutType': '2x2',
                'data': [
                    {
                        'Quarter: Expected Close Date': 'Q1 2022',
                        '': {
                            'SUM: Best': '<script>alert(1)</script>$22,837.78',
                            'AVG: Best': '<b>$2,076.16</b>',
                            'LBL_COUNT_LC': '11'
                        },
                        'Existing Business': {
                            'SUM: Best': '<a href="javascript:alert(42)">malicious link</a>$32,980.52',
                            'AVG: Best': '$2,355.75',
                            'LBL_COUNT_LC': '14'
                        },
                        'New Business': {
                            'SUM: Best': '$16,505.10',
                            'AVG: Best': '$2,357.87',
                            'LBL_COUNT_LC': '7'
                        },
                        'Total': {
                            'SUM: Best': '$72,323.40',
                            'AVG: Best': '$2,260.11',
                            'LBL_COUNT_LC': 32
                        }
                    },
                    {
                        'Quarter: Expected Close Date': 'Q2 2022',
                        '': {
                            'SUM: Best': '$2,289.46',
                            'AVG: Best': '$572.36',
                            'LBL_COUNT_LC': '4'
                        },
                        'Existing Business': {
                            'SUM: Best': '$8,966.82',
                            'AVG: Best': '$2,241.71',
                            'LBL_COUNT_LC': '4'
                        },
                        'New Business': {
                            'SUM: Best': '$10,440.32',
                            'AVG: Best': '$1,160.04',
                            'LBL_COUNT_LC': '9'
                        },
                        'Total': {
                            'SUM: Best': '$21,696.60',
                            'AVG: Best': '$1,276.27',
                            'LBL_COUNT_LC': 17
                        }
                    },
                    {
                        'Total': {
                            'SUM: Best': '$94,020.00',
                            'AVG: Best': '$1,918.78',
                            'LBL_COUNT_LC': 49
                        },
                        '': {
                            'SUM: Best': '$25,127.24',
                            'AVG: Best': '$1,675.15',
                            'LBL_COUNT_LC': 15
                        },
                        'Existing Business': {
                            'SUM: Best': '$41,947.34',
                            'AVG: Best': '$2,330.41',
                            'LBL_COUNT_LC': 18
                        },
                        'New Business': {
                            'SUM: Best': '$26,945.42',
                            'AVG: Best': '$1,684.09',
                            'LBL_COUNT_LC': 16
                        },
                        'Quarter: Expected Close Date': 'Grand Total'
                    }
                ]
            },
        });
        context.prepare();
        sinonSandbox = sinon.createSandbox();

        sinon.stub(app.api, 'call').callsFake(function() {});

        view = SugarTest.createView('base', module, 'matrix', {}, context, true, model, true);
        view.render();
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
        model = null;
        sinonSandbox.restore();
    });

    describe('_initProperties', function() {
        it('should set properties appropriately', function() {
            expect(Object.keys(view._matrixTypeMapping).length).toEqual(3);
        });
    });
    describe('Security', function() {
        it('should sanitize user-provided HTML', function() {
            expect(view.$('table').html().includes('<b>$2,076.16</b>')).toBe(true);
            expect(view.$('table').html().includes('alert(1)')).toBe(false);
            expect(view.$('table').html().includes('<a>malicious link</a>')).toBe(true);
        });
    });
});
