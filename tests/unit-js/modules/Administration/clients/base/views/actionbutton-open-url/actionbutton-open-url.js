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
describe('Administration.Views.ActionbuttonOpenUrlView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-open-url';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        app = SugarTest.app;

        SugarTest.testMetadata.set();

        testModelParams = {
            'data': {
                'buttons': {
                    'test_button_id': {
                        'actions': {
                            'test_action_id': {
                                'url': 'https://www.google.com',
                                'calculated': false,
                                'formula': 'concat("https://www.google.com/?q=",$name)'
                            }
                        }
                    }
                }
            }
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
                properties: {
                    'url': 'https://www.google.com',
                    'calculated': true,
                    'formula': 'concat("https://www.google.com/?q=",$name)'
                }
            }
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

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly set view properties based on options and context', function() {
            expect(testView._buttonId).toEqual('test_button_id');
            expect(testView._actionId).toEqual('test_action_id');
            expect(testView._properties).toEqual(initOptions.actionData.properties);
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
                return '<div><div data-fieldname="formula"></div></div>';
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
                    return '<div><div data-fieldname="formula"></div></div>';
                });
            });

            afterEach(function() {
                testView.dispose();
            });

            it('should properly dispose the formula builder field', function() {
                // Force a render of the wrapper and then clear it out
                testView.render();
                testView._createFormulaBuilder();

                var _formulaBuilder = testView._formulaBuilder;

                testView.dispose();

                expect(_formulaBuilder.disposed).toEqual(true);
                expect(testView._formulaBuilder).toBe(null);
                expect(testView.$el).toBeNull();
            });
        });
    });

    describe('calculatedChanged()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly change the calculated bool value on UI change event', function() {
            testView.calculatedChanged({
                currentTarget: {
                    checked: false
                }
            });

            expect(testView._properties).toEqual({
                'url': 'https://www.google.com',
                'calculated': false,
                'formula': 'concat("https://www.google.com/?q=",$name)'
            });

            expect(testView.context.get('model').get('data')
                .buttons.test_button_id.actions.test_action_id.properties)
                .toEqual({
                    'url': 'https://www.google.com',
                    'calculated': false,
                    'formula': 'concat("https://www.google.com/?q=",$name)'
                });
        });
    });

    describe('urlChanged()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly change the url on user input', function() {
            testView.urlChanged({
                currentTarget: {
                    value: 'https://www.youtube.com'
                }
            });

            expect(testView._properties.url).toEqual('https://www.youtube.com');

            var buttonData = testView.context.get('model').get('data').buttons.test_button_id;

            expect(buttonData.actions.test_action_id.properties.url).toEqual('https://www.youtube.com');
        });
    });

    describe('formulaChanged()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly change the url formula on field change event', function() {
            testView.formulaChanged('"https://www.test.com"');

            expect(testView._properties.formula).toEqual('"https://www.test.com"');

            var buttonData = testView.context.get('model').get('data').buttons.test_button_id;

            expect(buttonData.actions.test_action_id.properties.formula).toEqual('"https://www.test.com"');
        });
    });
});