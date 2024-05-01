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
describe('ProductBundleNotes.Base.Fields.Textarea', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'textarea';
    var fieldModule = 'ProductBundleNotes';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: fieldType,
            label: 'testLbl'
        };

        field = SugarTest.createField('base', fieldType, fieldType, 'detail',
            fieldDef, fieldModule, null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('setMode()', function() {
        beforeEach(function() {
            sinon.stub(app.view.Field.prototype.setMode, 'call').callsFake(function() {});
        });

        it('should call app.view.Field.prototype.setMode.call', function() {
            field.setMode();
            expect(app.view.Field.prototype.setMode.call).toHaveBeenCalled();
        });

        it('should call app.view.Field.prototype.setMode.call with list', function() {
            field.setMode('list');
            expect(app.view.Field.prototype.setMode.call.lastCall.args[1]).toBe('list');
        });

        it('should call app.view.Field.prototype.setMode.call with edit', function() {
            field.setMode('edit');
            expect(app.view.Field.prototype.setMode.call.lastCall.args[1]).toBe('edit');
        });
    });
});
