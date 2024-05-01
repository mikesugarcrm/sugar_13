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
describe('Base.Field.DateTimeCombo-ColorCoded', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'datetimecombo-colorcoded', 'datetimecombo-colorcoded', 'list', {
            'completed_status_value': 'Completed'
        });
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    it('should have overdue color code when not complete and date in the past', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().subtract(1, 'hours'));
        field.model.set('status', 'Not Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(true);
        expect(field.$el.hasClass(field.colorCodeClasses['upcoming'])).toEqual(false);
    });

    it('should have upcoming color code when not complete and date in the next 24 hours', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().add(1, 'hours'));
        field.model.set('status', 'Not Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
        expect(field.$el.hasClass(field.colorCodeClasses['upcoming'])).toEqual(true);
    });

    it('should have no color code when not complete and date beyond next 24 hours', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().add(25, 'hours'));
        field.model.set('status', 'Not Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
        expect(field.$el.hasClass(field.colorCodeClasses['upcoming'])).toEqual(false);
    });

    it('should have no color code when complete', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().subtract(1, 'hours'));
        field.model.set('status', field.def.completed_status_value);
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
        expect(field.$el.hasClass(field.colorCodeClasses['upcoming'])).toEqual(false);
    });

    it('should have no color code when action is not list', function() {
        field.action = 'edit';
        field.model.set(field.name, app.date().subtract(1, 'hours'));
        field.model.set('status', 'Not Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
        expect(field.$el.hasClass(field.colorCodeClasses['upcoming'])).toEqual(false);
    });

    it('should add color code when status is changed out of completed', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().subtract(1, 'hours'));
        field.model.set('status', 'Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
        field.model.set('status', 'Not Completed');
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(true);
    });

    it('should remove color code when status is changed to completed', function() {
        field.action = 'list';
        field.model.set(field.name, app.date().subtract(1, 'hours'));
        field.model.set('status', 'Not Completed');
        field.render();
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(true);
        field.model.set('status', 'Completed');
        expect(field.$el.hasClass(field.colorCodeClasses['overdue'])).toEqual(false);
    });
});
