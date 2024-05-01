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
describe('DRI_Workflow_Templates.Base.View.TemplateImportHeaderpaneView', function() {
    let app;
    let view;
    let context;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.prepare();
        context.parent = app.context.getContext();

        layout = SugarTest.createLayout(
            'base',
            'DRI_Workflow_Templates',
            'base',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'DRI_Workflow_Templates',
            'template-import-headerpane',
            {},
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app = null;
        layout = null;
        context = null;
        view = null;
    });

    describe('initiateFinish', () => {
        it('should show alert', () => {
            sinon.stub(app.cache, 'get').returns(true);
            sinon.stub(app.alert, 'show').returns(true);
            view.initiateFinish();
            expect(app.alert.show).toHaveBeenCalled();
        });
        it('should trigger context', () => {
            sinon.stub(app.cache, 'get').returns(false);
            sinon.stub(view.context, 'trigger').returns(true);
            view.initiateFinish();
            expect(view.context.trigger).toHaveBeenCalled();
        });
    });

    describe('initiateCancel', () => {
        beforeEach(function() {
            app.routing.start();
        });
        afterEach(function() {
            app.routing.stop();
        });
        it('should route', () => {
            sinon.stub(app.router, 'navigate');
            view.initiateCancel();
            expect(app.router.navigate).toHaveBeenCalled();
        });
    });
});
