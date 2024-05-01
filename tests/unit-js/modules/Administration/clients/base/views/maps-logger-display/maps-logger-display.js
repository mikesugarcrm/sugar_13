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
describe('Administration.Views.MapLoggerDisplayView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'maps-logger-display';
    var module = 'Administration';
    var parentLayout;
    var initOptions;
    var context;
    var displayLogsData;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
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

        displayLogsData = {
            totalPages: 5,
            nextOffset: 20,
            records: [
                {
                    error_message: '[JobUsageQuota: Account has already submitted 50 jobs in the past 24 hours]',
                    geocode_parent_type: 'Accounts',
                    geocoded: 0,
                    parent_id: '092d192c-f091-11ec-9cdb-0242ac140008',
                    parent_name: 'Income Free Investing LP',
                    parent_type: 'Accounts',
                    status: 'FAILED',
                }
            ]
        };
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

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
            parentLayout.dispose();
        });

        it('should properly call _initProperties', function() {
            expect(testView._initProperties.calledOnce).toEqual(true);
        });
    });

    describe('Data load', function() {
        var testView;
        var triggerStub;

        beforeEach(function() {
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, true);

            testView.layout = parentLayout;

            triggerStub = sinon.stub(testView, 'loadLogs');

            testView.model.set('enabledLoggingModules', ['Accounts']);
            testView.model.set('totalPages', 5);
        });

        it('should call loadLogs function', function() {
            testView.render();

            var enabledModule = testView.$('[data-action="paginate-next"]')[0];
            enabledModule.click();

            expect(triggerStub.calledOnce).toBe(true);
        });

        afterEach(function() {
            testView.dispose();
            parentLayout.dispose();
        });
    });

    describe('Create modal', function() {
        var testView;
        var triggerStub;

        beforeEach(function() {
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, true);

            testView.layout = parentLayout;

            sinon.stub(app.view, 'createView').callsFake(function() {
                return {
                    openModal: function() {},
                    dispose: function() {},
                };
            });

            testView.displayLogs(displayLogsData);
        });

        it('should call the create view for modal', function() {
            testView.render();

            expect(testView.modal).toBeUndefined();

            var enabledModule = testView.$('[data-action="log-details"]');
            enabledModule.click();

            expect(testView.modal).toBeTruthy();
        });

        afterEach(function() {
            testView.dispose();
            parentLayout.dispose();
        });
    });
});
