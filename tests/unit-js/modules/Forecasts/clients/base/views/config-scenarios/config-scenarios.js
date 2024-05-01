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
describe('Forecasts.View.ConfigScenarios', function() {
    var app,
        view,
        options,
        meta,
        context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var cfgModel = new Backbone.Model({
            is_setup: 1,
            show_worksheet_best: true,
            show_worksheet_likely: true,
            show_worksheet_worst: false
        });

        context.set({
            model: cfgModel,
            module: 'Forecasts'
        });

        meta = {
            label: 'testLabel',
            panels: [{
                fields: [
                    {
                        name: 'show_worksheet_likely',
                        type: 'bool',
                        label: 'LBL_LIKELY',
                        default: false,
                        enabled: true,
                        view: 'detail'
                    },
                    {
                        name: 'show_worksheet_best',
                        type: 'bool',
                        label: 'LBL_BEST',
                        default: false,
                        enabled: true,
                        view: 'forecastsWorksheet'
                    },
                    {
                        name: 'show_worksheet_worst',
                        type: 'bool',
                        label: 'LBL_WORST',
                        default: false,
                        enabled: true,
                        view: 'forecastsWorksheet'
                    }
                ]
            }]
        };

        options = {
            meta: meta,
            context: context
        };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('config-panel', 'view', 'base', 'title');
        SugarTest.testMetadata.set();

        // load the parent config-panel view
        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Forecasts', 'config-scenarios', meta, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set the first to be Likely', function() {
            view.initialize(options);
            expect(view.scenarioOptions[0].id).toEqual('show_worksheet_likely');
        });

        it('should set the scenarioOptions Likely to be locked', function() {
            view.initialize(options);
            expect(view.scenarioOptions[0].locked).toBeTruthy();
        });

        it('should set the scenarioOptions to Likely, Best and Worst', function() {
            view.initialize(options);
            var len = view.scenarioOptions.length;
            expect(view.scenarioOptions.length).toBe(3);
            expect(view.scenarioOptions[0].id).toBe('show_worksheet_likely');
            expect(view.scenarioOptions[1].id).toBe('show_worksheet_best');
            expect(view.scenarioOptions[2].id).toBe('show_worksheet_worst');
        });

        it('should set the selectedOptions to Likely, Best based on config', function() {
            view.initialize(options);
            var len = view.selectedOptions.length;
            expect(view.selectedOptions.length).toBe(2);
            expect(view.scenarioOptions[0].id).toBe('show_worksheet_likely');
            expect(view.selectedOptions[1].id).toBe('show_worksheet_best');
        });

        it('should set the selectedOptions to Likely, Best and Worst based on config', function() {
            options.context.get('model').set({
                show_worksheet_worst: true
            });
            view.initialize(options);
            var len = view.selectedOptions.length;
            expect(view.selectedOptions.length).toBe(3);
            expect(view.scenarioOptions[0].id).toBe('show_worksheet_likely');
            expect(view.selectedOptions[1].id).toBe('show_worksheet_best');
            expect(view.selectedOptions[2].id).toBe('show_worksheet_worst');

        });
    });

    describe('bindDataChange()', function() {
        var expected;

        it('should set this.titleSelectedValues based on config - only likely', function() {
            view.bindDataChange();
            view.model.trigger('change:scenarios', new Backbone.Model({
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            }));
            expected = 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY';
            expect(view.titleSelectedValues).toBe(expected);
        });

        it('should set this.titleSelectedValues based on config - likely & best', function() {
            view.bindDataChange();
            view.model.trigger('change:scenarios', new Backbone.Model({
                show_worksheet_best: true,
                show_worksheet_likely: true,
                show_worksheet_worst: false
            }));
            expected = 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY, '
                + 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST';
            expect(view.titleSelectedValues).toBe(expected);
        });

        it('should set this.titleSelectedValues based on config - likely & worst', function() {
            view.bindDataChange();
            view.model.trigger('change:scenarios', new Backbone.Model({
                show_worksheet_best: false,
                show_worksheet_likely: true,
                show_worksheet_worst: true
            }));
            expected = 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY, '
                + 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST';
            expect(view.titleSelectedValues).toBe(expected);
        });

        it('should set this.titleSelectedValues based on config - likely, best, & worst', function() {
            view.bindDataChange();
            view.model.trigger('change:scenarios', new Backbone.Model({
                show_worksheet_best: true,
                show_worksheet_likely: true,
                show_worksheet_worst: true
            }));
            expected = 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY, '
                + 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST, '
                + 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST';
            expect(view.titleSelectedValues).toBe(expected);
        });
    });
});
