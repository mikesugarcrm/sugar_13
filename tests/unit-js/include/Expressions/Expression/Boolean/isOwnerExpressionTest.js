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
describe('isOwner Function', function() {
    let app;
    let oldApp;
    let dm;
    let sinonSandbox;
    let meta;
    let model;

    let getSLContext = function(modelOrCollection, context) {
        let isCollection = (modelOrCollection instanceof dm.beanCollection);
        let model = isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || new app.Context({
            url: 'someurl',
            module: model.module,
            model: model
        });
        let view = SugarTest.createComponent('View', {
            context: context,
            type: 'edit',
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    beforeEach(function() {
        oldApp = App;
        App = App || SUGAR.App;
        sinonSandbox = sinon.createSandbox();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('revenue-line-item-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        model = dm.createBean('RevenueLineItems', SugarTest.loadFixture('rli'));
    });

    afterEach(function() {
        App = oldApp;
        sinonSandbox.restore();
    });

    it('should return true if current user is owner', function() {
        model.set('assigned_user_id', App.user.id);
        let res = new SUGAR.expressions.IsOwnerExpression([], getSLContext(model));
        expect(res.evaluate()).toBe('true');
    });

    it('should return false if current user is not owner', function() {
        model.set('assigned_user_id', '');
        let res = new SUGAR.expressions.IsOwnerExpression([], getSLContext(model));
        expect(res.evaluate()).toBe('false');
    });
});
