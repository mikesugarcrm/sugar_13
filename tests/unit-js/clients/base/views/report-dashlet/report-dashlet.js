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
describe('Base.View.ReportDashlet', function() {
    var app;
    var context;
    var layout;
    var meta;
    var view;
    var viewName = 'report-dashlet';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadPlugin('GridBuilder');
        SugarTest.loadPlugin('ReportIntelligenceDashlet');
        SugarTest.loadComponent('base', 'view', 'report-dashlet-chart-preview');
        SugarTest.loadComponent('base', 'view', 'report-dashlet-list-preview');

        context = new app.Context();
        context.set({
            model: new Backbone.Model(),
            module: 'Home',
        });

        meta = {
            config: false
        };

        context.parent = new app.Context({module: 'Home'});

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context.parent
        );

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', viewName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'dashlet-config');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'dashlet-preview');
        SugarTest.loadComponent('base', 'view', viewName);

        view = SugarTest.createView(
            'base',
            null,
            viewName,
            meta,
            context,
            true,
            layout,
            true
        );

        sinon.stub(view, '_buildUserLastStateKey').callsFake(function() {
            return '';
        });

        view.settings = new Backbone.Model({
            reportId: 'reportTestId',
        });

        app.drawer = {
            $: function() {
                return {
                    empty: sinon.stub(),
                    append: sinon.stub(),
                };
            },
        };

        app.sideDrawer = {
            context: context,
        };

        app.controller.context = context;
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();

        app = null;
        view = null;
        layout = null;
    });

    describe('initDashlet()', function() {
        beforeEach(function() {
            sinon.stub(view, '_syncReport').callsFake(function() {
                return '';
            });
        });

        it('should not add the reportId when it is in config state', function() {
            expect(view.reportId).toBeUndefined();

            view.meta.config = true;
            view.initDashlet();

            expect(view.reportId).toEqual('reportTestId');
            expect(view._categoryViews).toEqual([]);
        });

        it('should add the reportId when it is not in config', function() {
            expect(view.reportId).toBeUndefined();

            view.meta.config = false;
            view.initDashlet();

            expect(view.reportId).toEqual('reportTestId');
            expect(view._categoryViews).toBeUndefined();
        });

        afterEach(function() {
            sinon.restore();
        });
    });

    describe('_createAndShowDashlet()', function() {
        beforeEach(function() {
            sinon.stub(app.view, 'createLayout').callsFake(function() {
                return {
                    initComponents: function() {},
                    render: function() {},
                    $el: '<div></div>',
                    dispose: function() {},
                };
            });

            sinon.stub(view, '_syncReport').callsFake(function() {
                return '';
            });
        });

        it('render create the _reportDashletWrapper', function() {
            expect(view._reportDashletWrapper).toBeUndefined();

            view.meta.config = false;
            view.initDashlet();
            view._createAndShowDashlet();

            expect(view._reportDashletWrapper).not.toBeUndefined();

            view.dispose();

            expect(view._reportDashletWrapper).toBe(null);
        });

        afterEach(function() {
            sinon.restore();
        });
    });

    describe('_initConfigProperties()', function() {
        it('should properly setup default properties', function() {
            expect(view._categoryViews).toBeUndefined();
            expect(view._previewController).toBeUndefined();

            view.meta.config = true;
            view._initConfigProperties();

            expect(view._categoryViews).toEqual([]);
            expect(view._previewController).toEqual(false);
        });
    });

    describe('_setupSettings()', function() {
        it('should properly setup settings', function() {
            view._setupSettings({
                reportDef: {
                    module: 'Accounts',
                    chart_type: 'hBarF',
                    group_defs: ['test']
                },
            });

            expect(view.settings.get('module')).toEqual('Accounts');
            expect(view.settings.get('chartType')).toEqual('hBarF');
        });
    });

    describe('_generatePreview()', function() {
        it('should properly create the preview view', function() {
            expect(view._previewController).toBeUndefined();

            view._generatePreview('chart');

            expect(view._previewController.type).toEqual('report-dashlet-chart-preview');
        });
    });

    describe('_disposePreviewController()', function() {
        it('should properly dispose the preview controller', function() {
            view._generatePreview('filter');

            expect(view._previewController.type).toEqual('report-dashlet-filter-preview');

            view._disposePreviewController();

            expect(view._previewController).toEqual(false);
        });
    });

    describe('_dispose()', function() {
        it('should properly dispose the controller', function() {
            view._generatePreview('list');

            expect(view._previewController.type).toEqual('report-dashlet-list-preview');

            view.dispose();

            expect(view._previewController).toEqual(false);
            expect(view._categoryViews).toEqual([]);
        });
    });
});
