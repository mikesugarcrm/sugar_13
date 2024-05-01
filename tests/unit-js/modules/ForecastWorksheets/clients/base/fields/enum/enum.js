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
describe("ForecastWorksheets.Base.Field.Enum", function () {
    var app,
        field,
        moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadComponent('base', 'field', 'enum');

        var fieldDef = {
            "name": "test_field",
            "type": "enum",
            "len": 100,
            "comment": "The name of the account represented by the account_id field",
            "options": 'test_options'
        };

        field = SugarTest.createField("base", "enum", 'enum', 'record', fieldDef, moduleName, null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['ClickToEdit'];
        field = null;
        app = null;
    });

    it('should have ClickToEdit Plugin', function() {
        expect(field.plugins).toContain('ClickToEdit');
    });

    describe('ClickToEdit handleKeyDown', function() {
        var event;
        beforeEach(function() {
            sinon.stub(field, 'setMode').callsFake(function() { return true; });
            event = {};
        });

        afterEach(function() {
            field.disposed = false;
        });

        it('should set errorState to false and call setMode when key is 27', function() {
            event.which = 27;
            field.isErrorState = undefined;
            field._fieldHandleKeyDown(event, field);

            expect(field.isErrorState).toBeFalsy();
            expect(field.setMode).toHaveBeenCalled();
        });

        it('if value changed, should set a listener on the model and not call setMode when key is 13', function() {
            event.which = 13;
            sinon.stub(field, '_fieldValueChanged').callsFake(function() { return true; });
            sinon.stub(field.model, 'once').callsFake(function() { return true; });

            field._fieldHandleKeyDown(event, field);

            expect(field.model.once).toHaveBeenCalled();
            expect(field.setMode).not.toHaveBeenCalled();
        });

        it('if fieldValueChanged returns false, setDetail should be called when key is 13', function() {
            event.which = 13;
            sinon.stub(field, '_fieldValueChanged').callsFake(function() { return false; });
            sinon.stub(field.model, 'once').callsFake(function() { return true; });

            field._fieldHandleKeyDown(event, field);

            expect(field.model.once).not.toHaveBeenCalled();
            expect(field.setMode).toHaveBeenCalled();
        });

        it('field is disposed should not run set mode', function() {
            event.which = 27;

            field.isErrorState = undefined;
            field.disposed = true;
            field._fieldHandleKeyDown(event, field);

            expect(field.isErrorState).toBeUndefined();
            expect(field.setMode).not.toHaveBeenCalled();
        });
    });
});
