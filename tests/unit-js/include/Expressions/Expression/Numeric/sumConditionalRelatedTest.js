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
describe('Sum Conditional Related Expression Function', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model =  isCollection ? new modelOrCollection.model() : modelOrCollection;
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

    describe('Sum Conditional Related Expression Function', function() {
        it('should return the sum for a field of which another field matches a certain condition', function() {
            var opp = new SUGAR.expressions.StringLiteralExpression(['opportunities']);
            var fieldToSum = new SUGAR.expressions.StringLiteralExpression(['best_case']);
            var conditionField = new SUGAR.expressions.StringLiteralExpression(['name']);
            var conditionValue = new SUGAR.expressions.StringLiteralExpression(['Rubble Group Inc - 160 Units']);
            var payload = dm.createBeanCollection('Opportunities', [model.get('opportunities')], {});
            var res = new SUGAR.expressions.SumConditionalRelatedExpression(
                [opp, fieldToSum, conditionField, conditionValue], getSLContext(model)
            );
            //the following line stubs the related_collection referenced in SUGAR.expressions.{...}.evaluate
            sinonSandbox.stub(res.context.model, 'getRelatedCollection').withArgs('opportunities').returns(payload);
            //the following line stubs the value for current_value
            sinonSandbox.stub(res.context, 'getRelatedField').withArgs('opportunities', 'rollupConditionalSum',
                'best_case').returns(1000);
            //the following line stubs the values for best_case
            sinonSandbox.stub(res.context, 'getRelatedCollectionValues').withArgs(res.context.model, 'opportunities',
                'rollupConditionalSum','best_case').returns([600, 400]);
            expect(parseFloat(res.evaluate())).toBe(1000);
        });
    });
});
