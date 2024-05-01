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
describe('includes.javascript.pmse.ui.field_types', function() {
    describe('NumberField.setMinValue', function() {
        it('should to set correct minValue parameter', function() {
            const params = {
                minValue: 0,
            };
            let field = new NumberField(params);

            const minValue = 5;

            field.setMinValue(minValue);
            expect(field.minValue).toEqual(minValue);
        });
    });

    describe('NumberField.isValid', function() {
        it('should to mark field as invalid if value less than minValue', function() {
            let field = new NumberField();
            const minValue = 0;

            field.setValue(0, true);
            field.setMinValue(1);

            const validResult = field.isValid();
            expect(validResult).toEqual(false);
        });
    });

    describe('FieldsGroup.setDirty', function() {
        it('should start setDirty of FieldsGroup when setDirty of child start', function() {
            const field = new TextField();
            const group = new FieldsGroup({
                items: [
                    {
                        field: field,
                    },
                ],
            });

            const object = group.items[0].field.parent.setDirty(true);
            expect(object.type).toEqual('FieldsGroup');
        });
    });

    describe('FieldsGroup.attachListeners', function() {
        it('should attach listeners of the children', function() {
            const field = new TextField();
            sinon.stub(field, 'attachListeners');

            const group = new FieldsGroup({
                items: [
                    {
                        field: field,
                    },
                ],
            });

            group.attachListeners();
            expect(field.attachListeners).toHaveBeenCalled();
        });
    });

    describe('FieldsGroup.getObjectValue', function() {
        it('should return values of children', function() {
            const field = new TextField({
                name: 'field_name',
                value: 'field_value',
            });

            const group = new FieldsGroup({
                items: [
                    {
                        field: field,
                    },
                ],
            });

            const expected = {
                field_name: 'field_value',
            };
            const groupValue = group.getObjectValue();

            expect(groupValue).toEqual(expected);
        });
    });
});
