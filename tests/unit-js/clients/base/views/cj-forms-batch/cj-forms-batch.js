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
describe('Base.View.CJFormsBatchView', function() {
    let app;
    let view;
    let context;
    let moduleName = 'Accounts';
    let viewName = 'cj-forms-batch';
    let layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadPlugin('ToggleMoreLess');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: []
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();
        layout = SugarTest.createLayout('base', moduleName, 'dashboard');

        sinon.stub(app.CJBaseHelper, 'getBatchChunk').returns(2);

        view = SugarTest.createView('base', moduleName, viewName, {}, context, null, layout);
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();

        context = null;
        layout = null;
        view = null;
    });

    describe('getRecordsToUpdate', function() {
        it('should return chunk count records to update', function() {
            view.recordsToUpdate = [
                {
                    'id': 'Test-Id-1',
                    'module': 'Tasks',
                    'status': 'in_progress',
                },
                {
                    'id': 'Test-Id-2',
                    'module': 'Calls',
                    'status': 'not_applicable',
                },
                {
                    'id': 'Test-Id-3',
                    'module': 'Mettings',
                    'status': 'complete',
                },
                {
                    'id': 'Test-Id-4',
                    'module': 'Tasks',
                    'status': 'complete',
                },
            ];
            const recordsToUpdate = view.getRecordsToUpdate();

            expect(recordsToUpdate.length).toEqual(view.batchChunk);
        });
    });

    describe('startRecordSaving', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get');
            sinon.stub(view, '_getProgressView').returns({
                updateModal: sinon.stub()
            });
        });

        it('should call app.lang.get function', function() {
            view.startRecordSaving();

            expect(app.lang.get).toHaveBeenCalledWith('LBL_CJ_FORM_BATCH_ACTIVE_SMART_GUIDES_DETECTED');
        });

        it('should call _getProgressView function', function() {
            view.startRecordSaving();

            expect(view._getProgressView).toHaveBeenCalled();
        });

        it('should set batchProgressModel', function() {
            view.startRecordSaving();

            expect(view.batchProgressModel).not.toBeUndefined();
        });
    });

    describe('startBatchingProcess', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
            sinon.stub(app.lang, 'get');
            view.progressView = {
                updateModal: sinon.stub(),
            };
        });

        it('should call app.api.buildURL function', function() {
            view.startBatchingProcess({module: 'Accounts', record: 'test-id'});

            expect(app.api.buildURL).toHaveBeenCalledWith('Accounts', 'activitiesRSA', {id: 'test-id'});
        });

        it('should call app.lang.get function', function() {
            view.startBatchingProcess({module: 'Accounts', record: 'test-id'});

            expect(app.lang.get).toHaveBeenCalledWith('LBL_CJ_FORM_BATCH_PROCESSING_ACTIVE_SUGAR_ACTIONS');
        });

        it('should call _getProgressView function', function() {
            view.startBatchingProcess({module: 'Accounts', record: 'test-id'});

            expect(view.progressView.updateModal).toHaveBeenCalled();
        });

        it('should call app.api.call function', function() {
            view.startBatchingProcess();

            expect(app.api.call).not.toHaveBeenCalled();
        });
    });

    describe('getActivitiesRSASuccess', function() {
        const message = 'updating smart guide...';
        const data = {
            'Test-Id-1': {
                'module': 'Tasks',
                'status': 'in_progress',
            },
            'Test-Id-2': {
                'module': 'Calls',
                'status': 'not_applicable',
            },
        };

        beforeEach(function() {
            sinon.stub(view, 'updateRecords');
            sinon.stub(view, 'endBatchingProcess');
            sinon.stub(view, 'getRecordsToUpdate');
            sinon.stub(app.lang, 'get').returns(message);

            view.progressView = {
                reset: sinon.stub(),
                setTotalRecords: sinon.stub(),
                updateModal: sinon.stub()
            };
            view.batchProgressModel = new Backbone.Model();
        });

        it('should set total count', function() {
            view.getActivitiesRSASuccess(data);

            expect(view.totalCount).toEqual(Object.keys(data).length);
        });

        it('should call app.lang.get function', function() {
            view.getActivitiesRSASuccess(data);

            expect(app.lang.get).toHaveBeenCalledWith('LBL_CJ_FORM_BATCH_UPDATING_ACTIVE_SMART_GUIDES');
        });

        it('should call endBatchingProcess function', function() {
            view.getActivitiesRSASuccess();

            expect(view.endBatchingProcess).toHaveBeenCalled();
        });

        it('should call updateModal function of progress view', function() {
            view.getActivitiesRSASuccess(data);

            expect(view.progressView.updateModal).toHaveBeenCalledWith(message);
        });

        it('should call updateRecords function', function() {
            view.getActivitiesRSASuccess(data);

            expect(view.updateRecords).toHaveBeenCalled();
        });
    });

    describe('updateRecords', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
        });

        it('should call app.api.call function', function() {
            view.updateRecords([]);

            expect(app.api.call).toHaveBeenCalled();
        });

        it('should call app.api.buildURL function', function() {
            const data = [
                {
                    'id': 'Test-Id-1',
                    'module': 'Tasks',
                    'status': 'in_progress',
                },
                {
                    'id': 'Test-Id-2',
                    'module': 'Calls',
                    'status': 'not_applicable',
                },
            ];
            view.updateRecords(data);

            expect(app.api.buildURL).toHaveBeenCalledWith('CJ_Forms', 'performTargetActions');
        });
    });

    describe('performTargetActionsSuccess', function() {
        beforeEach(function() {
            sinon.stub(view, 'updateRecords');
            sinon.stub(view, 'getRecordsToUpdate');
            sinon.stub(view, 'endBatchingProcess');

            view.batchProgressModel = new Backbone.Model();
        });

        it('should call set errors array', function() {
            const response = {
                error: 'Something went wrong. Please contact your administrator.'
            };
            view.performTargetActionsSuccess(response);

            expect(_.first(view.errorsArray)).toEqual(response.error);
        });

        it('should call endBatchingProcess function', function() {
            view.performTargetActionsSuccess();

            expect(view.endBatchingProcess).toHaveBeenCalled();
        });

        it('should call updateRecords function', function() {
            view.totalCount = view.batchChunk + 1;
            view.performTargetActionsSuccess();

            expect(view.updateRecords).toHaveBeenCalled();
        });

        it('should call getRecordsToUpdate function', function() {
            view.totalCount = view.batchChunk + 1;
            view.performTargetActionsSuccess();

            expect(view.getRecordsToUpdate).toHaveBeenCalled();
        });
    });

    describe('endBatchingProcess', function() {
        it('should call showAlert method', function() {
            view.batchProgressModel = new Backbone.Model();
            sinon.stub(view, 'showAlert');
            view.endBatchingProcess();

            expect(view.showAlert).toHaveBeenCalled();
        });
    });

    describe('handleAlerts', function() {
        beforeEach(function() {
            sinon.stub(view, 'showAlert');
            sinon.stub(app.user, 'get').returns('admin');
        });

        it('should call showAlert method', function() {
            view.handleAlerts(true, false);

            expect(view.showAlert).toHaveBeenCalledWith('batching-success', 'success', 'LBL_RECORD_SAVED', false);
        });

        it('should call app.user.get method', function() {
            view.handleAlerts(false);

            expect(app.user.get).toHaveBeenCalledWith('type');
        });
    });

    describe('showAlert', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get');
            sinon.stub(app.alert, 'show');
        });

        it('should call app.alert.show method', function() {
            view.showAlert('record-success', 'success', 'LBL_RECORD_SAVED', true, 5000);

            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call app.lang.get method', function() {
            view.showAlert('record-success', 'success', 'LBL_RECORD_SAVED', true, 5000);

            expect(app.lang.get).toHaveBeenCalledWith('LBL_RECORD_SAVED');
        });
    });

    describe('_getProgressView', function() {
        it('should add progress view in layout _components', function() {
            const progressView = view._getProgressView();

            expect(_.last(view.layout._components)).toEqual(progressView);
        });

        it('should return progress view', function() {
            sinon.stub(view.layout.$el, 'append');
            const progressView = view._getProgressView();

            expect(view.layout.$el.append).toHaveBeenCalledWith(progressView.$el);
        });
    });
});
