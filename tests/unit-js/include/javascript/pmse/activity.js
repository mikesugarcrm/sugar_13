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
// Test activity.js
describe('includes.javascript.pmse.activity', function() {
    var app;
    var oldApp;
    var oldProject;
    var mockActivity;
    var mockValidationTools;
    var mockAPIData;
    var mockCriteria;

    beforeEach(function() {

        // Setting temporary globals in case they are altered during any tests
        app = SugarTest.app;

        oldProject = project;
        project = {};

        oldApp = App;
        App = app;

        // Mocking an AdamActivity
        mockActivity = new AdamActivity();

        // Mocking validationTools
        mockValidationTools = getValidationTools();

        // Mocking a basic API data response from the ActivityDefinition endpoint
        mockAPIData = {
            act_adhoc: 0,
            act_adhoc_behavior: null,
            act_adhoc_team: null,
            act_assign_team: null,
            act_assign_user: 'mock_act_assign_user',
            act_assignment_method: null,
            act_duration: 0,
            act_duration_unit: 'DAYS',
            act_expected_time: {time: '', unit: 'hour'},
            act_field_module: 'mock_act_field_module',
            act_fields: 'mock_act_fields',
            act_last_user_assigned: null,
            act_params: null,
            act_readonly_fields: [],
            act_reassign: 0,
            act_reassign_team: null,
            act_related_modules: [],
            act_required_fields: [],
            act_response_buttons: null,
            act_send_notification: 0,
            act_service_method: null,
            act_service_params: null,
            act_service_url: null,
            act_type: '4252863575bac6a40c22fd5036260728',
            act_update_record_owner: 0,
            act_value_based_assignment: null,
            assigned_user_id: null,
            created_by: '1',
            date_entered: '2018-10-01 17:33:21',
            date_modified: '2018-10-01 17:33:21',
            deleted: 0,
            description: null,
            execution_mode: 'DEFAULT',
            id: '17f4d672-c5a0-11e8-be3c-6003089fe26e',
            modified_user_id: '1',
            my_favorite: 0,
            name: 'Mock Activity',
            pro_id: '9b682ea2-c215-11e8-a88c-6003089fe26e',
            success: true
        };

        mockCriteria = [{
            field: 'assigned_user_id',
            label: 'Created by User',
            name: 'Assigned to',
            type: 'user',
            value: 'created_by'
        },
        {
            field: 'description',
            name: 'Description',
            type: 'TextArea',
            value: ''
        },
        {
            field: 'date_due',
            name: 'Due Date',
            type: 'Datetime',
            value: []
        },
        {
            field: 'priority',
            name: 'Priority',
            type: 'DropDown',
            value: 'High'
        },
        {
            field: 'date_start',
            name: 'Start Date',
            type: 'Datetime',
            value: []
        },
        {
            field: 'status',
            name: 'Status',
            type: 'DropDown',
            value: 'Not Started'
        },
        {
            field: 'name',
            name: 'Subject',
            type: 'Name',
            value: 'mock_task'
        },
        {
            append: false,
            field: 'teams',
            name: 'Teams',
            primary: null,
            selected_teams: [],
            type: 'team_list',
            value: []
        }];

        // Stub these ValidationTools functions that are unnecessary for these tests
        sinon.stub(mockValidationTools, 'validateNumberOfEdges');
        sinon.stub(mockValidationTools, 'validateAtom');
        sinon.stub(mockValidationTools, 'createWarning');
        sinon.stub(mockValidationTools, 'createError');
        sinon.stub(mockValidationTools, 'getTargetModule').returns('mock_act_field_module');
    });

    afterEach(function() {

        // Restore the local variables and stubs
        app = null;
        mockActivity = null;
        mockValidationTools = null;
        mockAPIData = null;
        sinon.restore();

        // Restore the global variables
        project = oldProject;
        App = oldApp;
    });

    describe('getBaseURL', function() {

        it('should return the correct base URL for activities', function() {
            expect(mockActivity.getBaseURL()).toBe('pmse_Project/ActivityDefinition/');
        });
    });

    describe('getValidationFunction', function() {

        it('should return the correct validation function for activities', function() {
            mockActivity.setTaskType('USERTASK');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForActivity);
        });

        it('should return the correct validation function for unassigned actions', function() {
            mockActivity.setTaskType('SCRIPTTASK');
            mockActivity.setScriptType('NONE');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForUnassignedAction);
        });

        it('should return the correct validation function for business rule actions', function() {
            mockActivity.setTaskType('SCRIPTTASK');
            mockActivity.setScriptType('BUSINESS_RULE');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForBusinessRuleAction);
        });

        it('should return the correct validation function for assign user actions', function() {
            mockActivity.setTaskType('SCRIPTTASK');
            mockActivity.setScriptType('ASSIGN_USER');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForAssignUserAction);
        });

        it('should return the correct validation function for change field actions', function() {
            mockActivity.setTaskType('SCRIPTTASK');
            mockActivity.setScriptType('CHANGE_FIELD');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForChangeFieldAction);
        });

        it('should return the correct validation function for add related record actions', function() {
            mockActivity.setTaskType('SCRIPTTASK');
            mockActivity.setScriptType('ADD_RELATED_RECORD');
            expect(mockActivity.getValidationFunction()).toBe(mockActivity.callbackFunctionForAddRelatedRecordAction);
        });
    });

    describe('callbackFunctionForActivity', function() {

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should generate an error if the expected time is set to a negative value', function() {
            mockAPIData.act_expected_time.time = -1;
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.createError).toHaveBeenCalledWith(mockActivity,
                'LBL_PMSE_ERROR_ACTIVITY_EXPECTED_TIME');
        });

        it('should not generate an error if the expected time is set to 0', function() {
            mockAPIData.act_expected_time.time = 0;
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should not generate an error if the expected time is set to greater than 0', function() {
            mockAPIData.act_expected_time.time = 1;
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should not generate an error if the expected time is not set', function() {
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should validate the selected user if "Static Assignment" is selected under "Users"', function() {
            mockAPIData.act_assignment_method = 'static';
            mockAPIData.act_assign_user = 'mock_user';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'USER_IDENTITY', value: 'mock_user'},
                mockActivity,
                mockValidationTools
            );
        });

        it('should not try to validate "Current User", "Record Owner", or "Supervisor" as users', function() {
            mockAPIData.act_assignment_method = 'static';
            mockAPIData.act_assign_user = 'currentuser';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            mockAPIData.act_assign_user = 'owner';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            mockAPIData.act_assign_user = 'supervisor';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
        });

        it('should not try to validate a user if "Round Robin" is selected under "Users"', function() {
            mockAPIData.act_assignment_method = 'balanced';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
        });

        it('should not try to validate a user if "Self Service" is selected under "Users"', function() {
            mockAPIData.act_assignment_method = 'selfservice';
            mockActivity.callbackFunctionForActivity(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
        });
    });

    describe('callbackFunctionForUnassignedAction', function() {

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForUnassignedAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should generate a warning that the action is unassigned', function() {
            mockActivity.callbackFunctionForUnassignedAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.createWarning).toHaveBeenCalled();
        });
    });

    describe('callbackFunctionForBusinessRuleAction', function() {

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForBusinessRuleAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should validate the selected business rule', function() {
            mockActivity.callbackFunctionForBusinessRuleAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'ALL_BUSINESS_RULES', value: 'mock_act_fields'},
                mockActivity,
                mockValidationTools
            );
        });
    });

    describe('callbackFunctionForAssignUserAction', function() {

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForAssignUserAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should validate the selected user', function() {
            mockActivity.callbackFunctionForAssignUserAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'USER_IDENTITY', value: 'mock_act_assign_user'},
                mockActivity,
                mockValidationTools);
        });
    });

    describe('callbackFunctionForChangeFieldAction', function() {

        beforeEach(function() {

            // Mock of two different fields being configured to change
            mockAPIData.act_fields = '[{"name":"Alternate Phone",' +
                '"field":"phone_alternate",' +
                '"value":"mock phone",' +
                '"type":"Phone"},' +
                '{"name":"Description",' +
                '"field":"description",' +
                '"value":"mock description",' +
                '"type":"TextArea"}]';
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForChangeFieldAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should validate each selected field', function() {
            mockActivity.callbackFunctionForChangeFieldAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'mock_act_field_module', module: 'phone_alternate'},
                mockActivity,
                mockValidationTools
            );
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'mock_act_field_module', module: 'description'},
                mockActivity,
                mockValidationTools
            );
        });
    });

    describe('callbackFunctionForAddRelatedRecordAction', function() {

        var mockCallFunction;
        var urlToReturn;

        beforeEach(function() {

            // Mocking field creation for adding a 'tasks' record
            // with only required fields filled in
            mockAPIData.act_field_module = 'tasks';
            mockAPIData.act_params = '{"module":"tasks"}';

            // Declaring the URL that our mock for buildURL will return, so that we can change it as needed
            urlToReturn = 'correct_endpoint';

            // Mocks an API call function to take the proper action depending on the URL
            // In this mock, 'mock_after_build_URL' is used as a valid URL endpoint for checking
            // the addRelatedRecord endpoint
            // We will use this in place of the App.api.call function to simulate an API call
            // and reponse action for both proper and improper URLs
            mockCallFunction = function(action, url, attributes, callbacks, options) {
                if (url === 'correct_endpoint') {
                    // Simulating the "success" function
                    callbacks.success();
                } else {
                    // Simulating the "error" function
                    callbacks.error();
                }
                // Simulating the "complete" function
                callbacks.complete();
            };

            // Mocking the buildURL function to instead return urlToReturn
            // This way, we can change urlToReturn to test when an incorrect URL
            // is used in the API call
            sinon.stub(App.api, 'buildURL').callsFake(function() {
                return urlToReturn;
            });

            // Mocking the App.api.call function with our own modified version
            // that simulates the actions of success/error/complete callbacks
            sinon.stub(App.api, 'call').callsFake(mockCallFunction);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockValidationTools.progressTracker, 'incrementTotalValidations');
            sinon.stub(mockValidationTools.progressTracker, 'incrementValidated');
            sinon.stub(mockActivity, 'validateAddRelatedRecordForm');
        });

        afterEach(function() {

            // Restore the local variables
            mockCallFunction = null;
            urlToReturn = null;
        });

        it('should build the correct URL for related records', function() {
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(App.api.buildURL).toHaveBeenCalledWith(
                'pmse_Project/CrmData/addRelatedRecord/tasks?base_module=mock_act_field_module');
        });

        it('should build the correct URL for related related records', function() {
            mockAPIData.act_params = '{"module":"accounts","chainedRelationship":{"module":"contacts"}}';
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(App.api.buildURL).toHaveBeenCalledWith(
                'pmse_Project/CrmData/addRelatedRecord/contacts?base_module=mock_act_field_module');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, null, mockActivity);
        });

        it('should increment the number of validations that need to be performed', function() {
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockValidationTools.progressTracker.incrementTotalValidations).toHaveBeenCalled();
        });

        it('should make the API call with the correct parameters', function() {
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(App.api.call).toHaveBeenCalledWith('read', 'correct_endpoint', null, jasmine.any(Object),
                {
                    'bulk': 'validate_element_settings'
                });
        });

        it('should try to validate the add related record form if the module relationship is valid', function() {
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockActivity.validateAddRelatedRecordForm).toHaveBeenCalled();
            expect(mockValidationTools.progressTracker.incrementValidated).toHaveBeenCalled();
        });

        it('should not try to validate the add related record form if the module relationship is invalid', function() {
            urlToReturn = 'Whoops, wrong URL';
            mockActivity.callbackFunctionForAddRelatedRecordAction(mockAPIData, mockActivity, mockValidationTools);
            expect(mockActivity.validateAddRelatedRecordForm).not.toHaveBeenCalled();
            expect(mockValidationTools.progressTracker.incrementValidated).toHaveBeenCalled();
        });
    });

    describe('validateAddRelatedRecordForm', function() {

        var mockFormData;

        beforeEach(function() {

            // Mocking the configuration retrieved from the API call to the element settings
            mockAPIData.act_fields = '[{"name":"Assigned to",' +
                '"field":"assigned_user_id",' +
                '"value":"created_by",' +
                '"type":"user",' +
                '"label":"Created by User"},' +
                '{"name":"Description",' +
                '"field":"description",' +
                '"value":"",' +
                '"type":"TextArea"},' +
                '{"name":"Due Date",' +
                '"field":"date_due",' +
                '"value":[],"type":"Datetime"},' +
                '{"name":"Priority",' +
                '"field":"priority",' +
                '"value":"High",' +
                '"type":"DropDown"},' +
                '{"name":"Start Date",' +
                '"field":"date_start",' +
                '"value":[],"type":"Datetime"},' +
                '{"name":"Status",' +
                '"field":"status",' +
                '"value":"Not Started",' +
                '"type":"DropDown"},' +
                '{"name":"Subject",' +
                '"field":"name",' +
                '"value":"mock_task",' +
                '"type":"Name"},' +
                '{"name":"Teams",' +
                '"field":"teams",' +
                '"value":[],' +
                '"primary":null,' +
                '"selected_teams":[],' +
                '"append":false,' +
                '"type":"team_list"}]';

            // Mocking the form information retrieved from the API call to the addRelatedRecord endpoint
            // The result array contains information about whether a particular field is required
            mockFormData = {
                groupFieldsMap: [],
                name: 'Tasks',
                result: [{
                    value: 'assigned_user_id',
                    text: 'Assigned to',
                    type: 'user',
                    required: true
                },
                {
                    value: 'description',
                    text: 'Description',
                    type: 'TextArea'
                },
                {
                    value: 'date_due',
                    text: 'Due Date',
                    type: 'Datetime'
                },
                {
                    value: 'priority',
                    text: 'Priority',
                    type: 'DropDown',
                    required: true
                },
                {
                    value: 'date_start',
                    text: 'Start Date',
                    type: 'Datetime'
                },
                {
                    value: 'status',
                    text: 'Status',
                    type: 'Dropdown',
                    required: true
                },
                {
                    value: 'name',
                    text: 'Subject',
                    type: 'Name',
                    required: true
                },
                {
                    value: 'teams',
                    text: 'Teams',
                    type: 'team_list',
                }],
                search: 'tasks',
                success: true
            };

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockActivity, 'checkIfRequiredFieldIsSet');
        });

        afterEach(function() {

            // Restore the local variables
            mockFormData = null;
        });

        it('should call checkIfRequiredFieldIsSet for each required field in the form result array', function() {
            mockActivity.validateAddRelatedRecordForm(mockFormData, mockAPIData, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).toHaveBeenCalledWith(mockFormData.result[0],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).toHaveBeenCalledWith(mockFormData.result[3],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).toHaveBeenCalledWith(mockFormData.result[5],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).toHaveBeenCalledWith(mockFormData.result[6],
                mockCriteria, mockActivity, mockValidationTools);
        });

        it('should not call checkIfRequiredFieldIsSet for non-required fields in the form result array', function() {
            mockActivity.validateAddRelatedRecordForm(mockFormData, mockAPIData, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).not.toHaveBeenCalledWith(mockFormData.result[1],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).not.toHaveBeenCalledWith(mockFormData.result[2],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).not.toHaveBeenCalledWith(mockFormData.result[4],
                mockCriteria, mockActivity, mockValidationTools);
            expect(mockActivity.checkIfRequiredFieldIsSet).not.toHaveBeenCalledWith(mockFormData.result[7],
                mockCriteria, mockActivity, mockValidationTools);
        });
    });

    describe('checkIfRequiredFieldIsSet', function() {

        it('should generate a warning if the required field is not set', function() {
            mockCriteria[6].value = '';
            mockActivity.checkIfRequiredFieldIsSet({
                    value: 'name',
                    text: 'Subject',
                    type: 'Name',
                    required: true
                }, mockCriteria, mockActivity, mockValidationTools);
            expect(mockValidationTools.createWarning).toHaveBeenCalled();
        });

        it('should not generate a warning if the required field is set', function() {
            mockActivity.checkIfRequiredFieldIsSet({
                    value: 'assigned_user_id',
                    text: 'Assigned to',
                    type: 'user',
                    required: true
                }, mockCriteria, mockActivity, mockValidationTools);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });
    });
});
