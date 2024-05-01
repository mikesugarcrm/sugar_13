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
describe('Reports.Base.Views.MapsLoggerDetailsModalView', function() {
    var app;
    var context;
    var view;
    var platform = 'base';
    var layoutName = 'record';
    var moduleName = 'Administration';
    var viewName = 'maps-logger-details-modal';
    var componentType = 'view';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent(platform, componentType, viewName, moduleName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', null, moduleName);
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
            viewName,
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

    describe('initialize()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, true);

            testView.initialize({
                context: context,
                detailedLogs: 'test',
            });
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly set view properties based on options', function() {
            expect(testView.detailedLogs).toEqual('test');
        });
    });

    describe('initialize modal', function() {
        it('Should create the modal on DOM', function() {
            var modalEl = view.$el.find('[data-content=maps-logger-details-modal]');

            expect(modalEl.length).toEqual(0);

            $('body').append(view.$el);

            view.openModal();

            modalEl = view.$el.find('[data-content=maps-logger-details-modal]');

            expect(modalEl.length).toEqual(1);
        });
    });
});
