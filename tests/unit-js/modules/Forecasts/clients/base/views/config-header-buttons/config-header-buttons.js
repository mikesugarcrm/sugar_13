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
describe('Forecasts.View.ConfigHeaderButtons', function() {
    var app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'config-header-buttons');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        view = SugarTest.createView('base', 'Forecasts', 'config-header-buttons', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('_beforeSaveConfig()', function() {
        it('should set is_setup true on the model', function() {
            view._beforeSaveConfig();
            expect(view.model.get('is_setup')).toBeTruthy();
        });

        describe('if forecast_ranges == "show_custom_buckets"', function() {
            var ctxModel,
                ranges,
                rangeLabelsObj,
                rangeLabelsArr;

            beforeEach(function() {
                ctxModel = view.context.get('model');
                ranges = {
                    include: {
                        min: 70,
                        max: 100,
                        included_in_total: true
                    },
                    custom_1: {
                        min: 50,
                        max: 69,
                        included_in_total: true
                    },
                    exclude: {
                        min: 0,
                        max: 49
                    }
                };

                rangeLabelsObj = {
                    include: 'Include',
                    custom_1: 'Custom',
                    exclude: 'Exclude'
                };

                rangeLabelsArr = [
                    ['include', 'Include'],
                    ['custom_1', 'Custom'],
                    ['exclude', 'Exclude']
                ];

                ctxModel.set({
                    forecast_ranges: 'show_custom_buckets',
                    show_custom_buckets_ranges: ranges,
                    show_custom_buckets_options: rangeLabelsObj,
                    commit_stages_included: ['trashValues']
                });
            });

            it('should unset any commit_stages_included and rebuild', function() {
                view._beforeSaveConfig();
                expect(ctxModel.get('commit_stages_included')).not.toBe(['trashValues']);
            });

            it('should unset any commit_stages_included and rebuild', function() {
                view._beforeSaveConfig();
                expect(ctxModel.get('show_custom_buckets_ranges')).toBe(ranges);
            });

            it('should unset any commit_stages_included and rebuild', function() {
                view._beforeSaveConfig();
                expect(ctxModel.get('show_custom_buckets_options')).toEqual(rangeLabelsArr);
            });
        });
    });
});
