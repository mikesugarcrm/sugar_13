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
// Test shapes.js
describe('includes.javascript.pmse.shapes', function() {

    var app;
    var oldApp;
    var oldProject;
    var mockShape;
    var mockValidationTools;
    var mockErrorLayer;

    beforeEach(function() {

        // Setting temporary globals in case they are altered during any tests
        app = SugarTest.app;

        oldProject = project;
        project = {};

        oldApp = App;
        App = app;

        // Mocking an AdamShape
        mockShape = new AdamShape();
        mockShape.id = 'mock_shape_id';
        mockShape.label = {
            message: 'Mock Name',
            getMessage: function() {
                return this.message;
            }
        };

        // Mocking the validation tools
        mockValidationTools = getValidationTools();

        // Mocking an error Layer object
        mockErrorLayer = mockShape.createLayer({
            layerName: 'mock_error_layer',
            priority: 3,
            visible: true,
            style: {
                cssClasses: []
            }
        });
    });

    afterEach(function() {

        // Restore the local variables and stubs
        app = null;
        mockShape = null;
        mockErrorLayer = null;
        mockValidationTools = null;
        sinon.restore();

        // Restore the global variables
        project = oldProject;
        App = oldApp;
    });

    describe('getSourceElements', function() {

        beforeEach(function() {

            // Mocking 2 events and a gateway
            // Adding flows from both events to the gateway
            mockEvent1 = new AdamEvent();
            mockEvent2 = new AdamEvent();
            mockGateway = new AdamGateway();

            mockConnection1 = new jCore.Connection();
            mockConnection1.srcPort = new jCore.Port();
            mockConnection1.srcPort.parent = mockEvent1;

            mockConnection2 = new jCore.Connection();
            mockConnection2.srcPort = new jCore.Port();
            mockConnection2.srcPort.parent = mockEvent2;

            mockPort1 = new jCore.Port();
            mockPort1.connection = mockConnection1;
            mockGateway.ports.insert(mockPort1);

            mockPort2 = new jCore.Port();
            mockPort2.connection = mockConnection2;
            mockGateway.ports.insert(mockPort2);
        });

        it('should return an array of the source elements', function() {

            expect(mockGateway.getSourceElements()).toEqual([mockEvent1, mockEvent2]);
        });
    });

    describe('getDestElementName', function() {

        var mockEvent;
        var mockPort;
        var mockConnection;

        beforeEach(function() {

            // Create a mock event to represent a destination element from this shape
            mockEvent = new AdamEvent();
            mockEvent.label = {
                message: 'Mock Destination Name',
                getMessage: function() {
                    return this.message;
                }
            };

            // Create a mock flow with ID 'mock_flow' to the mock event
            mockConnection = new jCore.Connection();
            mockConnection.flo_uid = 'mock_flow';
            mockConnection.destPort = new jCore.Port();
            mockConnection.destPort.parent = mockEvent;

            // Add the mock flow to the mock shape's connections
            mockPort = new jCore.Port();
            mockPort.connection = mockConnection;
            mockShape.ports.insert(mockPort);
        });

        it('should return the correct name for a destination element if the floID exists', function() {
            expect(mockShape.getDestElementName('mock_flow')).toBe('Mock Destination Name');
        });

        it('should return undefined if the floID does not exist', function() {
            expect(mockShape.getDestElementName('non_existent_flo_id')).toBe(undefined);
        });
    });

    describe('showWarningMarker', function() {

        beforeEach(function() {
            sinon.stub(mockShape, 'addErrorLayer');
        });

        it('should call addErrorLayer with the correct parameters', function() {
            mockShape.showWarningMarker();
            expect(mockShape.addErrorLayer).toHaveBeenCalledWith('error', 5, true);
        });
    });

    describe('showErrorMarker', function() {

        beforeEach(function() {
            sinon.stub(mockShape, 'addErrorLayer');
        });

        it('should call addErrorLayer with the correct parameters', function() {
            mockShape.showErrorMarker();
            expect(mockShape.addErrorLayer).toHaveBeenCalledWith('error', 2);
        });
    });

    describe('clearIssueMarkers', function() {

        var mockErrorMarkerElement;
        var mockWarningMarkerElement;
        var mockErrorMarker;
        var mockWarningMarker;

        beforeEach(function() {

            // Creating a mock marker HTML element
            mockErrorMarkerElement = $('<div id="mockErrorMarkerId"></div>');
            mockWarningMarkerElement = $('<div id="mockWarningMarkerId"></div>');
            $('body').append(mockErrorMarkerElement, mockWarningMarkerElement);

            // Creating a mock error marker
            mockErrorMarker = new AdamMarker({
                parent: mockErrorLayer,
                position: 2,
                height: 17,
                width: 17,
                markerZoomClasses: ['element-zoom-100-marker adam-error-color sicon sicon-warning-circle-lg']
            });
            mockErrorMarker.html = mockErrorMarkerElement;
            mockErrorMarker.id = 'mockErrorMarkerId';

            // Creating a mock warning marker
            mockWarningMarker = new AdamMarker({
                parent: mockErrorLayer,
                position: 5,
                height: 17,
                width: 17,
                markerZoomClasses: ['element-zoom-100-marker adam-warning-color sicon sicon-warning-lg']
            });
            mockWarningMarker.html = mockWarningMarkerElement;
            mockWarningMarker.id = 'mockWarningMarkerId';
        });

        afterEach(function() {

            // Restore the local variables
            mockErrorMarkerElement = null;
            mockWarningMarkerElement = null;
            mockErrorMarker = null;
            mockWarningMarker = null;

            // Remove the mock marker elements from the document
            $('#mockErrorMarkerId').remove();
            $('#mockWarningMarkerId').remove();
        });

        it('should remove the error marker HTML element from the document', function() {
            mockShape.markersArray.insert(mockErrorMarker);

            // The marker HTML element should exist before clearIssueMarkers is called,
            // but not after
            expect($('#mockErrorMarkerId').length).toBe(1);
            mockShape.clearIssueMarkers();
            expect($('#mockErrorMarkerId').length).toBe(0);
        });

        it('should remove the error marker from the markers array', function() {
            mockShape.markersArray.insert(mockErrorMarker);

            // The marker should be removed from the mock shape's markers array
            expect(mockShape.markersArray.getSize()).toBe(1);
            mockShape.clearIssueMarkers();
            expect(mockShape.markersArray.getSize()).toBe(0);
        });

        it('should remove the warning marker HTML element from the document', function() {
            mockShape.markersArray.insert(mockWarningMarker);

            // The marker HTML element should exist before clearIssueMarkers is called,
            // but not after
            expect($('#mockWarningMarkerId').length).toBe(1);
            mockShape.clearIssueMarkers();
            expect($('#mockWarningMarkerId').length).toBe(0);
        });

        it('should remove the warning marker from the markers array', function() {
            mockShape.markersArray.insert(mockWarningMarker);

            // The marker should be removed from the mock shape's markers array
            expect(mockShape.markersArray.getSize()).toBe(1);
            mockShape.clearIssueMarkers();
            expect(mockShape.markersArray.getSize()).toBe(0);
        });

        it('should remove both warning and error marker HTML elements if they both exist', function() {
            mockShape.markersArray.insert(mockWarningMarker);
            mockShape.markersArray.insert(mockErrorMarker);

            // Both the error and warning marker HTML elements should exist before
            // clearIssueMarkers is called, but not after
            expect($('#mockWarningMarkerId').length).toBe(1);
            expect($('#mockErrorMarkerId').length).toBe(1);
            mockShape.clearIssueMarkers();
            expect($('#mockWarningMarkerId').length).toBe(0);
            expect($('#mockErrorMarkerId').length).toBe(0);
        });

        it('should remove both warning and error marks from the markers array if they both exist', function() {
            mockShape.markersArray.insert(mockWarningMarker);
            mockShape.markersArray.insert(mockErrorMarker);

            // Both the warning and error marker should be removed from the mock
            // shape's markers array
            expect(mockShape.markersArray.getSize()).toBe(2);
            mockShape.clearIssueMarkers();
            expect(mockShape.markersArray.getSize()).toBe(0);
        });
    });

    describe('addErrorLayer', function() {

        beforeEach(function() {

            // Stub the addErrors and createLayer functions so they aren't actually called
            sinon.stub(AdamShape.prototype, 'addErrors');
            sinon.spy(AdamShape.prototype, 'createLayer');
        });

        afterEach(function() {

            // Restore the stubs
            sinon.restore();
        });

        it('should call addErrors with the correct parameters for warnings', function() {
            mockShape.addErrorLayer('error', 5, true);
            expect(mockShape.addErrors).toHaveBeenCalledWith(jasmine.any(jCore.Layer), 5, true);
        });

        it('should call addErrors with the correct parameters for errors', function() {
            mockShape.addErrorLayer('error', 2);
            expect(mockShape.addErrors).toHaveBeenCalledWith(jasmine.any(jCore.Layer), 2, undefined);
        });

        it('should make a new layer for the first error or warning created', function() {
            mockShape.addErrorLayer('error', 2);
            expect(mockShape.createLayer).toHaveBeenCalled();
        });

        it('should not make a new layer for the second error or warning created', function() {
            mockShape.addErrorLayer('error', 2);
            mockShape.addErrorLayer('error', 5, true);
            expect(AdamShape.prototype.createLayer.calledOnce).toBe(true);
        });
    });

    describe('addErrors', function() {

        beforeEach(function() {

            // Stub the paint and setElementClass functions of AdamMarker
            // so they aren't actually called
            sinon.stub(AdamMarker.prototype, 'paint');
            sinon.stub(AdamMarker.prototype, 'setElementClass');
        });

        afterEach(function() {

            // Restore the stubs
            sinon.restore();
        });

        it('should set the correct css classes for warnings', function() {
            mockShape.addErrors(mockErrorLayer, 5, true);
            expect(AdamMarker.prototype.setElementClass).toHaveBeenCalledWith([
                'element-zoom-50-marker adam-warning-color sicon sicon-warning-lg',
                'element-zoom-75-marker adam-warning-color sicon sicon-warning-lg',
                'element-zoom-100-marker adam-warning-color sicon sicon-warning-lg',
                'element-zoom-125-marker adam-warning-color sicon sicon-warning-lg',
                'element-zoom-150-marker adam-warning-color sicon sicon-warning-lg']);
        });

        it('should set the correct css classes for errors', function() {
            mockShape.addErrors(mockErrorLayer, 2, undefined);
            expect(AdamMarker.prototype.setElementClass).toHaveBeenCalledWith([
                'element-zoom-50-marker adam-error-color sicon sicon-warning-circle-lg',
                'element-zoom-75-marker adam-error-color sicon sicon-warning-circle-lg',
                'element-zoom-100-marker adam-error-color sicon sicon-warning-circle-lg',
                'element-zoom-125-marker adam-error-color sicon sicon-warning-circle-lg',
                'element-zoom-150-marker adam-error-color sicon sicon-warning-circle-lg']);
        });
    });

    describe('validate', function() {

        var mockCallbackFunction;
        var mockAPIResponseFunction;
        var correctURL;

        beforeEach(function() {

            // Create a mock getValidationFunction function since AdamShape itself
            // does not have one
            mockShape.getValidationFunction = function() {
            };

            // Create a mock getBaseURL function since AdamShape itself
            // does not have one
            mockShape.getBaseURL = function() {
                return 'mockBaseURL';
            };

            // Set the URL that is the 'correct' URL endpoint
            correctURL = 'builtURL/mockBaseURL/mock_shape_id';

            // Mock the App.api.call function to simulate the activity when the correct URL
            // vs an incorrect URL endpoint is provided
            mockAPICallFunction = function(action, url, attributes, callbacks, options) {

                // Since the error function makes a call with the variable "element", which we
                // don't have in this scope, we need to simulate the callbacks.error function
                callbacks.error = function(data) {
                    mockValidationTools.createWarning(mockShape, 'LBL_PMSE_ERROR_UNABLE_TO_VALIDATE',
                        mockShape.getName());
                };
                if (url === correctURL) {

                    // Correct URL, so simulate the 'success' function
                    callbacks.success();
                } else {

                    // Wrong URL, so simulate the 'error' function
                    callbacks.error();
                }
                callbacks.complete();
            };

            // Stub functions as needed
            sinon.stub(App.api, 'call').callsFake(mockAPICallFunction);
            sinon.stub(App.api, 'buildURL').returns('builtURL/' + mockShape.getBaseURL() +
                '/' + mockShape.id);
            sinon.stub(mockValidationTools.progressTracker, 'incrementTotalElements');
            sinon.stub(mockValidationTools.progressTracker, 'incrementSettingsGathered');
            sinon.stub(mockValidationTools, 'createWarning');
            mockCallbackFunction = sinon.stub();
        });

        describe('with a callback function', function() {

            beforeEach(function() {

                // Stub the getValidationFunction to return the mock callback function
                sinon.stub(mockShape, 'getValidationFunction').returns(mockCallbackFunction);
            });

            it('should call App.api.call with the correct action and URL', function() {
                mockShape.validate(mockValidationTools);
                expect(App.api.call).toHaveBeenCalledWith('read', 'builtURL/mockBaseURL/mock_shape_id', null,
                    jasmine.any(Object), jasmine.any(Object));
            });

            it('should call incrementTotalElements', function() {
                mockShape.validate(mockValidationTools);
                expect(mockValidationTools.progressTracker.incrementTotalElements).toHaveBeenCalled();
            });

            it('should execute only the "success" function then "complete" function if the URL is valid', function() {
                mockShape.validate(mockValidationTools);
                expect(mockCallbackFunction).toHaveBeenCalled();
                expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
                expect(mockValidationTools.progressTracker.incrementSettingsGathered).toHaveBeenCalled();
            });

            it('should execute only the "error" function then "complete" function if the URL is invalid', function() {
                correctURL = 'A different URL';
                mockShape.validate(mockValidationTools);
                expect(mockCallbackFunction).not.toHaveBeenCalled();
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
                expect(mockValidationTools.progressTracker.incrementSettingsGathered).toHaveBeenCalled();
            });
        });

        describe('without a callback function', function() {

            beforeEach(function() {

                // Stub the getValidationFunction to return 'undefined'
                sinon.stub(mockShape, 'getValidationFunction');
            });

            it('should not call incrementTotalElements', function() {
                mockShape.validate(mockValidationTools);
                expect(mockValidationTools.progressTracker.incrementTotalElements).not.toHaveBeenCalled();
            });

            it('should not call App.api.call', function() {
                mockShape.validate(mockValidationTools);
                expect(App.api.call).not.toHaveBeenCalled();
            });
        });
    });
});
