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
describe('Base.View.ReportDashletBody', function() {
    var app;
    var context;
    var layout;
    var view;
    var initOptions;
    var sandbox = sinon.createSandbox();
    var viewName = 'report-dashlet-body';

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();
        context.set({
            model: new Backbone.Model(),
            module: 'Home',
        });

        context.parent = new app.Context({
            module: 'Home',
            model: new Backbone.Model({
                defaultSelectView: 'list',
            }),
        });

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context.parent
        );

        initOptions = {
            context: context.parent
        };

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', viewName);
        SugarTest.loadComponent('base', 'view', viewName);
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
        layout.dispose();
        app = null;
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var testView;
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView(
                'base',
                null,
                viewName,
                null,
                context,
                true,
                layout,
                true
            );

            sandbox.spy(testView, '_initProperties');
            sandbox.spy(testView, '_registerEvents');

            testView.initialize(initOptions);

        });

        it('should init the default properties, also the properties from layout model', function() {
            expect(testView._cachedViews).toEqual({
                chartView: null,
                listView: null,
                filterView: null,
            });

            expect(testView._listViewType).toEqual('list');
            expect(testView._filterViewType).toEqual('filters');
            expect(testView._chartViewType).toEqual('chart');

            expect(testView._activeViewType).toEqual(testView._listViewType);
        });

        it('it should call the _initProperties and _registerEvents', function() {
            expect(testView._initProperties.called).toEqual(true);
            expect(testView._registerEvents.called).toEqual(true);
        });

        afterEach(function() {
            testView.dispose();
        });
    });

    describe('renderDashletContent()', function() {
        var testView;

        beforeEach(function() {
            testView = SugarTest.createView(
                'base',
                null,
                viewName,
                null,
                context,
                true,
                layout,
                true
            );

            sinon.stub(app.view, 'createView').callsFake(function() {
                return {
                    render: function() {},
                    $el: '<div></div>',
                    dispose: function() {},
                    show: function() {},
                };
            });

        });

        it('initial the cachedView should be undefined', function() {
            expect(testView._cachedViews[testView._activeViewType]).toBeUndefined();
        });

        it('after we call the renderDashletContent the cached selected view should be initialized', function() {
            expect(testView._cachedViews[testView._activeViewType]).toBeUndefined();

            testView.renderDashletContent();

            expect(testView._cachedViews[testView._activeViewType]).not.toBeUndefined();
        });

        it('after the disposed is called the cachedView should be null', function() {
            expect(testView._cachedViews[testView._activeViewType]).toBeUndefined();

            testView.renderDashletContent();

            expect(testView._cachedViews[testView._activeViewType]).not.toBeUndefined();

            testView.dispose();

            expect(testView._cachedViews[testView._activeViewType]).toBe(null);
        });

        afterEach(function() {
            testView.dispose();
        });
    });
});
