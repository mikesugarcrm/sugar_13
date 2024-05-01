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
describe('Actions.RunReportAction', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'RunReportAction', 'js', function(d) {
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
            expect(typeof app.actions.RunReport).toEqual('function');
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

        });

        it('should open a new browser tab loading a specific report', function() {
            var def = {
                properties: {
                    id: 123
                }
            };

            var opts = {
                recordModel: app.data.createBean('Contacts', {
                    id: _.uniqueId()
                })
            };

            var action = new app.actions.RunReport(def);

            sinon.stub(action, 'open');

            action.run(opts, currentExecution);

            expect(action.open).toHaveBeenCalledOnce();
            expect(action.open.firstCall.args[0].endsWith('#Reports/123')).toBe(true);
        });
    });
});
