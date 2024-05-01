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
describe('View.Views.Base.HelpletView', () => {
    let app;
    let view;
    let context;

    beforeEach(() => {
        app = SugarTest.app;
        app.user.set('licenses', ['SUGAR_SELL']);

        context = app.context.getContext({
            module: 'test',
            layout: 'test',
        });

        const meta = {
            resources: {
                community: {},
                marketplace: {},
                sugar_university: {},
            },
        };

        view = SugarTest.createView('base', '', 'helplet', meta, context);
    });

    afterEach(() => {
        sinon.restore();
        view.dispose();
        view = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('initialize', () => {
        it('should initialize resources before rendering', () => {
            view._super = () => {};
            const initResourcesStub = sinon.stub(view, 'initResources');

            view.initialize();
            expect(initResourcesStub).toHaveBeenCalled();
        });
    });

    describe('initResources', () => {
        it('should disable SugarOutfitters for Sell Essentials', () => {
            app.user.set('licenses', ['SUGAR_SELL_ESSENTIALS']);
            view.initResources();
            expect(view.resources.marketplace).toEqual(undefined);
        });

        it('SugarOutfitters should be available for not Sell Essentials licenses', () => {
            app.user.set('licenses', ['SUGAR_SELL', 'SUGAR_SERVE']);
            view.initResources();
            expect(view.resources.marketplace).not.toEqual(undefined);
        });
    });
});
