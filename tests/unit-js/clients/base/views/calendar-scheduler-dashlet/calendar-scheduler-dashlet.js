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
describe('Base.View.CalendarSchedulerDashlet', function() {
    var app;
    var view;
    var model;
    var module = 'Calendar';
    var context;
    var listenToStub;
    var meta;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        model = app.data.createBean(module);

        context = new app.Context();

        context.set({
            module: module,
            model: model,
        });

        meta = {
            config: false
        };

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'Home',
            'calendar-scheduler-dashlet',
            meta,
            context,
            false,
            layout,
            true
        );

        listenToStub = sinon.stub(view, 'listenTo');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.data.reset();
        app.view.reset();
    });

    describe('initialize', function() {
        it('should add listeners', function() {
            view.initialize({});

            expect(listenToStub.getCall(0).args[1]).toEqual('change:availableViews');

            expect(view.events['click span[name=addCalendar]']).toEqual('_addCalendar');
        });
    });

    describe('_render()', function() {
        it('should render the scheduler view', function() {
            var renderStub = sinon.stub(view.schedulerView, 'render');
            view._render();

            expect(renderStub).toHaveBeenCalledOnce();

            renderStub.restore();
        });
    });
});
