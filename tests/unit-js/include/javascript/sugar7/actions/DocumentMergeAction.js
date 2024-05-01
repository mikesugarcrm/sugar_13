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
describe('Actions.DocumentMergeAction', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'DocumentMergeAction', 'js', function(d) {
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
            expect(typeof app.actions.DocumentMerge).toEqual('function');
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

        it('should trigger the document:merge event', function() {
            var currentRecordId = _.uniqueId();
            var currentRecordModule = 'Contacts';
            var templateId = 'template_id_test';
            var templateName = 'template_name_test';
            var isPdf = false;

            var def = {
                properties: {
                    id: templateId,
                    name: templateName,
                    pdf: isPdf,
                }
            };

            var model = app.data.createBean(currentRecordModule, {
                id: currentRecordId,
            });

            var opts = {
                recordModel: model,
            };

            var action = new app.actions.DocumentMerge(def);

            var triggerStub = sandbox.stub(app.events, 'trigger');
            sinon.stub(app.user, 'hasSellServeLicense').returns(true);
            action.run(opts, currentExecution);

            expect(triggerStub).toHaveBeenCalledWith('document:merge', {
                currentRecordId,
                currentRecordModule,
                templateId,
                templateName,
                isPdf,
            });
        });
    });
});
