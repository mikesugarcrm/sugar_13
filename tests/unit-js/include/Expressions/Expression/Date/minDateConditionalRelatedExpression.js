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
describe('MinDateConditionalRelatedExpression', function() {
    var app;
    var oldApp;
    var dataManager;
    var sinonSandbox;
    var model;

    // Set up the expression parameters
    var link = new SUGAR.expressions.StringLiteralExpression(['revenuelineitems']);
    var field = new SUGAR.expressions.StringLiteralExpression(['date_closed']);
    var conditionFields = new SUGAR.expressions.DefineEnumExpression(['product_type', 'renewable']);
    var conditionValues = new SUGAR.expressions.DefineEnumExpression(['Existing Business', '1']);

    // Helper function to create the Sugar Logic context object for the expression
    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dataManager.beanCollection);
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
        app = SugarTest.app;
        dataManager = app.data;
        dataManager.reset();
        dataManager.declareModels();
        model = dataManager.createBean('Accounts');
    });

    afterEach(function() {
        App = oldApp;
        sinonSandbox.restore();
    });

    it('should return false if app is undefined', function() {
        var tempApp = App;
        App = undefined;
        var expression = new SUGAR.expressions.MinDateConditionalRelatedExpression([link, field, conditionFields,
            conditionValues], getSLContext(model));
        expect(expression.evaluate()).toBe('false');
        App = tempApp;
    });

    it('should find the earliest date from a related field collection in date format', function() {
        // Create the mock expression and spies
        var expression = new SUGAR.expressions.MinDateConditionalRelatedExpression([link, field, conditionFields,
            conditionValues], getSLContext(model));
        var mockObj = sinonSandbox.mock(expression.context);

        // Mock the sidecar methods to return particular values
        mockObj.expects('getRelatedCollectionValues').once().withArgs(expression.context.model, link.evaluate(),
            'rollupConditionalMinDate', field.evaluate()).returns(['2019-06-01', '2020-05-27', '2018-11-17']);
        mockObj.expects('getField').once().returns({
            'type': 'date'
        });

        // Evaluate the expression and validate the results
        expect(expression.evaluate()).toBe('2018-11-17');
        mockObj.verify();
    });

    it('should find the earliest date from a related field collection in timestamp format', function() {
        // Create the mock expression and spies
        var expression = new SUGAR.expressions.MinDateConditionalRelatedExpression([link, field, conditionFields,
            conditionValues], getSLContext(model));
        var mockObj = sinonSandbox.mock(expression.context);

        // Mock the sidecar methods to return particular values
        mockObj.expects('getRelatedCollectionValues').once().withArgs(expression.context.model, link.evaluate(),
            'rollupConditionalMinDate', field.evaluate()).returns([1531765837, 1531765290, 1531764120]);
        mockObj.expects('getField').once().returns({
            'type': 'int'
        });

        // Evaluate the expression and validate the results
        expect(expression.evaluate()).toBe(1531764120);
        mockObj.verify();
    });

    it('should set the value of the field element and update related fields', function() {
        // Create the mock expression and spies
        var expression = new SUGAR.expressions.MinDateConditionalRelatedExpression([link, field, conditionFields,
            conditionValues], getSLContext(model));
        var mockObj = sinonSandbox.mock(expression.context);
        var mockObjUnderscore = sinonSandbox.mock(_);
        var mockObjSetValue = sinonSandbox.mock(expression.context.model);

        // Mock the sidecar methods to return particular values
        mockObj.expects('getRelatedCollectionValues').once().withArgs(expression.context.model, link.evaluate(),
            'rollupConditionalMinDate', field.evaluate()).returns(['2019-06-01', '2020-05-27', '2018-11-17']);
        mockObj.expects('getField').once().returns({
            'type': 'date'
        });

        // Expect the field value and the related field values to be updated
        mockObjSetValue.expects('set').once();
        mockObj.expects('updateRelatedFieldValue').once().withArgs(link.evaluate(), 'rollupConditionalMinDate',
            field.evaluate());

        // Evaluate the expression and validate the results
        expect(expression.evaluate()).toBe('2018-11-17');
        mockObj.verify();
        mockObjUnderscore.verify();
        mockObjSetValue.verify();
    });
});
