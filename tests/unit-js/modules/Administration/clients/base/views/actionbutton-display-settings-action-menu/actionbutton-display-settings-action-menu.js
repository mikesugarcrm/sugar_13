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
describe('Administration.Views.ActionbuttonDisplaySettingsActionMenu', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-display-settings-action-menu';
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
                'actionMenu': {
                    orderNumber: 1,
                    listView: false,
                    recordView: false,
                    recordViewDashlet: false,
                    subpanels: false
                }
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

        it('should properly set view settings', function() {
            expect(testView._actionMenu).toEqual(testModelParams.data.actionMenu);
        });

    });

    describe('_updateActionMenuSettings()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly update view settings in context', function() {
            var newSettings = {
                orderNumber: 2,
                listView: false,
                recordView: true,
                recordViewDashlet: false,
                subpanels: false
            };

            testView._actionMenu = newSettings;
            testView._updateActionMenuSettings();
            
            expect(testView.context.get('model').get('data').actionMenu).toEqual(newSettings);
        });

    });
});
