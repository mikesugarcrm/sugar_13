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
describe('Sugar Translated Drop Down Expression Function', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

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
    });

    afterEach(function() {
        App = oldApp;
        sinonSandbox.restore();
    });

    describe('returns a collection of the translated values in the supplied dropdown list', function() {
        it('list name found', function() {
            var testParam = new SUGAR.expressions.StringLiteralExpression(['random_list_name']);
            var res = new SUGAR.expressions.SugarTranslatedDropDownExpression([testParam], getSLContext(model));
            var testArray = ['0','1','2','3','4'];
            SUGAR.lang = {get: function() {}};
            sinonSandbox.stub(App.lang, 'getAppListStrings').withArgs(testParam.evaluate()).returns(testArray);
            sinonSandbox.stub(SUGAR.lang, 'get').withArgs('app_list_strings', testParam.evaluate()).returns(testArray);
            expect(res.evaluate()).toEqual(testArray);
        });

        it('list name not found', function() {
            var testParam = new SUGAR.expressions.StringLiteralExpression(['random_list_name']);
            var res = new SUGAR.expressions.SugarTranslatedDropDownExpression([testParam], getSLContext(model));
            SUGAR.lang = {get: function() {}};
            sinonSandbox.stub(App.lang, 'getAppListStrings').withArgs(testParam.evaluate()).returns([]);
            sinonSandbox.stub(SUGAR.lang, 'get').withArgs('app_list_strings', testParam.evaluate()).returns([]);
            expect(res.evaluate()).toEqual([]);
        });
    });
});
