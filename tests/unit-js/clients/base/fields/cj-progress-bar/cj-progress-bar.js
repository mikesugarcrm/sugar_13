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
describe('Base.Field.CjProgressBar', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        field = SugarTest.createField('base', 'cj-progress-bar', 'cj-progress-bar');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('unformat', function() {
        using('input', [
            {
                value: 72,
                result: 0.72,
            },
            {
                value: 39,
                result: 0.39,
            },
        ],

        function(input) {
            it('unformat return value should match input.result', function() {
                expect(field.unformat(input.value)).toEqual(input.result);
            });
        });
    });

    describe('format', function() {
        using('input', [
            {
                value: 0.52,
                result: 52,
            },
            {
                value: 0.17,
                result: 17,
            },
        ],

        function(input) {
            it('format return value should match input.result', function() {
                expect(field.format(input.value)).toEqual(input.result);
            });
        });
    });

    describe('_loadTemplate', function() {
        it('options viewName should be detail', function() {
            field._loadTemplate();
            expect(field.options.viewName).toBe('detail');
        });
    });

    describe('render', function() {
        using('input', [
            {
                state: 'cancelled',
                color: 'cj-bar-red',
            },
            {
                state: 'completed',
                color: 'cj-bar-green',
            },
        ],

        function(input) {
            it('barColor should match the color given in input color', function() {
                field.model.set({
                    state: input.state,
                });

                field.render();
                expect(field.barColor).toBe(input.color);
            });
        });
    });
});
