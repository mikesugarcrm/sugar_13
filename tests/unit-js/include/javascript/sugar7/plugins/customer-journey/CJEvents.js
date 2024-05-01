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
describe('Plugins.CJEvents', function() {
    let moduleName = 'Accounts';
    let view;
    let pluginsBefore;
    let app;
    let model;
    let children;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        model = app.data.createBean('Tasks');
        view = SugarTest.createView('base', moduleName, 'dri-workflow');
        pluginsBefore = view.plugins;
        view.plugins = ['CJEvents'];
        SugarTest.loadPlugin('CJEvents', 'customer-journey');
        SugarTest.app.plugins.attach(view, 'view');
        view.trigger('init');

        app.routing.start();
        sinon.stub(app.router, 'navigate');

        view.getFieldsToValidate = sinon.stub();
    });

    afterEach(function() {
        view.plugins = pluginsBefore;
        view.fields = null;
        view.dispose();
        model.dispose();
        app.view.reset();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        app.routing.stop();

        view = null;
    });

    describe('workflowInfoClicked', function() {
        beforeEach(function() {
            sinon.stub(view, 'toggleMoreLess');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should not call toggleMoreLess method and should return nothing', function() {
            sinon.stub(view, 'isCJRenderedAsTab').returns(true);

            expect(view.workflowInfoClicked()).toEqual(undefined);
            expect(view.toggleMoreLess).not.toHaveBeenCalled();
        });

        it('should not call toggleMoreLess method and actionBtnClicked should be false', function() {
            sinon.stub(view, 'isCJRenderedAsTab').returns(false);
            view.actionBtnClicked = true;
            view.workflowInfoClicked();

            expect(view.actionBtnClicked).toEqual(false);
            expect(view.toggleMoreLess).not.toHaveBeenCalled();
        });

        it('should call toggleMoreLess method', function() {
            view.hidePanel = true;
            let $el = {
                siblings: function() {
                    return {
                        removeClass: sinon.stub(),
                    };
                },
                removeClass: sinon.stub(),
            };
            sinon.stub(view, 'isCJRenderedAsTab').returns(false);
            sinon.stub(view, '$').returns($el);
            view.workflowInfoClicked({currentTarget: '.dri-workflow-info'});

            expect(view.toggleMoreLess).toHaveBeenCalledWith('more');
        });
    });

    describe('getNotCompletedChildrenCount', function() {
        let activity;

        beforeEach(function() {
            sinon.stub(view, 'getCompleteStatusList').returns({
                'Completed': 'Completed',
                'Not Applicable': 'Not Applicable',
            });
            const children = [
                {
                    id: 'Test-Id',
                    status: 'Not Started',
                },
                {
                    id: 'Test-Id-2',
                    status: 'In Progress',
                }
            ];
            activity = new Backbone.Model({'children': children});
        });

        it('should call getCompleteStatusList function', function() {
            view.getNotCompletedChildrenCount(activity);

            expect(view.getCompleteStatusList).toHaveBeenCalled();
        });

        it('should return non zero count function', function() {
            const count = view.getNotCompletedChildrenCount(activity);

            expect(count).toEqual(2);
        });
    });

    describe('startActivity', function() {
        it('Activity is started', function() {
            let url = 'www.sugar_url.com/DRI_Workflows/update-activity-state';
            sinon.stub(view, 'disablingJourneyAndStartLoading');
            sinon.stub(view, 'buildUrlActivityStatusUpdateApi').returns([url, 'test-data']);
            sinon.stub(view, 'callActivityStatusUpdateApi');
            model.module = 'Tasks';
            view.startActivity(model);
            expect(view.disablingJourneyAndStartLoading).toHaveBeenCalled();
            expect(view.buildUrlActivityStatusUpdateApi).toHaveBeenCalledWith(model, 'In Progress');
            expect(view.callActivityStatusUpdateApi).toHaveBeenCalledWith(url, model, 'test-data');
        });
    });

    describe('completeActivityClick', function() {
        it('when activity is parent', function() {
            model.cj_parent_activity_id = '123';
            sinon.stub(app.alert, 'show');
            sinon.stub(view, 'isParent').returns(true);
            view.completeActivityClick(model);
            expect(app.alert.show).toHaveBeenCalled();
            expect(view.isParent).toHaveBeenCalled();
        });
        it('Handles the completion of a given activity', function() {
            sinon.stub(view, 'handleForms');
            sinon.stub(view, 'showProcessingAlert');
            sinon.stub(view, 'disablingJourneyAndStartLoading');
            view.completeActivityClick(model, true);
            expect(view.handleForms).toHaveBeenCalled();
            expect(view.showProcessingAlert).toHaveBeenCalled();
            expect(view.disablingJourneyAndStartLoading).toHaveBeenCalled();
        });
    });

    describe('completeActivitySuccess', function() {
        it('Successfully update activity status', function() {
            model.set('dri_subworkflow_id', '0');
            view.stages = [
                {
                    model: {
                        module: 'Tasks',
                        get: sinon.stub().returns('not_started'),
                    },
                },
            ];
            view.completeQueue.length = 1;
            view.childActivityCount = 1;
            sinon.stub(view, 'completeActivityClick');
            sinon.stub(view, 'handleFormsForStage');
            sinon.stub(app.alert, 'show');
            view.completeActivitySuccess(model);
            expect(view.completeActivityClick).toHaveBeenCalled();
            expect(view.handleFormsForStage).toHaveBeenCalled();
            expect(app.alert.show).not.toHaveBeenCalled();
        });
    });

    describe('completeActivityError', function() {
        it('Show error alert because error occured while updating Activity Status', function() {
            let result = {
                message: 'Error occured while updating Activity Status'
            };
            sinon.stub(view, 'reloadData');
            alertStub = sinon.stub(app.alert, 'show');
            view.completeActivityError(result);
            expect(view.reloadData).toHaveBeenCalled();
            expect(alertStub.calledWith('error')).toBe(true);
        });
    });

    describe('notApplicableActivity', function() {
        it('It should update the activity and reload the data in the view', function() {
            model.set('cj_parent_activity_id', '123');
            model.module = 'Tasks';
            let url = 'www.sugar_url.com/DRI_Workflows/update-activity-state';
            sinon.stub(view, 'disablingJourneyAndStartLoading');
            sinon.stub(view, 'buildUrlActivityStatusUpdateApi').returns([url, 'test-data']);
            sinon.stub(view, 'callActivityStatusUpdateApi');
            view.notApplicableActivity(model);
            expect(view.disablingJourneyAndStartLoading).toHaveBeenCalled();
            expect(view.buildUrlActivityStatusUpdateApi).toHaveBeenCalledWith(model, 'Not Applicable');
            expect(view.callActivityStatusUpdateApi).toHaveBeenCalledWith(url, model, 'test-data');
        });
    });

    describe('childrenActivitiesNotApplicable', function() {
        it('It should update the sub activity and reload the data in the view', function() {
            model.set('dri_subworkflow_id', '0');
            model.id = '123';
            model.module = 'Tasks';
            sinon.stub(view, 'reloadData');
            sinon.stub(view, 'handleFormsForStage');
            sinon.stub(view, 'getActivitiesInfo');
            view.stages = [
                {
                    model: {
                        module: 'Tasks',
                        get: sinon.stub().returns('not_started'),
                    },
                },
            ];
            apiCallStub = sinon.stub(app.api, 'call').callsFake(function(requestType, url, params, record, callbacks) {
                    callbacks.success({isValid: true});
                }
            );
            view.childrenActivitiesNotApplicable(model);
            expect(apiCallStub).toHaveBeenCalled();
            expect(view.handleFormsForStage).toHaveBeenCalledWith(model, '0', true);
        });
    });

    describe('addTask', function() {
        it('It should add a task by calling the addActivity function', function() {
            sinon.stub(view, 'addActivity');
            view.addTask(model);
            expect(view.addActivity).toHaveBeenCalledWith(model, 'Tasks');
        });
    });

    describe('addMeeting', function() {
        it('It should add a meeting by calling the addActivity function', function() {
            sinon.stub(view, 'addActivity');
            view.addTask(model);
            expect(view.addActivity).toHaveBeenCalled();
        });
    });

    describe('addCall', function() {
        it('It should add a call by calling the addActivity function', function() {
            sinon.stub(view, 'addActivity');
            view.addTask(model);
            expect(view.addActivity).toHaveBeenCalled();
        });
    });

    describe('addSubTask', function() {
        it('It should add a subtask by calling the addSubActivity function', function() {
            sinon.stub(view, 'addSubActivity');
            view.addSubTask(model);
            expect(view.addSubActivity).toHaveBeenCalledWith(model, 'Tasks');
        });
    });

    describe('addSubMeeting', function() {
        it('It should add a submeeting by calling the addSubActivity function', function() {
            sinon.stub(view, 'addSubActivity');
            view.addSubMeeting(model);
            expect(view.addSubActivity).toHaveBeenCalledWith(model, 'Meetings');
        });
    });

    describe('addSubCall', function() {
        it('It should add a subcall by calling the addSubActivity function', function() {
            sinon.stub(view, 'addSubActivity');
            view.addSubCall(model);
            expect(view.addSubActivity).toHaveBeenCalledWith(model, 'Calls');
        });
    });

    describe('linkExistingTask', function() {
        it('It should add a task by calling the linkExistingActivity function', function() {
            sinon.stub(view, 'linkExistingActivity');
            let stage = app.data.createBean('DRI_SubWorkflows');
            view.linkExistingTask(stage);
            expect(view.linkExistingActivity).toHaveBeenCalledWith(stage, 'Tasks');
        });
    });

    describe('linkExistingMeeting', function() {
        it('It should add a meeting by calling the linkExistingActivity function', function() {
            sinon.stub(view, 'linkExistingActivity');
            let stage = app.data.createBean('DRI_SubWorkflows');
            view.linkExistingMeeting(stage);
            expect(view.linkExistingActivity).toHaveBeenCalledWith(stage, 'Meetings');
        });
    });

    describe('linkExistingCall', function() {
        it('It should add a call by calling the linkExistingActivity function', function() {
            sinon.stub(view, 'linkExistingActivity');
            let stage = app.data.createBean('DRI_SubWorkflows');
            view.linkExistingCall(stage);
            expect(view.linkExistingActivity).toHaveBeenCalledWith(stage, 'Calls');
        });
    });

    describe('previewActivityClicked', function() {
        it('Hide the child activities', function() {
            sinon.stub($.fn, 'addClass');
            sinon.stub($.fn, 'removeClass');
            sinon.stub(view, 'setActivityDisplayChildren');
            view.hideActivityChildren('123');
            expect($('.dri-activity-children[data-id="123"]').
                addClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-show-children').
                addClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-hide-children').
                addClass).toHaveBeenCalledWith('hide');
            expect(view.setActivityDisplayChildren).toHaveBeenCalledWith('123', 'less');
        });
    });

    describe('_getFieldsForDuplicateButton', function() {
        it('It should return the unique list of fields for duplicate button', function() {
            expect(view._getFieldsForDuplicateButton([
                {
                    type: 'relate',
                    name: 'field1'
                },
                {
                    type: 'relate',
                    name: 'field2'
                },
                {
                    type: 'link',
                    name: 'field3'
                },
            ])).toEqual([
                'field1',
                'field2'
            ]);
        });
    });

    describe('duplicateButton', function() {
        it('It should return the unique list of fields for duplicate button', function() {
            model.set('id', '123');
            model.module = 'Tasks';
            sinon.stub(model, 'setOption');
            sinon.stub(app.data, 'createBean').callsFake(function() {
                return model;
            });
            sinon.stub(model, 'fetch').callsFake(function(callbacks) {
                callbacks.success();
            });
            sinon.stub(view, '_getFieldsForDuplicateButton');
            sinon.stub(view, 'reFetchActivitySuccess');
            view.duplicateButton(model);
            expect(view._getFieldsForDuplicateButton).toHaveBeenCalled();
            expect(model.setOption).toHaveBeenCalled();
        });
    });

    describe('hideActivityChildrenClicked', function() {
        it('Called by hide button click and it should call the hideActivityChildren function', function() {
            sinon.stub($.fn, 'data').callsFake(function() {
                return '123';
            });
            sinon.stub(view, 'hideActivityChildren');
            view.hideActivityChildrenClicked({
                currentTarget: 'abc',
            });
            expect(view.hideActivityChildren).toHaveBeenCalledWith('123');
        });
    });

    describe('hideActivityChildren', function() {
        it('Hide the child activities', function() {
            sinon.stub($.fn, 'addClass');
            sinon.stub($.fn, 'removeClass');
            sinon.stub(view, 'setActivityDisplayChildren');
            view.hideActivityChildren('123');
            expect($('.dri-activity-children[data-id="123"]').
                addClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-show-children').
                addClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-hide-children').
                addClass).toHaveBeenCalledWith('hide');
            expect(view.setActivityDisplayChildren).toHaveBeenCalledWith('123', 'less');
        });
    });

    describe('showActivityChildrenClicked', function() {
        it('Called by show button click and it should call the showActivityChildren function', function() {
            sinon.stub($.fn, 'data').callsFake(function() {
                return '123';
            });
            sinon.stub(view, 'showActivityChildren');
            view.showActivityChildrenClicked({
                currentTarget: 'abc',
            });
            expect(view.showActivityChildren).toHaveBeenCalledWith('123');
        });
    });

    describe('showActivityChildren', function() {
        it('Show the child activities', function() {
            sinon.stub($.fn, 'addClass');
            sinon.stub($.fn, 'removeClass');
            sinon.stub(view, 'setActivityDisplayChildren');
            view.showActivityChildren('123');
            expect($('.dri-activity-children[data-id="123"]').
                removeClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-show-children').
                addClass).toHaveBeenCalledWith('hide');
            expect($('.dri-subworkflow-activity[data-id="123"] .dri-activity-hide-children').
                removeClass).toHaveBeenCalledWith('hide');
            expect(view.setActivityDisplayChildren).toHaveBeenCalledWith('123', 'more');
        });
    });

    describe('setActivityDisplayChildren', function() {
        it('Should set the cache key for the activity display children', function() {
            sinon.stub(view, 'getActivityDisplayChildrenCacheKey');
            sinon.stub(app.user.lastState, 'set');
            view.setActivityDisplayChildren('123', 'val');
            expect(view.getActivityDisplayChildrenCacheKey).toHaveBeenCalledWith('123');
        });
    });

    describe('alertForRequiredFieldDependency', function() {
        it('completingActivity should be false and alert show should have been called', function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            view.completingActivity = true;

            view.alertForRequiredFieldDependency({});

            expect(view.completingActivity).toBe(false);
            expect(app.alert.show).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('validateChildren', function() {
        it('Validating activity children', function() {
            view.childActivity = {
                childActivity: {
                    status: 'Not Applicable',
                    blocked_by: '123232',
                    blocked_by_stages: 'stages',
                },
            },
            children = {
                get: sinon.stub().returns(view.childActivity),
            },
            expect(view.validateChildren(children)).toBe(1);
        });
    });

    describe('blockByWarnings', function() {
        it('Show alert when their is only one blocked_by activity', function() {
            view.childActivity = {
                childActivity: {
                    status: 'Not Applicable',
                },
            },
            children = {
                get: sinon.stub().returns(view.childActivity),
            },
            view.count = 1;
            sinon.stub(view, 'getBlockedByActivityName');
            sinon.stub(app.alert, 'show');
            view.blockByWarnings(view.childActivity, view.count);
            expect(view.getBlockedByActivityName).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalled();
        });
        it('Show alert when their are multiple blocked_by activities', function() {
            view.childActivity = {
                childActivity: {
                    status: 'Not Applicable',
                },
            },
            children = {
                get: sinon.stub().returns(view.childActivity),
            },
            view.count = 2;
            sinon.stub(app.lang, 'get');
            sinon.stub(app.alert, 'show');
            view.blockByWarnings(view.childActivity, view.count);
            expect(app.lang.get).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('completeActivityClickConfirm', function() {
        it('when confirm to complete activity is selected', function() {
            children = [
                {
                    id: 'activity_id',
                    get: sinon.stub().returns(children),
                },
            ],
            view.activities = {
                'activity_id': {
                    id: '123',
                    name: 'Test',
                    get: sinon.stub().returns(children),
                },
            };

            sinon.stub(view, 'validateChildren').returns(0);
            sinon.stub(view, 'handleFormsForActivities');
            sinon.stub(view, 'showProcessingAlert');
            sinon.stub(view, 'disablingJourneyAndStartLoading');
            sinon.stub(view, 'getCompleteStatusList').returns({
                'Completed': 'Completed',
                'Not Applicable': 'Not Applicable',
            });
            view.completeActivityClickConfirm('activity_id');
            expect(view.handleFormsForActivities).toHaveBeenCalled();
            expect(view.validateChildren).toHaveBeenCalled();
            expect(view.showProcessingAlert).toHaveBeenCalled();
        });
    });

    describe('getBlockedByActivityName', function() {
        it('Provide blocked_by activity name', function() {
            view.childActivity = {
                childActivity: {
                    blocked_by: '1234',
                    blocked_by_stages: 'stages',
                    name: '1234',
                },
            },
            children = {
                get: sinon.stub().returns(view.childActivity),
            },
            expect(view.getBlockedByActivityName(children)).toBe('1234');
        });
    });

    describe('getActivitiesInfo', function() {
        using('input', [
            {
                activities: [
                    {
                        id: 'TestTaskID',
                        module: 'Tasks'
                    },
                    {
                        id: 'TestCallID',
                        module: 'Calls'
                    },
                ],
                currentActivity: {},
                result: [
                    {
                        id: 'TestTaskID',
                        module: 'Tasks'
                    },
                    {
                        id: 'TestCallID',
                        module: 'Calls'
                    },
                ],
            },
            {
                activities: [],
                currentActivity: {
                    id: 'TestCallID',
                    module: 'Calls'
                },
                result: [
                    {
                        id: 'TestCallID',
                        module: 'Calls'
                    },
                ],
            },
            {
                activities: {},
                currentActivity: {},
                result: [],
            },
        ],

        function(input) {
            it('activitiesInfo should match the expected result', function() {
                let activitiesInfo = view.getActivitiesInfo(input.activities, input.currentActivity);

                expect(activitiesInfo).toEqual(input.result);
            });
        });
    });
});
