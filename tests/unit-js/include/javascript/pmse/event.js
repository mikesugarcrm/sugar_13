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
describe('includes.javascript.pmse.event', function() {
    var app;
    var event;
    var initObjectStub;

    var oldProject;
    var oldApp;

    var mockValidationTools;
    var mockAPIData;

    beforeEach(function() {
        app = SugarTest.app;

        // AdamEvent requires some global variables to be set to function without
        // shitting itself. I don't think this one is used outside of that, but
        // to make 100% sure I don't ruin other application stuff or tests I'll
        // keep a copy.
        oldProject = project;
        project = {};

        //Seriously this Adam stuff sucks.
        oldApp = App;
        App = app;

        // Stub out other objects used by the AdamEvent.
        // Since AdamShape is defined in the global scope, we can stub it like this.
        sinon.stub(window, 'AdamShape');

        /*
        createStubInstance is supposed to be used to make a stubbed object,
        but I don't think it works correctly in the version of sinon we use,
        so I'll avoid it. See https://github.com/sinonjs/sinon/issues/860
        */
        /*
        Instead because the constructor doesn't do anything but call AdamShape
        and AdamEvent.initObject, I'll just stub those two functions. It's not
        ideal but it'll have to do.
        When initObject needs to actually be tested, you can do that by calling
        initObjectStub.restore().
        */
        initObjectStub = sinon.stub(AdamEvent.prototype, 'initObject');

        // Create a mock event object to test with
        event = new AdamEvent();

        // Create a mock validationTools to test with
        mockValidationTools = getValidationTools();

        // Mock API response data template from an AdamEvent
        mockAPIData = {
            created_by: '1',
            date_entered: '2018-09-27 05:24:59',
            date_modified: '2018-09-27 05:25:05',
            deleted: 0,
            description: null,
            evn_criteria: null,
            evm_module: 'Accounts',
            evn_params: null,
            evn_script: null,
            evn_status: 'ACTIVE',
            evn_type: 'mock_event',
            evn_uid: '3276074795bad6ff64b45a1031513765',
            execution_mode: 'DEFAULT',
            id: '0615ed88-c2b2-11e8-8024-6003089fe26e',
            my_favorite: 0,
            name: null
        };

        // Stub these ValidationTools functions that are unnecessary for these tests
        sinon.stub(mockValidationTools, 'validateAtom');
        sinon.stub(mockValidationTools, 'validateNumberOfEdges');
        sinon.stub(mockValidationTools, 'createWarning');
        sinon.stub(mockValidationTools, 'createError');
    });

    afterEach(function() {
        sinon.restore();
        app = null;
        event = null;
        mockValidationTools = null;
        initObjectStub = null;

        // Restore the globals
        project = oldProject;
        App = oldApp;
    });

    describe('_makeCriteriaField', function() {
        // To mock out the constructor for CriteriaFields.
        // Defined here because not every function uses it.
        // Defined in a var because we need to spy on it.
        var fakeCriteriaField;
        // Data common to all event types can be kept in the before section;
        var commonData;

        beforeEach(function() {
            // Since CriteriaField is defined in the global scope, we can mock out
            // its constructor as a function in window;
            fakeCriteriaField = sinon.stub(window, 'CriteriaField');

            // _makeCriteriaField uses the "project" global for this function,
            // so it needs a mock.
            var replaceMock = sinon.stub().returns('b');
            project.getMetadata = sinon.stub().returns({url: {replace: replaceMock}});
            project.process_definition = {pro_module: 'goat'};

            // Mock out app functions called below and in makeCriteriaField
            sinon.stub(App.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {}
                };
            });
            sinon.stub(window, 'translate').returns('a');
            sinon.stub(App.date, 'getUserDateFormat').returns('c');
            sinon.stub(App.user, 'getPreference').returns('d');
            commonData = {
                name: 'evn_criteria',
                label: translate('LBL_PMSE_FORM_LABEL_CRITERIA'),
                required: false,
                fieldWidth: 414,
                decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                currencies: project.getMetadata('currencies'),
                dateFormat: App.date.getUserDateFormat(),
                timeFormat: App.user.getPreference('timepref'),
            };
        });

        afterEach(function() {
            //Reset data since it is mutated by the call
            commonData = null;
        });

        it('should send the right info for start events', function() {
            var startData = {
                fieldHeight: 250,
                fieldWidth: 850,
                panelContext: '#container',
                operators: {
                    logic: true,
                    group: true
                },
                constant: false
            };

            var data = _.extend({}, commonData, startData);

            event.evn_type = 'START';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(data));
        });

        it('should send the right info for receive message events', function() {
            var receiveData = {
                operators: {
                    logic: true,
                    group: true
                },
                constant: false,
                evaluation: {
                    module: {
                        dataURL: 'pmse_Project/CrmData/related/' + PROJECT_MODULE,
                        dataRoot: 'result',
                        fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                        fieldDataURLAttr: {
                            call_type: 'RM'
                        },
                        fieldDataRoot: 'result'
                    },
                    user: {
                        defaultUsersDataURL: 'pmse_Project/CrmData/defaultUsersList',
                        defaultUsersDataRoot: 'result',
                        userRolesDataURL: 'pmse_Project/CrmData/rolesList',
                        userRolesDataRoot: 'result',
                        usersDataURL: 'pmse_Project/CrmData/users',
                        usersDataRoot: 'result'
                    },
                    relationship: {
                        dataURL: 'pmse_Project/CrmData/related/' + PROJECT_MODULE,
                        dataRoot: 'result',
                        fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                        fieldDataURLAttr: {
                            // RM = Received Message
                            call_type: 'RM'
                        },
                        fieldDataRoot: 'result'
                    }
                }
            };

            event.evn_type = 'INTERMEDIATE';
            event.evn_marker = 'MESSAGE';
            event.evn_behavior = 'CATCH';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(commonData));
            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(receiveData));
        });

        it('should send the right info for timer events', function() {
            var timerData = {
                fieldHeight: 80,
                operators:
                    {
                        arithmetic: ['+', '-']
                    },
                constant:
                    {
                        datetime: true,
                        timespan: true,
                        businessHours: {
                            show: true,
                            targetModuleBC: false,
                            selectedModuleBC: ''
                        }
                    },
                variable:
                    {
                        dataURL: project.getMetadata('fieldsDataSource')
                            .url.replace('{MODULE}', project.process_definition.pro_module),
                        dataRoot: project.getMetadata('fieldsDataSource').root,
                        dataFormat: 'hierarchical',
                        dataChildRoot: 'fields',
                        textField: 'text',
                        valueField: 'value',
                        typeField: 'type',
                        typeFilter: ['Date', 'Datetime'],
                        moduleTextField: 'text',
                        moduleValueField: 'value'
                    }
            };

            event.evn_type = 'INTERMEDIATE';
            event.evn_marker = 'TIMER';
            event.evn_behavior = 'CATCH';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(commonData));
            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(timerData));
        });
    });

    // Test that the correct URL endpoint is returned from getBaseURL
    describe('getBaseURL', function() {
        it('should return the correct base API endpoint for event definitions', function() {
            expect(event.getBaseURL()).toBe('pmse_Project/EventDefinition/');
        });
    });

    // Test that getValidationFunction returns the correct validation function
    describe('getValidationFunction', function() {
        it('should return the correct validation function for start events', function() {
            event.setEventType('start');
            expect(event.getValidationFunction()).toBe(event.callbackFunctionForStartEvent);
        });

        it('should return the correct validation function for wait events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('TIMER');
            expect(event.getValidationFunction()).toBe(event.callbackFunctionForWaitEvent);
        });

        it('should return the correct validation function for receive message events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('catch');
            expect(event.getValidationFunction()).toBe(event.callbackFunctionForReceiveMessageEvent);
        });

        it('should return the correct validation function for send message events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('throw');
            expect(event.getValidationFunction()).toBe(event.callbackFunctionForSendMessageEvent);
        });

        it('should return the correct validation function for end events', function() {
            event.setEventType('end');
            expect(event.getValidationFunction()).toBe(event.callbackFunctionForEndEvent);
        });
    });

    // Test the callbackFunctionForStartEvent function
    describe('callbackFunctionForStartEvent', function() {

        beforeEach(function() {
            event.setEventType('start');
            sinon.stub(event, 'validateStartOrReceiveMessageCriteriaBox');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            event.callbackFunctionForStartEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(null, 0, 1, null, event);
        });

        it('should generate a warning if the "Applies to:" field is not set', function() {
            event.callbackFunctionForStartEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).toHaveBeenCalledWith(event, 'LBL_PMSE_ERROR_FIELD_REQUIRED',
                'Applies to');
        });

        it('should not generate a warning if the "Applies to:" field is set', function() {
            mockAPIData.evn_params = 'new';
            event.callbackFunctionForStartEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should call validateStartOrReceiveMessageCriteriaBox with the correct data', function() {
            mockAPIData.evn_params = 'new';
            mockAPIData.evn_criteria = '[{"expType":"MODULE",' +
                '"expSubtype":"Phone",' +
                '"expLabel":"Accounts (Alternate Phone is \\"123\\")",' +
                '"expValue":"123",' +
                '"expOperator":"equals",' +
                '"expModule":"Accounts",' +
                '"expField":"phone_alternate"}]';
            event.callbackFunctionForStartEvent(mockAPIData, event, mockValidationTools);
            expect(event.validateStartOrReceiveMessageCriteriaBox).toHaveBeenCalledWith(mockAPIData,
                event, mockValidationTools);
        });
    });

    // Test the callbackFunctionForWaitEvent function
    describe('callbackFunctionForWaitEvent', function() {

        beforeEach(function() {
            event.setEventType('intermediate');
            event.setEventMarker('TIMER');

            sinon.stub(event, 'validateWaitEventCriteriaBox');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, 1, event);
        });

        it('should call validateWaitEventCriteriaBox if "Fixed date" is selected, with the correct data', function() {
            mockAPIData.evn_params = 'fixed date';
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(event.validateWaitEventCriteriaBox).toHaveBeenCalledWith(mockAPIData, event, mockValidationTools);
        });

        it('should generate an error if "Duration" is selected and the time given is exactly 0', function() {
            mockAPIData.evn_criteria = '0';
            mockAPIData.evn_params = 'minute';
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).toHaveBeenCalledWith(event,
                'LBL_PMSE_ERROR_WAIT_EVENT_ZERO_DURATION');
        });

        it('should not generate an error if "Duration" is selected and the time given is positive', function() {
            mockAPIData.evn_criteria = '1';
            mockAPIData.evn_params = 'minute';
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should not generate an error if "Duration" is selected and the time given is negative', function() {
            mockAPIData.evn_criteria = '-1';
            mockAPIData.evn_params = 'minute';
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should generate an error if neither "Duration" or "Fixed date" is selected (no config)', function() {
            event.callbackFunctionForWaitEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createError).toHaveBeenCalledWith(event,
                'LBL_PMSE_ERROR_WAIT_EVENT_NO_PARAMETERS');
        });
    });

    // Test the callbackFunctionForReceiveMessageEvent function
    describe('callbackFunctionForReceiveMessageEvent', function() {

        beforeEach(function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('catch');

            sinon.stub(event, 'validateStartOrReceiveMessageCriteriaBox');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            event.callbackFunctionForReceiveMessageEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, 1, event);
        });

        it('should call validateStartOrReceiveMessageCriteriaBox with the correct data', function() {
            mockAPIData.evn_criteria = '[{"expType":"MODULE",' +
                '"expSubtype":"Phone",' +
                '"expLabel":"Accounts (Alternate Phone is \\"123\\")",' +
                '"expValue":"123",' +
                '"expOperator":"equals",' +
                '"expModule":"Accounts",' +
                '"expField":"phone_alternate"}]';
            event.callbackFunctionForReceiveMessageEvent(mockAPIData, event, mockValidationTools);
            expect(event.validateStartOrReceiveMessageCriteriaBox).toHaveBeenCalledWith(mockAPIData, event,
                mockValidationTools);
        });
    });

    // Test the callbackFunctionForSendMessageEvent function
    describe('callbackFunctionForSendMessageEvent', function() {
        beforeEach(function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('throw');

            sinon.stub(event, 'validateSendMessageData');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(1, null, 1, 1, event);
        });

        it('should call validateSendMessageData with the correct data', function() {
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(event.validateSendMessageData).toHaveBeenCalledWith(mockAPIData, event,
                mockValidationTools);
        });
    });

    // Test the callbackFunctionForEndEvent function
    describe('callbackFunctionForEndEvent', function() {

        beforeEach(function() {
            event.setEventType('end');
            sinon.stub(event, 'validateSendMessageData');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
        });

        describe('for send-message-end events', function() {

            beforeEach(function() {
                event.setEventMarker('MESSAGE');
            });

            it('should call validateSendMessageData', function() {
                event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
                expect(event.validateSendMessageData).toHaveBeenCalledWith(mockAPIData, event, mockValidationTools);
            });

        });

        describe('for do-nothing end events', function() {

            beforeEach(function() {
                event.setEventMarker('EMPTY');
            });

            it('should not try to validate the email template', function() {
                event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
                expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
            });

            it('should not try to validate the criteria boxes', function() {
                event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
                expect(event.validateSendMessageCriteriaBoxes).not.toHaveBeenCalled();
            });
        });

        describe('for terminate end events', function() {

            beforeEach(function() {
                event.setEventMarker('TERMINATE');
            });

            it('should not try to validate the email template', function() {
                event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
                expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
            });

            it('should not try to validate the criteria boxes', function() {
                event.callbackFunctionForEndEvent(mockAPIData, event, mockValidationTools);
                expect(event.validateSendMessageCriteriaBoxes).not.toHaveBeenCalled();
            });
        });
    });

    // Test that start and receive message event criteria boxes are properly validated
    describe('validateStartOrReceiveMessageCriteriaBox', function() {
        var mockCriteria;
        var mockParsedCriteria;

        beforeEach(function() {

            mockCriteria = '[{"expType":"MODULE",' +
                '"expSubtype":"Phone",' +
                '"expLabel":"Accounts (Alternate Phone is \\"123\\")",' +
                '"expValue":"123",' +
                '"expOperator":"equals",' +
                '"expModule":"Accounts",' +
                '"expField":"phone_alternate"}]';

            mockParsedCriteria = [{
                expType: 'MODULE',
                expSubtype: 'Phone',
                expLabel: 'Accounts (Alternate Phone is "123")',
                expValue: '123',
                expOperator: 'equals',
                expModule: 'Accounts',
                expField: 'phone_alternate'
            }];

            sinon.stub(event, 'checkForImpossibleLogic');
            sinon.stub(event, 'validateCriteriaBoxAtoms');
        });

        it('should call checkForImpossibleLogic with the correct data for start events', function() {
            event.setEventType('start');
            mockAPIData.evn_criteria = mockCriteria;
            event.validateStartOrReceiveMessageCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.checkForImpossibleLogic).toHaveBeenCalledWith(event, mockValidationTools, mockParsedCriteria);
        });

        it('should call checkForImpossibleLogic with the correct data for receive message events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('catch');
            mockAPIData.evn_criteria = mockCriteria;
            event.validateStartOrReceiveMessageCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.checkForImpossibleLogic).toHaveBeenCalledWith(event, mockValidationTools, mockParsedCriteria);
        });

        it('should call validateCriteriaBoxAtoms with the correct data for start events', function() {
            event.setEventType('start');
            mockAPIData.evn_criteria = mockCriteria;
            event.validateStartOrReceiveMessageCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedCriteria);
        });

        it('should call validateCriteriaBoxAtoms with the correct data for receive message events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('catch');
            mockAPIData.evn_criteria = mockCriteria;
            event.validateStartOrReceiveMessageCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedCriteria);
        });
    });

    // Test that wait event criteria boxes are properly validated
    describe('validateWaitEventCriteriaBox', function() {
        var mockCriteria;
        var mockParsedCriteria;

        beforeEach(function() {

            mockCriteria = '[{"expType":"CONSTANT",' +
                '"expSubtype":"datetime",' +
                '"expLabel":"%VALUE%",' +
                '"expValue":"2018-08-26T01:00:00-07:00"}]';

            mockParsedCriteria = [{
                expType: 'CONSTANT',
                expSubtype: 'datetime',
                expLabel: '%VALUE%',
                expValue: '2018-08-26T01:00:00-07:00'
            }];

            event.setEventType('intermediate');
            event.setEventMarker('TIMER');

            sinon.stub(event, 'validateCriteriaBoxAtoms');
            sinon.stub(event, 'validateCorrectNumberOfDateObjects');
        });

        it('should call validateCriteriaBoxAtoms with the correct data', function() {
            mockAPIData.evn_criteria = mockCriteria;
            event.validateWaitEventCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedCriteria);
        });

        it('should call validateCorrectNumberOfDateObjects with the correct data', function() {
            mockAPIData.evn_criteria = mockCriteria;
            event.validateWaitEventCriteriaBox(mockAPIData, event, mockValidationTools);
            expect(event.validateCorrectNumberOfDateObjects).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedCriteria);
        });
    });

    // Test that send message data fields are properly validated
    describe('validateSendMessageData', function() {

        var mockCriteria;
        var mockCriteriaParsed;

        beforeEach(function() {
            mockCriteria = '{"from":{"name":"Chris Olliver","id":"seed_chris_id"}}';
            mockAPIData.evn_params = mockCriteria;

            sinon.stub(event, 'validateSendMessageCriteriaBoxes');
        });

        it('should validate that the email template is set', function() {
            mockAPIData.evn_criteria = '6b9f5cae-c332-11e8-be1b-6003089fe26e';
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'TEMPLATE', value: '6b9f5cae-c332-11e8-be1b-6003089fe26e'},
                event,
                mockValidationTools
            );
        });

        it('should validate that the "From" field is set when it is set', function() {
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });

        it('should trigger a warning if the "From" field is not set', function() {
            mockAPIData.evn_params = '{"from":{"name": null,"id": null}}';
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(mockValidationTools.createWarning).toHaveBeenCalled();
        });

        it('should call validateSendMessageCriteriaBoxes with the correct data', function() {
            mockCriteriaParsed = JSON.parse(mockCriteria);
            event.callbackFunctionForSendMessageEvent(mockAPIData, event, mockValidationTools);
            expect(event.validateSendMessageCriteriaBoxes).toHaveBeenCalledWith(event,
                mockValidationTools, mockCriteriaParsed);
        });
    });

    // Test that send message event criteria boxes are properly validated
    describe('validateSendMessageCriteriaBoxes', function() {

        var mockCriteriaWithTo;
        var mockParsedCriteriaWithTo;
        var mockParsedTo;
        var mockParsedCC;
        var mockParsedBCC;

        beforeEach(function() {

            mockCriteriaWithTo = '{' +
                '"to":[{"type":"user",' +
                '"module":"Accounts",' +
                '"moduleLabel":"Accounts",' +
                '"value":"record_creator",' +
                '"user":"who",' +
                '"label":"User who created the %MODULE%",' +
                '"filter":{}}],' +
                '"cc":[{"type":"user",' +
                '"module":"Accounts",' +
                '"moduleLabel":"Accounts",' +
                '"value":"is_assignee",' +
                '"user":"who",' +
                '"label":"User who is assigned to the %MODULE%",' +
                '"filter":{}}],' +
                '"bcc":[{"type":"user",' +
                '"module":"Accounts",' +
                '"moduleLabel":"Accounts",' +
                '"value":"last_modifier",' +
                '"user":"who",' +
                '"label":"User who last modified the %MODULE%",' +
                '"filter":{}}]}';
            mockParsedCriteriaWithTo = JSON.parse(mockCriteriaWithTo);

            mockParsedTo = [{
                type: 'user',
                module: 'Accounts',
                moduleLabel: 'Accounts',
                value: 'record_creator',
                user: 'who',
                label: 'User who created the %MODULE%',
                filter: {}
            }];

            mockParsedCC = [{
                type: 'user',
                module: 'Accounts',
                moduleLabel: 'Accounts',
                value: 'is_assignee',
                user: 'who',
                label: 'User who is assigned to the %MODULE%',
                filter: {}
            }];

            mockParsedBCC = [{
                type: 'user',
                module: 'Accounts',
                moduleLabel: 'Accounts',
                value: 'last_modifier',
                user: 'who',
                label: 'User who last modified the %MODULE%',
                filter: {}
            }];

            sinon.stub(event, 'validateCriteriaBoxAtoms');
        });

        it('should generate a warning if the "To:" field is empty', function() {
            event.validateSendMessageCriteriaBoxes(event, mockValidationTools, []);
            expect(mockValidationTools.createWarning).toHaveBeenCalledWith(event,
                'LBL_PMSE_ERROR_FIELD_REQUIRED', 'To');
        });

        it('should not generate a warning if the "To:" field has content', function() {
            mockAPIData.evn_params = mockCriteriaWithTo;
            event.validateSendMessageCriteriaBoxes(event, mockValidationTools, mockParsedCriteriaWithTo);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });

        it('should call validateCriteriaBoxAtoms with the correct data for each criteria/recipients box', function() {
            mockAPIData.evn_params = mockCriteriaWithTo;
            event.validateSendMessageCriteriaBoxes(event, mockValidationTools, mockParsedCriteriaWithTo);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedTo, true);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedCC, true);
            expect(event.validateCriteriaBoxAtoms).toHaveBeenCalledWith(event, mockValidationTools,
                mockParsedBCC, true);
        });
    });

    // Test that criteria box atoms are properly validated
    describe('validateCriteriaBoxAtoms', function() {

        var mockSendEventCriteria;
        var mockStartEventCriteria;

        beforeEach(function() {
            mockSendEventCriteria = [{
                type: 'user',
                module: 'Accounts',
                moduleLabel: 'Accounts',
                value: 'record_creator',
                user: 'who',
                label: 'User who created the %MODULE%',
                filter: {}
            }];

            mockStartEventCriteria = [{
                expType: 'MODULE',
                expSubtype: 'Phone',
                expLabel: 'Accounts (Alternate Phone is "123")',
                expValue: '123',
                expOperator: 'equals',
                expModule: 'Accounts',
                expField: 'phone_alternate'
            }];

        });

        it('should call validateAtom with the right data from a send-message-type event', function() {
            event.setEventMarker('MESSAGE');
            event.setBehavior('throw');
            event.validateCriteriaBoxAtoms(event, mockValidationTools, mockSendEventCriteria, true);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'user', module: 'Accounts', field: undefined, value: 'record_creator', relation: undefined},
                event,
                mockValidationTools
            );
        });

        it('should call validateAtom with the right data from a non-send-message-type event', function() {
            event.setEventType('start');
            event.validateCriteriaBoxAtoms(event, mockValidationTools, mockStartEventCriteria, false);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123', relation: undefined},
                event,
                mockValidationTools
            );
        });
    });

    // Test that the number of date objects in a wait event criteria box is properly counted
    describe('validateCorrectNumberOfDateObjects', function() {

        var mockParsedDatetimeCriteria;
        var mockParsedDateCriteria;

        beforeEach(function() {

            event.setEventType('intermediate');
            event.setEventMarker('TIMER');

            mockParsedDatetimeCriteria = [{
                expType: 'CONSTANT',
                expSubtype: 'datetime',
                expLabel: '%VALUE%',
                expValue: '2018-08-26T01:00:00-07:00'
            }];

            mockParsedDateCriteria = [{
                expType: 'VARIABLE',
                expSubtype: 'Date',
                expLabel: 'Expiration Date',
                expValue: 'exp_date',
                expModule: 'documents'
            }];
        });

        it('should generate an error if there are no date objects', function() {
            event.validateCorrectNumberOfDateObjects(event, mockValidationTools, []);
            expect(mockValidationTools.createError).toHaveBeenCalled();
        });

        it('should generate an error if there are multiple date objects', function() {
            mockParsedDateCriteria.push(mockParsedDatetimeCriteria[0]);
            event.validateCorrectNumberOfDateObjects(event, mockValidationTools, mockParsedDateCriteria);
            expect(mockValidationTools.createError).toHaveBeenCalled();
        });

        it('should check for date objects of type "Date"', function() {
            event.validateCorrectNumberOfDateObjects(event, mockValidationTools, mockParsedDateCriteria);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should check for objects of type "datetime"', function() {
            event.validateCorrectNumberOfDateObjects(event, mockValidationTools, mockParsedDatetimeCriteria);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });
    });

    // Test that impossible logic is properly caught in a criteria box
    describe('checkForImpossibleLogic', function() {

        var emptyCriteria;
        var possibleCriteria;
        var impossibleCriteria;

        beforeEach(function() {
            emptyCriteria = [];
            possibleCriteria = [{
                expType: 'MODULE',
                expSubtype: 'Phone',
                expLabel: 'Accounts (Alternate Phone is "123")',
                expValue: '123',
                expOperator: 'equals',
                expModule: 'Accounts',
                expField: 'phone_alternate'
            }];
            impossibleCriteria = [{
                expType: 'MODULE',
                expSubtype: 'Phone',
                expLabel: 'Accounts (Alternate Phone is "123")',
                expValue: '123',
                expOperator: 'equals',
                expModule: 'Accounts',
                expField: 'phone_alternate'
            },
            {
                expType: 'LOGIC',
                expLabel: 'AND',
                expValue: 'AND'
            },
            {
                expType: 'MODULE',
                expSubtype: 'Phone',
                expLabel: 'Accounts (Alternate Phone is not "123")',
                expValue: '123',
                expOperator: 'not_equals',
                expModule: 'Accounts',
                expField: 'phone_alternate'
            }];
        });

        it('should generate an error for empty criteria boxes from receive message events', function() {
            event.setEventType('intermediate');
            event.setEventMarker('MESSAGE');
            event.setBehavior('catch');
            event.checkForImpossibleLogic(event, mockValidationTools, emptyCriteria);
            expect(mockValidationTools.createError).toHaveBeenCalled();
        });

        it('should not generate an error for empty criteria boxes from start events', function() {
            event.setEventType('start');
            event.checkForImpossibleLogic(event, mockValidationTools, emptyCriteria);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });

        it('should generate an error for impossible criteria box logic', function() {
            event.checkForImpossibleLogic(event, mockValidationTools, impossibleCriteria);
            expect(mockValidationTools.createError).toHaveBeenCalled();
        });

        it('should not generate an error for valid criteria box logic', function() {
            event.checkForImpossibleLogic(event, mockValidationTools, possibleCriteria);
            expect(mockValidationTools.createError).not.toHaveBeenCalled();
        });
    });
});
