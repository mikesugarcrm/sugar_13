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
describe('includes.javascript.pmse.designer', function() {

    var app;
    var oldApp;
    var oldProject;
    var mockSilentValidationTools;
    var mockNonSilentValidationTools;
    var mockSilentTracker;
    var mockNonSilentTracker;
    var mockStartEvent;
    var mockWaitEvent;
    var mockEndEvent;
    var mockSelectionContainer;
    var mockComment;
    var mockCanvas;
    var mockValidateButton;
    var mockSaveValidateButton;
    var mockErrorPaneToggleButton;

    beforeEach(function() {

        // Set temporary globals in case they are altered during any tests
        app = SugarTest.app;

        oldProject = project;
        project = new AdamProject();
        project.process_definition.pro_module = 'Accounts';
        project.uid = 'mockProjectID';

        oldApp = App;
        App = app;

        oldCanvas = canvas;
        canvas = new AdamCanvas();

        // Mock the process validator HTML elements being on the page
        mockValidateButton = $('<span id="ButtonValidate" type="" rel="tooltip" title="" data-bs-placement="bottom"' +
            'data-original-title="{{str "LBL_PMSE_ADAM_DESIGNER_VALIDATE" this.module}}" class="navbtn">' +
            '<i class="sicon sicon-lg sicon-check-circle-lg"></i></span>');
        mockSaveValidateButton = $('<span id="ButtonSaveValidate" type="" rel="tooltip" title=""' +
            ' data-bs-placement="bottom" data-original-title="{{str "LBL_PMSE_ADAM_DESIGNER_SAVE_AND_VALIDATE"' +
            ' this.module}}" class="navbtn"><i class="sicon sicon-sm sicon-save-lg"></i>' +
            '<i class="sicon sicon-sm sicon-check-circle-lg">' +
            '</i></span>');
        mockErrorPaneToggleButton = $('<span id="ButtonToggleErrorPane" type="" rel="tooltip" title=""' +
            ' data-bs-placement="bottom" data-original-title="{{str "LBL_PMSE_ADAM_DESIGNER_VIEW_ERRORS"' +
            'this.module}}" class="navbtn"><i class="sicon sicon-lg sicon-warning-lg exclamation-triangle-off">' +
            '</i></span>');
        mockErrorTable = $('<table class="table table-striped table-sm" id="Error-table" width="100%">' +
            '<thead><tr><th style="text-align: left">Element Name</th><th style="text-align: left">Issue</th>' +
            '</tr></thead><tbody></tbody></table>');
        mockRefreshingErrorsIndicator = $('<div id="refreshing-errors"></div>');
        $('body').append(mockValidateButton, mockSaveValidateButton, mockErrorPaneToggleButton, mockErrorTable,
            mockRefreshingErrorsIndicator);

        // Mock a small set of elements to be returned in the call to getAllElements
        mockStartEvent = new AdamEvent();
        mockStartEvent.setEventType('start');

        mockWaitEvent = new AdamEvent();
        mockWaitEvent.setEventType('intermediate');

        mockEndEvent = new AdamEvent();
        mockEndEvent.setEventType('end');

        // Mock the functions to return the elements' names
        // Use names close in alphabetical ordering to test
        // the correct table ordering
        mockStartEvent.label = {
            getMessage: function() {
                return 'mockName';
            }
        };

        mockWaitEvent.label = {
            getMessage: function() {
                return 'mn';
            }
        };

        mockEndEvent.label = {
            getMessage: function() {
                return 'mp';
            }
        };

        // Mock a canvas object containing a few elements
        // MultipleSelectionContainer is always included, so add it
        mockCanvas = new AdamCanvas();
        mockComment = new AdamArtifact();

        sinon.stub(jCore.MultipleSelectionContainer.prototype, 'initObject');
        mockSelectionContainer = new jCore.MultipleSelectionContainer();

        mockCanvas.children.insert(mockSelectionContainer);
        mockCanvas.children.insert(mockStartEvent);
        mockCanvas.children.insert(mockWaitEvent);
        mockCanvas.children.insert(mockEndEvent);
        mockCanvas.children.insert(mockComment);

        // Stub the jCore.getActiveCanvas() function to return the mock canvas
        sinon.stub(jCore, 'getActiveCanvas').returns(mockCanvas);

        // Mock the two different types of validationTools
        mockSilentValidationTools = getValidationTools(true);
        mockNonSilentValidationTools = getValidationTools();

        // Mock the two different types of ValidationProgressTracker
        mockSilentTracker = mockSilentValidationTools.progressTracker;
        mockNonSilentTracker = mockNonSilentValidationTools.progressTracker;

        // Mock an empty currentErrorTable object in the designer
        currentErrorTable = document.createElement('tbody');

        // Set the correct default button actions for the process validator buttons
        mockNonSilentTracker.updateButtons();

        // Mock the Layout object in the designer
        // Having the center pane at scrollLeft = 0 and scrollTop = 0
        // simulates the furthest top-left position of the canvas pane
        myLayout = {
            center: {
                pane: [{
                    clientWidth: 400,
                    clientHeight: 400,
                    scrollLeft: 0,
                    scrollTop: 0
                }]
            },
            open: function() {
            },
            close: function() {
            },
            toggle: function() {
            }
        };
    });

    afterEach(function() {

        // Restore the local variables and stubs
        app = null;
        currentErrorTable = undefined;
        sinon.restore();

        // Remove the mocked HTML elements
        mockValidateButton.remove();
        mockSaveValidateButton.remove();
        mockErrorPaneToggleButton.remove();
        mockErrorTable.remove();
        mockRefreshingErrorsIndicator.remove();

        // Restore the global variables
        project = oldProject;
        App = oldApp;
        canvas = oldCanvas;
    });

    // Test the validate button function
    describe('validate button', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(window, 'traverseProcess');
            sinon.stub(AdamCanvas.prototype, 'RemoveCurrentMenu');
            sinon.stub(mockNonSilentTracker, 'updateSaveValidateButton');
            sinon.stub(mockNonSilentTracker, 'updateErrorPaneToggleButton');
        });

        it('should close the menu and start validating when clicked', function() {

            // Mock the progress tracker updating the toolbar buttons when no validation is running
            project.isBeingValidated = false;
            mockNonSilentTracker.updateButtons();

            // After updating the toolbar, the button should have the correct action
            mockValidateButton.click();
            expect(traverseProcess).toHaveBeenCalled();
            expect(jCore.getActiveCanvas().RemoveCurrentMenu).toHaveBeenCalled();
        });

        it('should have no action if validation is currently running', function() {

            // Mock the progress tracker updating the toolbar buttons when validation is running
            project.isBeingValidated = true;
            mockNonSilentTracker.updateButtons();

            // After updating the toolbar, the button should no longer have action
            mockValidateButton.click();
            expect(traverseProcess).not.toHaveBeenCalled();
            expect(jCore.getActiveCanvas().RemoveCurrentMenu).not.toHaveBeenCalled();
        });
    });

    describe('validate and save button', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(AdamProject.prototype, 'save');
            sinon.stub(window, 'traverseProcess');
            sinon.stub(AdamCanvas.prototype, 'RemoveCurrentMenu');
            sinon.stub(mockNonSilentTracker, 'updateValidateButton');
            sinon.stub(mockNonSilentTracker, 'updateErrorPaneToggleButton');
        });

        it('should save the project, close the menu, and start validating when clicked', function() {

            // Mock the progress tracker updating the toolbar buttons when no validation is running
            // project.isBeingValidated = false;
            // mockNonSilentTracker.updateButtons();

            // After updating the toolbar, the button should have the correct action
            mockSaveValidateButton.click();
            expect(project.save).toHaveBeenCalled();
            expect(traverseProcess).toHaveBeenCalled();
            expect(jCore.getActiveCanvas().RemoveCurrentMenu).toHaveBeenCalled();
        });

        it('should have no action if validation is currently running', function() {

            // Mock the progress tracker updating the toolbar buttons when validation is running
            project.isBeingValidated = true;
            mockNonSilentTracker.updateButtons();

            // After updating the toolbar, the button should no longer have action
            mockSaveValidateButton.click();
            expect(project.save).not.toHaveBeenCalled();
            expect(traverseProcess).not.toHaveBeenCalled();
            expect(jCore.getActiveCanvas().RemoveCurrentMenu).not.toHaveBeenCalled();
        });
    });

    // Test the refreshMarkers function
    describe('refreshMarkers', function() {

        beforeEach(function() {

            mockStartEvent.hasError = true;
            mockStartEvent.hasWarning = false;

            mockWaitEvent.hasError = false;
            mockWaitEvent.hasWarning = true;

            mockEndEvent.hasError = false;
            mockEndEvent.hasWarning = false;

            // Stub the inner function calls that don't need to be actually called
            // We stub each element's function separately, rather than the AdamShape parent
            // functions once, in order to be able to tell that the given function was called
            // (or not called) independently for each element
            sinon.stub(window, 'getAllElements').returns([mockStartEvent, mockWaitEvent, mockEndEvent]);
            sinon.stub(mockStartEvent, 'clearIssueMarkers');
            sinon.stub(mockStartEvent, 'showErrorMarker');
            sinon.stub(mockStartEvent, 'showWarningMarker');

            sinon.stub(mockWaitEvent, 'clearIssueMarkers');
            sinon.stub(mockWaitEvent, 'showErrorMarker');
            sinon.stub(mockWaitEvent, 'showWarningMarker');

            sinon.stub(mockEndEvent, 'clearIssueMarkers');
            sinon.stub(mockEndEvent, 'showErrorMarker');
            sinon.stub(mockEndEvent, 'showWarningMarker');
        });

        it('should call clearIssueMarkers for each element gathered from getAllElements', function() {

            refreshMarkers();

            // Elements returned from getAllElements should have their markers cleared
            expect(mockStartEvent.clearIssueMarkers).toHaveBeenCalled();
            expect(mockWaitEvent.clearIssueMarkers).toHaveBeenCalled();
            expect(mockEndEvent.clearIssueMarkers).toHaveBeenCalled();
        });

        it('should add an error marker only to elements with errors', function() {

            refreshMarkers();

            // Only elements returned from getAllElements that have errors should have their error markers shown
            expect(mockStartEvent.showErrorMarker).toHaveBeenCalled();
            expect(mockWaitEvent.showErrorMarker).not.toHaveBeenCalled();
            expect(mockEndEvent.showErrorMarker).not.toHaveBeenCalled();
        });

        it('should add a warning marker only to elements with warnings', function() {

            refreshMarkers();

            // Only elements returned from getAllElements that have warnings should have their warning markers shown
            expect(mockStartEvent.showWarningMarker).not.toHaveBeenCalled();
            expect(mockWaitEvent.showWarningMarker).toHaveBeenCalled();
            expect(mockEndEvent.showWarningMarker).not.toHaveBeenCalled();
        });
    });

    // Test the traverseProcess function
    describe('traverseProcess', function() {

        beforeEach(function() {
            sinon.stub(window, 'getStartEvents').returns(['mock_element_1', 'mock_element_2']);
            sinon.spy(window, 'getValidationTools');
            sinon.stub(window, 'initializeTraversal');
            sinon.stub(window, 'validatePathFromStartNode');
            sinon.stub(window, 'finishTraversal');
        });

        it('should get the correct type of validationTools for non-silent traversals', function() {
            traverseProcess();
            expect(getValidationTools).toHaveBeenCalledWith(undefined);
        });

        it('should get the correct type of validationTools for silent traversals', function() {
            traverseProcess(true);
            expect(getValidationTools).toHaveBeenCalledWith(true);
        });

        it('should call initializeTraversal with a non-silent progressTracker for non-silent traversals', function() {
            traverseProcess();
            expect(initializeTraversal).toHaveBeenCalledWith(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: undefined
                })
            }));
        });

        it('should call initializeTraversal with a silent progressTracker for silent traversals', function() {
            traverseProcess(true);
            expect(initializeTraversal).toHaveBeenCalledWith(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: true
                })
            }));
        });

        it('should call validatePathFromStartNode for each start node', function() {
            traverseProcess();
            expect(validatePathFromStartNode).toHaveBeenCalledWith(['mock_element_1'], jasmine.any(Object));
            expect(validatePathFromStartNode).toHaveBeenCalledWith(['mock_element_2'], jasmine.any(Object));
            expect(validatePathFromStartNode).not.toHaveBeenCalledWith(['mock_element_3'], jasmine.any(Object));
        });

        it('should call finishTraversal with a non-silent progressTracker for non-silent traversals', function() {
            traverseProcess();
            expect(finishTraversal).toHaveBeenCalledWith(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: undefined
                })
            }));
        });

        it('should call finishTraversal with a silent progressTracker for silent traversals', function() {
            traverseProcess(true);
            expect(finishTraversal).toHaveBeenCalledWith(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: true
                })
            }));
        });
    });

    describe('initializeTraversal', function() {

        beforeEach(function() {

            mockStartEvent.hasBeenQueued = true;
            mockStartEvent.currentGatewayScope = [];
            mockWaitEvent.hasBeenQueued = true;
            mockWaitEvent.currentGatewayScope = ['PARALLEL'];
            mockEndEvent.hasBeenQueued = true;
            mockEndEvent.currentGatewayScope = [];

            // Mock getAllElements to return an array of the three mock elements
            sinon.stub(window, 'getAllElements').returns([mockStartEvent, mockWaitEvent, mockEndEvent]);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockNonSilentValidationTools.progressTracker, 'incrementTotalElements');
        });

        afterEach(function() {
            project.isBeingValidated = false;
        });

        it('should delete the hasBeenQueued and currentGatewayScope attributes of each element', function() {
            initializeTraversal(mockNonSilentValidationTools);
            expect(mockStartEvent.hasBeenQueued).toBe(undefined);
            expect(mockStartEvent.currentGatewayScope).toBe(undefined);
            expect(mockWaitEvent.hasBeenQueued).toBe(undefined);
            expect(mockWaitEvent.currentGatewayScope).toBe(undefined);
            expect(mockEndEvent.hasBeenQueued).toBe(undefined);
            expect(mockEndEvent.currentGatewayScope).toBe(undefined);
        });

        it('should mark the project as currently being validated', function() {
            expect(project.isBeingValidated).toBe(false);
            initializeTraversal(mockNonSilentValidationTools);
            expect(project.isBeingValidated).toBe(true);
        });

        it('should create a fresh empty error table element', function() {
            initializeTraversal(mockNonSilentValidationTools);
            expect(currentErrorTable.nodeName).toBe('TBODY');
            expect(currentErrorTable.rows.length).toBe(0);
        });

        it('should increment the total element count in the progressTracker', function() {
            initializeTraversal(mockNonSilentValidationTools);
            expect(mockNonSilentValidationTools.progressTracker.incrementTotalElements).toHaveBeenCalled();
        });
    });

    describe('finishTraversal', function() {

        beforeEach(function() {

            // Set one of the mock elements to have been queued
            mockStartEvent.hasBeenQueued = true;
            mockWaitEvent.hasBeenQueued = false;
            mockEndEvent.hasBeenQueued = false;

            // Mock getAllElements to return an array of the three mock elements
            sinon.stub(window, 'getAllElements').returns([mockStartEvent, mockWaitEvent, mockEndEvent]);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(App.api, 'triggerBulkCall');
            sinon.stub(mockNonSilentValidationTools, 'createWarning');
            sinon.stub(mockNonSilentValidationTools.progressTracker, 'incrementSettingsGathered');
        });

        it('should generate a warning for elements that were not reached during traversal', function() {
            finishTraversal(mockNonSilentValidationTools);
            expect(mockNonSilentValidationTools.createWarning).toHaveBeenCalledWith(mockWaitEvent,
                'LBL_PMSE_ERROR_ELEMENT_UNREACHABLE');
            expect(mockNonSilentValidationTools.createWarning).toHaveBeenCalledWith(mockEndEvent,
                'LBL_PMSE_ERROR_ELEMENT_UNREACHABLE');
        });

        it('should not generate a warning for elements that were reached during traversal', function() {
            finishTraversal(mockNonSilentValidationTools);
            expect(mockNonSilentValidationTools.createWarning).not.toHaveBeenCalledWith(mockStartEvent,
                'LBL_PMSE_ERROR_ELEMENT_UNREACHABLE');
        });

        it('should trigger the bulk API call to start retrieving element settings data', function() {
            finishTraversal(mockNonSilentValidationTools);
            expect(App.api.triggerBulkCall).toHaveBeenCalled();
        });

        it('should increment the total element settings gathered count in the progressTracker', function() {
            finishTraversal(mockNonSilentValidationTools);
            expect(mockNonSilentValidationTools.progressTracker.incrementSettingsGathered).toHaveBeenCalled();
        });
    });

    // Test the getAllElements function
    describe('getAllElements', function() {

        var result;

        it('should return all user-placed, non-comment elements', function() {
            result = getAllElements();
            expect(result).toContain(mockStartEvent);
            expect(result).toContain(mockWaitEvent);
            expect(result).toContain(mockEndEvent);
            expect(result).not.toContain(mockSelectionContainer);
            expect(result).not.toContain(mockComment);
        });
    });

    // Test the getStartEvents function
    describe('getStartEvents', function() {

        var result;

        it('should return only start events', function() {
            result = getStartEvents();
            expect(result).toContain(mockStartEvent);
            expect(result).not.toContain(mockWaitEvent);
            expect(result).not.toContain(mockEndEvent);
            expect(result).not.toContain(mockSelectionContainer);
            expect(result).not.toContain(mockComment);
        });
    });

    // Test the setGatewayScope function
    describe('setGatewayScope', function() {

        var mockDivergingGateway;
        var mockConvergingGateway;

        beforeEach(function() {
            mockDivergingGateway = new AdamGateway();
            mockDivergingGateway.setDirection('diverging');
            mockDivergingGateway.setGatewayType('parallel');

            mockConvergingGateway = new AdamGateway();
            mockConvergingGateway.setDirection('converging');
            mockConvergingGateway.setGatewayType(('exclusive'));
        });

        it('should copy gateway scope from currElement to destElement if both elements are non-gateway', function() {
            mockWaitEvent.currentGatewayScope = ['PARALLEL', 'EXCLUSIVE'];
            delete mockEndEvent.currentGatewayScope;
            expect(mockEndEvent.currentGatewayScope).not.toEqual(mockWaitEvent.currentGatewayScope);
            setGatewayScope(mockWaitEvent, mockEndEvent);
            expect(mockEndEvent.currentGatewayScope).toEqual(mockWaitEvent.currentGatewayScope);
        });

        it('should add a diverging gateway\'s type to the currentGatewayScope of the dest element', function() {
            mockDivergingGateway.currentGatewayScope = ['EXCLUSIVE'];
            delete mockWaitEvent.currentGatewayScope;
            expect(mockWaitEvent.currentGatewayScope).toBe(undefined);
            setGatewayScope(mockDivergingGateway, mockWaitEvent);
            expect(mockWaitEvent.currentGatewayScope).toEqual(['PARALLEL', 'EXCLUSIVE']);
        });

        it('should remove the first element of the currentGatewayScope array for converging gateways', function() {
            mockConvergingGateway.currentGatewayScope = ['EXCLUSIVE'];
            delete mockWaitEvent.currentGatewayScope;
            expect(mockWaitEvent.currentGatewayScope).toBe(undefined);
            setGatewayScope(mockConvergingGateway, mockWaitEvent);
            expect(mockWaitEvent.currentGatewayScope).toEqual([]);
        });
    });

    // Test the validatePathFromStartNode function
    describe('validatePathFromStartNode', function() {

        var mockQueue;

        beforeEach(function() {

            // Mock a traversal node queue starting from a start event
            mockQueue = [mockStartEvent];

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(window, 'processNextElement');
        });

        it('should mark the start element as queued', function() {
            expect(mockStartEvent.hasBeenQueued).toBe(undefined);
            validatePathFromStartNode(mockQueue, mockNonSilentValidationTools);
            expect(mockStartEvent.hasBeenQueued).toBe(true);
        });

        it('should set the start element\'s currentGatewayScope to an empty array', function() {
            expect(mockStartEvent.currentGatewayScope).toBe(undefined);
            validatePathFromStartNode(mockQueue, mockNonSilentValidationTools);
            expect(mockStartEvent.currentGatewayScope).toEqual([]);
        });

        it('should call processNextElement for each element in the queue', function() {
            mockQueue.push(mockWaitEvent);
            mockQueue.push(mockEndEvent);
            validatePathFromStartNode(mockQueue, mockNonSilentValidationTools);
            expect(processNextElement).toHaveBeenCalledWith(mockStartEvent, mockQueue, mockNonSilentValidationTools);
            expect(processNextElement).toHaveBeenCalledWith(mockWaitEvent, mockQueue, mockNonSilentValidationTools);
            expect(processNextElement).toHaveBeenCalledWith(mockEndEvent, mockQueue, mockNonSilentValidationTools);
        });

        it('should empty the queue by the time it is finished', function() {
            mockQueue.push(mockWaitEvent);
            mockQueue.push(mockEndEvent);
            expect(mockQueue.length).toBe(3);
            validatePathFromStartNode(mockQueue, mockNonSilentValidationTools);
            expect(mockQueue.length).toBe(0);
        });
    });

    // Test the processNextElement function
    describe('processNextElement', function() {

        beforeEach(function() {

            // Stub the getDestElements function for the mock start event to return the
            // mock wait event and mock end event as destination elements
            sinon.stub(mockStartEvent, 'getDestElements').returns([mockWaitEvent, mockEndEvent]);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(AdamShape.prototype, 'validate');
            sinon.stub(window, 'queueConnectedElement');
        });

        it('should validate the current element', function() {
            processNextElement(mockStartEvent, [], mockNonSilentValidationTools);
            expect(mockStartEvent.validate).toHaveBeenCalledWith(mockNonSilentValidationTools);
        });

        it('should add each connected element to the queue', function() {
            processNextElement(mockStartEvent, [], mockNonSilentValidationTools);
            expect(queueConnectedElement).toHaveBeenCalledWith(mockStartEvent, mockWaitEvent, []);
            expect(queueConnectedElement).toHaveBeenCalledWith(mockStartEvent, mockEndEvent, []);
        });
    });

    // Test the queueConnectedElement function
    describe('queueConnectedElement', function() {

        var mockQueue;

        beforeEach(function() {

            // Mock the queue to be empty going into the function
            mockQueue = [];

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(window, 'setGatewayScope');
        });

        it('should set the gateway scope of the destination element', function() {
            queueConnectedElement(mockStartEvent, mockWaitEvent, mockQueue);
            expect(setGatewayScope).toHaveBeenCalledWith(mockStartEvent, mockWaitEvent);
        });

        it('should push the destination element onto the queue', function() {
            expect(mockQueue).toEqual([]);
            queueConnectedElement(mockStartEvent, mockWaitEvent, mockQueue);
            expect(mockQueue).toEqual([mockWaitEvent]);
        });

        it('should mark the destination element as queued', function() {
            expect(mockWaitEvent.hasBeenQueued).toBe(undefined);
            queueConnectedElement(mockStartEvent, mockWaitEvent, mockQueue);
            expect(mockWaitEvent.hasBeenQueued).toBe(true);
        });
    });

    // Test the getValidationTools function
    describe('getValidationTools', function() {

        beforeEach(function() {

            // Spy on the getValidationTools function, so that we can compare its return value
            sinon.spy(window, 'getValidationTools');
        });

        it('should return an object with a silent progressTracker for silent traversals', function() {
            expect(getValidationTools(true)).toEqual(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: true
                }),
            }));
        });

        it('should return an object with a non-silent progressTracker for non-silent traversals', function() {
            expect(getValidationTools()).toEqual(jasmine.objectContaining({
                'progressTracker': jasmine.objectContaining({
                    silent: undefined
                })
            }));
        });
    });

    // Test the ValidationProgressTracker object
    describe('ValidationProgressTracker', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(myLayout, 'close');
            sinon.stub(myLayout, 'open');
            sinon.stub(myLayout, 'toggle');
        });

        // Test the start function of the ValidationProgressTracker
        describe('start', function() {

            beforeEach(function() {

                // Stub the inner function calls that don't need to be actually called
                sinon.stub(mockSilentTracker, 'showModal');
                sinon.stub(mockSilentTracker, 'updateButtons');
            });

            it('should mark the project as currenly being validated', function() {
                project.isBeingValidated = false;
                mockSilentTracker.start();
                expect(project.isBeingValidated).toBe(true);
            });

            it('should update the buttons on the canvas toolbar', function() {
                mockSilentTracker.start();
                expect(mockSilentTracker.updateButtons).toHaveBeenCalled();
            });

            it('should replace currentErrorTable with a new, fresh tbody', function() {
                currentErrorTable.insertRow(0);
                expect(currentErrorTable.rows.length).toBe(1);
                mockSilentTracker.start();
                expect(currentErrorTable.rows.length).toBe(0);
            });

            it('should show the "Refreshing error list..." indicator', function() {
                expect($('#refreshing-errors').hasClass('show')).toBe(false);
                mockSilentTracker.start();
                expect($('#refreshing-errors').hasClass('show')).toBe(true);
            });

            it('should update the progress modal being shown to the user', function() {
                mockSilentTracker.start();
                expect(mockSilentTracker.showModal).toHaveBeenCalled();
            });
        });

        describe('incrementTotalElements', function() {
            it('should increment the number of total elements by 1', function() {
                expect(mockSilentTracker.totalElements).toBe(0);
                mockSilentTracker.incrementTotalElements();
                expect(mockSilentTracker.totalElements).toBe(1);
            });
        });

        describe('incrementSettingsGathered', function() {

            beforeEach(function() {

                // Stub the inner function calls that don't need to be actually called
                sinon.stub(mockSilentTracker, 'startValidating');
            });

            it('should increment the number of settings gathered by 1', function() {
                expect(mockSilentTracker.numSettingsGathered).toBe(0);
                mockSilentTracker.incrementSettingsGathered();
                expect(mockSilentTracker.numSettingsGathered).toBe(1);
            });

            it('should call startValidating if totalElements === numSettingsGathered afterward', function() {
                mockSilentTracker.incrementTotalElements();
                expect(mockSilentTracker.totalElements).toBe(1);
                expect(mockSilentTracker.numSettingsGathered).toBe(0);
                mockSilentTracker.incrementSettingsGathered();
                expect(mockSilentTracker.startValidating).toHaveBeenCalled();
            });
        });

        describe('startValidating', function() {

            beforeEach(function() {
                sinon.stub(mockSilentTracker, 'incrementTotalValidations');
                sinon.stub(mockSilentTracker, 'showModal');
                sinon.stub(App.api, 'triggerBulkCall');
                sinon.stub(mockSilentTracker, 'incrementValidated');
            });

            it('should increment the total number of validations needed', function() {
                mockSilentTracker.startValidating();
                expect(mockSilentTracker.incrementTotalValidations).toHaveBeenCalled();
            });

            it('should update the modal displayed to the user after incrementing validations', function() {
                mockSilentTracker.startValidating();
                expect(mockSilentTracker.showModal).toHaveBeenCalled();
                expect(mockSilentTracker.incrementTotalValidations).toHaveBeenCalledBefore(
                    mockSilentTracker.showModal);
            });

            it('should trigger the validation bulk call after incrementing total validations', function() {
                mockSilentTracker.startValidating();
                expect(App.api.triggerBulkCall).toHaveBeenCalledWith('validate_element_settings');
                expect(mockSilentTracker.incrementTotalValidations).toHaveBeenCalledBefore(App.api.triggerBulkCall);
            });

            it('should increment the count of validations completed only after the bulk call is made', function() {
                mockSilentTracker.startValidating();
                expect(mockSilentTracker.incrementValidated).toHaveBeenCalled();
                expect(App.api.triggerBulkCall).toHaveBeenCalledBefore(mockSilentTracker.incrementValidated);
            });
        });

        describe('incrementTotalValidations', function() {
            it('should increment the number of total elements by 1', function() {
                expect(mockSilentTracker.totalValidations).toBe(0);
                mockSilentTracker.incrementTotalValidations();
                expect(mockSilentTracker.totalValidations).toBe(1);
            });
        });

        describe('incrementValidated', function() {

            beforeEach(function() {

                // Stub the inner function calls that don't need to be actually called
                sinon.stub(mockSilentTracker, 'finish');
            });

            it('should increment the number of settings validated by 1', function() {
                expect(mockSilentTracker.numValidated).toBe(0);
                mockSilentTracker.incrementValidated();
                expect(mockSilentTracker.numValidated).toBe(1);
            });

            it('should call finish if totalValidations === numValidated afterward', function() {
                mockSilentTracker.incrementTotalValidations();
                expect(mockSilentTracker.totalValidations).toBe(1);
                expect(mockSilentTracker.numValidated).toBe(0);
                mockSilentTracker.incrementValidated();
                expect(mockSilentTracker.finish).toHaveBeenCalled();
            });
        });

        describe('finish', function() {

            beforeEach(function() {

                // Mock the "Refreshing errors..." indicator in the error pane being visible
                $('#refreshing-errors').addClass('show');

                // Stub the inner function calls that don't need to be actually called
                sinon.stub(window, 'refreshMarkers');
                sinon.stub(mockSilentTracker, 'showModal');
                sinon.stub(mockSilentTracker, 'updateButtons');
            });

            it('should remove the "Refreshing errors..." indicator from the error pane', function() {
                expect($('#refreshing-errors').hasClass('show')).toBe(true);
                mockSilentTracker.finish();
                expect($('#refreshing-errors').hasClass('show')).toBe(false);
            });

            it('should replace an empty error table with the new error table if errors are found', function() {
                currentErrorTable.insertRow(0);
                currentErrorTable.insertRow(1);
                expect($('#Error-table').find('tbody').find('tr').length).toBe(0);
                mockSilentTracker.finish();
                expect($('#Error-table').find('tbody').find('tr').length).toBe(2);
            });

            it('should replace a non-empty error table with an empty error table if no errors are found', function() {
                currentErrorTable.insertRow(0);
                currentErrorTable.insertRow(1);
                mockSilentTracker.finish();
                expect($('#Error-table').find('tbody').find('tr').length).toBe(2);
                currentErrorTable = document.createElement('tbody');
                mockSilentTracker.finish();
                expect($('#Error-table').find('tbody').find('tr').length).toBe(0);
            });

            it('should close the south error pane if no errors are found', function() {
                mockSilentTracker.finish();
                expect(myLayout.close).toHaveBeenCalledWith('south');
                expect(myLayout.open).not.toHaveBeenCalled();
            });

            it('should open the south error pane if errors are found and validation is non-silent', function() {
                currentErrorTable.insertRow(0);
                mockNonSilentTracker.finish();
                expect(myLayout.open).toHaveBeenCalledWith('south');
                expect(myLayout.close).not.toHaveBeenCalled();
            });

            it('should not open the south error pane if errors are found and validation is silent', function() {
                currentErrorTable.insertRow(0);
                mockSilentTracker.finish();
                expect(myLayout.open).not.toHaveBeenCalled();
                expect(myLayout.close).not.toHaveBeenCalled();
            });

            it('should refresh the error markers on the canvas', function() {
                mockSilentTracker.finish();
                expect(window.refreshMarkers).toHaveBeenCalled();
            });

            it('should update the progress modal being shown to the user', function() {
                mockSilentTracker.finish();
                expect(mockSilentTracker.showModal).toHaveBeenCalled();
            });

            it('should mark the project as no longer being validated', function() {
                project.isBeingValidated = true;
                mockSilentTracker.finish();
                expect(project.isBeingValidated).toBe(false);
            });

            it('should update the toolbar buttons', function() {
                mockSilentTracker.finish();
                expect(mockSilentTracker.updateButtons).toHaveBeenCalled();
            });
        });

        describe('showModal', function() {

            beforeEach(function() {

                // Dismiss any alerts that were already open
                App.alert.dismissAll();

                // Spy on the alert show function
                sinon.spy(App.alert, 'show');

                // Stub the finish function so it isn't accidentally called, which can affect which modal is open
                sinon.stub(mockNonSilentTracker, 'finish');
            });

            afterEach(function() {

                // Dismiss any alerts that opened during the tests
                App.alert.dismissAll();
            });

            it('should show the correct modal for the first phase of non-silent validation', function() {
                mockNonSilentTracker.incrementTotalElements();
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
                mockNonSilentTracker.showModal();
                expect(App.alert.get('getting_element_settings')).not.toBe(undefined);
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                expect(App.alert.get('validation_results')).toBe(undefined);
            });

            it('should not show any modal for the first phase of silent validation', function() {
                mockSilentTracker.incrementTotalElements();
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
                mockSilentTracker.showModal();
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                expect(App.alert.get('validation_results')).toBe(undefined);
            });

            it('should show the correct modal for the second phase of non-silent validation', function() {
                mockNonSilentTracker.incrementTotalValidations();
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                mockNonSilentTracker.showModal();
                expect(App.alert.get('validating_element_settings')).not.toBe(undefined);
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
                expect(App.alert.get('validation_results')).toBe(undefined);
            });

            it('should not show any modal for the second phase of silent validation', function() {
                mockSilentTracker.incrementTotalValidations();
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                mockSilentTracker.showModal();
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
                expect(App.alert.get('validation_results')).toBe(undefined);
            });

            it('should show the correct modal for a finished non-silent validation', function() {
                mockNonSilentTracker.incrementTotalValidations();
                mockNonSilentTracker.incrementValidated();
                expect(App.alert.get('validation_results')).toBe(undefined);
                mockNonSilentTracker.showModal();
                expect(App.alert.get('validation_results')).not.toBe(undefined);
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
            });

            it('should not show any modal for a finished silent validation', function() {
                mockSilentTracker.incrementTotalValidations();
                mockSilentTracker.incrementValidated();
                expect(App.alert.get('validation_results')).toBe(undefined);
                mockSilentTracker.showModal();
                expect(App.alert.get('validation_results')).toBe(undefined);
                expect(App.alert.get('validating_element_settings')).toBe(undefined);
                expect(App.alert.get('getting_element_settings')).toBe(undefined);
            });
        });

        describe('updateButtons', function() {

            beforeEach(function() {

                // Stub the inner function calls that don't need to be actually called
                sinon.stub(mockSilentTracker, 'clearButtonStyleAndAction');
                sinon.stub(mockSilentTracker, 'updateValidateButton');
                sinon.stub(mockSilentTracker, 'updateSaveValidateButton');
                sinon.stub(mockSilentTracker, 'updateErrorPaneToggleButton');
            });

            it('should clear all style and action from the validator buttons', function() {
                mockSilentTracker.updateButtons();
                expect(mockSilentTracker.clearButtonStyleAndAction).toHaveBeenCalled();
            });

            it('should update the validate button', function() {
                mockSilentTracker.updateButtons();
                expect(mockSilentTracker.updateValidateButton).toHaveBeenCalled();
            });

            it('should update the save+validate button', function() {
                mockSilentTracker.updateButtons();
                expect(mockSilentTracker.updateSaveValidateButton).toHaveBeenCalled();
            });

            it('should update the error pane toggle button', function() {
                mockSilentTracker.updateButtons();
                expect(mockSilentTracker.updateErrorPaneToggleButton).toHaveBeenCalled();
            });
        });

        describe('clearButtonStyleAndAction', function() {

            beforeEach(function() {
                $('#ButtonToggleErrorPane').click(function() {
                    // Function stub
                });
            });

            it('should remove style from the process validator buttons', function() {
                expect($('#ButtonValidate > i').attr('class')).not.toEqual('');
                expect($('#ButtonSaveValidate > i').attr('class')).not.toEqual('');
                expect($('#ButtonToggleErrorPane > i').attr('class')).not.toEqual('');
                mockSilentTracker.clearButtonStyleAndAction();
                expect($('#ButtonValidate > i').attr('class')).toEqual('');
                expect($('#ButtonSaveValidate > i').attr('class')).toEqual('');
                expect($('#ButtonToggleErrorPane > i').attr('class')).toEqual('');
            });

            it('should remove action from the process validator buttons', function() {
                expect($._data(document.getElementById('ButtonValidate'), 'events')).not.toBe(undefined);
                expect($._data(document.getElementById('ButtonSaveValidate'), 'events')).not.toBe(undefined);
                expect($._data(document.getElementById('ButtonToggleErrorPane'), 'events')).not.toBe(undefined);
                mockSilentTracker.clearButtonStyleAndAction();
                expect($._data(document.getElementById('ButtonValidate'), 'events')).toBe(undefined);
                expect($._data(document.getElementById('ButtonSaveValidate'), 'events')).toBe(undefined);
                expect($._data(document.getElementById('ButtonToggleErrorPane'), 'events')).toBe(undefined);
            });
        });

        describe('updateValidateButton', function() {
            it('should give the button the correct action and style while the project is validating', function() {
                project.isBeingValidated = true;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateValidateButton();
                expect($._data(document.getElementById('ButtonValidate'), 'events')).toBe(undefined);
                expect($('#ButtonValidate > i').attr('class'))
                    .toEqual('sicon sicon-lg sicon-check-circle-lg check-square-off');
            });

            it('should give the button the correct style and no action if the project is not validating', function() {
                project.isBeingValidated = false;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateValidateButton();
                expect($._data(document.getElementById('ButtonValidate'), 'events')).not.toBe(undefined);
                expect($('#ButtonValidate > i').attr('class'))
                    .toEqual('sicon sicon-lg sicon-check-circle-lg check-square-on');
            });
        });

        describe('updateSaveValidateButton', function() {
            it('should give the button the correct action and style while the project is validating', function() {
                project.isBeingValidated = true;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateSaveValidateButton();
                expect($._data(document.getElementById('ButtonSaveValidate'), 'events')).toBe(undefined);
                expect($('#ButtonSaveValidate > i').filter(':first').attr('class')).toEqual(
                    'sicon sicon-sm sicon-save-lg save-off');
                expect($('#ButtonSaveValidate > i').filter(':last').attr('class')).toEqual(
                    'sicon sicon-sm sicon-check-circle-lg check-square-off');
            });

            it('should give the button the correct style and no action if the project is not validating', function() {
                project.isBeingValidated = false;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateSaveValidateButton();
                expect($._data(document.getElementById('ButtonSaveValidate'), 'events')).not.toBe(undefined);
                expect($('#ButtonSaveValidate > i').filter(':first').attr('class')).toEqual(
                    'sicon sicon-sm sicon-save-lg save-on');
                expect($('#ButtonSaveValidate > i').filter(':last').attr('class')).toEqual(
                    'sicon sicon-sm sicon-check-circle-lg check-square-on');
            });
        });

        describe('updateErrorPaneToggleButton', function() {

            it('should set the correct button style if errors have been found', function() {
                $('#Error-table').find('tbody').append('<tr><td>I am an error</td></tr>');
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                expect($('#ButtonToggleErrorPane > i').attr('class')).toEqual(
                    'sicon sicon-lg sicon-warning-lg exclamation-triangle-on');
            });

            it('should set the correct button action if errors have been found', function() {
                $('#Error-table').find('tbody').append('<tr><td>I am an error</td></tr>');
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                $('#ButtonToggleErrorPane').click();
                expect(myLayout.toggle).toHaveBeenCalled();
            });

            it('should set the correct button style if no errors have been found', function() {
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                expect($('#ButtonToggleErrorPane > i').attr('class')).toEqual(
                    'sicon sicon-lg sicon-warning-lg exclamation-triangle-off');
            });

            it('should set the correct button action if no errors have been found', function() {
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                expect($._data(document.getElementById('ButtonToggleErrorPane'), 'events')).toBe(undefined);
                $('#ButtonToggleErrorPane').click();
                expect(myLayout.toggle).not.toHaveBeenCalled();
            });

            it('should set the correct button tooltip if the project is being validated', function() {
                project.isBeingValidated = true;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                expect($('#ButtonToggleErrorPane').attr('data-original-title')).toEqual(
                    translate('LBL_PMSE_VALIDATOR_TOOLTIP_IN_PROGRESS'));
            });

            it('should set the correct button tooltip if the project is not being validated', function() {

                currentErrorTable.insertRow(0);
                currentErrorTable.insertRow(1);
                currentErrorTable.insertRow(2);

                project.isBeingValidated = false;
                mockNonSilentTracker.clearButtonStyleAndAction();
                mockNonSilentTracker.updateErrorPaneToggleButton();
                expect($('#ButtonToggleErrorPane').attr('data-original-title')).toEqual(
                    3 + translate('LBL_PMSE_VALIDATOR_TOOLTIP_ISSUES'));
            });
        });
    });

    describe('validateNumberOfEdges', function() {

        var mockIncoming;
        var mockOutgoing;
        var returnMockIncoming;
        var returnMockOutgoing;

        beforeEach(function() {

            mockIncoming = [];
            mockOutgoing = [];

            // Create two functions to return the current values of mockIncoming and mockOutgoing
            // Allows us to change the return values on different tests
            returnMockIncoming = function() {
                return mockIncoming;
            };
            returnMockOutgoing = function() {
                return mockOutgoing;
            };

            // Stub the getSourceElements to return the incoming
            sinon.stub(AdamShape.prototype, 'getSourceElements').callsFake(returnMockIncoming);

            // Stub the getDestElements to return the outgoing
            sinon.stub(AdamShape.prototype, 'getDestElements').callsFake(returnMockOutgoing);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(window, 'createWarning');
        });

        it('should generate a warning if incoming edges < minumum incoming edges', function() {
            mockIncoming = [mockStartEvent, mockWaitEvent];
            mockOutgoing = [];
            validateNumberOfEdges(3, null, null, null, mockEndEvent);
            expect(createWarning).toHaveBeenCalledWith(mockEndEvent, 'LBL_PMSE_ERROR_FLOW_INCOMING_MINIMUM');
        });

        it('should not generate a warning if incoming edges >= minumum incoming edges', function() {
            mockIncoming = [mockStartEvent, mockWaitEvent];
            mockOutgoing = [];
            validateNumberOfEdges(2, null, null, null, mockEndEvent);
            expect(createWarning).not.toHaveBeenCalledWith(mockEndEvent, 'LBL_PMSE_ERROR_FLOW_INCOMING_MINIMUM');
        });

        it('should generate a warning if incomings edges > maximum incoming edges', function() {
            mockIncoming = [mockStartEvent, mockWaitEvent];
            mockOutgoing = [];
            validateNumberOfEdges(null, 1, null, null, mockEndEvent);
            expect(createWarning).toHaveBeenCalledWith(mockEndEvent, 'LBL_PMSE_ERROR_FLOW_INCOMING_MAXIMUM');
        });

        it('should not generate a warning if incoming edges <= maximum incoming edges', function() {
            mockIncoming = [mockStartEvent, mockWaitEvent];
            mockOutgoing = [];
            validateNumberOfEdges(null, 2, null, null, mockEndEvent);
            expect(createWarning).not.toHaveBeenCalledWith(mockEndEvent, 'LBL_PMSE_ERROR_FLOW_INCOMING_MAXIMUM');
        });

        it('should generate a warning if outgoing edges < minimum outgoing edges', function() {
            mockIncoming = [];
            mockOutgoing = [mockWaitEvent, mockEndEvent];
            validateNumberOfEdges(null, null, 3, null, mockStartEvent);
            expect(createWarning).toHaveBeenCalledWith(mockStartEvent, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MINIMUM');
        });

        it('should not generate a warning if outgoing edges >= minimum outgoing edges', function() {
            mockIncoming = [];
            mockOutgoing = [mockWaitEvent, mockEndEvent];
            validateNumberOfEdges(null, null, 2, null, mockStartEvent);
            expect(createWarning).not.toHaveBeenCalledWith(mockStartEvent, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MINIMUM');
        });

        it('should generate a warning if outgoing edges > maximum outgoing edges', function() {
            mockIncoming = [];
            mockOutgoing = [mockWaitEvent, mockEndEvent];
            validateNumberOfEdges(null, null, null, 1, mockStartEvent);
            expect(createWarning).toHaveBeenCalledWith(mockStartEvent, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MAXIMUM');
        });

        it('should not generate a warning if outgoing edges <= maximum outgoing edges', function() {
            mockIncoming = [];
            mockOutgoing = [mockWaitEvent, mockEndEvent];
            validateNumberOfEdges(null, null, null, 2, mockStartEvent);
            expect(createWarning).not.toHaveBeenCalledWith(mockStartEvent, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MAXIMUM');
        });

        it('should not generate a warning for any null min/max arguments', function() {
            mockIncoming = [];
            mockOutgoing = [mockWaitEvent, mockEndEvent];
            validateNumberOfEdges(null, null, null, null, mockStartEvent);
            expect(createWarning).not.toHaveBeenCalled();
        });
    });

    describe('validateAtom', function() {
        var mockSearchInfo;
        var mockResult;
        var mockAPICall;
        var correctURL;

        beforeEach(function() {
            // Mock a basic searchInfo object to return from a getSearchInfo stub
            mockSearchInfo = {
                url: undefined,
                text: undefined,
                key: undefined
            };

            // Mock the API call result object
            mockResult = {result: true};

            // Mock the correct API URL so that we can change it to see that error
            // code is successfully run if the API endpoint is not correct
            correctURL = '';

            // Mock the API call function to simulate the results of an API call
            mockAPICall = function(action, url, attributes, callbacks, options) {
                if (url === correctURL) {
                    callbacks.success(mockResult);
                } else {
                    callbacks.error(mockResult);
                }
                callbacks.complete(mockResult);
            };

            // Replace the function calls with the stubbed functions
            sinon.stub(window, 'getSearchInfo').callsFake(function() {
                return mockSearchInfo;
            });
            sinon.stub(App.api, 'call').callsFake(mockAPICall);

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(window, 'createWarning');
            sinon.stub(mockSilentTracker, 'incrementTotalValidations');
            sinon.stub(mockSilentTracker, 'incrementValidated');
        });

        it('should increment the total validations number before the API call', function() {
            mockSearchInfo.url = 'mockURL';
            mockSearchInfo.key = 'mockKey';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(mockSilentTracker.incrementTotalValidations).toHaveBeenCalledBefore(App.api.call);
        });

        it('should App.api.call with the correct parameters if the searchInfo has a URL', function() {
            mockSearchInfo.url = 'mockURL';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(App.api.call).toHaveBeenCalledWith('read', 'mockURL', null, jasmine.any(Object), {
                'bulk': 'validate_element_settings'
            });
        });

        it('should not validate the atom if no searchInfo URL exists', function() {
            mockSearchInfo.url = '';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(mockSilentTracker.incrementTotalValidations).not.toHaveBeenCalled();
            expect(App.api.call).not.toHaveBeenCalled();
        });

        it('should not generate an error if the URL is correct and the key exists in the system', function() {
            mockSearchInfo.url = 'mockURL';
            correctURL = 'mockURL';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(createWarning).not.toHaveBeenCalled();
        });

        it('should generate an error if URL is correct, but the key does not exist in the result data', function() {
            mockSearchInfo.url = 'mockURL';
            correctURL = 'mockURL';
            mockResult = {result: false};
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(createWarning).toHaveBeenCalled();
        });

        it('should generate an error if the URL is incorrect', function() {
            mockSearchInfo.url = 'wrongURL';
            correctURL = 'correctURL';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(createWarning).toHaveBeenCalled();
        });

        it('should increment the validated count after calling the success function (URL is correct)', function() {
            mockSearchInfo.url = 'mockURL';
            correctURL = 'mockURL';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(mockSilentTracker.incrementValidated).toHaveBeenCalled();
        });

        it('should increment the validated count after calling the error function (URL is incorrect)', function() {
            mockSearchInfo.url = 'wrongURL';
            correctURL = 'correctURL';
            validateAtom(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockStartEvent,
                mockSilentValidationTools
            );
            expect(mockSilentTracker.incrementValidated).toHaveBeenCalled();
        });
    });

    describe('getSearchInfo', function() {
        it('should return the correct information for atoms of type "MODULE"', function() {
            expect(getSearchInfo({type: 'MODULE', module: 'Accounts', field: 'name', value: '123'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/fields/Accounts?key=name&base_module=Accounts'),
                text: jasmine.any(String),
                backupSearchFunction: jasmine.any(Function)
            });
        });

        it('should return the correct information for atoms of type "VARIABLE"', function() {
            expect(getSearchInfo({type: 'VARIABLE', module: 'Accounts', value: 'name'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/fields/Accounts?key=name&base_module=Accounts'),
                text: jasmine.any(String),
                backupSearchFunction: jasmine.any(Function)
            });
        });

        it('should return the correct information for atoms of type "recipient"', function() {
            expect(getSearchInfo({type: 'recipient', module: 'Accounts', value: 'employees'})).toEqual({
                url: App.api.buildURL(
                    'pmse_Project/validateCrmData/fields/Accounts?key=employees&base_module=Accounts'
                ),
                text: jasmine.any(String),
                backupSearchFunction: jasmine.any(Function)
            });
        });

        it('should return the correct information for atoms of type "USER_IDENTITY"', function() {
            expect(getSearchInfo({type: 'USER_IDENTITY', field: 'current_user', value: 'seed_sally_id'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/users/?key=seed_sally_id'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "USER_ROLE"', function() {
            expect(getSearchInfo({type: 'USER_ROLE', field: 'current_user', value: '5e03ac3c-cf10-11e8-9ffc'}))
                .toEqual({
                    url: App.api.buildURL('pmse_Project/validateCrmData/rolesList/?key=5e03ac3c-cf10-11e8-9ffc'),
                    text: jasmine.any(String),
                    backupSearchFunction: null
                });
        });

        it('should return the correct information for atoms of type "role"', function() {
            expect(getSearchInfo({type: 'role', value: '5e03ac3c-cf10-11e8-9ffc'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/rolesList/?key=5e03ac3c-cf10-11e8-9ffc'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "RELATIONSHIP"', function() {
            expect(getSearchInfo({type: 'RELATIONSHIP', value: '5e03ac3c-cf10-11e8-9ffc'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/related/Accounts?key=5e03ac3c-cf10-11e8-9ffc'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "user"', function() {
            expect(getSearchInfo({type: 'user', module: 'Accounts', value: 'record_creator'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/related/Accounts?key=Accounts'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "TEAM"', function() {
            expect(getSearchInfo({type: 'TEAM', value: 'mock_team'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/teams/all?key=mock_team'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "team"', function() {
            expect(getSearchInfo({type: 'team', value: 'east'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/teams/all?key=east'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "CONTROL"', function() {
            expect(getSearchInfo({type: 'CONTROL', field: '12345', value: 'Approved'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/activities/mockProjectID?key=12345'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "ALL_BUSINESS_RULES"', function() {
            expect(getSearchInfo({type: 'ALL_BUSINESS_RULES', value: 'mockBusinessRuleID'})).toEqual({
                url: App.api.buildURL('pmse_Project/validateCrmData/rulesets/mockProjectID?key=mockBusinessRuleID'),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "BUSINESS_RULES"', function() {
            expect(getSearchInfo({type: 'BUSINESS_RULES', field: '51d2-cf34-11e8-bbb5', value: '1'})).toEqual({
                url: App.api.buildURL(
                    'pmse_Project/validateCrmData/businessrules/mockProjectID?key=51d2-cf34-11e8-bbb5'
                ),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return the correct information for atoms of type "TEMPLATE"', function() {
            expect(getSearchInfo({type: 'TEMPLATE', value: 'a44737ee-cf2f-11e8-ba31'})).toEqual({
                url: App.api.buildURL(
                    'pmse_Project/validateCrmData/emailtemplates/Accounts?key=a44737ee-cf2f-11e8-ba31'
                ),
                text: jasmine.any(String),
                backupSearchFunction: null
            });
        });

        it('should return null for relationship validation that has no field criteria', function() {
            expect(getSearchInfo({type: 'MODULE', relation: 'Added'})).toEqual(null);
        });
    });

    describe('createWarning', function() {

        beforeEach(function() {
            sinon.stub(window, 'createError');
        });

        it('should call createError with the correct flag for warning', function() {
            createWarning(mockStartEvent, 'This is a mock warning', 'mock_field');
            expect(createError).toHaveBeenCalledWith(mockStartEvent, 'This is a mock warning', 'mock_field', true);
        });
    });

    describe('createError', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(jCore.Canvas.prototype, 'emptyCurrentSelection');
            sinon.stub(jCore.Canvas.prototype, 'addToSelection');
            sinon.stub(window, 'centerCanvasOnElement');
        });

        it('should add a row to the error table with the correct element name in the first cell', function() {
            expect(currentErrorTable.rows.length).toBe(0);
            createError(mockStartEvent, 'mockErrorLabel', 'mockField');
            expect(currentErrorTable.rows.length).toBe(1);
            expect(currentErrorTable.rows[0].cells[0].textContent).toEqual('mockName');
        });

        it('should add the correct onclick action to the element name in the first cell', function() {
            createError(mockStartEvent, 'mockErrorLabel', 'mockField');
            currentErrorTable.rows[0].cells[0].childNodes[0].click();
            expect(centerCanvasOnElement).toHaveBeenCalledWith(mockStartEvent);
            expect(canvas.emptyCurrentSelection).toHaveBeenCalled();
            expect(canvas.addToSelection).toHaveBeenCalledWith(mockStartEvent);
        });

        it('should add the correct icon for warnings in the second cell', function() {
            createError(mockStartEvent, 'mockErrorLabel', 'mockField', true);
            expect(currentErrorTable.rows[0].cells[1].childNodes[0].className)
                .toEqual('sicon sicon-lg sicon-warning-lg');
        });

        it('should add the correct icon for errors in the second cell', function() {
            createError(mockStartEvent, 'mockErrorLabel', 'mockField');
            expect(currentErrorTable.rows[0].cells[1].childNodes[0].className)
                .toEqual('sicon sicon-lg sicon-warning-circle-lg');
        });

        it('should add the correct error text in the second cell if field is not given', function() {
            createError(mockStartEvent, 'mockErrorLabel');
            expect(currentErrorTable.rows[0].cells[1].childNodes[1].textContent).toEqual('  mockErrorLabel');
        });

        it('should add the correct error text in the second cell if field is given', function() {
            createError(mockStartEvent, 'mockErrorLabel', 'mockField');
            expect(currentErrorTable.rows[0].cells[1].childNodes[1].textContent).toEqual('  mockErrorLabel: mockField');
        });

        it('should set only the element\'s hasError status to true if this is an error', function() {
            mockStartEvent.hasError = false;
            mockStartEvent.hasWarning = false;
            createError(mockStartEvent, 'mockErrorLabel', 'mockField');
            expect(mockStartEvent.hasError).toBe(true);
            expect(mockStartEvent.hasWarning).toBe(false);
        });

        it('should set only the element\'s hasWarning status to true if this is a warning', function() {
            mockStartEvent.hasError = false;
            mockStartEvent.hasWarning = false;
            createError(mockStartEvent, 'mockErrorLabel', 'mockField', true);
            expect(mockStartEvent.hasError).toBe(false);
            expect(mockStartEvent.hasWarning).toBe(true);
        });

        it('should add elements in alphabetical order in the table', function() {
            createError(mockStartEvent, 'mockErrorLabel', 'mockField', true);
            createError(mockWaitEvent, 'mockErrorLabel', 'mockField', true);
            createError(mockEndEvent, 'mockErrorLabel', 'mockField', true);
            expect(currentErrorTable.rows[0].cells[0].textContent).toEqual('mn');
            expect(currentErrorTable.rows[1].cells[0].textContent).toEqual('mockName');
            expect(currentErrorTable.rows[2].cells[0].textContent).toEqual('mp');
        });
    });

    describe('createErrorRow', function() {

        beforeEach(function() {

            // Mock two rows of errors already existing in the table, so that
            // we can check if the next row is added at the correct index
            createError(mockWaitEvent, 'mockErrorLabel', 'mockField', true);
            createError(mockEndEvent, 'mockErrorLabel', 'mockField', true);

            sinon.spy(currentErrorTable, 'insertRow');
        });

        it('should create a new row in the error table', function() {
            expect(currentErrorTable.rows.length).toBe(2);
            createErrorRow(mockStartEvent);
            expect(currentErrorTable.rows.length).toBe(3);
        });

        it('should find the correct index to alphabetically place the new row', function() {
            createErrorRow(mockStartEvent);
            expect(currentErrorTable.insertRow).toHaveBeenCalledWith(1);
        });

        it('should return a reference to the new row', function() {
            expect(createErrorRow(mockStartEvent)).toBe(currentErrorTable.rows[1]);
        });
    });

    describe('createErrorName', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(jCore.Canvas.prototype, 'emptyCurrentSelection');
            sinon.stub(jCore.Canvas.prototype, 'addToSelection');
            sinon.stub(window, 'centerCanvasOnElement');
        });

        it('should create an element with the correct text content', function() {
            expect(createErrorName(mockStartEvent).textContent).toEqual('mockName');
        });

        it('should give the proper click action to the element created', function() {
            createErrorName(mockStartEvent).click();
            expect(canvas.emptyCurrentSelection).toHaveBeenCalled();
            expect(canvas.addToSelection).toHaveBeenCalledWith(mockStartEvent);
            expect(centerCanvasOnElement).toHaveBeenCalledWith(mockStartEvent);
        });

        it('should return a reference to the newly created element', function() {
            expect(createErrorName(mockStartEvent) instanceof HTMLElement).toBe(true);
        });
    });

    describe('createErrorIcon', function() {

        var mockIcon;

        it('should create the correct icon for warnings', function() {
            mockIcon = createErrorIcon(true);
            expect(mockIcon.className).toEqual('sicon sicon-lg sicon-warning-lg');
            expect(mockIcon.style.color).toEqual('rgb(255, 204, 0)');
            expect(mockIcon.getAttribute('data-original-title')).toEqual(translate('LBL_PMSE_VALIDATOR_WARNING_INFO'));
        });

        it('should create the correct icon for errors', function() {
            mockIcon = createErrorIcon(undefined);
            expect(mockIcon.className).toEqual('sicon sicon-lg sicon-warning-circle-lg');
            expect(mockIcon.style.color).toEqual('red');
            expect(mockIcon.getAttribute('data-original-title')).toEqual(translate('LBL_PMSE_VALIDATOR_ERROR_INFO'));
        });

        it('should return a reference to the newly created element', function() {
            expect(createErrorIcon(mockStartEvent) instanceof HTMLElement).toBe(true);
        });
    });

    describe('createErrorText', function() {

        var mockText;

        it('should correctly create the error text', function() {
            mockText = createErrorText('This is a mock error', 'A mock error is a pretend error for testing');
            expect(mockText.textContent).toEqual('  This is a mock error');
        });

        it('should add the correct tooltip to the error text', function() {
            mockText = createErrorText('This is a mock error', 'A mock error is a pretend error for testing');
            expect(mockText.getAttribute('data-original-title')).toEqual(
                'A mock error is a pretend error for testing');
        });

        it('should return a reference to the newly created element', function() {
            expect(createErrorText('mockError', 'mockErrorInfo') instanceof HTMLElement).toBe(true);
        });
    });

    describe('centerCanvasOnElement', function() {

        beforeEach(function() {

            // Mock the start event's position at (500, 800) at the current
            // zoom level
            mockStartEvent.zoomX = 500;
            mockStartEvent.zoomY = 800;
        });

        it('should move the center pane scroll values to keep the element in the center', function() {
            centerCanvasOnElement(mockStartEvent);
            expect(myLayout.center.pane[0].scrollLeft).toBe(300);
            expect(myLayout.center.pane[0].scrollTop).toBe(600);
        });
    });

    describe('getTargetModule', function() {

        beforeEach(function() {
            project.process_definition.pro_module = 'Accounts';
        });

        it('should return the correct target module of the project', function() {
            expect(getTargetModule()).toEqual('Accounts');
        });
    });

    describe('CriteriaEvaluator', function() {

        var mockEvaluatorEmptyIsTrue;
        var mockEvaluatorEmptyIsFalse;
        var phoneIs123;
        var phoneIsNot123;
        var phoneIs456;
        var and;
        var or;
        var not;
        var openParenthesis;
        var closeParenthesis;

        beforeEach(function() {

            // Create a CriteriaEvaluator to evaluate criteria boxes, treating empty criteria
            // boxes as always true
            mockEvaluatorEmptyIsTrue = new CriteriaEvaluator();
            mockEvaluatorEmptyIsTrue.emptyCriteriaIsTrue = true;

            // Create a CriteriaEvaluator to evaluate criteria boxes, treating empty criteria
            // boxes as always false
            mockEvaluatorEmptyIsFalse = new CriteriaEvaluator();

            phoneIs123 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '123'
            };

            phoneIsNot123 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'not_equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '123'
            };

            phoneIs456 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '456'
            };

            and = {
                expLabel: 'label',
                expType: 'LOGIC',
                expValue: 'AND'
            };

            or = {
                expLabel: 'label',
                expType: 'LOGIC',
                expValue: 'OR'
            };

            not = {
                expLabel: 'NOT',
                expType: 'LOGIC',
                expValue: 'NOT'
            };

            openParenthesis = {
                expLabel: '(',
                expType: 'GROUP',
                expValue: '('
            };

            closeParenthesis = {
                expLabel: ')',
                expType: 'GROUP',
                expValue: ')'
            };

        });

        describe('addOr', function() {

            it('should not add an "OR" object at the front if CriteriaEvaluator is empty', function() {
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([]);
                mockEvaluatorEmptyIsFalse.addOr([phoneIs123]);
                expect(mockEvaluatorEmptyIsFalse.criteria[0]).not.toEqual(or);
            });

            it('should add an "OR" object to CriteriaEvaluator first if CriteriaEvaluator is not empty', function() {
                mockEvaluatorEmptyIsFalse.addOr([phoneIs123]);
                expect(mockEvaluatorEmptyIsFalse.criteria).not.toEqual([]);
                mockEvaluatorEmptyIsFalse.addOr([phoneIsNot123]);
                expect(mockEvaluatorEmptyIsFalse.criteria[1].expType).toEqual('LOGIC');
                expect(mockEvaluatorEmptyIsFalse.criteria[1].expValue).toEqual('OR');
            });

            it('should add a simplified version of the criteria to the CriteriaEvaluator', function() {
                mockEvaluatorEmptyIsFalse.addOr([not, openParenthesis, phoneIs123, closeParenthesis]);
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([[[phoneIsNot123]]]);
            });

            it('should not add anything to the CriteriaEvaluator if newCriteria is empty', function() {
                mockEvaluatorEmptyIsFalse.addOr([]);
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([]);
            });
        });

        describe('addAnd', function() {

            it('should not add an "AND" object at the front if CriteriaEvaluator is empty', function() {
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([]);
                mockEvaluatorEmptyIsFalse.addAnd([phoneIs123]);
                expect(mockEvaluatorEmptyIsFalse.criteria[0]).not.toEqual(and);
            });

            it('should add an "AND" object to CriteriaEvaluator first if CriteriaEvaluator is not empty', function() {
                mockEvaluatorEmptyIsFalse.addAnd([phoneIs123]);
                expect(mockEvaluatorEmptyIsFalse.criteria).not.toEqual([]);
                mockEvaluatorEmptyIsFalse.addAnd([phoneIsNot123]);
                expect(mockEvaluatorEmptyIsFalse.criteria[1].expType).toEqual('LOGIC');
                expect(mockEvaluatorEmptyIsFalse.criteria[1].expValue).toEqual('AND');
            });

            it('should add a simplified version of the criteria to the CriteriaEvaluator', function() {
                mockEvaluatorEmptyIsFalse.addAnd([not, openParenthesis, phoneIs123, closeParenthesis]);
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([[[phoneIsNot123]]]);
            });

            it('should not add anything to the CriteriaEvaluator if newCriteria is empty', function() {
                mockEvaluatorEmptyIsFalse.addAnd([]);
                expect(mockEvaluatorEmptyIsFalse.criteria).toEqual([]);
            });
        });

        describe('isAlwaysTrue', function() {

            it('should return true for empty criteria if emptyCriteriaIsTrue is true', function() {
                mockEvaluatorEmptyIsTrue.addAnd([]);
                expect(mockEvaluatorEmptyIsTrue.isAlwaysTrue()).toBe(true);
                mockEvaluatorEmptyIsTrue.addAnd([]);
                expect(mockEvaluatorEmptyIsTrue.isAlwaysTrue()).toBe(true);
            });

            it('should return false for empty criteria if emptyCriteriaIsTrue is false', function() {
                mockEvaluatorEmptyIsFalse.addAnd([]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysTrue()).toBe(false);
                mockEvaluatorEmptyIsFalse.addAnd([]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysTrue()).toBe(false);
            });

            it('should return false for single-expression criteria', function() {

                // "Alternate Phone is 123"
                mockEvaluatorEmptyIsFalse.addAnd([phoneIs123]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysTrue()).toBe(false);
            });

            it('should return true for a logical expression that is always true', function() {

                // "Alternate Phone is 123 OR Alternate Phone is not 123"
                mockEvaluatorEmptyIsFalse.addOr([phoneIs123]);
                mockEvaluatorEmptyIsFalse.addOr([phoneIsNot123]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysTrue()).toBe(true);
            });

            it('should return false for a logical expression that is not always true', function() {

                // "Alternate Phone is 123 OR Alternate Phone is 456"
                mockEvaluatorEmptyIsFalse.addOr([phoneIs123]);
                mockEvaluatorEmptyIsFalse.addOr([phoneIs456]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysTrue()).toBe(false);
            });
        });

        describe('isAlwaysFalse', function() {

            it('should return false for empty criteria if emptyCriteriaIsTrue is true', function() {
                mockEvaluatorEmptyIsTrue.addAnd([]);
                expect(mockEvaluatorEmptyIsTrue.isAlwaysFalse()).toBe(false);
                mockEvaluatorEmptyIsTrue.addAnd([]);
                expect(mockEvaluatorEmptyIsTrue.isAlwaysFalse()).toBe(false);
            });

            it('should return true for empty criteria if emptyCriteriaIsTrue is false', function() {
                mockEvaluatorEmptyIsFalse.addAnd([]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysFalse()).toBe(true);
                mockEvaluatorEmptyIsFalse.addAnd([]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysFalse()).toBe(true);
            });

            it('should return true for criteria that are logically impossible', function() {

                // Alternate Phone is 123 AND Alternate Phone is not 123
                mockEvaluatorEmptyIsFalse.addAnd([phoneIs123, and, phoneIsNot123]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysFalse()).toBe(true);
            });

            it('should return false for criteria that are logically possible', function() {

                // Alternate Phone is 123 OR Alternate Phone is 456
                mockEvaluatorEmptyIsFalse.addAnd([phoneIs123, or, phoneIs456]);
                expect(mockEvaluatorEmptyIsFalse.isAlwaysFalse()).toBe(false);
            });
        });

        describe('simplifyCriteria', function() {

            it('should simplify simple criteria with parentheses by changing them to nested arrays', function() {

                // (Alternate Phone is 123)
                expect(mockEvaluatorEmptyIsFalse.simplifyCriteria([openParenthesis, phoneIs123,
                    closeParenthesis])).toEqual([[phoneIs123]]);
            });

            it('should simplify complex criteria with parentheses by changing them to nested arrays', function() {

                // Alternate Phone is 123 OR (Alternate Phone is 456 AND (Alternate Phone is not 123))
                expect(mockEvaluatorEmptyIsFalse.simplifyCriteria([phoneIs123, or, openParenthesis, phoneIs456,
                    and, openParenthesis, phoneIsNot123, closeParenthesis, closeParenthesis])).toEqual([
                    phoneIs123, or, [phoneIs456, and, [phoneIsNot123]]]);
            });

            it('should simplify simple criteria with NOT operators by removing the NOT operators', function() {

                // NOT Alternate Phone is 123
                expect(mockEvaluatorEmptyIsFalse.simplifyCriteria([not, phoneIs123])).toEqual([phoneIsNot123]);
            });

            it('should simplify complex criteria with NOT operators by removing the NOT operators', function() {

                // Alternate Phone is 123 OR NOT (Alternate Phone is 123 AND NOT (Alternate Phone is not 123))
                expect(mockEvaluatorEmptyIsFalse.simplifyCriteria([phoneIs123, or, not, openParenthesis, phoneIs123,
                    and, not, openParenthesis, phoneIsNot123, closeParenthesis, closeParenthesis])).toEqual([
                    phoneIs123, or, [phoneIsNot123, or, [phoneIsNot123]]]);
            });
        });

        describe('generatePossibilities', function() {

            it('should return the correct possibilities for empty criteria', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([])).toEqual([]);
            });

            it('should return the correct possibilities for a single criterion atom', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([[phoneIs123]])).toEqual([[phoneIs123]]);
            });

            it('should return the correct possibilities for two-criterion criteria with an AND', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([[phoneIs123, and, phoneIsNot123]])).toEqual(
                    [[phoneIs123, phoneIsNot123]]);
            });

            it('should return the correct possibilities for two-criterion criteria with an OR', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([[phoneIs123, or, phoneIsNot123]])).toEqual(
                    [[phoneIs123], [phoneIsNot123]]);
            });

            it('should return the correct possibilities for complex criteria with ANDs in parentheses ', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([[phoneIs123, or, [phoneIsNot123, and,
                    phoneIs456]]])).toEqual([
                    [phoneIs123],
                    [phoneIsNot123, phoneIs456]
                    ]);
            });

            it('should return the correct possibilities for complex criteria with ORs in parentheses ', function() {
                expect(mockEvaluatorEmptyIsFalse.generatePossibilities([[phoneIs123, and, [phoneIsNot123, or,
                    phoneIs456]]])).toEqual([
                    [phoneIs123, phoneIsNot123],
                    [phoneIs123, phoneIs456]
                    ]);
            });
        });

        describe('negateExpression', function() {

            var expression;

            it('should correctly negate a single expression', function() {
                expression = Object.assign({}, phoneIs123);
                mockEvaluatorEmptyIsFalse.negateExpression(expression);
                expect(expression).toEqual(phoneIsNot123);
            });

            it('should correctly negate an array of criteria', function() {

                // Using Object.assign to copy the objects, since the negateExpression function alters the
                // original objects
                expression = [Object.assign({}, phoneIs123), Object.assign({}, and), Object.assign({}, phoneIsNot123)];
                mockEvaluatorEmptyIsFalse.negateExpression(expression);
                expect(expression).toEqual([phoneIsNot123, or, phoneIs123]);
            });

            it('should correctly negate nested arrays', function() {
                expression = [Object.assign({}, phoneIs123), Object.assign({}, and), [Object.assign({}, phoneIsNot123),
                    Object.assign({}, or), Object.assign({}, phoneIs123)]];
                mockEvaluatorEmptyIsFalse.negateExpression(expression);
                expect(expression).toEqual([phoneIsNot123, or, [phoneIs123, and, phoneIsNot123]]);
            });
        });

        describe('negateSingleExpression', function() {

            it('should correctly negate a single expression', function() {
                expression = Object.assign({}, phoneIs123);
                mockEvaluatorEmptyIsFalse.negateSingleExpression(expression);
                expect(expression).toEqual(phoneIsNot123);
            });
        });
    });

    describe('LogicTracker', function() {

        var phoneIs123;
        var phoneIsNot123;
        var phoneIs456;
        var campaignDescriptionIsHello;
        var mockLogicTracker;
        var mockSingleProperty;

        beforeEach(function() {

            mockLogicTracker = new LogicTracker();

            phoneIs123 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '123'
            };

            phoneIsNot123 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'not_equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '123'
            };

            phoneIs456 = {
                expField: 'phone_alternate',
                expLabel: 'label',
                expModule: 'Accounts',
                expOperator: 'equals',
                expSubtype: 'Phone',
                expType: 'MODULE',
                expValue: '456'
            };

            campaignDescriptionIsHello = {
                expField: 'content',
                expLabel: 'Campaigns (Description  is "Hello")',
                expModule: 'campaign_accounts',
                expOperator: 'equals',
                expSubtype: 'TextArea',
                expType: 'MODULE',
                expValue: 'Hello'
            };
        });

        describe('add', function() {

            it('should correctly add an atom to an empty LogicTracker', function() {
                expect(mockLogicTracker.atoms.length).toBe(0);
                mockLogicTracker.add([phoneIs123]);
                expect(mockLogicTracker.atoms.length).toBe(1);
                expect(mockLogicTracker.atoms[0].type).toEqual('MODULE');
                expect(mockLogicTracker.atoms[0].module).toEqual('Accounts');
                expect(mockLogicTracker.atoms[0].field).toEqual('phone_alternate');
                expect(mockLogicTracker.atoms[0].operators).toEqual({
                    'equals': ['123'],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });

            it('should add to a LogicTracker that already contains the property/operator', function() {
                mockLogicTracker.add([phoneIs123, phoneIs456]);
                expect(mockLogicTracker.atoms.length).toBe(1);
                expect(mockLogicTracker.atoms[0].type).toEqual('MODULE');
                expect(mockLogicTracker.atoms[0].module).toEqual('Accounts');
                expect(mockLogicTracker.atoms[0].field).toEqual('phone_alternate');
                expect(mockLogicTracker.atoms[0].operators).toEqual({
                    'equals': ['123', '456'],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });

            it('should add to a LogicTracker that already contains the property, but with new operator', function() {
                mockLogicTracker.add([phoneIs123, phoneIsNot123]);
                expect(mockLogicTracker.atoms.length).toBe(1);
                expect(mockLogicTracker.atoms[0].type).toEqual('MODULE');
                expect(mockLogicTracker.atoms[0].module).toEqual('Accounts');
                expect(mockLogicTracker.atoms[0].field).toEqual('phone_alternate');
                expect(mockLogicTracker.atoms[0].operators).toEqual({
                    'equals': ['123'],
                    'not_equals': ['123'],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });

            it('should correctly add a new property to a LogicTracker that already has one', function() {
                mockLogicTracker.add([phoneIs123, campaignDescriptionIsHello]);
                expect(mockLogicTracker.atoms.length).toBe(2);
                expect(mockLogicTracker.atoms[0].type).toEqual('MODULE');
                expect(mockLogicTracker.atoms[0].module).toEqual('Accounts');
                expect(mockLogicTracker.atoms[0].field).toEqual('phone_alternate');
                expect(mockLogicTracker.atoms[0].operators).toEqual({
                    'equals': ['123'],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
                expect(mockLogicTracker.atoms[1].type).toEqual('MODULE');
                expect(mockLogicTracker.atoms[1].module).toEqual('campaign_accounts');
                expect(mockLogicTracker.atoms[1].field).toEqual('content');
                expect(mockLogicTracker.atoms[1].operators).toEqual({
                    'equals': ['Hello'],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });
        });

        describe('isValid', function() {

            it('should report an empty LogicTracker as valid', function() {
                expect(mockLogicTracker.isValid()).toBe(true);
            });

            it('should correctly detect a valid set of LogicAtoms', function() {
                mockLogicTracker.add([phoneIs123, campaignDescriptionIsHello]);
                expect(mockLogicTracker.isValid()).toBe(true);
            });

            it('should correctly detect an invalid set of LogicAtoms', function() {
                mockLogicTracker.add([phoneIs123, phoneIs456]);
                expect(mockLogicTracker.isValid()).toBe(false);
            });
        });
    });

    describe('LogicAtom', function() {

        var mockLogicAtom;

        beforeEach(function() {

            // Mock a LogicAtom representing the Accounts "Alternate Phone" field
            mockLogicAtom = new LogicAtom('MODULE', 'Accounts', 'phone_alternate');
        });

        describe('add', function() {

            it('should correctly add an operator/value combo to an empty LogicAtom', function() {
                mockLogicAtom.add('equals', '123');
                expect(mockLogicAtom.operators).toEqual({
                    'equals': ['123'],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });

            it('should correctly add an operator/value combo to a non-empty LogicAtom', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('starts_with', '12');
                expect(mockLogicAtom.operators).toEqual({
                    'equals': ['123'],
                    'not_equals': [],
                    'starts_with': ['12'],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });

            it('should do nothing if the given operator is not valid', function() {
                mockLogicAtom.add('notARealOperator', '123');
                expect(mockLogicAtom.operators).toEqual({
                    'equals': [],
                    'not_equals': [],
                    'starts_with': [],
                    'not_starts_with': [],
                    'ends_with': [],
                    'not_ends_with': [],
                    'contains': [],
                    'does_not_contain': []
                });
            });
        });

        describe('isValid', function() {

            it('should return true for a valid LogicAtom with a single operator/value combo', function() {
                mockLogicAtom.add('equals', '123');
                expect(mockLogicAtom.isValid()).toBe(true);
            });

            it('should return true for a valid LogicAtom with two operator/value combos', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('not_equals', 'abc');
                expect(mockLogicAtom.isValid()).toBe(true);
            });

            it('should detect an equals/equals conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('equals', '456');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/not_equals conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('not_equals', '123');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/starts_with conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('starts_with', '4');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/not_starts_with conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('not_starts_with', '12');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/ends_with conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('ends_with', '32');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/not_ends_with conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('not_ends_with', '23');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/contains conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('contains', 'a');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an equals/does_not_contain conflict', function() {
                mockLogicAtom.add('equals', '123');
                mockLogicAtom.add('does_not_contain', '2');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect a starts_with/starts_with conflict', function() {
                mockLogicAtom.add('starts_with', '123');
                mockLogicAtom.add('starts_with', '13');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect a starts_with/not_starts_with conflict', function() {
                mockLogicAtom.add('starts_with', '123');
                mockLogicAtom.add('not_starts_with', '12');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect a starts_with/does_not_contain conflict', function() {
                mockLogicAtom.add('starts_with', '123');
                mockLogicAtom.add('does_not_contain', '3');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an ends_with/ends_with conflict', function() {
                mockLogicAtom.add('ends_with', '123');
                mockLogicAtom.add('ends_with', '132');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an ends_with/not_ends_with conflict', function() {
                mockLogicAtom.add('ends_with', '123');
                mockLogicAtom.add('not_ends_with', '23');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect an ends_with/does_not_contain conflict', function() {
                mockLogicAtom.add('ends_with', '123');
                mockLogicAtom.add('does_not_contain', '1');
                expect(mockLogicAtom.isValid()).toBe(false);
            });

            it('should detect a contains/does_not_contain conflict', function() {
                mockLogicAtom.add('contains', '123');
                mockLogicAtom.add('does_not_contain', '2');
                expect(mockLogicAtom.isValid()).toBe(false);
            });
        });
    });
});
