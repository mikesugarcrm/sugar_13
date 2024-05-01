
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
describe('Base.FieldEnumField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        field = SugarTest.createField(
            'base',
            'subject',
            'field-enum',
            'edit',
            {},
            module,
            model,
            context,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('bindDataChange()', function() {
        var listenToStub;
        var updateFieldDropdownStub;

        beforeEach(function() {
            listenToStub = sinon.stub(field, 'listenTo');
            updateFieldDropdownStub = sinon.stub(field, '_updateFieldDropdown');
        });

        afterEach(function() {
            context.unset('copiedFromModelId');
        });

        it('should update field dropdown immediately if the record is copied', function() {
            context.set('copiedFromModelId', 'string');
            field.bindDataChange();
            expect(updateFieldDropdownStub).toHaveBeenCalled();
        });

        it('should not immediately update the field if the record is not copied', function() {
            context.unset('copiedFromModelId');
            field.bindDataChange();
            expect(updateFieldDropdownStub).not.toHaveBeenCalled();
        });

        it('should add listener for change:calendar_module on field.model', function() {
            field.bindDataChange();
            expect(listenToStub).toHaveBeenCalled();
            expect(listenToStub.getCall(1).args[1]).toEqual('change:calendar_module');
        });
    });
});
