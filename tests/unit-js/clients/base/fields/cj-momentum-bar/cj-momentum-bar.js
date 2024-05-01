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
describe('Base.Field.CjMomentumBar', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        field = SugarTest.createField('base', 'cj-momentum-bar', 'cj-momentum-bar');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('render', function() {
        using('input', [
            {
                ratio: 1,
                color: 'cj-bar-green',
            },
            {
                ratio: 0.74,
                color: 'cj-bar-yellow',
            },
            {
                ratio: 0.49,
                color: 'cj-bar-orange',
            },
            {
                ratio: 0.11,
                color: 'cj-bar-red',
            },
        ],

        function(input) {
            it('barColor should match the color given in input color', function() {
                field.model.set({
                    momentum_ratio: input.ratio,
                });

                field.render();
                expect(field.barColor).toBe(input.color);
            });
        });
    });

    describe('unformat', function() {
        using('input', [
            {
                value: 100,
                result: 1,
            },
            {
                value: 50,
                result: 0.5,
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
                value: 0.75,
                result: 75,
            },
            {
                value: 0.34,
                result: 34,
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
});
