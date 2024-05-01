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
describe('Administration.Layouts.MapsModuleSetup', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var layoutName = 'maps-module-setup';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        context = app.context.getContext();
        context.set({
            module: module,
            layout: layoutName,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = app.context.getContext();

        initOptions = {
            context: context,
        };
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app = null;
    });

    describe('initialize()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly set _configView attribute', function() {
            expect(testLayout._configView).toEqual(null);
        });
    });

    describe('dispose()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_disposeConfigView');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly call the _disposeConfigView function', function() {
            testLayout.dispose();
            expect(testLayout._disposeConfigView.calledOnce).toEqual(true);
            expect(testLayout._configView).toEqual(null);
        });
    });

    describe('updateUI()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_createConfigView');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('should properly create a new config view component', function() {
            //module, moduleData, viewName, $container
            testLayout.updateUI({
                viewName: 'maps-module-settings',
                widgetModule: 'Accounts'
            });

            expect(testLayout._createConfigView.calledOnce).toEqual(true);
            expect(testLayout._configView.options.widgetModule). toEqual('Accounts');
        });
    });
});
