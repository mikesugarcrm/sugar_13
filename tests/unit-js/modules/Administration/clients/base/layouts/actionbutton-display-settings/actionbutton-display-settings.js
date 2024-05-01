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
describe('Administration.Layouts.ActionbuttonDisplaySettings', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var layoutName = 'actionbutton-display-settings';
    var module = 'Administration';
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
            model: new Backbone.Model()
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

            sandbox.spy(testLayout, '_initProperties');
            sandbox.spy(testLayout, '_registerEvents');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly call layout _initProperties', function() {
            expect(testLayout._initProperties.calledOnce).toEqual(true);
        });

        it('should properly call layout _registerEvents', function() {
            expect(testLayout._registerEvents.calledOnce).toEqual(true);
        });

        it('should properly evaluate initial properties', function() {
            expect(testLayout._sideView).toEqual(null);
            expect(testLayout._sidePreview).toEqual(null);
        });
    });

    describe('dispose()', function() {
        var testLayout;

        beforeEach(function() {
            // createLayout() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testLayout = SugarTest.createLayout('base', 'Administration', layoutName, {}, context, true, initOptions);

            sandbox.spy(testLayout, '_disposeSideView');
            sandbox.spy(testLayout, '_disposeSidePreview');

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();

        });

        it('should properly call the _disposeSideView and _disposeSidePreview function', function() {
            testLayout.dispose();
            expect(testLayout._disposeSideView.calledOnce).toEqual(true);
            expect(testLayout._disposeSidePreview.calledOnce).toEqual(true);

            expect(testLayout._sideView).toEqual(null);
            expect(testLayout._sidePreview).toEqual(null);
        });

    });
});
