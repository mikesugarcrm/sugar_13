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
describe('View.Views.Base.DriLicenseErrors', function() {
    let app;
    let view;
    let context;
    let layout;
    let initOptions;
    let moduleName = 'Accounts';
    let viewName = 'dri-license-errors';
    let layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();
        context.parent = app.context.getContext();
        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });
        view = SugarTest.createView(
            'base',
            moduleName,
            'dri-license-errors',
            null,
            context,
            null,
            layout
        );
        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.fields = null;
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('should call the initialize function and initialze some properties', function() {
            sinon.stub(view, 'listenTo');
            view.initialize(initOptions);
            expect(view.listenTo).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('onError', function() {
        it('should call the onError function to displays an error when having an invalid license', function() {
            let method = 'onError';
            let model = new Backbone.Model();
            let error = {
                code: 'invalid_license',
            };
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            view.onError(method, model, initOptions, error);
            expect(app.alert.show).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });
});
