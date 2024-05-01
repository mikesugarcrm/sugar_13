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
describe('Administration.Views.MapsConfigView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'maps-config';
    var module = 'Administration';
    var modStringMock;
    var fieldsId;
    var testModelParams;
    var initOptions;
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

        initOptions = {
            context: context,
            meta: {}
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

            sandbox.spy(testView, '_initProperties');

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly call _initProperties', function() {
            expect(testView._initProperties.calledOnce).toEqual(true);
        });

        it('should properly set label property', function() {
            if (testView.meta) {
                expect(testView.meta.label).toEqual('LBL_MAP_CONFIG_TITLE');
            }
        });

        it('should properly bind safeRetrieveModulesData to context', function() {
            expect(typeof testView.context.safeRetrieveModulesData).toBe('function');
        });
    });

    describe('safeRetrieveModulesData()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            modStringMock = sinon.stub(app.lang, 'getModString').returns('Coordinates');
            fieldsId = sinon.stub(app.utils, 'generateUUID').returns('field_id');

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly return a default config object', function() {
            const modulesData = testView.safeRetrieveModulesData('Accounts');
            const moduleData = modulesData.Accounts;

            expect(moduleData.settings).toEqual({
                'autopopulate': false
            });

            expect(moduleData.subpanelConfig).toEqual([]);
            expect(moduleData.mappings).toEqual({
                'mappings': true
            });
        });
    });
});
