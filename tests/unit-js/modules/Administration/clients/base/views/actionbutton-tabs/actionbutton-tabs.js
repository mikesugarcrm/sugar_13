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
describe('Administration.Views.ActionbuttonTabs', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-tabs';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;
    var encodeData;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        app = SugarTest.app;

        SugarTest.testMetadata.set();

        encodeData = function() { };

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
        app = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly set the view `buttons` attribute', function() {
            expect(testView.buttons).toEqual(testModelParams.data.buttons);
        });
    });

    describe('buttons: adding/removing', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly add another definition to the buttons array', function() {
            testView._createButton();

            var buttonIds = Object.keys(testView.buttons);
            expect(buttonIds.length).toEqual(2);
        });

        it('should properly remove a button from the button array', function() {
            testView._createButton();

            buttonIds = Object.keys(testView.buttons);
            expect(buttonIds.length).toEqual(2);

            sinon.stub(app.alert, 'show')
                .yieldsTo('onConfirm');

            testView.deleteButton({
                currentTarget: $('div').data('id', buttonIds[1])
            });

            var buttonIds = Object.keys(testView.buttons);
            expect(buttonIds.length).toEqual(1);
        });
    });
});
