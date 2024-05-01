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
describe('Metrics.View.ConfigTabSettingsView', function() {
    let app;
    let view;
    let layout;
    let context;
    let ctxModel;

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('Metrics');
        context.set('model', ctxModel);
        context.set('collection', app.data.createBeanCollection('Metrics'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer-content', 'Metrics');
        layout = SugarTest.createLayout('base', 'Metrics', 'record', {},  context);
        layout.name = 'side-pane';

        view = SugarTest.createView(
            'base',
            'Metrics',
            'config-tab-settings',
            {},
            context,
            true,
            layout
        );
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view = null;
        layout = null;
        context = null;
        ctxModel = null;
    });

    describe('initialize', function() {
        let options;
        beforeEach(function() {
            options = {test: 'test'};
            sinon.stub(view, 'checkAdminAccess').returns(true);
        });

        it('should check admin access', function() {
            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
            expect(view.checkAdminAccess).toHaveBeenCalled();
            expect(view.isAdmin).toBeTruthy();
        });
    });

    describe('restoreAdminDefaults', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'call');
            sinon.stub(app.api, 'buildURL');
        });

        it('should not do anything if metric context or module is not set', function() {
            sinon.stub(view.model, 'get').returns(undefined);

            view.restoreAdminDefaults();
            expect(app.api.call).not.toHaveBeenCalled();
        });

        it('should call the restore-defaults api if metric context and module is not set', function() {
            sinon.stub(view.model, 'get')
                .withArgs('metric_context').returns('service_console')
                .withArgs('metric_module').returns('Cases');

            view.restoreAdminDefaults();
            expect(app.api.buildURL).toHaveBeenCalledWith('Metrics', 'restore-defaults', null, {
                metric_context: 'service_console',
                metric_module: 'Cases'
            });
            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
