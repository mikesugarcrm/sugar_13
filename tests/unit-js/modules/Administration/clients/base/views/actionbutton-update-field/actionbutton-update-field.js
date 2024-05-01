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
describe('Administration.Views.ActionbuttonUpdateFieldView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-update-field';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;
    var metadata;

    beforeEach(function() {
        metadata = {
            fields: {
                name: {
                    name: 'name',
                    vname: 'LBL_NAME',
                    type: 'varchar',
                    len: 255,
                    comment: 'Name of this bean'
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: 'bc6fc50d9d0d3064f5d522d9e15968fa'
        };

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Accounts', metadata);
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        testModelParams = {
            'module': 'Accounts'
        };

        var testModel = new Backbone.Model(testModelParams);

        testModel.fields = testModel.fields || {};
        testModel.fields.name = {
            name: 'name',
            type: 'name'
        };

        context = new app.Context();
        context.set({
            module: module,
            model: testModel
        });

        context.prepare();
        context.parent = new app.Context();

        initOptions = {
            context: context,
            isCalculated: true,
            fieldName: 'name',
            formula: '"TEST"',
            value: {
                'name': '"TEST"'
            },
            callback: sinon.stub(),
            deleteCallback: sinon.stub(),
            fieldModule: 'Accounts'
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
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly set view properties based on options and context', function() {
            expect(testView._properties).toEqual({
                _isCalculated: initOptions.isCalculated,
                _fieldName: initOptions.fieldName,
                _value: initOptions.value,
                _formula: initOptions.formula,
            });

            expect(testView._callback).toEqual(initOptions.callback);
            expect(testView._deleteCallback).toEqual(initOptions.deleteCallback);
            expect(testView._module).toEqual(initOptions.fieldModule);
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            testView.initialize(initOptions);

            sinon.stub(testView, 'template').callsFake(function() {
                return '<div class="ab-update-field-container" data-container="field">';
            });

            testView.render();
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly create the field controller', function() {
            expect(testView._controller).toNotEqual(null);
        });
    });

    describe('dispose()', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            testView.initialize(initOptions);

            sinon.stub(testView, 'template').callsFake(function() {
                return '<div class="ab-update-field-container" data-container="field">';
            });

            testView.render();
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly dispose controller', function() {
            var _controller = testView._controller;
            testView.dispose();

            expect(testView._controller).toEqual(null);
            expect(_controller.disposed).toEqual(true);
        });
    });
});
