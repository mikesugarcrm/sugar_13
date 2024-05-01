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
describe('Reports.Base.Views.ReportDetailModal', function() {
    var app;
    var context;
    var view;
    var platform = 'base';
    var layoutName = 'record';
    var moduleName = 'Reports';
    var componentType = 'view';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent(platform, componentType, 'report-detail-modal', moduleName);
        SugarTest.loadHandlebarsTemplate('report-detail-modal', 'view', 'base', null, moduleName);
        SugarTest.testMetadata.set();

        var model = app.data.createBean(moduleName);

        app.routing.start();
        app.data.declareModels();

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName,
            model: model,
            collection: app.data.createBeanCollection(),
        });

        sinonSandbox = sinon.createSandbox();
        view = SugarTest.createView(
            platform,
            moduleName,
            'report-detail-modal',
            {
                module: moduleName,
                model: model,
            },
            context,
            true
        );

        if (!$.fn.modal) {
            $.fn.modal = function() {};
        }
    });

    afterEach(function() {
        $('body').remove(view.$el);
        view.dispose();
        view = null;
        app.cache.cutAll();
        app.view.reset();
        $.fn.modal = null;
        app = null;
    });

    describe('initialize modal', function() {
        it('Should create the modal on DOM', function() {
            var modalEl = view.$el.find('[data-content=report-details-modal]');

            expect(modalEl.length).toEqual(0);

            $('body').append(view.$el);

            view.openModal();

            modalEl = view.$el.find('[data-content=report-details-modal]');

            expect(modalEl.length).toEqual(1);
        });

        it('Should call _createReportDetails', function() {
            var createReportDtailsStub = sinonSandbox.spy(view, '_createReportDetails');

            view.initialize({
                context: context
            });

            expect(createReportDtailsStub.called).toBeTruthy();

            createReportDtailsStub.restore();
        });
    });
});
