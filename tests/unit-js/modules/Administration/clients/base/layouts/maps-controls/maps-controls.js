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
describe('Administration.Layouts.MapsControls', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var layoutName = 'maps-controls';
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

            sandbox.spy(testLayout, '_getSelect2Data');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly set _modulesWidgets attribute', function() {
            expect(testLayout._modulesWidgets).toEqual([]);
        });

        it('should properly set the _actions attribute', function() {
            expect(testLayout._select2Data).toEqual(
                {
                    'logLevel': {
                        'fatal': 'LBL_MAPS_LOG_LVL_FATAL',
                        'debug': 'LBL_MAPS_LOG_LVL_DEBUG',
                        'error': 'LBL_MAPS_LOG_LVL_ERROR'
                    },
                    'unitType': {
                        'miles': 'LBL_MAPS_UNIT_TYPE_MILES',
                        'km': 'LBL_MAPS_UNIT_TYPE_KM'
                    }
                }
            );
        });

        it('should properly call layout _getSelect2Data', function() {
            expect(testLayout._getSelect2Data.calledOnce).toEqual(true);
        });
    });

    describe('dispose()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_disposeModulesWidgets');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly call the _disposeModulesWidgets function', function() {
            testLayout.dispose();
            expect(testLayout._disposeModulesWidgets.calledOnce).toEqual(true);
            expect(testLayout._modulesWidgets).toEqual([]);
        });

    });

    describe('_createModuleWidgetView()', function() {
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

        it('should properly create a new module widget view component', function() {
            const existingWidgets = testLayout._modulesWidgets.length;
            //module, moduleData, viewName, $container
            testLayout._createModuleWidgetView('Accounts', {}, {
                append: function() {},
            });

            var moduleWidgetLength = testLayout._modulesWidgets.length - 1;

            expect(testLayout._modulesWidgets[moduleWidgetLength].options.widgetModule).toEqual('Accounts');
            expect(testLayout._modulesWidgets.length).toEqual(existingWidgets + 1);
        });
    });
});
