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
describe('Administration.Layouts.ActionbuttonAction', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var layoutName = 'actionbutton-action';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

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
                                    'value': {}
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
            context: context,
            actionId: '118c5c26-46a5-4a26-a85f-0b0b9fe189d2',
            actionType: 'create-record',
            actionData: testModelParams.data.buttons['7fae990d-e32d-47d4-9785-4f6c64968847']
                .actions['118c5c26-46a5-4a26-a85f-0b0b9fe189d2']
        };
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
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

        it('should properly set _actionId attribute', function() {
            expect(testLayout._actionId).toEqual(initOptions.actionId);
        });

        it('should properly set the _actions attribute', function() {
            expect(testLayout._actions).toEqual(
                {
                    label: 'LBL_ACTIONBUTTON_ACTION',
                    id: 'actionsDropdown',
                    value: initOptions.actionType,
                    options: testModelParams.actions,
                    disabled: false
                }
            );
        });

        it('should properly evaluate layout _buttonData', function() {
            expect(testLayout._getActiveButtonData.calledOnce).toEqual(true);
            expect(testLayout._buttonData).toEqual(
                testModelParams.data.buttons['7fae990d-e32d-47d4-9785-4f6c64968847']
            );
        });

        it('should properly set _actionData attribute', function() {
            expect(testLayout._actionData).toEqual(initOptions.actionData);
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
});
