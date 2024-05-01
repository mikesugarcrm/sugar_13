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
describe('Base.Field.Timeperiod', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo',
        fieldDef = {
            events: {
                'click input.selection': 'toggleSelect'
            }
        };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('timeperiod', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('timeperiod', 'field', 'base', 'tooltip-default');
        SugarTest.loadHandlebarsTemplate('timeperiod', 'field', 'base', 'tooltip');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', 'enum');
        field = SugarTest.createField('base', fieldName, 'timeperiod', 'detail', fieldDef, module);
        field.cssClassSelector = 'test';
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.restore();
        field.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    using('values', ['ltr', 'rtl'], function(value) {
        beforeEach(function() {
            app.lang.direction = value;
        });

        describe('initialize', function() {
            it('should set the proper language direction label', function() {
                var lbl = 'LBL_DROPDOWN_TOOLTIP' + (value == 'rtl' ? '_RTL' : '');
                expect(field.tooltipKey).toBe(lbl);
            });
        });
    });

    describe('getLoadEnumOptionsModule', function() {
        it('will return forecasts', function() {
            expect(field.getLoadEnumOptionsModule(), 'Forecasts');
        });
    });

    describe('_destroyTpCollection', function() {
        it('will run the off command on the tpl collection and set it to null on the model', function() {
            var offStub = sinon.stub(field.tpCollection, 'off');
            field._destroyTpCollection();
            expect(offStub).toHaveBeenCalled();
            expect(field.tpCollection).toBe(null);
        });
    });

    describe('getSelect2Options', function() {
        it('will return something', function() {
            var options = field.getSelect2Options(['']);
            expect(options.formatResult).toBeDefined();
            expect(options.formatSelection).toBeDefined();
            expect(field.cssClassSelector).toEqual(options.dropdownCssClass);
        });
    });

    describe('formatTooltips', function() {
        it('creates the tooltip map', function() {
            sinon.stub(field, '_destroyTpCollection');
            var data = new Backbone.Collection([
                new Backbone.Model(
                    {id: 'one', start_date: '2015-08-01', end_date: '2015-08-30'}
                )
            ]);

            sinon.stub(app.user, 'getPreference').callsFake(function() {
                return 'Y-m-d';
            });

            field.formatTooltips(data);

            expect(field.tpTooltipMap).toEqual(
                {one: {start: '2015-08-01', end: '2015-08-30'}}
            );
            expect(field._destroyTpCollection).toHaveBeenCalled();

        });
    });

    describe('formatOption', function() {
        it('will contain the text passed in and set updateDefaultTooltip to be true', function() {
            var val = field.formatOption({id: 'test', text: 'Jasmine Test'});
            expect(val).toContain('Jasmine Test');
            expect(field.updateDefaultTooltip).toBe(true);
        });

        it('will contain the text passed in and set updateDefaultTooltip to be false', function() {
            field.tpTooltipMap = {test: {}};
            var val = field.formatOption({id: 'test', text: 'Jasmine Test'});
            expect(val).toContain('Jasmine Test');
            expect(field.updateDefaultTooltip).toBe(false);
        });
    });
});
