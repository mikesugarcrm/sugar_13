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
describe('Administration.Views.ActionbuttonParentFieldView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-parent-field';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;
    var testView;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        testModelParams = {
            'module': 'Contacts'
        };

        context = new app.Context();
        context.set({
            module: module,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = new app.Context();

        initOptions = {
            context: context,
            fieldName: 'phone_home',
            parentFieldName: 'phone_home',
            callback: sinon.stub(),
            deleteCallback: sinon.stub(),
            fieldModule: 'Contacts'
        };

        sinon.stub($.fn, 'select2').callsFake(function(sel) {
            var select2 = sinon.stub().returns({
                onSelect: function() {

                }
            });

            $(sel).data('select2', select2);
            return $(sel);
        });
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
                _fieldName: initOptions.fieldName,
                _parentFieldName: initOptions.parentFieldName
            });

            expect(testView._callback).toEqual(initOptions.callback);
            expect(testView._deleteCallback).toEqual(initOptions.deleteCallback);
            expect(testView._module).toEqual(initOptions.fieldModule);
            expect(testView._parentFields).toEqual({
                'phone_home': 'LBL_PHONE_HOME'
            });
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
                return '<div class="span12" data-fieldname="field">';
            });

            testView.render();
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly create select2 component', function() {
            expect(testView.$field.length).toEqual(1);
        });
    });
});
