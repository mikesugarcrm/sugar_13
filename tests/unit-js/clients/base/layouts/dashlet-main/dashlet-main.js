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

describe('Base.Layout.DashletMain', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Home', 'dashlet-main');
        sinon.stub(layout, 'closestComponent').callsFake($.noop);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe('setMetadata', function() {
        var initComponentsStub;
        var loadDataStub;
        var renderStub;
        var triggerStub;

        beforeEach(function() {
            layout.layout = {
                getComponent: $.noop,
                off: $.noop
            };
            initComponentsStub = sinon.stub(layout, 'initComponents');
            loadDataStub = sinon.stub(layout, 'loadData');
            renderStub = sinon.stub(layout, 'render');
            triggerStub = sinon.stub(layout.context, 'trigger');
        });

        it('should load data and render once it gets metadata', function() {
            layout.model.set('metadata', {i: 'am fake metadata'}, {silent: true});

            layout.setMetadata();

            expect(loadDataStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
        });

        it('should not do anything if there is no metadata', function() {
            layout.model.unset('metadata', {silent: true});

            layout.setMetadata();

            expect(loadDataStub).not.toHaveBeenCalled();
            expect(renderStub).not.toHaveBeenCalled();
        });

        it('should initialize legacy components as grids', function() {
            var comps1 = {rows: ['row 1', 'row 2'], width: 5};
            var comps2 = {rows: ['row 3', 'row 4'], width: 8};
            var initialMeta = {components: [comps1, comps2]};
            layout.model.set('metadata', initialMeta, {silent: true});

            layout.setMetadata();

            var expected = [
                {layout: {name: 'dashboard-grid', css_class: 'grid-stack'}}
            ];
            var updatedMeta = layout.model.get('metadata');
            expect(initComponentsStub).toHaveBeenCalledOnce();
            expect(initComponentsStub).toHaveBeenCalledWith(expected);
            expect(triggerStub).not.toHaveBeenCalled();
            expect(updatedMeta.legacyComponents).toEqual(initialMeta.components);
        });

        it('should set up tabs, including the active tab, but still convert legacy components', function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{rows: ['row 1, tab 1', 'row 2, tab 1'], width: 14}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.model.set('metadata', metadata, {silent: true});

            layout.setMetadata({tabIndex: 1});

            expect(triggerStub).toHaveBeenCalledWith(
                'tabbed-dashboard:update',
                {
                    activeTab: 1,
                    tabs: [
                        {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]},
                        {name: 'tab1', components: [{rows: ['row 1, tab 1', 'row 2, tab 1'], width: 14}]}
                    ],
                }
            );
            var expected = [
                {layout: {name: 'dashboard-grid', css_class: 'grid-stack'}}
            ];
            expect(initComponentsStub).toHaveBeenCalledWith(expected);
        });

        it('should allow non-dashlet tab for tabbed dashboard', function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.model.set('metadata', metadata, {silent: true});

            layout.setMetadata({tabIndex: 1});

            var expected = [{view: 'multi-line-list'}];
            expect(initComponentsStub).toHaveBeenCalledWith(expected);
        });
    });

    describe('getComponentsFromMetadata', function() {
        it('should return component from current tab', function() {
            var currentTab = 1;
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.context = {
                get: sinon.stub().returns(currentTab),
                off: $.noop
            };
            expect(layout.getComponentsFromMetadata(metadata)).toEqual(metadata.tabs [currentTab].components);
        });
    });
});
