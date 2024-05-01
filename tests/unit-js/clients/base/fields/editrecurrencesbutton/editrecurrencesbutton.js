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
describe('Base.Fields.Editrecurrencesbutton', function() {
    var app, field, sandbox;

    beforeEach(function() {
        var fieldName = 'editrecurrencesbutton';
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField('base', fieldName, fieldName);
        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('Hiding the button for non-recurring events', function() {
        var hideStub;

        beforeEach(function() {
            hideStub = sandbox.stub(field, 'hide');
        });

        it('should not hide button when rset field is set', function() {
            field.model.set('rset', '{"rrule":"FREQ=DAILY;INTERVAL=1;COUNT=7;"}');
            expect(hideStub).not.toHaveBeenCalled();
        });

        it('should hide button when repeat_type field is not set', function() {
            field.model.set('repeat_type', '');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    it('should re-render field when repeat_type field is changed', function() {
        var spy = sandbox.spy(field, '_render');
        field.model.set('repeat_type', '', {silent: true});
        field.model.set('repeat_type', 'true');
        expect(spy.callCount).toBe(1);
    });
});
