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
describe('Actions.AssignRecordAction', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'AssignRecordAction', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
    });

    describe('Action registration', function() {
        it('should have been added to the `app.actions` registry', function() {
            expect(typeof app.actions.AssignRecord).toEqual('function');
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

        it('should properly update the assigned user of a record', function() {
            var def = {
                properties: {
                    id: '123',
                    name: 'John Doe'
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            var saveStub = sinon.stub(Backbone.Model.prototype, 'save');

            var opts = {
                recordModel: model
            };

            var action = new app.actions.AssignRecord(def);

            action.run(opts, currentExecution);

            var patchModel = saveStub.getCall(0).thisValue;

            expect(saveStub).toHaveBeenCalledOnce();
            expect(patchModel.get('id')).toBe(model.get('id'));
            expect(patchModel.get('assigned_user_id')).toBe('123');
            expect(patchModel.get('assigned_user_name')).toBe('John Doe');

            saveStub.restore();
        });
    });
});
