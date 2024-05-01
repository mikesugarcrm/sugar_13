
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
describe('Base.Calendar.ColorpickerField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('colorpicker', 'field', 'base', 'detail', 'Calendar');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);
        model.set('color', '#FFFFFF');

        field = SugarTest.createField(
            'base',
            'color',
            'colorpicker',
            'detail',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('_render()', function() {
        it('should not call fillIconBackground when is in edit view', function() {
            var fillIconBackgroundStub = sinon.stub(field, 'fillIconBackground');
            field.action = 'edit';
            field._render();
            expect(fillIconBackgroundStub).not.toHaveBeenCalled();
        });

        it('should fill icon background when is in detail view', function() {
            field.action = 'detail';
            field.model.set(field.name, '#FFFFFF');
            field._render();
            expect(field.$('[data-content=color-picker-icon]').css('background-color')).toBe('rgb(255, 255, 255)');
        });

        it('should fill icon background when is in list view', function() {
            field.action = 'list';
            field.model.set(field.name, '#FFFFFF');
            field._render();
            expect(field.$('[data-content=color-picker-icon]').css('background-color')).toBe('rgb(255, 255, 255)');
        });
    });
});
