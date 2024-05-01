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
describe('Base.Fields.EnumColorcoded', function() {
    var app;
    var field;
    var fieldName = 'test_enum';
    var model;
    var module = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);

        field = SugarTest.createField(
            'base',
            fieldName,
            'enum-colorcoded',
            'list',
            {},
            module,
            model,
            null
        );

        field.items = {
            'New': 'New',
            'Duplicate': 'Duplicate',
        };
    });

    afterEach(function() {
        sinon.restore();
        model = null;
        field = null;
    });

    describe('bindDataChange', function() {
        it('should call setColorCoding on change', function() {
            field.bindDataChange();

            var setColorCodingStub = sinon.stub(field, 'setColorCoding');
            field.model.set(field.name, 'Duplicate');

            expect(setColorCodingStub).toHaveBeenCalled();
        });
    });

    describe('setColorCoding', function() {
        beforeEach(function() {
            field.$el.attr('class', ''); // remove all classes
        });

        it('should add classes based on action and status plus the default classes on detail/list view', function() {
            field.model.set(field.name, 'Duplicate', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').split(' ');
            expect(classes.length).toEqual(5);
            expect(classes).toContain('list');
            expect(classes).toContain('blue');
            expect(classes).toContain('label');
            expect(classes).toContain('pill');
            expect(classes).toContain('text-white');

            // test changing from one action and status to another
            field.model.set(field.name, 'New', {silent: true});
            field.action = 'detail';

            field.setColorCoding();

            classes = field.$el.attr('class').split(' ');
            expect(classes).not.toContain('list');
            expect(classes).not.toContain('blue');
            expect(classes).toContain('detail');
            expect(classes).toContain('dark-green');
            expect(classes).toContain('label');
            expect(classes).toContain('pill');
            expect(classes).toContain('text-white');
        });

        it('should not add any classes if the action is not detail or list', function() {
            field.action = 'edit';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });

        it('should not add any classes for an empty value', function() {
            field.model.set(field.name, '', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });

        it('should not add any classes if there is no color defined', function() {
            field.model.set(field.name, 'A status with no color', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });
    });
});
