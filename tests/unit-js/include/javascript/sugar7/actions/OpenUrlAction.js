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
describe('Actions.OpenUrlAction', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'OpenUrlAction', 'js', function(d) {
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
            expect(typeof app.actions.OpenUrl).toEqual('function');
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

        it('should open a new tab with a static url', function() {
            var def = {
                properties: {
                    formula: '',
                    calculated: false,
                    url: 'https://www.test.com'
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            var opts = {
                recordModel: model
            };

            var action = new app.actions.OpenUrl(def);

            action.openNewWindow = sinon.stub();

            action.run(opts, currentExecution);

            expect(action.openNewWindow.calledWith('https://www.test.com')).toBe(true);
        });

        it('should properly update record and enter edit mode with static values', function() {
            var def = {
                properties: {
                    formula: '$website',
                    calculated: true,
                    url: ''
                }
            };

            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
            });

            var opts = {
                recordModel: model
            };

            var action = new app.actions.OpenUrl(def);

            action.openNewWindow = sinon.stub();

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*rest\/v10\/actionButton\/evaluateCalculatedUrl.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(
                    {
                        buildUrlTempField: {
                            value: 'https://www.sugarcrm.com'
                        },
                    }
                )]);

            action.run(opts, currentExecution);

            SugarTest.server.respond();

            expect(action.openNewWindow.calledWith('https://www.sugarcrm.com')).toBe(true);
        });
    });
});
