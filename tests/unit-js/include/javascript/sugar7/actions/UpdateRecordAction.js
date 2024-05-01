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
describe('Actions.UpdateRecordAction', function() {
    var app;
    var sandbox;
    var originalContext;

    beforeEach(function() {
        app = SugarTest.app;
        originalContext = app.controller.context;
        app.controller.context = new app.Context();

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'UpdateRecordAction', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        app.controller.context = originalContext;
        sinon.restore();
        sandbox.restore();
    });

    describe('Action registration', function() {
        it('should have been added to the `app.actions` registry', function() {
            expect(typeof app.actions.UpdateRecord).toEqual('function');
        });
    });

    describe('run()', function() {
        var currentExecution;

        beforeEach(function() {
            currentExecution = {
                nextAction: sinon.stub()
            };
        });

        afterEach(function() {
            sinon.restore();
            sandbox.restore();
        });

        it('should properly update and save record with static values', function() {
            var def = {
                properties: {
                    autoSave: true,
                    fieldsToBeUpdated: {
                        description: {
                            fieldName: 'description',
                            isCalculated: false,
                            formula: '',
                            value: {
                                description: 'TEST'
                            }
                        }
                    }
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            model.fields.description = {
                name: 'description',
                type: 'textarea'
            };

            var saveStub = sinon.stub(Backbone.Model.prototype, 'save');

            var opts = {
                recordModel: model
            };

            var action = new app.actions.UpdateRecord(def);

            action.run(opts, currentExecution);

            var patchModel = saveStub.getCall(0).thisValue;

            expect(saveStub).toHaveBeenCalledOnce();
            expect(patchModel.get('description')).toBe('TEST');

            saveStub.restore();
        });

        it('should properly update record and enter edit mode with static values', function() {
            var def = {
                properties: {
                    autoSave: false,
                    fieldsToBeUpdated: {
                        description: {
                            fieldName: 'description',
                            isCalculated: false,
                            formula: '',
                            value: {
                                description: 'TEST'
                            }
                        }
                    }
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            model.fields.description = {
                name: 'description',
                type: 'textarea'
            };

            sinon.stub(model, 'save');
            var triggerSpy = sinon.spy(app.controller.context, 'trigger');

            var opts = {
                recordModel: model
            };

            var action = new app.actions.UpdateRecord(def);

            action.run(opts, currentExecution);

            expect(model.save.called).toBe(false);
            expect(model.get('description')).toBe('TEST');

            expect(triggerSpy).toHaveBeenCalledWith('button:edit_button:click');
        });

        it('should properly update and save record with calculated values', function() {
            var def = {
                properties: {
                    autoSave: true,
                    fieldsToBeUpdated: {
                        description: {
                            fieldName: 'description',
                            isCalculated: true,
                            formula: '"TEST"',
                            value: {
                            }
                        }
                    }
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            model.fields.description = {
                name: 'description',
                type: 'textarea'
            };

            var saveStub = sinon.stub(Backbone.Model.prototype, 'save');

            var opts = {
                recordModel: model
            };

            var action = new app.actions.UpdateRecord(def);

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*rest\/v10\/actionButton\/evaluateExpression.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(
                    {
                        description: 'TEST',
                    }
                )]);

            action.run(opts, currentExecution);

            SugarTest.server.respond();

            var patchModel = saveStub.getCall(0).thisValue;

            expect(saveStub).toHaveBeenCalledOnce();
            expect(patchModel.get('description')).toBe('TEST');

            saveStub.restore();
        });
    });
});
