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
describe('ProductTemplates.Base.Views.ProductCatalogDashletDrawerRecord.Record', function() {
    let app;
    let view;
    let oldDrawer;
    let oldUserId;
    let clock;

    beforeEach(function() {
        app = SugarTest.app;
        oldDrawer = app.drawer;
        oldUserId = app.user.id;
        app.routing.start();
        clock = sinon.useFakeTimers();

        view = SugarTest.createView(
            'base',
            'ProductTemplates',
            'product-catalog-dashlet-drawer-record',
            {
                panels: []
            },
            null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        clock.restore();
        app.drawer = oldDrawer;
        app.user.id = oldUserId;
        app.router.stop();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('_drawerAddToQuoteClicked', function() {
        beforeEach(function(){
            app.drawer = {
                close: sinon.stub()
            }
            app.user.id = '1';
            sinon.stub(app.controller.context, 'trigger');
            view.model.clear();
            view.model.set({
                id: '123',
                date_entered: '456',
                date_modified: '789',
                my_favorite: '123',
                team_count: '456',
                team_count_link: '789',
                team_name: '123',
                team_id: '456',
                team_set_id: '789',
                name: 'name'
            });
            view.model.viewId = 'testId';
        });

        it('should only inherit some fields from the catalog item', function() {
            view._drawerAddToQuoteClicked();
            clock.tick(800);
            expect(app.controller.context.trigger).toHaveBeenCalledWith('testId:productCatalogDashlet:add', {
                product_template_id: '123',
                name: 'name',
                product_template_name: 'name',
                assigned_user_id: '1'
            });
        });
    });
});
