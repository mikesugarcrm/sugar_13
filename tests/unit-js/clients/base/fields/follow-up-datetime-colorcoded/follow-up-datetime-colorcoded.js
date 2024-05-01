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
describe('Base.Fields.FollowUpDatetimeColorCoded', function() {
    var app;
    var field;
    var fieldName = 'test_follow_up_datetime';
    var model;
    var module = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        field = SugarTest.createField({
            client: 'base',
            name: fieldName,
            type: 'follow-up-datetime-colorcoded',
            viewName: 'list',
            fieldDef: {
                color_code_classes: {
                    'overdue': 'expired',
                    'more_than_a_day': 'white black-text',
                },
            },
            module: module,
            model: model,
        });
    });

    afterEach(function() {
        sinon.restore();
        model = null;
        field = null;
    });

    describe('setColorCoding', function() {
        var getLabelStub;

        beforeEach(function() {
            field.$el.attr('class', ''); // remove all classes
            getLabelStub = sinon.stub(field, '_getColorCodeLabel');
        });

        it('should add appropiate classes with default classes when label value is not empty', function() {
            field.model.set(field.name, 'non_empty_label', {silent: true});
            field.action = 'list';

            getLabelStub.returns('overdue');
            field.setColorCoding();

            var classes = field.$el.attr('class').split(' ');
            expect(classes.length).toEqual(3);
            expect(classes).toContain('expired');
            expect(classes).toContain('label');
            expect(classes).toContain('pill');
        });

        it('should add default classes only when label value is empty', function() {
            field.model.set(field.name, 'empty_label', {silent: true});
            field.action = 'list';

            getLabelStub.returns('more_than_a_day');
            field.setColorCoding();

            var classes = field.$el.attr('class').split(' ');
            expect(classes.length).toEqual(4);
            expect(classes).toContain('label');
            expect(classes).toContain('pill');
            expect(classes).toContain('white');
            expect(classes).toContain('black-text');
        });

        it('should not add any classes if the action is not list or detail', function() {
            field.action = 'create';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes).toBe('');
        });

        it('should not add any classes for an empty label', function() {
            field.model.set(field.name, '', {silent: true});
            field.action = 'list';

            getLabelStub.returns('');
            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes).toBe('');
        });
    });

    describe('_getColorCodeLabel', function() {
        var dateStub;

        beforeEach(function() {
            dateStub = sinon.stub(app, 'date');
            dateStub.returns('now');
        });

        it('should return empty string when field value is empty', function() {
            field.model.set(field.name, '', {silent: true});

            expect(field._getColorCodeLabel()).toBe('');
        });

        it('should return \'overdue\' for follow up datetime is past', function() {
            field.model.set(field.name, 'fake_past_time', {silent: true});

            dateStub.withArgs('fake_past_time').returns({
                isBefore: sinon.stub().withArgs('now').returns(true),
            });

            expect(field._getColorCodeLabel()).toBe('overdue');
        });

        it('should return \'in_a_day\' for a follow up datetime within a day from now', function() {
            field.model.set(field.name, 'due_in_a_day', {silent: true});

            dateStub.withArgs('due_in_a_day').returns({
                isBefore: sinon.stub().withArgs('now').returns(false),
                subtract: sinon.stub().withArgs(1, 'days').returns({
                    isBefore: sinon.stub().withArgs('now').returns(true)
                }),
            });

            expect(field._getColorCodeLabel()).toBe('in_a_day');
        });

        it('should return \'more_than_a day\' for a follow up datetime is more than a day away', function() {
            field.model.set(field.name, 'far_from_a_day', {silent: true});

            dateStub.withArgs('far_from_a_day').returns({
                isBefore: sinon.stub().withArgs('now').returns(false),
                subtract: sinon.stub().withArgs(1, 'days').returns({
                    isBefore: sinon.stub().withArgs('now').returns(false)
                }),
            });

            expect(field._getColorCodeLabel()).toBe('more_than_a_day');
        });
    });
});
