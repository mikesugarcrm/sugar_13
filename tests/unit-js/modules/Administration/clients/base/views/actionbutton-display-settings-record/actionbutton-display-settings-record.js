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
describe('Administration.Views.ActionbuttonDisplaySettingsRecord', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-display-settings-record';
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
                'settings': {
                    type: 'button',
                    size: 'default',
                    showFieldLabel: true,
                    showInRecordHeader: true,
                    hideOnEdit: true
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
            expect(testView._settings).toEqual(testModelParams.data.settings);
        });

    });

    describe('_updateDisplaySettings()', function() {
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
                type: 'button',
                size: 'default',
                showFieldLabel: false,
                showInRecordHeader: false,
                hideOnEdit: false
            };

            testView._settings = newSettings;
            testView._updateDisplaySettings();
            expect(testView.context.get('model').get('data').settings).toEqual(newSettings);
        });

    });
});
