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
describe('Base.Field.Status', function() {
    var app,
        field,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        var model = new Backbone.Model();
        model.set({
            status: 'testStatus'
        });

        field = SugarTest.createField('base', 'status', 'status', 'list', null, null, model);

        sandbox.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        field = null;
    });

    describe('initialize()', function() {
        it('should call buildCSSClasses to set css class', function() {
            sandbox.spy(field, 'buildCSSClasses');
            field.initialize();
            expect(field.buildCSSClasses).toHaveBeenCalled();
        });
    });

    describe('buildCSSClasses()', function() {
        it('should populate cssClasses properly based on field name and value', function() {
            field.buildCSSClasses();
            expect(field.cssClasses).toBe('field_status_testStatus');
        });
    });
});
