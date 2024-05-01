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
describe('Administration.Views.ActionbuttonProperties', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-properties';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        app = SugarTest.app;

        SugarTest.loadHandlebarsTemplate('formula-builder', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', 'formula-builder');

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

            sandbox.spy(testView, '_getActiveButtonData');
            sandbox.spy(testView, 'changeProperties');

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly evaluate view buttonData', function() {
            expect(testView._getActiveButtonData.calledOnce).toEqual(true);
            expect(testView.buttonData).toEqual(testModelParams.data.buttons['7fae990d-e32d-47d4-9785-4f6c64968847']);
        });

        it('should properly register context events', function() {
            testView.context.get('model').trigger('update:button:view');
            expect(testView.changeProperties.calledOnce).toEqual(true);
        });
    });

    describe('_createFormulaBuilder()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);

            testView.initialize(initOptions);

            sinon.stub(testView, 'template').callsFake(function() {
                return '<div><span data-fieldname="formula"></span></div>';
            });
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should create a formula builder type field and add it to the view', function() {
            // Force a render of the wrapper and then clear it out
            testView.render();
            testView.$('div[data-fieldname="formula"]').empty();

            testView._createFormulaBuilder();

            expect(testView._formulaBuilder.def.type).toEqual('formula-builder');

            expect(testView.$el.html()).toContain('<div class="span12 formula-builder">');
        });

        describe('dispose()', function() {
            beforeEach(function() {
                // createView() implicitly calls initialize() through the class constructor,
                // so theoretically no need to call it independently, however, in order to spy functions
                // that are called in the initialize, we'll have to reinit it anyways
                testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);

                testView.initialize(initOptions);

                sinon.stub(testView, 'template').callsFake(function() {
                    return '<div><span data-fieldname="formula"></span></div>';
                });
            });

            afterEach(function() {
                testView.dispose();
            });

            it('should properly dispose the formula builder field', function() {
                // Force a render of the wrapper and then clear it out
                testView.render();
                testView._createFormulaBuilder();
                testView.dispose();

                expect(testView._formulaBuilder.disposed).toEqual(true);
                expect(testView.$el).toBeNull();
            });
        });
    });
});
