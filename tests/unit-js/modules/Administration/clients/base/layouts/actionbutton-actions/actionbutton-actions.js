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
describe('Administration.Layouts.ActionbuttonActions', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var layoutName = 'actionbutton-actions';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var viewActionbuttonCreateRecord;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        app = SugarTest.app;

        viewActionbuttonCreateRecord = app.view.createView({name: 'actionbutton-create-record'});
        viewActionbuttonCreateRecord.setup = function setup() { };
        viewActionbuttonCreateRecord.getProperties = function() {
            return {
                attributes: {},
                parentAttributes: {},
                module: '',
                link: '',
                mustLinkRecord: false,
                copyFromParent: false,
                autoCreate: false
            };
        };

        sinon.stub(viewActionbuttonCreateRecord, 'setup');
        SugarTest.addComponent('base', 'view', 'actionbutton-create-record',
            viewActionbuttonCreateRecord, 'Administration');

        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'layout', 'actionbutton-action', 'Administration');

        var buttons = {
            '7fae990d-e32d-47d4-9785-4f6c64968847': {
                'active': true,
                'buttonId': '7fae990d-e32d-47d4-9785-4f6c64968847',
                'orderNumber': 0,
                'properties': {
                    'label': 'Update Name',
                    'description': '',
                    'showLabel': true,
                    'showIcon': true,
                    'colorScheme': 'secondary',
                    'icon': 'sicon-settings',
                    'isDependent': false,
                    'formula': ''
                },
                'actions': {
                    '118c5c26-46a5-4a26-a85f-0b0b9fe189d2': {
                        'actionType': 'updateRecord',
                        'orderNumber': 0,
                        'properties': {
                            'fieldsToBeUpdated': {
                                'name': {
                                    'fieldName': 'name',
                                    'isCalculated': true,
                                    'formula': 'concat($name, \'blabla\')',
                                    'value': {

                                    }
                                }
                            },
                            'autoSave': true
                        }
                    }
                }
            }
        };

        testModelParams = {
            'actions': {
                'assign-record': 'LBL_ACTIONBUTTON_ASSIGN_RECORD',
                'compose-email': 'LBL_ACTIONBUTTON_COMPOSE_EMAIL',
                'create-record': 'LBL_ACTIONBUTTON_CREATE_RECORD',
                'open-url': 'LBL_ACTIONBUTTON_OPEN_URL',
                'run-report': 'LBL_ACTIONBUTTON_RUN_REPORT',
                'update-record': 'LBL_ACTIONBUTTON_UPDATE_RECORD'
            },
            'data': {
                'settings': {
                    'type': 'button',
                    'showFieldLabel': false,
                    'showInRecordHeader': true,
                    'hideOnEdit': false,
                    'size': 'small'
                },
                'buttons': buttons
            }
        };

        context = app.context.getContext();
        context.set({
            module: module,
            layout: layoutName,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = app.context.getContext();

        initOptions = {
            context: context
        };
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        viewActionbuttonCreateRecord.dispose();
        app = null;
    });

    describe('initialize()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_getActiveButtonData');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('should properly evaluate layout _buttonData', function() {
            expect(testLayout._getActiveButtonData.calledOnce).toEqual(true);
            expect(testLayout.buttonData).toEqual(testModelParams.data.buttons['7fae990d-e32d-47d4-9785-4f6c64968847']);
        });

    });

    describe('dispose()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_disposeSubComponents');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('should properly call the _disposeSubComponents function', function() {
            testLayout.dispose();
            expect(testLayout._disposeSubComponents.calledOnce).toEqual(true);
            expect(testLayout._subComponents).toEqual([]);
        });

    });

    describe('addNewAction()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_createAction');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('should properly add a new action component', function() {
            testLayout.addNewAction();
            expect(testLayout._createAction.calledOnce).toEqual(true);
            expect(testLayout._subComponents.length);
        });
    });
});
