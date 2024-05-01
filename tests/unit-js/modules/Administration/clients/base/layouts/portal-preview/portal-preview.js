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

describe('View.Layouts.Base.AdministrationPortalPreviewLayout', function() {
    var app;
    var context;
    var layout;
    var layoutName = 'portal-preview';
    var module = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        SugarTest.loadComponent('base', 'layout', layoutName, module);
        layout = SugarTest.createLayout('base', module, layoutName, {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        context = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('initComponents', function() {
        var fakeComponents;
        beforeEach(function() {
            fakeComponents = [
                {
                    layout: {
                        type: 'dashboard',
                        components: ['fake'],
                    }
                }
            ];

            sinon.stub(layout, '_super');
            sinon.stub(layout.context, 'set');
            layout.initComponents(fakeComponents, null, null);
        });

        afterEach(function() {
            fakeComponents = null;
        });

        it('should call _super with initComponents', function() {
            expect(layout._super).toHaveBeenCalledWith('initComponents', [fakeComponents, null, null]);
        });
        it('should set config-layout to true', function() {
            expect(layout.context.set).toHaveBeenCalledWith('config-layout', true);
        });
    });

    describe('bindDataChange', function() {
        it('should bind the click event on layout', function() {
            sinon.stub(layout, '_super');
            sinon.stub(layout.context, 'on');
            sinon.stub(layout, 'on');
            sinon.stub(layout, 'restorePortalHomeDashlets');
            sinon.stub(layout, 'handleConfigPreview');

            layout.bindDataChange();
            expect(layout._super).toHaveBeenCalledWith('bindDataChange');
            expect(layout.context.on).toHaveBeenCalledWith('portal:config:preview',
                layout.handleConfigPreview, layout);
            expect(layout.on).toHaveBeenCalledWith('dashboard:restore_dashlets_button:click',
                layout.restorePortalHomeDashlets, layout);
        });
    });

    describe('restorePortalHomeDashlets', function() {
        var context;
        var apiCallStub;
        var buildURLStub;
        var getStub;
        beforeEach(function() {
            apiCallStub = sinon.stub(app.api, 'call');
            buildURLStub = sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'testUrl';
            });

            getStub = sinon.stub().returns('test');
        });

        afterEach(function() {
            context = null;
            apiCallStub = null;
            buildURLStub = null;
            getStub = null;
        });

        it('should not call api if model is empty', function() {
            context = {
                get: function() {
                    return null;
                }
            };

            layout.restorePortalHomeDashlets(context);
            expect(apiCallStub).not.toHaveBeenCalled();
        });

        it('should call api if model is not empty', function() {
            context = {
                get: function() {
                    return {
                        get: getStub
                    };
                }
            };

            layout.restorePortalHomeDashlets(context);
            expect(buildURLStub).toHaveBeenCalledWith('Dashboards', 'restore-metadata', {id: 'test'},
                {
                    dashboard_module: 'test',
                    dashboard: 'portal-home',
                });
            expect(apiCallStub).toHaveBeenCalledWith('update', 'testUrl');
        });
    });

    describe('handleConfigPreview', function() {
        var viewComponent;

        beforeEach(function() {
            viewComponent = app.view.createView({
                name: 'test-view'
            });
            sinon.stub(viewComponent, 'trigger');
        });

        afterEach(function() {
            viewComponent.dispose();
        });

        it('should trigger data:preview on the component', function() {
            sinon.stub(layout, 'getPreviewComponent').returns(viewComponent);

            layout.handleConfigPreview({
                preview_components: [
                    {
                        layout: 'test-layout',
                        view: 'test-view'
                    }
                ],
                preview_data: 'Preview Data'
            });

            expect(viewComponent.trigger).toHaveBeenCalledWith('data:preview');
        });
    });

    describe('getPreviewComponent', function() {
        it('should not return a component when the definition is misdefined', function() {
            var actual = layout.getPreviewComponent({
                layout: 'test-layout'
            });

            expect(actual).toBeNull();
        });

        it('should return the component and cache it', function() {
            var childLayout = app.view.createLayout({
                name: 'test-layout'
            });

            var component = app.view.createView({
                name: 'test-view'
            });

            childLayout.addComponent(component);
            layout.addComponent(childLayout);

            var actual = layout.getPreviewComponent({
                layout: 'test-layout',
                view: 'test-view'
            });

            expect(actual).toEqual(component);
            expect(layout.componentsCache['test-layout']['test-view']).toEqual(component);
        });
    });

    describe('getLayoutChain', function() {
        it('should return correct child layout', function() {
            var childLayout1 = app.view.createLayout({
                name: 'layout1',
            });

            var childLayout2 = app.view.createLayout({
                name: 'layout2',
            });

            childLayout1.addComponent(childLayout2);
            layout.addComponent(childLayout1);

            var actual = layout.getLayoutChain('layout1.layout2');

            expect(actual.name).toEqual('layout2');
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            layout.componentsCache = 'test';

            sinon.stub(layout.context, 'unset');
            sinon.stub(layout.context, 'off');
            sinon.stub(layout, '_super');
            sinon.stub(layout, 'handleConfigPreview');

            layout._dispose();
        });

        it('should set componentsCache to null', function() {

            expect(layout.componentsCache).toBeNull();
        });

        it('should unset config-layout', function() {

            expect(layout.context.unset).toHaveBeenCalledWith('config-layout');
        });

        it('should remove listener event from portal:config:preview event', function() {

            expect(layout.context.off).toHaveBeenCalledWith('portal:config:preview',
                layout.handleConfigPreview, layout);
        });

        it('should call view._super method with _dispose', function() {

            expect(layout._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
