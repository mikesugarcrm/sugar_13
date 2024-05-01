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
describe('View.Views.Base.DriWorkflowsWidgetConfiguration', function() {
    let app;
    let view;
    let context;
    let layout;
    let initOptions;
    let moduleName = 'Accounts';
    let viewName = 'dri-workflows-widget-configuration';
    let layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
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
            'dri-workflows-widget-configuration',
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
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins.view.Dashlet;
        layout = null;
        view = null;
        data = null;
    });

    describe('delegateButtonEvents', function() {
        it('should call the delegateButtonEvents function to add button click event', function() {
            sinon.stub(view, 'listenTo');
            view.delegateButtonEvents();
            expect(view.listenTo).toHaveBeenCalled();
        });
    });

    describe('delegateButtonEvents', function() {
        beforeEach(function() {
            app.drawer = {
                close: $.noop
            };
            sinon.stub(app.drawer, 'close');
            view.actualViewFields = [
                'cj_active_or_archive_filter',
                'cj_presentation_mode',
            ];
            view.toggleViewFields = [
                'cj_active_or_archive_filter',
                'cj_presentation_mode',
            ];
            view.fields = [
                {
                    'name': 'cj_active_or_archive_filter',
                    'type': 'cj_widget_config_toggle_field',
                    'label_left': 'LBL_CUSTOMER_JOURNEY_WIDGET_ACTIVE',
                    'label_right': 'LBL_CUSTOMER_JOURNEY_WIDGET_ARCHIVE',
                    'keyName': 'toggleActiveArchived',
                    'defaultStateValue': 'active',
                    'setToggleFieldStateInCache': function() { return true;}
                }
            ];
            sinon.stub(app.lang, 'get');
            sinon.stub(app.alert, 'show');
        });
        afterEach(function() {
            delete app.drawer;
        });
        it('should call app.drawer.close', function() {
            sinon.stub(view, 'closestComponent').returns(true);
            sinon.stub(view.layout.context, 'get');
            sinon.stub(view.context.parent, 'get');
            view.save();
            expect(app.drawer.close).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalled();
            expect(view.layout.context.get).toHaveBeenCalled();
            expect(view.context.parent.get).toHaveBeenCalled();
            expect(view.closestComponent).toHaveBeenCalled();
        });
        it('should call app.navigate', function() {
            sinon.stub(view, 'closestComponent').returns(false);
            sinon.stub(app, 'navigate');
            view.save();
            expect(app.navigate).toHaveBeenCalled();
        });
    });

    describe('cancel', function() {
        let cl;
        let cv;
        beforeEach(function() {
            app.drawer = {
                close: $.noop,
                count: $.noop,
                open: $.noop
            };
            app.routing.start();
            sinon.stub(app.router, 'navigate');
            SugarTest.loadComponent('base', 'view', 'record');
            cl = SugarTest.createLayout(
                'base',
                'layout',
                'base',
                null,
                context
            );
            cv = SugarTest.createView(
                'base',
                'view',
                'record',
                null,
                context,
                true,
                cl,
                true
            );
            cv.initialize(initOptions);
            sinon.stub(cv, '_dismissAllAlerts');
            view._dismissAllAlerts = cv._dismissAllAlerts;
            sinon.stub(view.$el, 'off');
            sinon.stub(app.drawer, 'close');
        });
        afterEach(function() {
            cv.dispose();
            cl.dispose();
            app.router.stop();
            delete app.drawer;
        });
        it('should call app.drawer.close', function() {
            sinon.stub(app.drawer, 'count').returns(true);
            view.cancel();
            expect(view.$el.off).toHaveBeenCalled();
            expect(app.drawer.count).toHaveBeenCalled();
            expect(app.drawer.close).toHaveBeenCalled();
        });
        it('should call app.router.navigate', function() {
            sinon.stub(app.drawer, 'count').returns(false);
            view.cancel();
            expect(view.$el.off).toHaveBeenCalled();
            expect(app.drawer.count).toHaveBeenCalled();
            expect(app.router.navigate).toHaveBeenCalled();
        });
    });

    describe('getCustomSaveOptions', function() {
        it('should call the getCustomSaveOptions function to o return set of custom options', function() {
            expect(view.getCustomSaveOptions(initOptions)).toEqual({});
        });
    });
});
