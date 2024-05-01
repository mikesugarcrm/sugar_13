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
describe('Reports.Base.Views.ReportCopyModal', function() {
    var app;
    var context;
    var testView;
    var initOptions;
    var model;
    var platform = 'base';
    var moduleName = 'Reports';
    var componentType = 'view';
    var viewName = 'report-copy-modal';
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent(platform, componentType, viewName, moduleName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', null, moduleName);
        SugarTest.testMetadata.set();

        app = SugarTest.app;

        model = app.data.createBean(moduleName, {
            report_type: 'Matrix',
        });

        app.routing.start();

        context = app.context.getContext();
        context.set({
            module: moduleName,
            model: model
        });
        context.prepare();
        context.parent = app.context.getContext();

        initOptions = {
            context: context
        };

        if (!$.fn.modal) {
            $.fn.modal = function() {};
        }
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        $.fn.modal = null;
        app = null;
    });

    describe('initialize modal', function() {
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView(
                platform,
                moduleName,
                viewName,
                {
                    module: moduleName,
                    model: model
                },
                context,
                true
            );
        });

        afterEach(function() {
            $('body').remove(testView.$el);
            testView.dispose();
        });

        it('Should create the modal on DOM', function() {
            var modalEl = testView.$el.find('[data-content=report-copy-modal]');

            expect(modalEl.length).toEqual(0);

            $('body').append(testView.$el);

            testView.openModal();

            modalEl = testView.$el.find('[data-content=report-copy-modal]');

            expect(modalEl.length).toEqual(1);
        });
    });

    describe('Navigate', function() {
        var navigateStub;

        beforeEach(function() {
            navigateStub = sinon.stub(app.router, 'navigate').callsFake(function() {
                return true;
            });

            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView(
                platform,
                moduleName,
                viewName,
                {
                    module: moduleName,
                    model: model
                },
                context,
                true
            );

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('Should navigate', function() {

            testView.initialize({
                context: context
            });

            expect(navigateStub.called).toBeFalsy();

            testView.copyAs({
                currentTarget: {
                    dataset: {
                        type: 'Matrix',
                    },
                },
            });

            expect(navigateStub.called).toBeTruthy();

            navigateStub.restore();
        });
    });
});
