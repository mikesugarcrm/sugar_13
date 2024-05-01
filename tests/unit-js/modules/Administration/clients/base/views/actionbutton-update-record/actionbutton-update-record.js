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
describe('Administration.Views.ActionbuttonUpdateRecordView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-update-record';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        var actionDef = {
            'fieldsToBeUpdated': {
                'name': {
                    'fieldName': 'name',
                    'isCalculated': true,
                    'formula': '"TEST"',
                    'value': {
                        'name': '"TEST"'
                    }
                }
            }
        };

        testModelParams = {
            'data': {
                'buttons': {
                    'test_button_id': {
                        'actions': {
                            'test_action_id': actionDef
                        }
                    }
                }
            },
            'module': 'Accounts'
        };

        context = new app.Context();
        context.set({
            module: module,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = new app.Context();

        initOptions = {
            context: context,
            buttonId: 'test_button_id',
            actionId: 'test_action_id',
            actionData: {
                properties: actionDef
            }
        };

        sinon.stub($.fn, 'select2').callsFake(function(sel) {
            var select2 = sinon.stub().returns({
                onSelect: function() {

                }
            });

            $(sel).data('select2', select2);
            return $(sel);
        });
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
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly set view properties based on options and context', function() {
            expect(testView._buttonId).toEqual('test_button_id');
            expect(testView._actionId).toEqual('test_action_id');
            expect(testView._module).toEqual('Accounts');
            expect(testView._properties).toEqual(initOptions.actionData.properties);
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            testView.initialize(initOptions);
            testView.render();
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly create field controllers', function() {
            expect(testView._subComponents.length).toEqual(1);
        });
    });
});