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
describe('SugarDropDownValue Expression Function', function() {
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

    describe('SugarDropDownValue Expression Function', function() {
        it('should the value related to a selection in a drop down menu (in this case foo and ' +
            'sales stage)', function() {
            var opp = new SUGAR.expressions.StringLiteralExpression(['foo']);
            var key = new SUGAR.expressions.StringLiteralExpression([model.get('sales_stage')]);
            var dict = {
                'Closed Lost': 0,
                'Prospecting': 10,
                'Qualification': 20,
                'Need Analysis': 25,
                'Value Proposition': 30,
                'Id. Decision Makers': 40,
                'Perception Analysis': 50,
                'Proposal': 65,
                'Negotiation/Review': 80,
                'Closed Won': 100,
            };
            var res = new SUGAR.expressions.SugarDropDownValueExpression([opp, key], getSLContext(model));
            sinonSandbox.stub(res.context, 'getAppListStrings').withArgs(opp.evaluate()).returns(dict);
            expect(parseFloat(res.evaluate())).toBe(dict[key.evaluate()]);
        });
    });
});
