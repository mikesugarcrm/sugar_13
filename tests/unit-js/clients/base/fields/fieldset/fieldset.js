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
describe('Base.Field.Fieldset', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        Handlebars.templates = {};
    });

    describe('normal render of child fields', function() {
        var field;

        beforeEach(function() {
            var fieldDef = {
                css_class: 'address_fields',
                fields: [
                    'address_street',
                    'address_city',
                    'address_state',
                    'address_postalcode',
                    'address_country'
                ]
            };
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'edit', fieldDef);
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });

        //FIXME: SC-3363 Remove this spec when this becomes automatically handled by `field.js`.
        it('should only use the field view fallback template if the template exists', function() {
            //a template does not exist
            field.view.fallbackFieldTemplate = 'blah';
            var fallbackTemplate = field._getFallbackTemplate();
            expect(fallbackTemplate).toEqual('detail');

            //a template that exists
            field.view.fallbackFieldTemplate = 'list';
            sinon.stub(app.template, 'get').withArgs('f.fieldset.list').returns(true);
            fallbackTemplate = field._getFallbackTemplate();
            expect(fallbackTemplate).toEqual('list');
        });

        it('should initialize child fields based on field defs without attaching them to the parent view', function() {
            expect(field.fields.length).toEqual(5);
            expect(field.fields[0] instanceof app.view.Field);
            expect(Object.keys(field.view.fields).length).toEqual(1);
        });

        it('should render nested fields on render', function() {
            field._getChildFields();
            _.each(field.fields, function(childField) {
                sinon.spy(childField, 'render');
            });

            field.render();

            expect(_.isEmpty(field.fields)).not.toBe(true);
            _.each(field.fields, function(childField) {
                expect(childField.render).toHaveBeenCalledOnce();
            });
        });

        it('should render with css classes', function() {

            var addClass = sinon.spy(field.getFieldElement(), 'addClass');

            field.render();

            expect(addClass).toHaveBeenCalled();
            expect(field.getFieldElement().hasClass('address_fields')).toBeTruthy();
        });

        it('should update the CSS classes of the itself and its child fields', function() {
            var editClass = 'edit',
                viewClass = 'view',
                addViewClassSpy = sinon.spy(field, '_addViewClass'),
                removeViewClassSpy = sinon.spy(field, '_removeViewClass');

            field._getChildFields();
            _.each(field.fields, function(childField) {
                sinon.spy(childField, '_addViewClass');
                sinon.spy(childField, '_removeViewClass');
            });

            field.render();
            expect(addViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeFalsy();
            _.each(field.fields, function(childField) {
                expect(childField._addViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeFalsy();
            });

            field.setMode('view');
            expect(removeViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeTruthy();
            _.each(field.fields, function(childField) {
                expect(childField._removeViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeTruthy();
            });
        });

        it('should not show no data if not readonly', function() {
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });

        it('should not create new fields after the initial render', function() {
            field.render();
            var sfIds = _.pluck(field.fields, 'sfId');
            field.setMode('list');
            expect(_.pluck(field.fields, 'sfId')).toEqual(sfIds);
        });
    });

    describe('render with nodata/readonly fields', function() {
        var field;

        beforeEach(function() {
            var fieldDef = {
                readonly: true,
                fields: [
                    {
                        name: 'date_entered'
                    },
                    {
                        name: 'created_by'
                    }
                ]
            };
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'edit', fieldDef, 'Contacts');
            field.render();
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });

        it('should show no data if readonly and none of its data fields have data', function() {
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(true);
            //after one of the child field's value is assigned, it should fall back to false
            field.model.set('date_entered', '1999-01-01T12:00');
            actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });
    });

    describe('render "No Data" for Accounts module', function() {
        var field;

        beforeEach(function() {
            var fieldDef = {
                readonly: true,
                fields: [
                    {
                        name: 'date_created'
                    },
                    {
                        name: 'date_modified'
                    }
                ]
            };
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'nodata');

            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'edit', fieldDef, 'Accounts');
            field.render();
        });

        afterEach(function() {
            field.dispose();
            field = null;
            Handlebars.templates = {};
        });

        it('should show "No Data" if readonly and its data fields have data', function() {
            expect(field.$el.text().trim()).toEqual('LBL_NO_DATA');
        });
    });

    describe('setMode', function() {
        var field;

        beforeEach(function() {
            var fieldDef = {
                css_class: 'address_fields',
                fields: [
                    'address_street',
                    'address_city',
                    'address_state',
                    'address_postalcode',
                    'address_country'
                ]
            };
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'detail', fieldDef);
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });

        it('should apply `setMode` to all child fields', function() {
            field.render();
            //we start off with all child fields in detail mode
            expect(field.action).toEqual('detail');
            expect(_.unique(_.pluck(field.fields, 'action'))).toEqual(['detail']);

            field.setMode('edit');

            expect(field.action).toEqual('edit');
            expect(_.unique(_.pluck(field.fields, 'action'))).toEqual(['edit']);
        });

        it('should only call render once for each child field', function() {
            field.render();

            _.each(field.fields, function(field) {
                sinon.spy(field, 'render');
            });

            field.setMode('edit');

            _.each(field.fields, function(field) {
                expect(field.render.calledOnce).toBeTruthy();
            });
        });
    });

    describe('focus on tabbing', function() {
        var field;

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
            var fieldDef = {
                css_class: 'address_fields',
                fields: [
                    'address_street',
                    'address_city',
                    'address_state',
                    'address_postalcode',
                    'address_country'
                ]
            };
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'detail', fieldDef);
            sinon.spy(field, 'focus');
            field.render();
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });

        it('should focus the next field if it is currently focusing the last child field', function() {
            field.focusIndex = 5; //all 5 fields have been focused

            expect(field.focus()).toBeFalsy();
            expect(field.focus.calledOnce).toBeTruthy();
            expect(field.focusIndex).toEqual(-1);
        });

        it('should focus this fieldset if it is not currently focusing the last child field', function() {
            field.focusIndex = 0;

            expect(field.focus()).toBeTruthy();
            expect(field.focusIndex).toEqual(1);
        });

        it('should skip disabled child fields when focusing', function() {
            sinon.stub(field.fields[0], 'isDisabled').returns(true);
            field.focusIndex = 0;
            expect(field.focus()).toBeTruthy();
            expect(field.focus.calledTwice).toBeTruthy();
            expect(field.focusIndex).toEqual(2);
        });
    });
});
