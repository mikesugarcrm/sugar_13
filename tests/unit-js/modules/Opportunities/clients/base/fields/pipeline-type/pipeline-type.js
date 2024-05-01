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

describe('Opportunities.Base.Fields.PipelineType', function() {
    var app;
    var context;
    var model;
    var moduleName;
    var field;
    var fieldDef;
    var config;
    var fields;
    var getModStub;

    beforeEach(function() {
        app = SugarTest.app;
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });
        context = new app.Context();
        context.set({model: model});

        fieldDef = {
            label: 'LBL_PIPELINE_TYPE',
            name: 'pipeline_type',
            type: 'pipeline-type'
        };

        config = {
            enabled_modules: ['Opportunities', 'Tasks'],
            is_setup: 1,
            records_per_column: 10,
            table_header: {Opportunities: 'sales_status', Tasks: 'status'},
            tile_header: {Opportunities: 'name', Tasks: 'name'}
        };
        fields = {
            date_closed: {
                vname: 'LBL_DATE_CLOSED'
            },
            sales_status: {
                vname: 'LBL_SALES_STATUS'
            }
        };

        sinon.stub(app.lang, 'get')
            .withArgs('LBL_PIPELINE_VIEW_TAB_NAME', 'Opportunities', {
                module: 'Opportunities',
                fieldName: 'Status'
            }).returns('Opportunities by Status')
            .withArgs('LBL_PIPELINE_VIEW_TAB_NAME', 'Opportunities', {
                module: 'Opportunities',
                fieldName: 'Time'
            }).returns('Opportunities by Time')
            .withArgs('LBL_MODULE_NAME', 'Opportunities')
            .returns('Opportunities');
        getModStub = sinon.stub(app.lang, 'getModString');

        sinon.stub(app.metadata, 'getModule').withArgs('VisualPipeline').returns(config)
            .withArgs('Opportunities').returns(fields);
        field = SugarTest.createField('base', 'pipeline-type', 'pipeline-type',
            'detail', fieldDef, 'Opportunities', model, context, true);
        sinon.stub(field.context, 'get').callsFake(function() {
            return {
                get: function() {
                    return 'testField';
                }
            };
        });
        field.getTabs();
    });

    afterEach(function() {
        sinon.restore();
        getModStub = null;
        app = null;
        context = null;
        model = null;
        field = null;
        fieldDef = null;
        moduleName = null;
    });

    describe('getTabs()', function() {
        var metaObject;

        beforeEach(function() {
            metaObject = [
                {
                    'headerLabel': 'Time',
                    'moduleField': 'date_closed',
                    'tabLabel': 'Opportunities by Time'
                },
                {
                    'headerLabel': 'Status',
                    'moduleField': 'sales_status',
                    'tabLabel': 'Opportunities by Status'
                }
            ];
            getModStub.withArgs('LBL_TIME', field.module).returns('Time');
            getModStub.withArgs('LBL_SALES_STATUS', field.module).returns('Status');
        });

        afterEach(function() {
            metaObject = null;
        });

        it('should push metaObject into field.tabs', function() {
            field.getTabs();
            expect(field.tabs).toEqual(metaObject);
        });
    });
});

