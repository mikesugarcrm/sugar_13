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
describe('View.Fields.Base.RecurrenceField', function() {
    var app, field, createFieldProperties, sandbox, fieldVisibility,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        createFieldProperties = {
            client: 'base',
            name: 'recurrence',
            type: 'recurrence',
            viewName: 'edit',
            module: module
        };
        field = SugarTest.createField(createFieldProperties);

        fieldVisibility = {};
        sandbox.stub(field, '_showField').callsFake(function(fieldName) {
            fieldVisibility[fieldName] = 'shown';
        });
        sandbox.stub(field, '_hideField').callsFake(function(fieldName) {
            fieldVisibility[fieldName] = 'hidden';
        });
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
    });

    describe('Render', function() {
        it('should not show recurrence field when repeat type is blank', function() {
            field.model.set('repeat_type', '');
            expect(field.isVisible()).toBe(false);
        });

        it('should show recurrence field when repeat type is Daily', function() {
            field.model.set('repeat_type', 'Daily');
            expect(field.isVisible()).toBe(true);
        });

        it('should show repeat day of week field when repeat type is weekly', function() {
            field.model.set('repeat_type', 'Weekly');
            expect(fieldVisibility.repeat_dow).toEqual('shown');
        });

        it('should hide repeat day of week field when repeat type is not weekly', function() {
            field.model.set('repeat_type', 'Daily');
            expect(fieldVisibility.repeat_dow).toEqual('hidden');
        });
    });

    describe('Repeat End Field Behavior', function() {
        it('changing repeat_end_type to Occurrences should hide repeat_until and show repeat_count', function() {
            field.model.set('repeat_end_type', 'Occurrences');
            expect(fieldVisibility.repeat_until).toEqual('hidden');
            expect(fieldVisibility.repeat_count).toEqual('shown');
        });

        it('changing repeat_end_type to Until should hide repeat_count and show repeat_until', function() {
            field.model.set('repeat_end_type', 'Until');
            expect(fieldVisibility.repeat_until).toEqual('shown');
            expect(fieldVisibility.repeat_count).toEqual('hidden');
        });

        it('should clear repeat_until when switching to Occurrences mode', function() {
            field.model.set('repeat_end_type', 'Until');
            field.model.set('repeat_until', '1/1/2015');
            field.model.set('repeat_end_type', 'Occurrences');
            expect(field.model.has('repeat_until')).toEqual(false);
        });

        it('should clear repeat_count when switching to Until mode', function() {
            field.model.set('repeat_end_type', 'Occurrences');
            field.model.set('repeat_count', 3);
            field.model.set('repeat_end_type', 'Until');
            expect(field.model.has('repeat_count')).toEqual(false);
        });

        it('should remember my previous repeat_until value when switching to Occurrences mode and back', function() {
            var repeatUntil = '1/1/2015';
            field.model.set('repeat_end_type', 'Until');
            field.model.set('repeat_until', repeatUntil);
            field.model.set('repeat_end_type', 'Occurrences');
            field.model.set('repeat_end_type', 'Until');
            expect(field.model.get('repeat_until')).toEqual(repeatUntil);
        });

        it('should remember my previous repeat_count value when switching to Until mode and back', function() {
            var repeatCount = 3;
            field.model.set('repeat_end_type', 'Occurrences');
            field.model.set('repeat_count', repeatCount);
            field.model.set('repeat_end_type', 'Until');
            field.model.set('repeat_end_type', 'Occurrences');
            expect(field.model.get('repeat_count')).toEqual(repeatCount);
        });

        it('should default repeat_end_type to "Occurrences" when repeat_count has a value', function() {
            field.model.set('repeat_count', 3);
            field.model.trigger('sync');
            expect(field.model.get('repeat_end_type')).toEqual('Occurrences');
        });

        it('should default repeat_end_type to "Until" when repeat_until has a value', function() {
            field.model.set('repeat_until', '1/1/2015');
            field.model.trigger('sync');
            expect(field.model.get('repeat_end_type')).toEqual('Until');
        });

        it('should not hide repeat_count or repeat_until when neither field nor repeat_end_type have a value on render', function() {
            field.model.set('repeat_end_type', '');
            field.model.set('repeat_until', '');
            field.model.set('repeat_count', '');
            field._showField('repeat_until');
            field._showField('repeat_count');
            field.render();
            expect(field.model.get('repeat_end_type')).toEqual('');
            expect(fieldVisibility.repeat_until).toEqual('shown');
            expect(fieldVisibility.repeat_count).toEqual('shown');
        });
    });

    describe('Custom Date Repeat Selector Behavior', function() {
        using(
            'different repeat_selector values',
            [
                {
                    expectation: 'should not show any dependent fields',
                    repeatSelector: '',
                    repeatSelectorVisible: true,
                    repeatDays: 'hidden',
                    repeatOrdinal: 'hidden',
                    repeatUnit: 'hidden'
                },
                {
                    expectation: 'should show only repeat_days',
                    repeatSelector: 'Each',
                    repeatSelectorVisible: true,
                    repeatDays: 'shown',
                    repeatOrdinal: 'hidden',
                    repeatUnit: 'hidden'
                },
                {
                    expectation: 'should show repeat_ordinal and repeat_unit',
                    repeatSelector: 'On',
                    repeatSelectorVisible: true,
                    repeatDays: 'hidden',
                    repeatOrdinal: 'shown',
                    repeatUnit: 'shown'
                },
                {
                    expectation: 'should not show repeat_days when selector not visible',
                    repeatSelector: 'Each',
                    repeatSelectorVisible: false,
                    repeatDays: 'hidden',
                    repeatOrdinal: 'hidden',
                    repeatUnit: 'hidden'
                },
                {
                    expectation: 'should not show repeat_ordinal and repeat_unit when selector not visible',
                    repeatSelector: 'On',
                    repeatSelectorVisible: false,
                    repeatDays: 'hidden',
                    repeatOrdinal: 'hidden',
                    repeatUnit: 'hidden'
                }
            ],
            function(value) {
                it(value.expectation, function() {
                    sandbox.stub(field, '_isFieldVisible').returns(value.repeatSelectorVisible);
                    field.model.set('repeat_selector', value.repeatSelector);
                    expect(fieldVisibility.repeat_days).toEqual(value.repeatDays);
                    expect(fieldVisibility.repeat_ordinal).toEqual(value.repeatOrdinal);
                    expect(fieldVisibility.repeat_unit).toEqual(value.repeatUnit);
                });
            }
        );
    });

    describe('Defaulting Fields', function() {
        it('should set fields to defaults when repeat type changes and field is empty', function() {
            var expected = {
                repeat_type: 'Weekly',
                repeat_interval: 1,
                repeat_count: 10
            };

            field.fields = [
                { name: 'repeat_end_type', def: { 'default': expected.repeat_end_type } },
                { name: 'repeat_interval', def: { 'default': expected.repeat_interval } },
                { name: 'repeat_count', def: { 'default': expected.repeat_count } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_interval: 0,
                repeat_count: undefined
            });
            field.model.set('repeat_type', 'Weekly');
            expect(field.model.attributes).toEqual(expected);
        });

        it('should not set repeat_count field to default when repeat type changes and repeat_end_type is "Until"', function() {
            var expected = {
                repeat_end_type: 'Until',
                repeat_type: 'Weekly',
                repeat_until: '1/1/2015'
            };

            field.fields = [
                { name: 'repeat_count', def: { 'default': 10 } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_end_type: 'Until',
                repeat_count: undefined,
                repeat_until: expected.repeat_until
            });
            field.model.set('repeat_type', expected.repeat_type);
            expect(field.model.attributes).toEqual(expected);
        });

        it('should not set repeat_until field to default when repeat type changes and repeat_end_type is "Occurrences"', function() {
            var expected = {
                repeat_end_type: 'Occurrences',
                repeat_type: 'Weekly',
                repeat_count: 10
            };

            field.fields = [
                { name: 'repeat_until', def: { 'default': '1/1/2015' } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_end_type: 'Occurrences',
                repeat_count: expected.repeat_count,
                repeat_until: undefined
            });
            field.model.set('repeat_type', expected.repeat_type);
            expect(field.model.attributes).toEqual(expected);
        });

        it('should not set fields to defaults when repeat type changes and field is not blank', function() {
            var expected = {
                repeat_end_type: 'Occurrences',
                repeat_type: 'Weekly',
                repeat_interval: 2,
                repeat_count: 11
            };

            field.fields = [
                { name: 'repeat_end_type', def: { 'default': expected.repeat_end_type } },
                { name: 'repeat_interval', def: { 'default': 1 } },
                { name: 'repeat_count', def: { 'default': 10 } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_end_type: expected.repeat_end_type,
                repeat_interval: expected.repeat_interval,
                repeat_count: expected.repeat_count
            });
            field.model.set('repeat_type', 'Weekly');
            expect(field.model.attributes).toEqual(expected);
        });

        it('should set fields other than repeat_end_type to defaults when repeat type changes to be non-repeating', function() {
            var expected = {
                repeat_type: '',
                repeat_interval: 1,
                repeat_count: 10
            };

            field.fields = [
                { name: 'repeat_end_type', def: { 'default': expected.repeat_end_type } },
                { name: 'repeat_interval', def: { 'default': expected.repeat_interval } },
                { name: 'repeat_count', def: { 'default': expected.repeat_count } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_interval: null,
                repeat_count: undefined
            });
            field.model.set('repeat_type', '');
            expect(field.model.attributes).toEqual(expected);
        });
    });

    describe('validating the fields', function() {
        var errors;

        beforeEach(function() {
            errors = {};
        });

        describe('are valid', function() {
            using('empty values', ['', null, undefined], function(value) {
                it('when repeat_count and repeat_until are empty for non-recurring events', function() {
                    field.model.set('repeat_type', '', {silent: true});
                    field.model.set('repeat_count', value, {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(_.size(errors)).toBe(0);
                });
            });

            it('when repeat_count is the minimum value', function() {
                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_end_type', 'Occurrences', {silent: true});
                field.model.set('repeat_count', field.repeatCountMin, {silent: true});
                field.model.set('repeat_until', '', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(_.size(errors)).toBe(0);
            });

            it('when repeat_count is greater than the minimum value', function() {
                // always more than the minimum, even if the minimum changes
                var repeatCount = field.repeatCountMin + 1;

                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_end_type', 'Occurrences', {silent: true});
                field.model.set('repeat_count', repeatCount, {silent: true});
                field.model.set('repeat_until', '', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(_.size(errors)).toBe(0);
            });

            it('when repeat_end_type is "Until" and repeat_count is empty', function() {
                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_end_type', 'Until', {silent: true});
                field.model.set('repeat_count', '', {silent: true});
                field.model.set('repeat_until', '1/1/2015', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(_.size(errors)).toBe(0);
            });

            it('when repeat_end_type is "Occurrences" and repeat_until is empty', function() {
                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_end_type', 'Occurrences', {silent: true});
                field.model.set('repeat_count', 5, {silent: true});
                field.model.set('repeat_until', '', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(_.size(errors)).toBe(0);
            });
        });

        describe('are invalid', function() {
            using('empty values', ['', null, undefined], function(value) {
                it('when repeat_until is empty for recurring event with repeat_end_type of "Until"', function() {
                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_end_type', 'Until', {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(errors.repeat_until).toEqual({required: true});
                });
                it('when repeat_count is empty for recurring event with repeat_end_type of "Occurrences"', function() {
                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_end_type', 'Occurrences', {silent: true});
                    field.model.set('repeat_count', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(errors.repeat_count).toEqual({required: true});
                });
            });

            it('when repeat_count is less than the minimum value', function() {
                // always less than the minimum, even if the minimum changes
                var repeatCount = field.repeatCountMin - 1;

                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_end_type', 'Occurrences', {silent: true});
                field.model.set('repeat_count', repeatCount, {silent: true});
                field.model.set('repeat_until', '', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(errors.repeat_count).toEqual({minValue: field.repeatCountMin});
            });
        });

        describe('Unsupported Rrule behavior', function() {
            it('should change the field type to unsupportedRrule', function() {
                field.model.set(
                    'rset',
                    '{\"sugarSupportedRrule\":false}'
                );

                field.updateFieldType();

                expect(field.type).toEqual('unsupportedRrule');
            });

            it('should not change the field type', function() {
                field.model.set(
                    'rset',
                    '{\"sugarSupportedRrule\":true}'
                );

                var result = field.updateFieldType();

                expect(result).toEqual(false);
            });
        });
    });
});
