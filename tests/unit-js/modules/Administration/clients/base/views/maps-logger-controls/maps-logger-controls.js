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
describe('Administration.Views.MapLoggerControlsView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'maps-logger-controls';
    var module = 'Administration';
    var parentLayout;
    var initOptions;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate(
            viewName,
            'view',
            'base',
            'maps-logger-module-widget',
            module
        );

        SugarTest.loadHandlebarsTemplate(
            viewName,
            'view',
            'base',
            viewName,
            module
        );

        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: module,
            model: new Backbone.Model('Geocode'),
        });

        context.prepare();
        context.parent = app.context.getContext();

        initOptions = {
            context: context,
        };

        parentLayout = app.view.createLayout({type: 'base'});
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app = null;
    });

    describe('initialize()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.layout = parentLayout;

            sandbox.spy(testView, '_initProperties');
            sandbox.spy(testView, 'setAvailableSugarModules');

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
            parentLayout.dispose();
        });

        it('should properly call _initProperties', function() {
            expect(testView._initProperties.calledOnce).toEqual(true);
        });

        it('should properly set defaultData property', function() {
            testView.configRetrieved();

            var defaultDate = app.date().subtract(1, 'days').format('YYYY-MM-DD');

            expect(testView.model.get('maps_loggerLevel')).toEqual('error');
            expect(testView.model.get('maps_loggerStartdate')).toEqual(defaultDate);
            expect(testView.model.get('maps_enabled_modules')).toEqual([]);
            expect(testView.model.get('enabledLoggingModules')).toEqual([]);
        });
    });

    describe('Data changed', function() {
        var testView;
        var triggerStub;

        beforeEach(function() {
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, true);

            testView.layout = parentLayout;

            triggerStub = sinon.stub(testView.context, 'trigger');
            testView.availableModulesLoaded = true;
            testView.model.set('maps_enabled_modules', ['Accounts']);
            testView.model.set('enabledLoggingModules', ['Accounts']);
        });

        it('should fire notify event', function() {
            testView.render();

            var enabledModul = testView.$('input[data-fieldname="enableModule"]')[0];

            enabledModul.click();

            expect(triggerStub.calledWith('retrieved:maps:logs')).toBe(true);
        });

        afterEach(function() {
            testView.dispose();
            parentLayout.dispose();
        });
    });
});
