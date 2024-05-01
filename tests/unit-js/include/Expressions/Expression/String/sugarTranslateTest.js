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
describe('Sugar Translate Expression Function', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;
    var oldLang;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model = isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || new app.Context({
            url: 'someurl',
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent('View', {
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
        oldLang = SUGAR.language;
        SUGAR.language = {get: function() {}};
    });

    afterEach(function() {
        SUGAR.language = oldLang;
        App = oldApp;
        sinonSandbox.restore();
    });

    describe('Sugar Translate Expression Function', function() {
        it('returns translated label given a label and module)', function() {
            var lbl = new SUGAR.expressions.StringLiteralExpression(['LBL_NAME']);
            var mod = new SUGAR.expressions.StringLiteralExpression(['Accounts']);
            var res = new SUGAR.expressions.SugarTranslateExpression([lbl,mod], getSLContext(model));
            sinonSandbox.stub(SUGAR.language, 'get').withArgs('Accounts', 'LBL_NAME').returns('Name');
            expect(res.evaluate()).toBe('Name');
        });
    });
});
