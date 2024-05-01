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
describe('Current User Field Function', function() {
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
        sinon.restore();
        sinonSandbox.restore();
        App = oldApp;
    });

    it('should throw an error when a name for a non-existent field is used', function() {
        let fieldDefs = [
            {
                name: 'first_name',
                type: 'varchar'
            }
        ];

        let fieldName = new SUGAR.expressions.StringLiteralExpression(['not_a_real_field']);
        let res = new SUGAR.expressions.CurrentUserFieldExpression([fieldName], getSLContext(model));

        let stub = sinon.stub(App.user, 'get');
        stub.withArgs('sugar_logic_fielddefs').returns(fieldDefs);
        stub.withArgs('sugar_logic_fields').returns({
            first_name: 'test_first_name'
        });

        expect(() => res.evaluate()).toThrow(
            'currentUserField: Parameter "not_a_real_field" is not a valid User field'
        );
    });

    it('should return the value of the field for a valid field name', function() {
        let fieldDefs = [
            {
                name: 'first_name',
                type: 'varchar'
            }
        ];

        let fieldName = new SUGAR.expressions.StringLiteralExpression(['first_name']);
        let res = new SUGAR.expressions.CurrentUserFieldExpression([fieldName], getSLContext(model));

        let stub = sinon.stub(App.user, 'get');
        stub.withArgs('sugar_logic_fielddefs').returns(fieldDefs);
        stub.withArgs('sugar_logic_fields').returns({
            first_name: 'test_first_name'
        });

        expect(res.evaluate()).toBe('test_first_name');
    });

    it('should return false when used from a BWC context', function() {
        let fieldName = new SUGAR.expressions.StringLiteralExpression(['first_name']);
        let res = new SUGAR.expressions.CurrentUserFieldExpression([fieldName], getSLContext(model));

        App = undefined;

        expect(res.evaluate()).toBe('false');
    });
});
