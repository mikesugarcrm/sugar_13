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
describe('Base.Field.Toggle', function() {
    let app;
    let model;
    let initOptions;
    let fieldName = 'toggle';
    let context;
    let field;

    function createField(model) {
        return SugarTest.createField('base', fieldName, 'toggle');
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = new Backbone.Model();
        SugarTest.loadComponent('base', 'field', 'base');
        field = createField(model);
        SugarTest.testMetadata.set();
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        initOptions = {
            context: context,
        };
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;
    });

    describe('initialize', function() {
        it('Should initialize the toggle', function() {
            sinon.stub(field, '_super');
            sinon.stub(field, 'listenTo');
            field.initialize(initOptions);
            expect(field._super).toHaveBeenCalled();
            expect(field.listenTo).toHaveBeenCalled();
        });
    });

    describe('toggleValue', function() {
        it('Should Set field value when toggle is done', function() {
            sinon.stub(field.model, 'get').returns(true);
            sinon.stub(field.model, 'set').returns(true);
            field.toggleValue();
            expect(field.model.get).toHaveBeenCalled();
            expect(field.model.set).toHaveBeenCalled();
        });
    });
});
