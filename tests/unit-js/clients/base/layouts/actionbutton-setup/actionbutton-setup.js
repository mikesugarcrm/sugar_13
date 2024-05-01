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
describe('Base.Layout.ActionbuttonSetup', function() {
    var layout;
    var app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        var testMeta;
        var testLayout;
        var testParams;
        var initOptions;

        beforeEach(function() {
            testMeta = {
                'name': 'actionbutton-setup',
                'type': 'actionbutton-setup',
                'span': 12,
                'actions': {
                    'assign-record': 'LBL_ACTIONBUTTON_ASSIGN_RECORD',
                    'compose-email': 'LBL_ACTIONBUTTON_COMPOSE_EMAIL',
                    'create-record': 'LBL_ACTIONBUTTON_CREATE_RECORD',
                    'open-url': 'LBL_ACTIONBUTTON_OPEN_URL',
                    'run-report': 'LBL_ACTIONBUTTON_RUN_REPORT',
                    'update-record': 'LBL_ACTIONBUTTON_UPDATE_RECORD'
                },
            };

            var context = app.context.getContext();
            context.set({
                module: 'Administration',
                layout: 'actionbutton-setup',
                model: new Backbone.Model()
            });

            context.prepare();
            context.parent = app.context.getContext();

            initOptions = {
                meta: testMeta,
                context: context
            };

            testLayout = SugarTest.createLayout('base', 'Administration', 'actionbutton-setup', {});
            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('will set context actions atttribute properly', function() {
            expect(testLayout.context.get('model').get('actions')).toEqual(testMeta.actions);
        });
    });
});
