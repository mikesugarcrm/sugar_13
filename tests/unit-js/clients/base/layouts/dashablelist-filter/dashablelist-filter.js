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

describe('Base.Layout.DashablelistFilter', function() {

    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
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

    describe('DashablelistFilter', function() {
        var parentLayout;

        beforeEach(function() {
            parentLayout = app.view.createLayout({type: 'base', name: 'dashablelist-filter-parent-layout'});
            layout = SugarTest.createLayout('base', 'Accounts', 'dashablelist-filter', {}, false, false, {layout: parentLayout});
        });

        afterEach(function() {
            parentLayout.dispose();
        });

        describe('initComponents', function() {
            var _comp, getComponentStub;

            beforeEach(function() {
                _comp = {before: sinon.stub(), render: sinon.stub()};
                getComponentStub = sinon.stub(layout, 'getComponent').returns(_comp);
            });

            it('should initialize components and bind events', function() {
                layout.initComponents();

                expect(getComponentStub).toHaveBeenCalledWith('filterpanel');
                expect(_comp.before).toHaveBeenCalled();

                // Event should be bound on the parent layout.
                expect(parentLayout._events['dashlet:filter:reinitialize']).toBeDefined();
            });

            it('should call render when dashlet:filter:reinitialize is triggered', function() {
                layout.initComponents();
                parentLayout.trigger('dashlet:filter:reinitialize');

                expect(_comp.render).toHaveBeenCalled();
            });
        });

        describe('_reinitializeFilterPanel', function() {
            var _comp, getComponentStub;

            beforeEach(function() {
                _comp = {currentModule: 'testModule'};
                getComponentStub = sinon.stub(layout, 'getComponent').returns(_comp);
            });

            it('should set the currentModule and currentFilterId on the filter layout and context', function() {
                var getStub = sinon.stub(layout.model, 'get').callsFake(function(arg) {
                    if (arg === 'filter_id') {
                        return 'test_filter_id';
                    } else if (arg === 'module') {
                        return 'Accounts';
                    }
                });

                layout._reinitializeFilterPanel();

                expect(getComponentStub).toHaveBeenCalledWith('filterpanel');
                expect(getStub).toHaveBeenCalledWith('filter_id');
                expect(getStub).toHaveBeenCalledWith('module');
                expect(_comp.currentModule).toEqual('Accounts');
                expect(layout.context.get('currentFilterId')).toEqual('test_filter_id');
            });
        });

    });
});
