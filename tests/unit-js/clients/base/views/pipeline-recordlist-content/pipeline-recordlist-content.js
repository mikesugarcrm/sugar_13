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

describe('Base.Views.PipelineRecordlistContent', function() {
    var view;
    var app;
    var context;
    var viewMeta;
    var pipelineChangeDataMock;

    beforeEach(function() {
        app = SugarTest.app;
        app.sideDrawer = {
            drawerConfigs: {
                left: '100px'
            },
            defaultDrawerConfigs: {
                left: '100px'
            },
            closingDuration: 300,
            $el: {
                toggleClass: sinon.stub()
            },
            isOpen: sinon.stub()
        };

        var context = new app.Context({
            module: 'Opportunities',
            model: app.data.createBean('Opportunities'),
            layout: 'pipeline-records'
        });
        viewMeta = {
            fields: {
                label: 'LBL_PIPELINE_TYPE',
                name: 'pipeline_type',
                type: 'pipeline-type'
            }
        };
        view = SugarTest.createView('base', 'Opportunities', 'pipeline-recordlist-content', viewMeta, context, false);
        sinon.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns(
            {
                table_header: {
                    Leads: 'date_closed',
                    Opportunities: 'status'
                },
                available_columns: {
                    Opportunities: {
                        status: {
                            Lost: 'Lost',
                            New: 'New'
                        }
                    }
                },
                header_colors: ['#FFFFFF', '#000000']
            }).withArgs(view.module, 'fields').returns(
                {
                    name: {
                        type: 'text',
                        name: 'name'
                    },
                    amount: {
                        type: 'currency',
                        name: 'amount'
                    },
                    sales_status: {
                        options: 'sales_status_dom'
                    },
                    status: {
                        options: 'status'
                    },
                    dupeTest: {
                        type: 'test'
                    },
                    test_id: {
                        name: 'test_id'
                    },
                    dupeTest_subfield: {
                        name: 'dupeTest_subField'
                    },
                    dupeTest_relatedField1: {
                        name: 'dupeTest_relatedField1'
                    },
                    dupeTest_relatedField2: {
                        name: 'dupeTest_relatedField2'
                    }
                }
            );

        pipelineChangeDataMock = {
            ui: {
                item: 'test'
            },
            oldCollection: 'oldCollection',
            newCollection: 'newCollection'
        };

        sinon.stub(view.context, 'on');
        sinon.stub(view, '_super');
        sinon.stub(app.metadata, 'getView').returns({});
    });

    afterEach(function() {
        sinon.restore();
        app.view.reset();
        view = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            view.initialize({context: context});
        });

        it('should set format for start date', function() {
            expect(view.startDate).toEqual(app.date().format('YYYY-MM-DD'));
        });

        it('should call app.metadata.getModule method with VisualPipeline and config', function() {
            expect(app.metadata.getModule).toHaveBeenCalledWith('VisualPipeline', 'config');
        });

        it('should initialize view.pipelineFilters with []', function() {
            expect(view.pipelineFilters).toEqual([]);
        });

        it('should initialize view.hiddenHeaderValues with []', function() {
            expect(view.hiddenHeaderValues).toEqual([]);
        });

        it('should initialize view.action as list', function() {
            expect(view.action).toEqual('list');
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'listenTo');
            sinon.stub(window, 'addEventListener');
        });

        it('should add Backbone listeners', function() {
            view.bindDataChange();
            expect(view.listenTo).toHaveBeenCalledWith(view.context, 'pipeline:recordlist:model:created');
            expect(view.listenTo).toHaveBeenCalledWith(view.context, 'pipeline:recordlist:filter:changed');
            expect(view.listenTo).toHaveBeenCalledWith(view.context, 'side-drawer:before:open');
            expect(view.listenTo).toHaveBeenCalledWith(view.context, 'side-drawer:start:close');
        });

        it('should add window listeners', function() {
            view.bindDataChange();
            expect(window.addEventListener).toHaveBeenCalledWith('resize', view.resizeContainerHandler);
        });
    });

    describe('buildTileMeta', function() {
        beforeEach(function() {
            view.meta.tileDef = {
                fields: {
                    label: 'LBL_PIPELINE_TYPE',
                    name: 'pipeline_type',
                    type: 'pipeline-type'
                },
                panels: [
                    {
                        is_header: true,
                        name: 'header',
                        fields: []
                    },
                    {
                        name: 'body',
                        fields: []
                    }
                ]
            };
            view.pipelineConfig = {
                tile_header: {
                    Opportunities: 'name'
                },
                tile_body_fields: {
                    Opportunities: ['amount']
                }
            };
            view.pipelineType = 'date_closed';
            view.buildTileMeta();
        });

        afterEach(function() {
            view.pipelineConfig = undefined;
            view.meta.tileDef = undefined;
        });

        it('should call app.metadata.getModule method with view.module and fields', function() {
            expect(app.metadata.getModule).toHaveBeenCalled();
        });

        it('should update fields in view.meta.tileDef', function() {
            expect(view.meta.tileDef.panels[0].fields).toEqual([{
                type: 'text',
                name: 'name',
                link: true
            }]);
            expect(view.meta.tileDef.panels[1].fields).toEqual([{
                type: 'currency',
                name: 'amount'
            }]);
        });
    });

    describe('setResultsPerPageColumn', function() {
        var resultsNum;
        beforeEach(function() {
            view.module = 'Leads';
        });
        describe('when records_per_column is a number', function() {
            it('should assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = undefined;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 20
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(20);
            });

            it('should assign resultsNum to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = undefined;
                resultsNum = 50;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 20
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(50);
            });
        });

        describe('when records_per_column is a not number', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 'test'
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(7);
            });
        });

        describe('when records_per_column is a numeric string', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: '15'
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(15);
            });
        });

        describe('when records_per_column is a not defined', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                view.pipelineConfig = {
                    records_per_column: 'test'
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(7);
            });
        });
    });

    describe('setHiddenHeaderValues', function() {
        var hiddenValues;
        beforeEach(function() {
            view.module = 'Opportunities';
        });
        describe('when view.pipelineConfig.hiddenValues is empty', function() {
            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = [];
                view.pipelineConfig = {
                    hidden_values: {
                        Tasks: []
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toBe(undefined);
            });

            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = [];
                view.pipelineConfig = {
                    hidden_values: {
                        Opportunities: []
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toBe(undefined);
            });
        });

        describe('when view.pipelineConfig.hiddenValues is not empty', function() {
            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = undefined;
                view.pipelineConfig = {
                    hidden_values: {
                        Cases: [],
                        Leads: [],
                        Opportunities: ['Closed Won', 'Closed Lost']
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toEqual(['Closed Won', 'Closed Lost']);
            });

            it('should not assign hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = ['Test1', 'Test2'];
                view.pipelineConfig = {
                    hidden_values: {
                        Cases: [],
                        Leads: [],
                        Opportunities: ['Closed Won', 'Closed Lost']
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toEqual(['Test1', 'Test2']);
            });
        });
    });

    describe('buildFilters', function() {
        var filterDef;
        beforeEach(function() {
            filterDef = ['test'];
            sinon.stub(view, 'loadData');
            view.buildFilters(filterDef);
        });

        it('should set view.offset to 0', function() {
            expect(view.offset).toBe(0);
        });

        it('should assign filterDef to view.pipelineFilters', function() {
            expect(view.pipelineFilters).toEqual(['test']);
        });

        it('should should call loadData method', function() {
            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('loadData', function() {
        beforeEach(function() {
            sinon.stub(view, 'buildTileMeta');
            sinon.stub(view, 'setResultsPerPageColumn');
            sinon.stub(view, 'setHiddenHeaderValues');
            sinon.stub(view, 'getTableHeader');
            sinon.stub(view, 'buildRecordsList');
        });

        it('should set view.recordsToDisplay as an empty array', function() {
            view.loadData();

            expect(view.recordsToDisplay).toEqual([]);
        });

        it('should call the view.buildTileMeta method', function() {
            view.loadData();

            expect(view.buildTileMeta).toHaveBeenCalled();
        });

        it('should call the view.setResultsPerPageColumn method', function() {
            view.loadData();

            expect(view.setResultsPerPageColumn).toHaveBeenCalled();
        });

        it('should call the view.setHiddenHeaderValues method', function() {
            view.loadData();

            expect(view.setHiddenHeaderValues).toHaveBeenCalled();
        });

        it('should call the view.getTableHeader method', function() {
            view.loadData();

            expect(view.getTableHeader).toHaveBeenCalled();
        });

        describe('when view.hasAccessToView is true', function() {
            it('should call the view.buildRecordsList method', function() {
                view.hasAccessToView = true;
                view.loadData();

                expect(view.buildRecordsList).toHaveBeenCalled();
            });
        });

        describe('when view.hasAccessToView is false', function() {
            it('should not call the view.buildRecordsList method', function() {
                view.hasAccessToView = false;
                view.loadData();

                expect(view.buildRecordsList).not.toHaveBeenCalled();
            });
        });
    });

    describe('_setRecordsToDisplay', function() {
        it('should populate view.recordsToDisplay', function() {
            var options = {
                Lost: 'Closed Lost',
                New: 'New'
            };
            view.pipelineType = 'status';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.recordsToDisplay = [];
            view._setRecordsToDisplay('status', options);

            expect(view.recordsToDisplay.length).toEqual(2);
        });
    });

    describe('getTableHeader', function() {
        var headerColors;
        beforeEach(function() {
            view.recordsToDisplay = [];
        });

        it('should update view.hasAccessToView', function() {
            view.module = 'Opportunities';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.getTableHeader();

            expect(view.hasAccessToView).toBeTruthy();
        });

        it('should call view._super with render', function() {
            view.module = 'Opportunities';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.getTableHeader();

            expect(view._super).toHaveBeenCalledWith('render');
        });

        describe('when pipeline_type is not date_closed', function() {
            beforeEach(function() {
                view.pipelineType = 'sales_status';
                view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            });

            it('should assign headerField to view.headerField', function() {
                view.getTableHeader();

                expect(view.headerField).toEqual('status');
            });

            describe('when app.acl.hasAccessToModel is false', function() {
                it('should call view.context.trigger to have been called with open:config:fired', function() {
                    sinon.stub(app.acl, 'hasAccessToModel').withArgs('read', view.model, 'status')
                        .returns(false);
                    sinon.stub(view.context, 'trigger');
                    view.getTableHeader();

                    expect(view.context.trigger).toHaveBeenCalledWith('open:config:fired');
                });
            });

            describe('when app.acl.hasAccessToModel is true', function() {
                it('should not call view.context.trigger to have been called with open:config:fired', function() {
                    sinon.stub(app.acl, 'hasAccessToModel').withArgs('read', view.model, 'status')
                        .returns(true);
                    sinon.stub(view.context, 'trigger');
                    view.getTableHeader();

                    expect(view.context.trigger).not.toHaveBeenCalledWith('open:config:fired');
                });

                describe('when headerField is defined', function() {
                    beforeEach(function() {
                        view.recordsToDisplay = [];
                    });

                    describe('when optionList is defined', function() {
                        it('should call app.lang.getAppListStrings', function() {
                            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                                return {
                                    Lost: 'Closed Lost',
                                    New: 'New'
                                };
                            });
                            view.getTableHeader();

                            expect(app.lang.getAppListStrings).toHaveBeenCalled();
                        });
                    });

                    describe('when options is empty', function() {
                        it('should call enum api', function() {
                            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                                return [];
                            });
                            var apiStub = sinon.stub(app.api, 'enumOptions');
                            view.getTableHeader();

                            expect(apiStub).toHaveBeenCalled();
                        });
                    });
                });
            });
        });

        describe('when pipeline_type is date_closed', function() {
            beforeEach(function() {
                view.module = 'Leads';
                view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
                view.pipelineType = 'date_closed';
            });
            it('should set view.headerField as date_closed', function() {
                view.getTableHeader();

                expect(view.headerField).toEqual('date_closed');
            });

            it('should populate view.recordsToDisplay', function() {
                view.monthsToDisplay = 6;
                view.getTableHeader();

                expect(view.recordsToDisplay.length).toEqual(6);
            });

            it('should call app.date with view.startDate', function() {
                view.monthsToDisplay = 6;
                sinon.stub(app, 'date').callsFake(function() {
                    return {
                        add: sinon.stub(),
                        format: sinon.stub()
                    };
                });
                sinon.stub(view, 'formatStringID');
                view.getTableHeader();

                expect(app.date).toHaveBeenCalledWith(view.startDate);
            });
        });
    });

    describe('preRender', function() {
        it('should set the view.offset to 0', function() {
            view.offset = 10;
            view.preRender();

            expect(view.offset).toBe(0);
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.stub(view, 'preRender');
            sinon.stub(view, 'postRender');
            view.render();
        });

        it('should call preRender function', function() {

            expect(view.preRender).toHaveBeenCalled();
        });

        it('should call _super with render function', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call postRender function', function() {

            expect(view.postRender).toHaveBeenCalled();
        });
    });

    describe('postRender', function() {
        beforeEach(function() {
            sinon.stub(view, 'resizeContainer');
            sinon.stub(view, 'buildDraggable');
            sinon.stub(view, 'bindColumnScroll');
            sinon.stub(view, 'displayDownArrows');
            view.postRender();
        });

        it('should call resizeContainer function', function() {

            expect(view.resizeContainer).toHaveBeenCalled();
        });

        it('should call buildDraggable function', function() {

            expect(view.buildDraggable).toHaveBeenCalled();
        });

        it('should call bindColumnScroll function', function() {

            expect(view.bindColumnScroll).toHaveBeenCalled();
        });

        it('should call displayDownArrows function', function() {

            expect(view.displayDownArrows).toHaveBeenCalled();
        });
    });

    describe('addModelToCollection', function() {
        var collection;
        var literal;
        var model;
        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            literal = [];
            sinon.stub(view, 'addTileVisualIndicator').callsFake(function() {
                return [{
                    tileVisualIndicator: '#F0F0F0'
                }];
            });
            sinon.stub(view, 'postRender');
        });

        it('should add model to the column when visible', function() {
            sinon.stub(view, 'getColumnCollection').callsFake(function() {
                return {
                    color: '#FFF000',
                    headerKey: 'testKey',
                    headerName: 'testName',
                    records: {
                        models: [],
                        add: function() {
                            return model;
                        }
                    }
                };
            });

            collection = view.getColumnCollection();

            view.addModelToCollection(model);
            expect(view.getColumnCollection).toHaveBeenCalled();
            expect(view.addTileVisualIndicator).toHaveBeenCalled();
            expect(model.attributes.tileVisualIndicator).toEqual('#F0F0F0');
            expect(view._super).toHaveBeenCalledWith('render');
            expect(view.postRender).toHaveBeenCalled();
        });

        it('should not add model when the column header is not visible', function() {
            sinon.stub(view, 'getColumnCollection').callsFake(function() {
                return null;
            });

            collection = view.getColumnCollection();

            view.addModelToCollection(model);
            expect(view.getColumnCollection).toHaveBeenCalled();
            expect(view.addTileVisualIndicator).not.toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('render');
            expect(view.postRender).toHaveBeenCalled();
        });

    });

    describe('getColumnCollection', function() {
        var model;
        beforeEach(function() {
            model = app.data.createBean('Opportunities');
        });

        afterEach(function() {
            model = null;
        });

        describe('when pipeline_type is date_closed', function() {
            it('should check the pipeline-type of the model', function() {
                view.pipelineType = 'date_closed';
                sinon.stub(app, 'date').callsFake(function() {
                    return {
                        format: function() {}
                    };
                });
                view.getColumnCollection(model);

                expect(app.date).toHaveBeenCalled();
            });
        });
    });

    describe('buildRecordList', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show').callsFake($.noop);
            sinon.stub(view, 'getRecords');
            view.buildRecordsList();
        });

        it('should find the #loadingCell element and call show method on it', function() {
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call the view.getRecords method', function() {
            expect(view.getRecords).toHaveBeenCalled();
        });
    });

    describe('getFilters', function() {
        var filter;
        var column;
        beforeEach(function() {
            column = {
                color: '#36850F',
                headerKey: 'April 2019',
                headerName: 'April 2019',
                records: []
            };
            filter = [];
        });

        afterEach(function() {
            filter = null;
        });

        describe('when pipeline_type is not date_closed', function() {
            beforeEach(function() {
                sinon.stub(view.context, 'get').callsFake(function() {
                    return {
                        get: function() {
                            return 'testType';
                        }
                    };
                });
            });

            it('should set headerField object in filter', function() {
                view.headerField = 'sales_status';
                filter = view.getFilters(column);

                expect(filter[0].sales_status).toEqual({
                    '$equals': column.headerKey
                });
            });

            it('should add all the view.pipelineFilters to filter array', function() {
                view.headerField = 'date_closed';
                view.pipelineFilters = [
                    {
                        test_filter: {
                            $random_check: 'testFilter'
                        }
                    }
                ];
                filter = view.getFilters(column);

                expect(filter.length).toEqual(2);
                expect(filter[1].test_filter).toEqual({
                    $random_check: 'testFilter'
                });
            });
        });

        describe('when pipeline_type is date_closed', function() {
            beforeEach(function() {
                view.pipelineType = 'date_closed';
                view.headerField = 'date_closed';
            });

            it('should set the start and end dates in filter', function() {
                filter = view.getFilters(column);

                expect(filter[0].date_closed).toEqual({
                    '$dateBetween': [
                        app.date(column.headerName, 'MMMM YYYY').startOf('month').format('YYYY-MM-DD'),
                        app.date(column.headerName, 'MMMM YYYY').endOf('month').format('YYYY-MM-DD')
                    ]
                });
            });

            it('should add all the view.pipelineFilters to filter array', function() {
                view.pipelineFilters = [
                    {
                        sales_status: {
                            $not_empty: ''
                        }
                    },
                    {
                        test_filter: {
                            $random_check: 'testFilter'
                        }
                    }
                ];
                filter = view.getFilters(column);

                expect(filter.length).toEqual(3);
                expect(filter[2].test_filter).toEqual({
                    $random_check: 'testFilter'
                });
            });
        });
    });

    describe('getFieldsForFetch', function() {
        beforeEach(function() {
            view.meta.tileDef = {
                panels: [
                    {
                        is_header: true,
                        name: 'header',
                        fields: [{
                            name: 'name'
                        }]
                    },
                    {
                        name: 'body',
                        fields: [
                            {
                                name: 'amount'
                            },
                            {
                                name: 'account_name'
                            },
                            {
                                name: 'sales_status'
                            },
                            {
                                name: 'dupeTest'
                            },
                            {
                                name: 'dupeTest',
                                id_name: 'test_id',
                                fields: [
                                    {
                                        name: 'dupeTest_subfield'
                                    }
                                ],
                                related_fields: [
                                    'dupeTest_relatedField1',
                                    'dupeTest_relatedField2'
                                ]
                            }
                        ]
                    }
                ]
            };

            view.tileVisualIndicatorFields = {
                Leads: 'status',
                Opportunities: 'date_closed'
            };
        });

        it('should call app.metadata.getModule method', function() {
            view.getFieldsForFetch();
            expect(app.metadata.getModule).toHaveBeenCalled();
        });

        it('should reject the invalid field names from fields array', function() {
            let fields = view.getFieldsForFetch();
            expect(fields).not.toContain('account_name');
        });

        it('should include the id field names from relate-type fields', function() {
            let fields = view.getFieldsForFetch();
            expect(fields).toContain('test_id');
        });

        it('should include sub-field names', function() {
            let fields = view.getFieldsForFetch();
            expect(fields).toContain('dupeTest_subfield');
        });

        it('should include related field names', function() {
            let fields = view.getFieldsForFetch();
            expect(fields).toContain('dupeTest_relatedField1', 'dupeTest_relatedField2');
        });
    });

    describe('getRecords', function() {
        beforeEach(function() {
            sinon.stub(view, 'getFieldsForFetch');
            sinon.stub(view, 'buildRequests');
            sinon.stub(view, 'fetchData');

            view.getRecords();
        });

        it('should call getFieldsForFetch method', function() {

            expect(view.getFieldsForFetch).toHaveBeenCalled();
        });

        it('should call buildRequests method', function() {

            expect(view.buildRequests).toHaveBeenCalled();
        });

        it('should call fetchData method', function() {

            expect(view.fetchData).toHaveBeenCalled();
        });
    });

    describe('buildRequests', function() {
        var request;
        beforeEach(function() {
            request = {
                requests: []
            };

            view.recordsToDisplay = [
                {
                    color: '#FFF000',
                    headerKey: 'Test Key1',
                    headerName: 'Test name1',
                    records: []
                },
                {
                    color: '#000FFF',
                    headerKey: 'Test Key2',
                    headerName: 'Test name2',
                    records: []
                }
            ];

            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });
        });
        afterEach(function() {
            request = null;
        });

        it('should populate the request object', function() {
            request = view.buildRequests();

            expect(request.requests).toEqual(
                [
                    {
                        dataType: 'json',
                        method: 'GET',
                        url: 'testUrl'
                    },
                    {
                        dataType: 'json',
                        method: 'GET',
                        url: 'testUrl'
                    }
                ]);
        });
    });

    describe('fetchData', function() {
        var url;

        beforeEach(function() {
            url = 'testUrl';

            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });
            sinon.stub(app.api, 'call');
            view.fetchData('testRequest');
        });

        afterEach(function() {
            url = null;
        });

        it('should set view.modeData to false', function() {

            expect(view.moreData).toBe(false);
        });

        it('should call app.api.buildURL', function() {

            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call with create, url and requests', function() {

            expect(app.api.call).toHaveBeenCalledWith('create', url, 'testRequest');
        });
    });

    describe('resizeContainer', function() {
        let jqueryStub;

        beforeEach(function() {
            jqueryStub = {
                height: sinon.stub(),
                scrollLeft: sinon.stub(),
                closest: sinon.stub().returns(
                    {
                        innerWidth: sinon.stub(),
                        width: sinon.stub(),
                        get: sinon.stub().returns(
                            {
                                scrollWidth: 1,
                            }
                        ),
                        toggleClass: sinon.stub(),
                        height: sinon.stub(),
                    }
                ),
                length: sinon.stub(),
                css: sinon.stub(),
                innerWidth: sinon.stub(),
                width: sinon.stub(),
                children: sinon.stub().returns(
                    {
                        hide: sinon.stub(),
                        show: sinon.stub(),
                    }
                ),
                find: sinon.stub().returns(
                    {
                        hide: sinon.stub(),
                        show: sinon.stub(),
                        width: sinon.stub(),
                        height: sinon.stub().returns(
                            {
                                show: sinon.stub(),
                            }
                        ),
                    }
                ),
            };
            sinon.stub(window, '$').returns(jqueryStub);
            sinon.stub(_, 'first').returns(
                {
                    scrollWidth: 1,
                }
            );
        });

        afterEach(function() {
            jqueryStub = {};
        });

        it('should call displayDownArrows function', function() {
            sinon.stub(view, 'displayDownArrows');

            view.resizeContainer();
            expect(view.displayDownArrows).toHaveBeenCalled();
        });

        it('should set scrollLeft value for \'.my-pipeline-content\'', function() {
            jqueryStub.scrollLeft = sinon.stub().returns(1234);
            view.resizeContainer();

            expect(jqueryStub.scrollLeft).toHaveBeenCalledWith(1234);
        });
    });

    describe('buildDraggable', function() {
        var addClassStub;
        var findStub;
        var sortableStub;
        beforeEach(function() {
            addClassStub = sinon.stub();
            findStub = sinon.stub();
            sortableStub = sinon.stub();

            sinon.stub(view, '$').callsFake(function() {
                return {
                    sortable: sortableStub,
                    addClass: function() {
                        return {
                            find: function() {
                                return {
                                    addClass: addClassStub
                                };
                            }
                        };
                    }
                };
            });
        });

        describe('when app.acl.hasAccessTOModel is false', function() {
            beforeEach(function() {
                view.headerField = 'date_closed';
            });

            it('should not call view.$.sortable method', function() {
                sinon.stub(app.acl, 'hasAccessToModel').withArgs('edit', view.model)
                    .returns(false);
                view.buildDraggable();

                expect(sortableStub).not.toHaveBeenCalled();
            });
        });

        describe('when app.acl.hasAccessTOModel is true', function() {
            beforeEach(function() {
                view.headerField = 'date_closed';
            });
            it('should not call view.$.sortable method', function() {
                sinon.stub(app.acl, 'hasAccessToModel').withArgs('edit', view.model)
                    .returns(true);
                view.buildDraggable();

                expect(sortableStub).toHaveBeenCalled();
            });
        });
    });

    describe('switchCollection', function() {
        var oldCollection;
        var newCollection;
        var model;
        beforeEach(function() {
            model = {
                cid: 'testCid2'
            };

            oldCollection = {
                color: '#000000',
                headerKey: 'April 2019',
                headerName: 'April 2019',
                records: {
                    models: [
                        {
                            cid: 'testCid1'
                        },
                        {
                            cid: 'testCid2'
                        },
                        {
                            cid: 'testCid3'
                        }
                    ],

                    remove: sinon.stub()
                }
            };

            newCollection = {
                color: '#FFFFFF',
                headerKey: 'May 2019',
                headerName: 'May 2019',
                records: {
                    models: [
                        {
                            cid: 'testCid4'
                        },
                        {
                            cid: 'testCid5'
                        },
                        {
                            cid: 'testCid6'
                        }
                    ],

                    add: sinon.stub()
                }
            };

            view.switchCollection(oldCollection, model, newCollection);
        });

        it('should remove the model from oldCollection', function() {

            expect(oldCollection.records.remove).toHaveBeenCalled();
        });

        it('should add the model into newCollection', function() {

            expect(newCollection.records.add).toHaveBeenCalled();
        });
    });

    describe('saveModel', function() {
        var model;
        var senderMock;
        var sideDrawer;

        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            senderMock = {
                parent: function() {
                    return {
                        attr: function() {
                            return 'testColumn';
                        }
                    };
                },
                sortable: function() {}
            };
            sideDrawer = {
                showComponent: () => null,
                hide: () => null
            };

            view.headerField = 'testHeader';

            sinon.stub(view, '$').callsFake(function() {
                return senderMock;
            });
            sinon.stub(model, 'set');
            sinon.stub(model, 'save');
            sinon.stub(view, '_getSideDrawer').returns(sideDrawer);
        });

        it('should set view.headerField for the model', function() {
            view.saveModel(model, pipelineChangeDataMock);
            expect(model.set).toHaveBeenCalledWith('testHeader', 'testColumn');
        });

        it('should validate the record in the side drawer', function() {
            sinon.stub(sideDrawer, 'showComponent');
            view.saveModel(model, pipelineChangeDataMock);
            expect(sideDrawer.showComponent).toHaveBeenCalled();
        });

        describe('when validation fails', function() {
            beforeEach(function() {
                sinon.stub(sideDrawer, 'showComponent').callsFake(function(def) {
                    def.context.validationCallback(false);
                });
                sinon.stub(view, '_handleValidationResults');
            });

            it('should handle the validation failure', function() {
                view.saveModel(model, pipelineChangeDataMock);
                expect(view._handleValidationResults).toHaveBeenCalled();
            });
        });

        describe('when validation succeeds', function() {
            beforeEach(function() {
                sinon.stub(view, '_callWithTileModel');
                view.meta.tileDef = {
                    panels: [
                        {
                            is_header: true,
                            name: 'header',
                            fields: [{
                                name: 'name'
                            }]
                        },
                        {
                            name: 'body',
                            fields: [
                                {
                                    name: 'amount'
                                },
                                {
                                    name: 'account_name'
                                },
                                {
                                    name: 'sales_status'
                                },
                                {
                                    name: 'dupeTest'
                                },
                                {
                                    name: 'dupeTest'
                                }
                            ]
                        }
                    ]
                };
            });

            it('should signal that changes should be reverted if saving fails', function() {
                sinon.stub(sideDrawer, 'showComponent').callsFake(function(def) {
                    def.context.saveCallback(false);
                });
                view.saveModel(model, pipelineChangeDataMock);
                expect(view._callWithTileModel).toHaveBeenCalledWith(
                    model,
                    '_postChange',
                    [true, pipelineChangeDataMock]
                );
            });

            it('should signal that changes should not be reverted if saving succeeds', function() {
                sinon.stub(sideDrawer, 'showComponent').callsFake(function(def) {
                    def.context.saveCallback(true);
                });
                view.saveModel(model, pipelineChangeDataMock);
                expect(view._callWithTileModel).toHaveBeenCalledWith(
                    model,
                    '_postChange',
                    [false, pipelineChangeDataMock]
                );
            });
        });
    });

    describe('_revertChanges', function() {
        var model;
        var senderMock;
        var oldValues;
        var newValues;

        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            senderMock = {
                parent: function() {
                    return {
                        attr: function() {
                            return 'testColumn';
                        }
                    };
                },
                sortable: function() {}
            };
            oldValues = {
                attr1: 'old1',
                attr2: 'old2',
                attr3: 'old3'
            };
            newValues = {
                attr1: 'new1',
                attr2: 'new2',
                attr3: 'new3'
            };

            model.set(newValues);
            model.oldValues = oldValues;

            sinon.stub(view, '$').callsFake(function() {
                return senderMock;
            });
            sinon.stub(view, 'switchCollection');
            sinon.stub(senderMock, 'sortable');
        });

        it('should set the old fields on the model', function() {
            expect(model.get('attr1')).toEqual('new1');
            expect(model.get('attr2')).toEqual('new2');
            expect(model.get('attr3')).toEqual('new3');

            view._revertChanges(model, pipelineChangeDataMock);

            expect(model.get('attr1')).toEqual('old1');
            expect(model.get('attr2')).toEqual('old2');
            expect(model.get('attr3')).toEqual('old3');
        });

        it('should switch the model back to the old collection', function() {
            view._revertChanges(model, pipelineChangeDataMock);
            expect(view.switchCollection).toHaveBeenCalledWith('newCollection', model, 'oldCollection');
        });

        it('should cancel the UI change and place the record back in the original column', function() {
            view._revertChanges(model, pipelineChangeDataMock);
            expect(senderMock.sortable).toHaveBeenCalledWith('cancel');
        });
    });

    describe('_handleValidationResults', function() {
        var model;

        beforeEach(function() {
            model = {
                set: function() {}
            };
            app.drawer = {
                open: function(def, onClose) {},
                close: function(saved) {}
            };
        });

        afterEach(function() {
            delete app.drawer;
        });

        describe('when validation fails', function() {
            beforeEach(function() {
                sinon.stub(app.drawer, 'close');
            });

            it('should open the app drawer to fix the fields that failed validation', function() {
                sinon.stub(app.drawer, 'open');
                view._handleValidationResults(false, model, pipelineChangeDataMock);
                expect(app.drawer.open).toHaveBeenCalled();
            });

            describe('when the record is edited and saved in the resulting drawer', function() {
                beforeEach(function() {
                    sinon.stub(app.drawer, 'open').callsFake(function(def, onClose) {
                        def.context.saveCallback(true);
                    });
                });

                it('should close the drawer and indicate the record change was saved', function() {
                    view._handleValidationResults(false, model, pipelineChangeDataMock);
                    expect(app.drawer.close).toHaveBeenCalledWith(true);
                });
            });

            describe('when the record view drawer is cancelled', function() {
                beforeEach(function() {
                    sinon.stub(app.drawer, 'open').callsFake(function(def, onClose) {
                        def.context.cancelCallback();
                    });
                });

                it('should close the drawer and indicate the record change was not saved', function() {
                    view._handleValidationResults(false, model, pipelineChangeDataMock);
                    expect(app.drawer.close).toHaveBeenCalledWith(false);
                });
            });
        });

        describe('when validation succeeds', function() {
            it('should not open the app drawer', function() {
                sinon.stub(app.drawer, 'close');
                view._handleValidationResults(true, model, pipelineChangeDataMock);
                expect(app.drawer.open).not.toHaveBeenCalled();
            });
        });
    });

    describe('_postChange', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            app.router = {
                navigate: function() {}
            };

            sinon.stub(view, '_revertChanges');
            sinon.stub(model, 'setSyncedAttributes');
            sinon.stub(view, 'postRender');
            sinon.stub(view, '$').returns({
                sortable: function() {}
            });
            sinon.stub(view.$('.column'), 'sortable');

            sinon.stub(view, 'getColumnCollection').callsFake(function() {
                return app.data.createBeanCollection('Opportunities');
            });
        });

        afterEach(function() {
            delete app.router;
        });

        it('should revert changes if needed', function() {
            view._postChange(model, true, pipelineChangeDataMock);
            expect(view._revertChanges).toHaveBeenCalled();
        });

        it('should not revert changes if not needed', function() {
            view._postChange(model, false, pipelineChangeDataMock);
            expect(view._revertChanges).not.toHaveBeenCalled();
        });

        it('should sync the new attributes', function() {
            view._postChange(model, false, pipelineChangeDataMock);
            expect(model.setSyncedAttributes).toHaveBeenCalledWith(model.attributes);
        });

        it('should re-enable the pipeline view drag/drop functionality', function() {
            view._postChange(model, false, pipelineChangeDataMock);
            expect(view.$('.column').sortable).toHaveBeenCalledWith('enable');
        });
    });

    describe('bindScroll', function() {
        it('should bind scroll to the .my-pipeline-content element', function() {
            sinon.stub(view, 'listScrolled');
            sinon.stub(view.$el, 'on');
            view.bindScroll();

            expect(view.$el.on).toHaveBeenCalledWith('scroll');
        });
    });

    describe('addTileVisualIndicator', function() {
        var modelsList;
        beforeEach(function() {
            view.tileVisualIndicator = {
                default: '#000000'
            };

            sinon.stub(view, 'addIndicatorBasedOnStatus');
            sinon.stub(view, 'addIndicatorBasedOnDate');
        });

        describe('when module is Cases', function() {
            it('should call view.addIndicatorBasedOnStatus with model', function() {
                modelsList = [
                    {
                        _module: 'Cases',
                        tileVisualIndicator: '#FFFFFF'
                    }
                ];
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnStatus).toHaveBeenCalledWith(modelsList[0]);
            });
        });

        describe('when module is Opportunities', function() {
            it('should call view.addIndicatorBasedOnDate with model and expectedCloseDate', function() {
                modelsList = [
                    {
                        _module: 'Opportunities',
                        tileVisualIndicator: '#FFFFFF',
                        date_closed: '2019-04-04'
                    }
                ];
                expectedCloseDate = app.date(modelsList[0].date_closed, 'YYYY-MM-DD');
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnDate).toHaveBeenCalledWith(modelsList[0], expectedCloseDate);
            });
        });

        describe('when module is Tasks', function() {
            it('should call view.addIndicatorBasedOnDate with model and expectedCloseDate', function() {
                modelsList = [
                    {
                        _module: 'Tasks',
                        tileVisualIndicator: '#FFFFFF',
                        date_due: '2019-04-04'
                    }
                ];
                dueDate = app.date.parseZone(modelsList[0].date_due);
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnDate).toHaveBeenCalledWith(modelsList[0], dueDate);
            });
        });

        describe('when module is not Opportunities/Cases/Tasks/Leads', function() {
            it('should assign tileVisualIndicator to view.tileVisualIndicator.default', function() {
                modelsList = [
                    {
                        _module: 'Accounts',
                        tileVisualIndicator: '#FFFFFF'
                    }
                ];
                view.addTileVisualIndicator(modelsList);

                expect(modelsList[0].tileVisualIndicator).toEqual('#000000');
            });
        });
    });

    describe('addIndicatorBasedOnDate', function() {
        var model;
        var date;
        beforeEach(function() {
            date = app.date('2019-04-04', 'YYYY-MM-DD');
            model = {
                tileVisualIndicator: '#000000'
            };
            view.tileVisualIndicator = {
                outOfDate: '#FFFFFF',
                inFuture: '#F0F0F0',
                nearFuture: '#000FFF'
            };
        });

        describe('when date is before now', function() {
            it('should set model.tileVisualIndicator to outOfDate', function() {
                sinon.stub(date, 'isBefore').callsFake(function() {
                    return true;
                });
                sinon.stub(date, 'isAfter').callsFake(function() {
                    return false;
                });
                sinon.stub(date, 'isBetween').callsFake(function() {
                    return false;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#FFFFFF');
            });
        });

        describe('when date is after now', function() {
            it('should set model.tileVisualIndicator to inFuture', function() {
                sinon.stub(date, 'isBefore').callsFake(function() {
                    return false;
                });
                sinon.stub(date, 'isAfter').callsFake(function() {
                    return true;
                });
                sinon.stub(date, 'isBetween').callsFake(function() {
                    return false;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#F0F0F0');
            });
        });

        describe('when date is between now and a month from now', function() {
            it('should set model.tileVisualIndicator to nearFuture', function() {
                sinon.stub(date, 'isBefore').callsFake(function() {
                    return false;
                });
                sinon.stub(date, 'isAfter').callsFake(function() {
                    return false;
                });
                sinon.stub(date, 'isBetween').callsFake(function() {
                    return true;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#000FFF');
            });
        });
    });

    describe('addIndicatorBasedOnStatus', function() {
        var inFuture;
        var outOfDate;
        var nearFuture;
        var model;
        beforeEach(function() {
            inFuture = ['New', 'Converted'];
            outOfDate = ['Dead', 'Closed', 'Rejected', 'Duplicate', 'Recycled'];
            nearFuture = ['Assigned', 'In Process', , 'Pending Input', ''];

            view.tileVisualIndicator = {
                outOfDate: '#FFFFFF',
                inFuture: '#F0F0F0',
                nearFuture: '#000FFF'
            };
        });

        describe('when model.status is in outOfDate', function() {
            it('should set model.tileVisualIndicator to outOfDate', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Duplicate'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#FFFFFF');
            });
        });

        describe('when model.status is in inFuture', function() {
            it('should set model.tileVisualIndicator to inFuture', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Converted'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#F0F0F0');
            });
        });

        describe('when model.status is in nearFuture', function() {
            it('should set model.tileVisualIndicator to nearFuture', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Assigned'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#000FFF');
            });

            describe('when model.status is empty', function() {
                it('should set model.tileVisualIndicator to nearFuture', function() {
                    model = {
                        tileVisualIndicator: '#000000',
                        status: ''
                    };
                    view.addIndicatorBasedOnStatus(model);

                    expect(model.tileVisualIndicator).toEqual('#000FFF');
                });
            });

            describe('when model.status is not defined', function() {
                it('should set model.tileVisualIndicator to nearFuture', function() {
                    model = {
                        tileVisualIndicator: '#000000'
                    };
                    view.addIndicatorBasedOnStatus(model);

                    expect(model.tileVisualIndicator).toEqual('#000FFF');
                });
            });
        });
    });

    describe('navigateLeft', function() {
        beforeEach(function() {
            sinon.stub(view, 'loadData');
            view.startDate = app.date('2019-04-04', 'YYYY-MM-DD');
            view.navigateLeft();
        });

        it('should set the startDate to 5 months earlier', function() {

            expect(view.startDate).toEqual('2018-11-04');
        });

        it('should set offset to 0', function() {

            expect(view.offset).toBe(0);
        });

        it('should call view.loadData method', function() {

            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('navigateRight', function() {
        beforeEach(function() {
            sinon.stub(view, 'loadData');
            view.startDate = app.date('2019-04-04', 'YYYY-MM-DD');
            view.navigateRight();
        });

        it('should set the startDate to 5 months later', function() {

            expect(view.startDate).toEqual('2019-09-04');
        });

        it('should set offset to 0', function() {

            expect(view.offset).toBe(0);
        });

        it('should call view.loadData method', function() {

            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        let $ul = $('<ul></ul>');

        beforeEach(function() {
            sinon.stub(view, '_toggleSideDrawerColumnFocusStyling');
            sinon.stub(view, 'stopListening');
            sinon.stub(view.$el, 'off');
            sinon.stub(view, '$').returns($ul);
            sinon.stub($ul, 'off');
            sinon.stub(window, 'removeEventListener');

            view._dispose();
        });

        it('should remove any temporary styling set on the side drawer', function() {
            expect(view._toggleSideDrawerColumnFocusStyling).toHaveBeenCalledWith(false);
        });

        it('should remove backbone listeners', function() {
            expect(view.stopListening).toHaveBeenCalled();
        });

        it('should remove window listeners', function() {
            expect(window.removeEventListener).toHaveBeenCalledWith('resize', view.resizeContainerHandler);
        });

        it('should remove jQuery listeners', function() {
            expect(view.$el.off).toHaveBeenCalledWith('scroll');
            expect($ul.off).toHaveBeenCalledWith('scroll');
        });

        it('should call view._super wtih _dispose', function() {
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });

    describe('handleBeforeSideDrawerOpens', function() {
        beforeEach(function() {
            sinon.stub(view, 'setCurrentColumn');
            sinon.stub(view, '_toggleSideDrawerColumnFocusStyling');

            sinon.stub(view.$el, 'closest').callsFake(function() {
                return {
                    index: sinon.stub(),
                    offset: function() {
                        return {
                            left: 123
                        };
                    },
                    closest: function() {
                        return {
                            index: sinon.stub()
                        };
                    },
                    scrollLeft: sinon.stub(),
                    width: sinon.stub(),
                    length: 1
                };
            });
        });

        it('should call the animate method', function() {
            view.handleBeforeSideDrawerOpens(view.$el);
            expect(view.setCurrentColumn).toHaveBeenCalled();
        });

        it('should apply special Tile View styling to the side drawer', function() {
            view.handleBeforeSideDrawerOpens(view.$el);
            expect(view._toggleSideDrawerColumnFocusStyling).toHaveBeenCalledWith(true);
        });
    });

    describe('handleSideDrawerCloses', function() {
        beforeEach(function() {
            sinon.stub(view, '_toggleSideDrawerColumnFocusStyling');
            sinon.stub(jQuery.fn, 'show');
            sinon.stub(jQuery.fn, 'animate').callsFake(function() {
                $('.table th, table td').show();
            });
        });

        it('should call the animate method', function() {
            view.handleSideDrawerCloses();
            expect(jQuery.fn.show).toHaveBeenCalled();
        });

        it('should remove any temporary side drawer styling applied', function() {
            view.handleSideDrawerCloses();
            expect(view._toggleSideDrawerColumnFocusStyling).toHaveBeenCalledWith(false);
        });
    });

    describe('listColumnScrolled', function() {
        let event;
        let caretUpJqueryStub;
        let caretDownJqueryStub;
        let columnBodyJqueryStub;
        let currentTargetJqueryStub;
        let firstElemTargetJqueryStub;

        beforeEach(function() {
            event = {};
            event.currentTarget = 'ul';
            caretUpJqueryStub = {toggleClass: sinon.stub()};
            caretDownJqueryStub = {toggleClass: sinon.stub()};
            columnBodyJqueryStub = {find: sinon.stub()};
            columnBodyJqueryStub.find.withArgs('.sicon-caret-up').returns(caretUpJqueryStub);
            columnBodyJqueryStub.find.withArgs('.sicon-caret-down').returns(caretDownJqueryStub);
            currentTargetJqueryStub = {
                closest: sinon.stub().withArgs('td').returns(columnBodyJqueryStub),
                scrollTop: sinon.stub().returns(0),
                outerHeight: sinon.stub().returns(0),
                attr: sinon.stub(),
            };
            firstElemTargetJqueryStub = {
                scrollHeight: 10,
            };
            sinon.stub(window, '$').returns(currentTargetJqueryStub);
            sinon.stub(_, 'first').returns(firstElemTargetJqueryStub);
            sinon.stub(view, 'getColumnRecords');
        });

        afterEach(function() {
            currentTargetJqueryStub.scrollTop = sinon.stub().returns(0);
            currentTargetJqueryStub.outerHeight = sinon.stub().returns(0);
        });

        it('should hide up arrow icon and display down arrow icon in column', function() {

            view.listColumnScrolled(event);

            expect(caretUpJqueryStub.toggleClass).toHaveBeenCalledWith('invisible', true);
            expect(caretDownJqueryStub.toggleClass).toHaveBeenCalledWith('invisible', false);
        });

        it('should display up arrow icon and hide down arrow icon in column', function() {
            currentTargetJqueryStub.scrollTop = sinon.stub().returns(10);

            view.listColumnScrolled(event);

            expect(caretUpJqueryStub.toggleClass).toHaveBeenCalledWith('invisible', false);
            expect(caretDownJqueryStub.toggleClass).toHaveBeenCalledWith('invisible', true);
        });

        it('getColumnRecords method is not called', function() {

            view.listColumnScrolled(event);

            expect(view.getColumnRecords).not.toHaveBeenCalled();
        });

        it('getColumnRecords method is called', function() {
            currentTargetJqueryStub.scrollTop = sinon.stub().returns(10);

            view.listColumnScrolled(event);

            expect(view.getColumnRecords).toHaveBeenCalled();
        });

        it('should block call the getColumnRecords method', function() {
            currentTargetJqueryStub.scrollTop = sinon.stub().returns(10);
            view._isFetchingColumn = true;

            view.listColumnScrolled(event);

            expect(view.getColumnRecords).not.toHaveBeenCalled();
        });
    });

    describe('displayDownArrows', function() {
        let caretDownJqueryStub;
        let columnBodyJqueryStub;
        let currentULJqueryStub;
        let firstElemInULJqueryStub;

        beforeEach(function() {
            caretDownJqueryStub = {removeClass: sinon.stub()};
            columnBodyJqueryStub = {find: sinon.stub()};
            columnBodyJqueryStub.find.withArgs('.sicon-caret-down').returns(caretDownJqueryStub);
            currentULJqueryStub = {
                closest: sinon.stub().withArgs('td').returns(columnBodyJqueryStub),
                scrollTop: sinon.stub().returns(0),
                outerHeight: sinon.stub().returns(0),
                attr: sinon.stub(),
            };
            firstElemInULJqueryStub = {
                scrollHeight: 10,
            };
            sinon.stub(view, '$').withArgs('ul').returns(['ul']);
            sinon.stub(window, '$').returns(currentULJqueryStub);
            sinon.stub(_, 'first').returns(firstElemInULJqueryStub);
            sinon.stub(view, 'getColumnRecords');
        });

        afterEach(function() {
            currentULJqueryStub.scrollTop = sinon.stub().returns(0);
            currentULJqueryStub.outerHeight = sinon.stub().returns(0);
        });

        it('should display down arrow icon in column and should not fetch more records', function() {
            view.displayDownArrows();

            expect(caretDownJqueryStub.removeClass).toHaveBeenCalledWith('invisible');
            expect(view.getColumnRecords).not.toHaveBeenCalled();
        });

        it('should not display down arrow icon in column', function() {
            currentULJqueryStub.scrollTop = sinon.stub().returns(10);

            view.displayDownArrows();

            expect(caretDownJqueryStub.removeClass).not.toHaveBeenCalled();
        });

        it('should fetch more records in column when there is more space', function() {
            currentULJqueryStub.scrollTop = sinon.stub().returns(10);
            firstElemInULJqueryStub.offset = 10;
            view.displayDownArrows();

            expect(view.getColumnRecords).toHaveBeenCalled();
        });
    });

    describe('sortData', function() {
        it('should check setting last state order for user and calling loadData', function() {
            sinon.stub(app.user.lastState, 'set');
            sinon.stub(view, 'loadData');

            view.sortData();

            expect(app.user.lastState.set).toHaveBeenCalledWith(view.orderByLastStateKey, view.orderBy);
            expect(view.offset).toBe(0);
            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('fetchColumnData', function() {
        let url;
        let requests;

        beforeEach(function() {
            url = 'testUrl';
            requests = 'testRequest';

            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });
            sinon.stub(app.api, 'call');
            view.fetchColumnData(requests);
        });

        afterEach(function() {
            url = null;
        });

        it('should call app.api.buildURL', function() {

            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call with create, url and requests', function() {

            expect(app.api.call).toHaveBeenCalledWith('create', url, {requests});
        });
    });

    describe('buildColumnRequests', function() {
        let request;
        let headerKey;
        let fields;

        beforeEach(function() {
            headerKey = 'Test Key1';
            fields = [];
            request = [];

            view.recordsToDisplay = [
                {
                    color: '#FFF000',
                    headerKey: 'Test Key1',
                    headerName: 'Test name1',
                    records: [],
                    offset: 10,
                },
                {
                    color: '#000FFF',
                    headerKey: 'Test Key2',
                    headerName: 'Test name2',
                    records: [],
                    offset: 10,
                },
                {
                    color: '#000FFF',
                    headerKey: 'Test Key3',
                    headerName: 'Test name1',
                    records: [],
                    offset: -1,
                },
            ];

            sinon.stub(view, 'getFilters');
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });
        });

        afterEach(function() {
            request = null;
        });

        it('should populate the request object', function() {
            request = view.buildColumnRequests(fields, headerKey);

            expect(request).toEqual([
                {
                    dataType: 'json',
                    method: 'GET',
                    url: 'testUrl'
                },
            ]);
        });

        it('should add the sort order if specified', function() {
            view.orderBy = {
                field: 'potato',
                direction: 'DESC'
            };

            request = view.buildColumnRequests(fields, headerKey);
            expect(app.api.buildURL).toHaveBeenCalledWith(view.module, null, null, jasmine.objectContaining({
                order_by: `potato:DESC`
            }));
        });
    });

    describe('getColumnRecords', function() {
        beforeEach(function() {
            sinon.stub(view, 'getFieldsForFetch');
            sinon.stub(view, 'fetchColumnData');
        });

        it('should call getFieldsForFetch method', function() {
            view.getColumnRecords();

            expect(view.getFieldsForFetch).toHaveBeenCalled();
        });

        it('should call buildColumnRequests method', function() {
            sinon.stub(view, 'buildColumnRequests').callsFake(function() {
                let requests = ['foo'];
                return requests;
            });

            view.getColumnRecords();

            expect(view.buildColumnRequests).toHaveBeenCalled();
        });

        it('should call fetchColumnData method if \'requests\' does not empty', function() {
            sinon.stub(view, 'buildColumnRequests').callsFake(function() {
                let requests = ['foo'];
                return requests;
            });

            view.getColumnRecords();

            expect(view.fetchColumnData).toHaveBeenCalled();
        });

        it('should not call fetchColumnData method if \'requests\' is empty', function() {
            sinon.stub(view, 'buildColumnRequests').callsFake(function() {
                let requests = [];
                return requests;
            });

            view.getColumnRecords();

            expect(view.fetchColumnData).not.toHaveBeenCalled();
            expect(view._isFetchingColumn).toBeFalsy();
        });
    });

    describe('bindColumnScroll', function() {
        it('should bind scroll to the \'ul\' element', function() {
            sinon.stub(view, 'listColumnScrolled');
            sinon.stub(jQuery.fn, 'on');
            view.bindColumnScroll();

            expect(jQuery.fn.on).toHaveBeenCalledWith('scroll');
        });
    });

    describe('displayColumnCount', function() {
        let url;

        beforeEach(function() {
            url = 'testUrl';
            sinon.stub(view, 'getFilters');
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return url;
            });
            sinon.stub(app.api, 'call');
            view.displayColumnCount(view, {});
        });

        afterEach(function() {
            url = null;
        });

        it('should call app.api.buildURL', function() {

            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call with read and url', function() {

            expect(app.api.call).toHaveBeenCalledWith('read', url);
        });
    });

    describe('_toggleSideDrawerColumnFocusStyling', function() {
        using('different boolean inputs', [true, false], function(input) {
            it('should toggle the pipeline class on the side drawer', function() {
                view._toggleSideDrawerColumnFocusStyling(input);
                expect(app.sideDrawer.$el.toggleClass).toHaveBeenCalledWith('pipeline', input);
            });
        });

        it('should set and restore side drawer styling configs when toggling pipeline mode', function() {
            view._toggleSideDrawerColumnFocusStyling(true);
            expect(app.sideDrawer.drawerConfigs.left).toEqual('21rem');
            view._toggleSideDrawerColumnFocusStyling(false);
            expect(app.sideDrawer.drawerConfigs.left).toEqual('100px');
        });
    });
});
