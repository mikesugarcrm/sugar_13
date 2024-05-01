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
// Test gateway.js
describe('includes.javascript.pmse.gateway', function() {
    var app;
    var oldApp;
    var oldProject;
    var mockGateway;
    var mockValidationTools;
    var mockDataObjectEmptyLogic;
    var mockDataObjectBadLogic;
    var mockDataObjectOKLogic;
    var mockParsedCriteriaSimple;
    var mockParsedCriteriaPossible;
    var mockParsedCriteriaImpossible;

    beforeEach(function() {

        // Setting temporary globals in case they are altered during any tests
        app = SugarTest.app;

        oldProject = project;
        project = {};

        oldApp = App;
        App = app;

        // Mocking a gateway element
        mockGateway = new AdamGateway();

        // Mocking validationTools
        mockValidationTools = getValidationTools();

        // Mocking an API response from the GatewayDefinition endpoint
        // with no criteria entered
        mockDataObjectEmptyLogic = {

            data: [
            {
                flo_uid: '3415215495ba98221232878056213499',
                flo_condition: ''
            },
            {
                flo_uid: '5595228865ba98225232a91078691030',
                flo_condition: ''
            }
            ]
        };

        // Mocking an API response from the GatewayDefinition endpoint
        // with criteria that does not cover all cases
        mockDataObjectBadLogic = {

            data: [
                {
                    flo_uid: '3415215495ba98221232878056213499',
                    flo_condition: '[{"expType":"MODULE",' +
                        '"expSubtype":"Phone",' +
                        '"expLabel":"Accounts (Alternate Phone is \\"123\\")",' +
                        '"expValue":"123",' +
                        '"expOperator":"equals",' +
                        '"expModule":"Accounts",' +
                        '"expField":"phone_alternate"}]'
                },
                {
                    flo_uid: '5595228865ba98225232a91078691030',
                    flo_condition: '[{"expType":"MODULE",' +
                        '"expSubtype":"Phone",' +
                        '"expLabel":"Accounts (Alternate Phone is \\"456\\")",' +
                        '"expValue":"456",' +
                        '"expOperator":"equals",' +
                        '"expModule":"Accounts",' +
                        '"expField":"phone_alternate"}]'
                }
            ]
        };

        // Mocking an API response from the GatewayDefinition endpoint
        // with criteria that covers all cases
        mockDataObjectOKLogic = {

            data: [
                {
                    flo_uid: '3415215495ba98221232878056213499',
                    flo_condition: '[{"expType":"MODULE",' +
                        '"expSubtype":"Phone",' +
                        '"expLabel":"Accounts (Alternate Phone is \\"123\\")",' +
                        '"expValue":"123",' +
                        '"expOperator":"equals",' +
                        '"expModule":"Accounts",' +
                        '"expField":"phone_alternate"}]'
                },
                {
                    flo_uid: '5595228865ba98225232a91078691030',
                    flo_condition: '[{"expType":"MODULE",' +
                        '"expSubtype":"Phone",' +
                        '"expLabel":"Accounts (Alternate Phone is not \\"123\\")",' +
                        '"expValue":"123",' +
                        '"expOperator":"not_equals",' +
                        '"expModule":"Accounts",' +
                        '"expField":"phone_alternate"}]'
                }
            ]
        };

        // Mocking the criteria parsed from a data object flo_condition
        // with one piece of criteria
        mockParsedCriteriaSimple = [{
            expType: 'MODULE',
            expSubtype: 'Phone',
            expLabel: 'Accounts (Alternate Phone is "123")',
            expValue: '123',
            expOperator: 'equals',
            expModule: 'Accounts',
            expField: 'phone_alternate'
        }];

        // Mocking the criteria parsed from a data object flo_condition
        // with two pieces of criteria that is logically possible
        mockParsedCriteriaPossible = [
        {
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
            expLabel: 'Accounts (Alternate Phone is not \"456\")',
            expValue: '456',
            expOperator: 'not_equals',
            expModule: 'Accounts',
            expField: 'phone_alternate'
        }];

        // Mocking the criteria parsed from a data object flo_condition
        // with two pieces of criteria that is logically impossible
        mockParsedCriteriaImpossible = [{
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
            expLabel: 'Accounts (Alternate Phone is "456")',
            expValue: '456',
            expOperator: 'equals',
            expModule: 'Accounts',
            expField: 'phone_alternate'
        }];

        // Stub these ValidationTools functions that are unnecessary for these tests
        sinon.stub(mockValidationTools, 'validateAtom');
        sinon.stub(mockValidationTools, 'validateNumberOfEdges');
        sinon.stub(mockValidationTools, 'createWarning');
    });

    afterEach(function() {

        // Restore the local variables and stubs
        app = null;
        mockGateway = null;
        mockValidationTools = null;
        sinon.restore();

        // Restore the global variables
        project = oldProject;
        App = oldApp;
    });

    // Test the getBaseURL function
    describe('getBaseURL', function() {
        it('should return the correct base URL for gateway elements', function() {
            expect(mockGateway.getBaseURL()).toBe('pmse_Project/GatewayDefinition/');
        });
    });

    // Test the getValidationFunction function
    describe('getValidationFunction', function() {
        it('should return the correct validation function for diverging exclusive gateways', function() {
            mockGateway.setDirection('diverging');
            mockGateway.setGatewayType('exclusive');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForDivergingGateway);
        });

        it('should return the correct validation function for converging exclusive gateways', function() {
            mockGateway.setDirection('converging');
            mockGateway.setGatewayType('exclusive');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForConvergingGateway);
        });

        it('should return the correct validation function for diverging parallel gateways', function() {
            mockGateway.setDirection('diverging');
            mockGateway.setGatewayType('parallel');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForDivergingGateway);
        });

        it('should return the correct validation function for converging parallel gateways', function() {
            mockGateway.setDirection('converging');
            mockGateway.setGatewayType('parallel');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForConvergingGateway);
        });

        it('should return the correct validation function for event-based gateways', function() {
            mockGateway.setDirection('unspecified');
            mockGateway.setGatewayType('eventbased');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForDivergingGateway);
        });

        it('should return the correct validation function for inclusive gateways', function() {
            mockGateway.setDirection('diverging');
            mockGateway.setGatewayType('inclusive');
            expect(mockGateway.getValidationFunction()).toBe(mockGateway.callbackFunctionForDivergingGateway);
        });
    });

    // Test the callbackFunctionForDivergingGateway function
    describe('callbackFunctionForDivergingGateway', function() {

        beforeEach(function() {

            // Set the mock gateway as a diverging gateway
            mockGateway.setDirection('diverging');

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockGateway, 'validateCriteriaBoxes');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockGateway.callbackFunctionForDivergingGateway(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(null, null, 2, null, mockGateway);
        });

        it('should validate the criteria boxes with the correct data if this is an exclusive gateway', function() {
            mockGateway.setGatewayType('exclusive');
            mockGateway.callbackFunctionForDivergingGateway(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
            expect(mockGateway.validateCriteriaBoxes).toHaveBeenCalledWith(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
        });

        it('should validate the criteria boxes with the correct data if this is an inclusive gateway', function() {
            mockGateway.setGatewayType('inclusive');
            mockGateway.callbackFunctionForDivergingGateway(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
            expect(mockGateway.validateCriteriaBoxes).toHaveBeenCalledWith(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
        });

        it('should not validate the criteria boxes if this is a parallel gateway', function() {
            mockGateway.setGatewayType('eventbased');
            mockGateway.callbackFunctionForDivergingGateway(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
            expect(mockGateway.validateCriteriaBoxes).not.toHaveBeenCalled();
        });

        it('should not validate the criteria boxes if this is an event-based gateway', function() {
            mockGateway.setGatewayType('parallel');
            mockGateway.callbackFunctionForDivergingGateway(mockDataObjectEmptyLogic,
                mockGateway, mockValidationTools);
            expect(mockGateway.validateCriteriaBoxes).not.toHaveBeenCalled();
        });
    });

    // Test the callbackFunctionForConvergingGateway function
    describe('callbackFunctionForConvergingGateway', function() {

        beforeEach(function() {

            // Set the mock gateway direction as a converging gateway
            mockGateway.setDirection('converging');
        });

        it('should call validateNumberOfEdges with the correct max/min number of incoming/outgoing edges', function() {
            mockGateway.currentGatewayScope = [];
            mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
            expect(mockValidationTools.validateNumberOfEdges).toHaveBeenCalledWith(2, null, 1, 1, mockGateway);
        });

        describe('with parallel type', function() {

            beforeEach(function() {

                // Set the gateway type as a parallel gateway
                mockGateway.setGatewayType('parallel');
            });

            it('should generate a warning if the last diverging type was exclusive', function() {
                mockGateway.currentGatewayScope = ['EXCLUSIVE'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });

            it('should generate a warning if the last diverging type was event-based', function() {
                mockGateway.currentGatewayScope = ['EVENTBASED'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });

            it('should not generate a warning if the last diverging type was parallel or inclusive', function() {
                mockGateway.currentGatewayScope = ['PARALLEL'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                mockGateway.currentGatewayScope = ['INCLUSIVE'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
            });

            it('should generate a warning if there was no previous diverging gateway', function() {
                mockGateway.currentGatewayScope = [];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });
        });

        describe('with exclusive type', function() {

            beforeEach(function() {

                // Set the gateway type as an exclusive gateway
                mockGateway.setGatewayType('exclusive');
            });

            it('should generate a warning if the last diverging type was parallel', function() {
                mockGateway.currentGatewayScope = ['PARALLEL'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });

            it('should generate a warning if the last diverging type was inclusive', function() {
                mockGateway.currentGatewayScope = ['INCLUSIVE'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });

            it('should not generate a warning if the last diverging type was exlusive or event-based', function() {
                mockGateway.currentGatewayScope = ['EXCLUSIVE'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                mockGateway.currentGatewayScope = ['EVENTBASED'];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
            });

            it('should generate a warning if there was no previous diverging gateway', function() {
                mockGateway.currentGatewayScope = [];
                mockGateway.callbackFunctionForConvergingGateway({}, mockGateway, mockValidationTools);
                expect(mockValidationTools.createWarning).toHaveBeenCalled();
            });
        });
    });

    // Test the validateCriteriaBoxes function
    describe('validateCriteriaBoxes', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockGateway, 'validateSingleCriteriaBox');
        });

        it('should call validateSingleCriteriaBox with the correct data', function() {
            mockGateway.validateCriteriaBoxes(mockDataObjectBadLogic, mockGateway, mockValidationTools);
            expect(mockGateway.validateSingleCriteriaBox).toHaveBeenCalledWith(mockDataObjectBadLogic.data[0],
                mockGateway, mockValidationTools);
            expect(mockGateway.validateSingleCriteriaBox).toHaveBeenCalledWith(mockDataObjectBadLogic.data[1],
                mockGateway, mockValidationTools);
        });

        it('should generate a warning if no default path and not all cases covered in logic', function() {
            mockGateway.gat_default_flow = undefined;
            mockGateway.validateCriteriaBoxes(mockDataObjectBadLogic, mockGateway, mockValidationTools);
            expect(mockValidationTools.createWarning).toHaveBeenCalledWith(mockGateway,
                'LBL_PMSE_ERROR_GATEWAY_NO_GUARANTEED_PATH');
        });

        it('should not generate a warning if no default path and all cases covered in logic', function() {
            mockGateway.gat_default_flow = undefined;
            mockGateway.validateCriteriaBoxes(mockDataObjectOKLogic, mockGateway, mockValidationTools);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });

        it('should not generate a warning if default path is chosen and not all cases covered in logic', function() {
            mockGateway.gat_default_flow = 'Mock flow UID';
            mockGateway.validateCriteriaBoxes(mockDataObjectBadLogic, mockGateway, mockValidationTools);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });
    });

    // Test the validateSingleCriteriaBox function
    describe('validateSingleCriteriaBox', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockGateway, 'checkForImpossibleLogic');
            sinon.stub(mockGateway, 'validateCriteriaBoxAtoms');
        });

        it('should call checkForImpossibleLogic with the correct data', function() {
            mockGateway.validateSingleCriteriaBox(mockDataObjectOKLogic.data[0], mockGateway, mockValidationTools);
            expect(mockGateway.checkForImpossibleLogic).toHaveBeenCalledWith(mockDataObjectOKLogic.data[0],
                mockGateway, mockValidationTools, mockParsedCriteriaSimple);
        });

        it('should call validateCriteriaBoxAtoms with the correct data', function() {
            mockGateway.validateSingleCriteriaBox(mockDataObjectOKLogic.data[0], mockGateway, mockValidationTools);
            expect(mockGateway.validateCriteriaBoxAtoms).toHaveBeenCalledWith(mockGateway, mockValidationTools,
                mockParsedCriteriaSimple);
        });
    });

    // Test the validateCriteriaBoxAtoms function
    describe('validateCriteriaBoxAtoms', function() {

        it('should call validateAtom with the correct data', function() {
            mockGateway.validateCriteriaBoxAtoms(mockGateway, mockValidationTools, mockParsedCriteriaSimple);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockGateway,
                mockValidationTools);
        });

        it('should not call validateAtom if the criteria is empty', function() {
            mockGateway.validateCriteriaBoxAtoms(mockGateway, mockValidationTools, []);
            expect(mockValidationTools.validateAtom).not.toHaveBeenCalled();
        });

        it('should call validateAtom for each atom in the criteria', function() {
            mockGateway.validateCriteriaBoxAtoms(mockGateway, mockValidationTools, mockParsedCriteriaImpossible);
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '123'},
                mockGateway,
                mockValidationTools
            );
            expect(mockValidationTools.validateAtom).toHaveBeenCalledWith(
                {type: 'MODULE', module: 'Accounts', field: 'phone_alternate', value: '456'},
                mockGateway,
                mockValidationTools
            );
        });
    });

    // Test the checkForImpossibleLogic function
    describe('checkForImpossibleLogic', function() {

        beforeEach(function() {

            // Stub the inner function calls that don't need to be actually called
            sinon.stub(mockGateway, 'getDestElementName').returns('Fake name');
        });

        it('should generate a warning for an empty criteria box', function() {
            mockGateway.checkForImpossibleLogic(mockDataObjectEmptyLogic, mockGateway,
                mockValidationTools, mockParsedCriteriaImpossible);
            expect(mockValidationTools.createWarning).toHaveBeenCalledWith(mockGateway,
                'LBL_PMSE_ERROR_LOGIC_IMPOSSIBLE', 'Fake name');
        });

        it('should generate a warning for an impossible logical statement', function() {
            mockGateway.checkForImpossibleLogic(mockDataObjectBadLogic, mockGateway,
                mockValidationTools, mockParsedCriteriaImpossible);
            expect(mockValidationTools.createWarning).toHaveBeenCalledWith(mockGateway,
                'LBL_PMSE_ERROR_LOGIC_IMPOSSIBLE', 'Fake name');
        });

        it('should not generate a warning for a possible logical statement with one atom', function() {
            mockGateway.checkForImpossibleLogic(mockDataObjectBadLogic, mockGateway,
                mockValidationTools, mockParsedCriteriaSimple);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });

        it('should not generate a warning for a possible logical statement with multiple atoms', function() {
            mockGateway.checkForImpossibleLogic(mockDataObjectBadLogic, mockGateway,
                mockValidationTools, mockParsedCriteriaPossible);
            expect(mockValidationTools.createWarning).not.toHaveBeenCalled();
        });
    });
});
