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
describe('Max Related Date Expression Function', function() {
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
        var App = oldApp;
        sinonSandbox.restore();
    });

    var link = new SUGAR.expressions.StringLiteralExpression(['opportunities']);
    var field = new SUGAR.expressions.StringLiteralExpression(['date_closed_timestamp']);

    describe('returns the highest value of field in records related by a link', function() {
        it('case: app = undefined, returns false', function() {
            var temp = App;
            App = undefined;
            var res = new SUGAR.expressions.MaxRelatedDateExpression([link, field], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            App = temp;
        });

        it('case: normal, returns the max date', function() {
            var res = new SUGAR.expressions.MaxRelatedDateExpression([link, field], getSLContext(model));
            var mockObj = sinonSandbox.mock(res.context);
            mockObj.expects('getRelatedField').once().withArgs(link.evaluate(), 'maxRelatedDate',
                field.evaluate()).returns(1531765837);
            mockObj.expects('getRelatedCollectionValues').once().withArgs(res.context.model,
                link.evaluate(), 'maxRelatedDate', field.evaluate()).returns([1531765837, 1531765290]);
            expect(res.evaluate()).toBe(1531765837);
            mockObj.verify();
        });

        it('case: go to end of expression evaluate function', function() {
            var res = new SUGAR.expressions.MaxRelatedDateExpression([link, field], getSLContext(model));
            var mockObj = sinonSandbox.mock(res.context);
            var mockObjUnderscore = sinonSandbox.mock(_);
            var mockObjSetValue = sinonSandbox.mock(res.context.model);
            mockObj.expects('getRelatedField').once().withArgs(link.evaluate(), 'maxRelatedDate',
                field.evaluate()).returns(1531765837);
            mockObj.expects('getRelatedCollectionValues').once().withArgs(res.context.model, link.evaluate(),
                'maxRelatedDate', field.evaluate()).returns([1531765837,1531765290]);
            mockObjUnderscore.expects('isEqual').once().withArgs('','').returns(false);
            mockObjUnderscore.expects('isEqual').once().withArgs('1970-01-18',1531765837);
            mockObjSetValue.expects('set').once();
            mockObj.expects('updateRelatedFieldValue').once().withArgs(link.evaluate(), 'maxRelatedDate',
                field.evaluate());
            res.evaluate();
            mockObj.verify();
            mockObjUnderscore.verify();
            mockObjSetValue.verify();
        });
    });
});
