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
describe('Base.Field.CJFieldsetForDateInPopulateFields', () => {
    var app;
    var field;

    beforeEach(() => {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        field = SugarTest.createField(
            'base',
            'cj-fieldset-for-date-in-populate-fields',
            'cj-fieldset-for-date-in-populate-fields'
        );
        SugarTest.testMetadata.set();
    });

    afterEach(() => {
        sinon.restore();
        field.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', () => {
        it('should initialize the fieldset', () => {
            expect(field.type).toBe('fieldset');
        });
    });

    describe('_render', () => {
        beforeEach(() => {
            sinon.stub(field, 'bindDomChange');
            sinon.stub(field, 'hideOrShowRelatedDateFields');
        });

        it('should call bindDomChange function', () => {
            field._render();
            expect(field.bindDomChange).toHaveBeenCalled();
        });

        it('should call hideOrShowRelatedDateFields function', () => {
            field._render();
            expect(field.hideOrShowRelatedDateFields).toHaveBeenCalled();
        });
    });

    describe('bindDomChange', () => {
        let selectiveField;

        beforeEach(() => {
            selectiveField = SugarTest.createField(
                'base',
                'cj_selective_date_type',
                'fieldset'
            );
            sinon.stub(field, 'bindDomChange');
            sinon.stub(selectiveField.$el, 'on');
            sinon.stub(field, 'toggle_selective_date_type');
        });

        it('selective dropdown should exist', () => {
            field._render();
            selectiveField._render();

            field.bindDomChange();
            expect(selectiveField.$el.length).toBe(1);
        });
    });

    describe('toggle_selective_date_type', () => {
        using('different date fields type', [
            {
                type: 'relative',
                expectedResult: true,
            },
            {
                type: 'fixed',
                expectedResult: false,
            },
        ], (data) => {
            it('should hide or show cj_selective_date_type accordingly', () => {
                sinon.stub(field, 'hideOrShowRelatedDateFields');

                const e = {val: data.type};
                field.toggle_selective_date_type(e);

                expect(field.hideOrShowRelatedDateFields).toHaveBeenCalledWith(data.expectedResult, true);
            });
        });
    });

    describe('hideOrShowRelatedDateFields', () => {
        let childField;

        const stubChildField = (cssClass) => {
            childField = SugarTest.createField('base', 'test-field', 'base', 'detail', {
                css_class: cssClass,
            });
            sinon.stub(childField, '_show');
            sinon.stub(childField, '_hide');
            sinon.stub(field, '_getChildFields').returns([
                childField,
            ]);
        };

        beforeEach(() => {
            sinon.stub(field, '_callPopulateAddedFieldsDefsHelper');
        });

        it('_show should be called', () => {
            stubChildField('cj_relative_date_type');

            field.hideOrShowRelatedDateFields(true, true);
            expect(childField._show).toHaveBeenCalled();
        });

        it('_hide should be called', () => {
            stubChildField('cj_int_date_type');

            field.hideOrShowRelatedDateFields(false, true);
            expect(childField._hide).toHaveBeenCalled();
        });

        it('_callPopulateAddedFieldsDefsHelper should be called', () => {
            stubChildField('cj_main_date');

            field.hideOrShowRelatedDateFields(true, true);
            expect(field._callPopulateAddedFieldsDefsHelper)
                .toHaveBeenCalledWith('remove', childField.name, childField.def);
        });
    });

    describe('_callPopulateAddedFieldsDefsHelper', () => {
        let populateField;

        beforeEach(() => {
            populateField = SugarTest.createField('base', 'populate_fields', 'cj-populate-fields');

            sinon.stub(populateField, 'populateAddedFieldsDefsHelper');
            sinon.stub(field.view, 'getField').returns(populateField);
        });

        it('populateAddedFieldsDefsHelper function should be called', () => {
            const testField = {
                name: 'test-field',
                def: {
                    css_class: 'cj_test_field',
                },
            };

            field._callPopulateAddedFieldsDefsHelper('add', testField.name, testField.def);
            expect(populateField.populateAddedFieldsDefsHelper)
                .toHaveBeenCalledWith('add', testField.name, testField.def);
        });
    });
});
