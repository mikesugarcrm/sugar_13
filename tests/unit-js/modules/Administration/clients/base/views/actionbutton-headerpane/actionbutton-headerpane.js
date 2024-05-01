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
describe('Administration.Views.ActionbuttonHeaderpane', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'actionbutton-headerpane';
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
            'label': 'test',
            'data': {
                'attr': 'value'
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
            expect(testView.actionButtonLabel).toEqual(testModelParams.label);
        });
    });

    describe('saveConfig()', function() {
        var saveCallback = sinon.stub();

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);
            testView.context.set('saveCallback', saveCallback);
            sinon.stub(testView, 'canSaveConfig').returns(true);
            sinon.stub(testView, 'isDropdownValid').returns(true);
            sinon.stub(testView, 'closeDrawer');
        });

        afterEach(function() {
            testView.dispose();
            sinon.restore();
        });

        it('should properly set view settings', function() {
            testView.saveSettings();
            expect(saveCallback).toHaveBeenCalledWith(testModelParams.data);
            expect(testView.closeDrawer).toHaveBeenCalled();
        });
    });

    describe('closeDrawer()', function() {
        var cancelCallback = sinon.stub();

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true);
            testView.context.set('cancelCallback', cancelCallback);

            app.drawer = {
                close: sinon.stub()
            };
        });

        afterEach(function() {
            testView.dispose();
            sinon.restore();
            delete app.drawer;
        });

        it('should properly close drawer and execute callback', function() {
            testView.closeDrawer();
            expect(cancelCallback).toHaveBeenCalled();
            expect(app.drawer.close).toHaveBeenCalled();
        });
    });
});
