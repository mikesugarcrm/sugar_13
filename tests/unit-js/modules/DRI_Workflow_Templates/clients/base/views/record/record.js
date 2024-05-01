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

describe('DRI_Workflow_Task_Templates.Views.Record', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'record';
    let module = 'DRI_Workflow_Templates';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        context = app.context.getContext({
            module: module,
            model: model,
            create: true
        });
        context.prepare(true);

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', viewName);

        layout = SugarTest.createLayout(
            'base',
            module,
            viewName,
            {},
            null,
            false
        );
        view = SugarTest.createView(
            'base',
            module,
            viewName,
            null,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();

        app = null;
        view = null;
        model = null;
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(app.CJBaseHelper, 'invalidLicenseError');
            sinon.stub(view, '_super');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should call hasAutomateLicense and CJBaseHelper invalidLicenseError', function() {
            sinon.stub(app.user, 'hasAutomateLicense').returns(false);
            view.initialize();

            expect(app.user.hasAutomateLicense).toHaveBeenCalled();
            expect(app.CJBaseHelper.invalidLicenseError).toHaveBeenCalled();
        });

        it('should call hasAutomateLicense and should not call CJBaseHelper invalidLicenseError', function() {
            sinon.stub(app.user, 'hasAutomateLicense').returns(true);
            view.initialize();

            expect(app.user.hasAutomateLicense).toHaveBeenCalled();
            expect(app.CJBaseHelper.invalidLicenseError).not.toHaveBeenCalled();
        });
    });

    describe('hasUnsavedChanges', function() {
        it('should call alert show and dismiss, api buildURL and fileDownload and lang get functions', function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.lang, 'get');
            sinon.stub(app.api, 'fileDownload').callsFake(function(url, callbacks) {
                callbacks.complete();
            });
            view.exportClicked();

            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.fileDownload).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalled();
            expect(app.alert.dismiss).toHaveBeenCalled();
        });
    });

    describe('deleteModel', function() {
        beforeEach(function() {
            if (_.isUndefined(app.router)) {
                app.router = {
                    navigate: function() {}
                };
            }

            sinon.stub(app.router, 'navigate');
            sinon.stub(view, 'unbindBeforeRouteDelete');
            sinon.stub(view, 'getDeleteMessages').returns({success: true});
            sinon.stub(view.context, 'trigger');
        });

        afterEach(function() {
            sinon.restore();
        });

        using('input', [
            {
                success: true,
                _targetUrl: 'https://www.test.com',
            },
            {
                success: false,
                _targetUrl: undefined,
            },
        ],

        function(input) {
            it('should call model destroy, view getDeleteMessages and _modelToDelete should be false', function() {
                sinon.stub(view.model, 'destroy').callsFake(function(request) {
                    input.success ? request.success() : request.error();
                });
                view._targetUrl = input._targetUrl;
                view.deleteModel();

                expect(view._modelToDelete).toBe(false);
                expect(view.model.destroy).toHaveBeenCalled();
                expect(view.getDeleteMessages).toHaveBeenCalled();

                if (input.success) {
                    expect(view.unbindBeforeRouteDelete).toHaveBeenCalled();
                    expect(view.context.trigger).toHaveBeenCalled();
                    expect(app.router.navigate).toHaveBeenCalled();
                }
            });
        });
    });
});
