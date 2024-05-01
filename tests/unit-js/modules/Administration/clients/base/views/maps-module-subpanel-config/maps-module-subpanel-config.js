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
describe('Administration.Views.MapsModuleSubpanelConfigView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'maps-module-subpanel-config';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var getModuleStub;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: module,
            layout: viewName,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = app.context.getContext();

        getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
            return {
                fields: {
                    'name': {
                        vname: 'name'
                    },
                    'description': {
                        vname: 'description'
                    }
                }
            };
        });

        context.safeRetrieveModulesData = function(module) {
            const _modulesData = this.model.get('maps_modulesData') || {};
            let modulesData = app.utils.deepCopy(_modulesData);

            if (_.isEmpty(modulesData)) {
                modulesData[module] = {};
            }

            if (!_.has(modulesData, module)) {
                modulesData[module] = {};
            }

            if (!_.has(modulesData[module], 'mappings')) {
                modulesData[module].mappings = {};
            }

            if (!_.has(modulesData[module], 'settings')) {
                modulesData[module].settings = {};
            }

            if (!_.has(modulesData[module], 'subpanelConfig')) {
                modulesData[module].subpanelConfig = [
                    {
                        name: 'name',
                        fieldName: 'name',
                        label: 'name',
                        position: 0
                    }
                ];
            }

            return modulesData;
        };

        initOptions = {
            context: context,
            widgetModule: 'Accounts'
        };
    });

    afterEach(function() {
        getModuleStub.restore();
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

            sandbox.spy(testView, '_initProperties');
            sandbox.spy(testView, '_registerEvents');

            testView.context.safeRetrieveModulesData = testView.context.safeRetrieveModulesData.bind(testView);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly call _initProperties', function() {
            expect(testView._initProperties.calledOnce).toEqual(true);
        });

        it('should properly call _registerEvents', function() {
            expect(testView._registerEvents.calledOnce).toEqual(true);
        });

        it('should properly set _fields property', function() {
            expect(testView._fields.all).toEqual(testView.model.get('maps_modulesData').Accounts.subpanelConfig);
        });
    });

    describe('resetDefault()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.context.safeRetrieveModulesData = testView.context.safeRetrieveModulesData.bind(testView);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly reset model to default values', function() {
            testView.resetDefault();

            expect(testView._fields.all).toEqual(testView.model.get('maps_modulesData').Accounts.subpanelConfig);
        });
    });

    describe('addColumn()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.context.safeRetrieveModulesData = testView.context.safeRetrieveModulesData.bind(testView);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly add a column to the layout', function() {
            var columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;
            const noColumnsBefore = Object.keys(columns).length;

            testView.addColumn();

            columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;
            const noColumnsAfter = Object.keys(columns).length;

            expect(noColumnsAfter).toEqual(noColumnsBefore + 1);
        });
    });

    describe('removeColumn()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.context.safeRetrieveModulesData = testView.context.safeRetrieveModulesData.bind(testView);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly remove one column from the layout', function() {
            var columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;
            const firstColumnId = columns[0].name;
            const noColumnsBefore = columns.length;

            testView.removeColumn({
                currentTarget: {
                    dataset: {
                        fieldname: firstColumnId
                    }
                }
            });

            columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;
            const noColumnsAfter = columns.length;

            expect(noColumnsAfter).toEqual(noColumnsBefore - 1);
        });
    });

    describe('fieldsChanged()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.context.safeRetrieveModulesData = testView.context.safeRetrieveModulesData.bind(testView);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly change the fields of a column', function() {
            var columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;
            const firstColumnId = columns[0].name;

            testView.fieldsChanged({
                currentTarget: {
                    dataset: {
                        type: firstColumnId
                    },
                },
                val: 'description',
            });

            columns = testView.model.get('maps_modulesData').Accounts.subpanelConfig;

            expect(columns[0].fieldName).toEqual('description');
        });
    });
});
