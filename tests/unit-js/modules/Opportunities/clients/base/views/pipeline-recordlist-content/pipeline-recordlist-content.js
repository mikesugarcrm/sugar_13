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

describe('Opportunities.Base.Views.PipelineRecordlistContent', function() {
    var view;
    var app;
    var viewMeta;
    var viewModeStub;

    beforeEach(function() {
        app = SUGAR.App;
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
        SugarTest.loadComponent('base', 'view', 'pipeline-recordlist-content');
        view = SugarTest.createView(
            'base',
            'Opportunities',
            'pipeline-recordlist-content',
            viewMeta,
            context,
            true
        );

        sinon.stub(view.context, 'on').callsFake(function() {});
        //sinon.stub(view, '_super').callsFake(function() {});
        viewModeStub = sinon.stub(app.metadata, 'getModule').withArgs('Opportunities', 'config').returns({
            opps_view_by: 'RevenueLineItems'
        });
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('saveModel', function() {
        var model;
        var ui;
        var pipelineData;

        beforeEach(function() {
            view.headerField = 'testHeader';
            ui = {
                item: 'test'
            };
            model = app.data.createBean('Opportunities');
            pipelineData = {
                ui: ui,
                oldCollection: 'oldCollection',
                newCollection: 'newCollection'
            };

            sinon.stub(model, 'set').callsFake(function() {});
            sinon.stub(model, 'save').callsFake(function() {});
            sinon.stub(view, '_getSideDrawer');
        });

        describe('when pipeline_type is date_closed', function() {
            it('should set date_closed to the last day of the month selected', function() {
                sinon.stub(jQuery.fn, 'parent').callsFake(function() {
                    return {
                        attr: function() {
                            return 'May 2019';
                        }
                    };
                });

                sinon.stub(app.user, 'getPreference')
                    .withArgs('datepref').returns('YYYY-MM-DD');

                sinon.stub(app.date, 'getUserDateFormat')
                    .returns('YYYY-MM-DD');

                sinon.stub(view.context, 'get').callsFake(function() {
                    return {
                        get: function() {
                            return 'date_closed';
                        }
                    };
                });
                view.saveModel(model, pipelineData);
                expect(model.set).toHaveBeenCalledWith('date_closed', '2019-05-31');
                expect(model.set).toHaveBeenCalledWith('date_closed_cascade', '2019-05-31');
            });
        });

        describe('when pipeline_type is sales_stage', function() {
            it('should set probability, commit stage, and sales_stage_cascade', function() {
                view.headerField = 'sales_stage';
                sinon.stub(jQuery.fn, 'parent').callsFake(function() {
                    return {
                        attr: function() {
                            return 'Qualification';
                        }
                    };
                });
                sinon.stub(view.context, 'get').callsFake(function() {
                    return {
                        get: function() {
                            return 'sales_stage';
                        }
                    };
                });
                sinon.stub(app.utils, 'getProbabilityBySalesStage').callsFake(function() {
                    return '10%';
                });
                sinon.stub(app.utils, 'getCommitStageBySalesStage').callsFake(function() {
                    return 'test_stage';
                });
                view.saveModel(model, pipelineData);
                expect(model.set).toHaveBeenCalledWith('sales_stage', 'Qualification');
                expect(model.set).toHaveBeenCalledWith({
                    probability: '10%',
                    commit_stage: 'test_stage',
                    sales_stage_cascade: 'Qualification'
                });
            });
        });

        describe('when pipeline_type is a dropdown field in Opportunities', function() {
            it('should set the field to the column value', function() {
                var status = 'In Progress';
                sinon.stub(jQuery.fn, 'parent').callsFake(function() {
                    return {
                        attr: function() {
                            return status;
                        }
                    };
                });

                view.saveModel(model, pipelineData);
                expect(model.set).toHaveBeenCalledWith(view.headerField, status);
            });
        });

        describe('when certain readonly values are being changed', function() {
            using('different values', [
                {
                    headerField: 'date_closed',
                    attr: {'sales_status': 'Closed Won'},
                    mode: 'RevenueLineItems',
                    shouldSave: false
                },
                {
                    headerField: 'date_closed',
                    attr: {'sales_status': 'Closed Lost'},
                    mode: 'RevenueLineItems',
                    shouldSave: false
                },
                {
                    headerField: 'date_closed',
                    attr: {'sales_status': 'In Progress'},
                    mode: 'RevenueLineItems',
                    shouldSave: true
                },
                {
                    headerField: 'date_closed',
                    attr: {'sales_status': 'Closed Won'},
                    mode: 'Opportunities',
                    shouldSave: true
                },
                {
                    headerField: 'date_closed',
                    attr: {'sales_status': 'Closed Won'},
                    mode: 'Opportunities',
                    shouldSave: true
                },
                {
                    headerField: 'sales_stage',
                    attr: {'sales_status': 'Closed Won'},
                    mode: 'RevenueLineItems',
                    shouldSave: false
                },
                {
                    headerField: 'sales_stage',
                    attr: {'sales_status': 'Closed Won'},
                    mode: 'Opportunities',
                    shouldSave: true
                },
            ], function(data) {
                it('should not move the tile', function() {
                    viewModeStub.withArgs('Opportunities', 'config').returns({
                        opps_view_by: data.mode
                    });
                    var postChangeStub = sinon.stub(view, '_postChange');
                    var superStub = sinon.stub(view, '_super').callsFake(function() {});
                    var fieldDef = {name: data.headerField, vname: 'LBL_FIELD'};
                    sinon.stub(app.metadata, 'getField').returns(fieldDef);
                    sinon.stub(app.lang, 'get');
                    view.headerField = data.headerField;
                    var model = app.data.createBean('Opportunities', data.attr);
                    view.saveModel(model, pipelineData);

                    if (data.shouldSave) {
                        expect(postChangeStub).not.toHaveBeenCalled();
                        expect(superStub).toHaveBeenCalled();
                    } else {
                        expect(postChangeStub).toHaveBeenCalledWith(model, true, pipelineData);
                    }
                });
            });
        });
    });
});
