
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
describe('Forecasts.View.ConfigRanges', function() {
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
            has_commits: 1,
            forecast_ranges: 'show_binary',
            commit_stages_included: ['include'],
            buckets_dom: 'commit_stage_dom'
        });

        context.set({
            model: cfgModel,
            module: 'Forecasts'
        });

        meta = {
            label: 'testLabel',
            panels: [{
                fields: {
                    forecast_ranges: {
                        name: 'forecast_ranges',
                        type: 'radioenum',
                        label: 'LBL_FORECASTS_CONFIG_RANGES_OPTIONS',
                        view: 'edit',
                        options: 'forecasts_config_ranges_options_dom',
                        'default': false,
                        enabled: true,
                        value: ''
                    },
                    category_ranges: {
                        name: 'category_ranges'
                    },
                    buckets_dom: {
                        name: 'buckets_dom',
                        options: {
                            show_binary: 'commit_stage_binary_dom',
                            show_buckets: 'commit_stage_dom'
                        },
                        value: ''
                    }
                }
            }]
        };

        options = {
            context: context,
            meta: meta
        };

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                is_setup: 1,
                has_commits: 1,
                forecast_ranges: 'show_binary',
                commit_stages_included: ['include'],
                buckets_dom: 'commit_stage_dom'
            };
        });
        // load the parent config-panel view
        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Forecasts', 'config-ranges', meta, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            view.initialize(options);
        });

        describe('forecastRangesField field', function() {
            it("should be defined", function() {
                expect(view.forecastRangesField).toBeDefined();
            });

            it("should be set to metadata params", function() {
                expect(view.forecastRangesField).toEqual(_.first(view.meta.panels).fields.forecast_ranges);
            });
        });

        describe('bucketsDomField field', function() {
            it("should be defined", function() {
                expect(view.bucketsDomField).toBeDefined();
            });

            it("should be set to metadata params", function() {
                expect(view.bucketsDomField).toEqual(_.first(view.meta.panels).fields.buckets_dom);
            });
        });

        describe('categoryRangesField field', function() {
            it("should be defined", function() {
                expect(view.categoryRangesField).toBeDefined();
            });

            it("should be set to metadata params", function() {
                expect(view.categoryRangesField).toEqual(_.first(view.meta.panels).fields.category_ranges);
            });
        });

        describe('selectedRange field', function() {
            it("should be defined", function() {
                expect(view.selectedRange).toBeDefined();
            });

            it("should be set to metadata/module params", function() {
                expect(view.selectedRange).toEqual(view.model.get('forecast_ranges'));
            });
        });
    });

    describe('_updateTitleValues()', function() {
        var ranges;

        beforeEach(function() {
            view.initialize(options);

            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                return {
                    show_binary: 'Two Ranges',
                    show_buckets: 'Three Ranges',
                    show_custom_buckets: 'Custom Ranges'
                }
            });
        });

        describe('when forecast_ranges == "show_binary"', function() {
            beforeEach(function() {
                ranges = {
                    include: {
                        min: 85,
                        max: 100
                    },
                    exclude: {
                        min: 0,
                        max: 84
                    }
                };
                view.model.set({
                    forecast_ranges: 'show_binary',
                    show_binary_ranges: ranges
                });
            });

            it('titleSelectedValues', function() {
                view._updateTitleValues();
                expect(view.titleSelectedValues).toEqual('0% - 84%, 85% - 100%');
            });

            it('titleSelectedRange', function() {
                view._updateTitleValues();
                expect(view.titleSelectedRange).toEqual('Two Ranges');
            });
        });

        describe('when forecast_ranges == "show_buckets"', function() {
            beforeEach(function() {
                ranges = {
                    include: {
                        min: 85,
                        max: 100
                    },
                    upside: {
                        min: 70,
                        max: 84
                    },
                    exclude: {
                        min: 0,
                        max: 69
                    }
                };
                view.model.set({
                    forecast_ranges: 'show_buckets',
                    show_buckets_ranges: ranges
                });
            });

            it('titleSelectedValues', function() {
                view._updateTitleValues();
                expect(view.titleSelectedValues).toEqual('0% - 69%, 70% - 84%, 85% - 100%');
            });

            it('titleSelectedRange', function() {
                view._updateTitleValues();
                expect(view.titleSelectedRange).toEqual('Three Ranges');
            });
        });

        describe('when forecast_ranges == "show_custom_buckets"', function() {
            beforeEach(function() {
                ranges = {
                    include: {
                        min: 85,
                        max: 100
                    },
                    upside: {
                        min: 70,
                        max: 84
                    },
                    custom_1: {
                        min: 60,
                        max: 69
                    },
                    exclude: {
                        min: 0,
                        max: 59
                    }
                };
                view.model.set({
                    forecast_ranges: 'show_custom_buckets',
                    show_custom_buckets_ranges: ranges
                });
            });

            it('titleSelectedValues', function() {
                view._updateTitleValues();
                expect(view.titleSelectedValues).toEqual('0% - 59%, 60% - 69%, 70% - 84%, 85% - 100%');
            });

            it('titleSelectedRange', function() {
                view._updateTitleValues();
                expect(view.titleSelectedRange).toEqual('Custom Ranges');
            });
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'updateTitle').callsFake(function() {});
            view.bindDataChange();
        });

        it('updateTitle() should be called when show_binary_ranges changes on the model', function() {
            view.model.trigger('change:show_binary_ranges');
            expect(view.updateTitle).toHaveBeenCalled();
        });

        it('updateTitle() should be called when show_buckets_ranges changes on the model', function() {
            view.model.trigger('change:show_buckets_ranges');
            expect(view.updateTitle).toHaveBeenCalled();
        });

        it('updateTitle() should be called when show_custom_buckets_ranges changes on the model', function() {
            view.model.trigger('change:show_custom_buckets_ranges');
            expect(view.updateTitle).toHaveBeenCalled();
        });

        describe('change:forecast_ranges', function() {
            beforeEach(function() {
                sinon.stub(view, 'updateCustomRangesCheckboxes').callsFake(function() {});
            });

            it('updateTitle() should be called when forecast_ranges changes on the model', function() {
                view.model.set({
                    forecast_ranges: 'changing!'
                });
                expect(view.updateTitle).toHaveBeenCalled();
            });

            it('updateCustomRangesCheckboxes() should be called when forecast_ranges == "show_custom_buckets"',
                function() {
                    view.model.set({
                        forecast_ranges: 'show_custom_buckets'
                    });
                    expect(view.updateCustomRangesCheckboxes).toHaveBeenCalled();
            });

            it('updateCustomRangesCheckboxes() should not be called when forecast_ranges != "show_custom_buckets"',
                function() {
                    view.model.set({
                        forecast_ranges: 'test'
                    });
                    expect(view.updateCustomRangesCheckboxes).not.toHaveBeenCalled();
            });
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.spy(view, '$');
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, 'updateTitle').callsFake(function() {});
            sinon.stub(view, 'updateCustomRangesCheckboxes').callsFake(function() {});
            sinon.stub(view, 'connectSliders').callsFake(function() {});
            view.$el.html('<input type="radio" checked>')
        });

        it('$(":radio:checked") should be called', function() {
            view._render();
            expect(view.$).toHaveBeenCalled();
        });

        it('should not call updateCustomRangesCheckboxes() if forecast_ranges != show_custom_buckets', function() {
            view._render();
            expect(view.updateCustomRangesCheckboxes).not.toHaveBeenCalled();
        });
        it('should call updateCustomRangesCheckboxes() if forecast_ranges == show_custom_buckets', function() {
            view.model.set({
                forecast_ranges: 'show_custom_buckets'
            });
            view._render();
            expect(view.updateCustomRangesCheckboxes).toHaveBeenCalled();
        });
    });

    describe('selectionHandler()', function() {
        var expectedVal;
        beforeEach(function() {
            sinon.stub(view, '_selectionHandler').callsFake(function() {});
            sinon.stub(view, '_customSelectionHandler').callsFake(function() {});
            sinon.stub(view, 'connectSliders').callsFake(function() {});
            sinon.stub(view, 'updateTitle').callsFake(function() {});

            expectedVal = '';
            sinon.stub($.fn, 'val').callsFake(function() {
                return expectedVal;
            });
        });

        describe('call selectionHandler for show_binary', function() {
            it('should call _selectionHandler()', function() {
                view.selectionHandler({});
                expect(view._selectionHandler).toHaveBeenCalled();
            });

            it('should not call _customSelectionHandler()', function() {
                view.selectionHandler({});
                expect(view._customSelectionHandler).not.toHaveBeenCalled();
            });

            it('should set this.selectedRange', function() {
                expectedVal = 'show_binary';
                view.selectionHandler({});
                expect(view.selectedRange).toEqual(expectedVal);
            });
        });

        describe('call selectionHandler for show_buckets', function() {
            it('should call _selectionHandler()', function() {
                view.selectionHandler({});
                expect(view._selectionHandler).toHaveBeenCalled();
            });

            it('should not call _customSelectionHandler()', function() {
                view.selectionHandler({});
                expect(view._customSelectionHandler).not.toHaveBeenCalled();
            });

            it('should set this.selectedRange', function() {
                expectedVal = 'show_buckets';
                view.selectionHandler({});
                expect(view.selectedRange).toEqual(expectedVal);
            });
        });

        describe('call selectionHandler for show_custom_buckets', function() {
            it('should call _selectionHandler()', function() {
                view.selectionHandler({});
                expect(view._selectionHandler).toHaveBeenCalled();
            });

            it('should not call _customSelectionHandler()', function() {
                view.selectionHandler({});
                expect(view._customSelectionHandler).not.toHaveBeenCalled();
            });

            it('should set this.selectedRange', function() {
                expectedVal = 'show_custom_buckets';
                view.selectionHandler({});
                expect(view.selectedRange).toEqual(expectedVal);
            });
        });
    });

    describe('_getLastCustomRangeIndex()', function() {
        var lastIndex;

        beforeEach(function() {
            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    customType: 'custom_default',
                    customIndex: 0
                },
                upside: {
                    customType: 'custom_default',
                    customIndex: 0
                },
                exclude: {
                    customType:'custom_default',
                    customIndex: 0
                }
            }
        });

        it("default set", function() {
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(0);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(0);
        });

        it("test _getLastCustomRangeIndex method - case 1", function() {
            view.fieldRanges['show_custom_buckets']['custom_1'] = {
                customType: 'custom',
                customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_1'] = {
                customType: 'custom_without_probability',
                customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_2'] = {
                customType: 'custom_without_probability',
                customIndex: 2
            };
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(1);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(2);
        });

        it("case 2", function() {
            view.fieldRanges['show_custom_buckets']['custom_10'] = {
                customType: 'custom',
                customIndex: 10
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_21'] = {
                customType: 'custom_without_probability',
                customIndex: 21
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_11'] = {
                customType: 'custom_without_probability',
                customIndex: 11
            };
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(10);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(21);
        });
    });

    describe('_getLastCustomRange()', function() {
        var lastRange;

        beforeEach(function() {
            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    label: 'include',
                    customType: 'custom_default',
                    customIndex: 0
                },
                upside: {
                    label: 'upside',
                    customType: 'custom_default',
                    customIndex: 0
                },
                exclude: {
                    label: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0
                }
            }
        });

        it("default set", function() {
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('upside');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('exclude');
        });

        it("case 1", function() {
            view.fieldRanges['show_custom_buckets']['custom_1'] = {
                label: 'custom_1',
                customType: 'custom',
                customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_1'] = {
                label: 'custom_without_probability_1',
                customType: 'custom_without_probability',
                customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_2'] = {
                label: 'custom_without_probability_2',
                customType: 'custom_without_probability',
                customIndex: 2
            };
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_1');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_without_probability_2');
        });

        it("case 2", function() {
            view.fieldRanges['show_custom_buckets']['custom_10'] = {
                label: 'custom_10',
                customType: 'custom',
                customIndex: 10
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_21'] = {
                label: 'custom_without_probability_21',
                customType: 'custom_without_probability',
                customIndex: 21
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_11'] = {
                label: 'custom_without_probability_11',
                customType: 'custom_without_probability',
                customIndex: 11
            };
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_10');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_without_probability_21');
        });
    });

    describe('addCustomRange()', function() {
        var ranges,
            customBucketOptions;

        beforeEach(function() {
            sinon.stub(view, 'updateTitle').callsFake(function() {});

            // stub method _renderCustomRange, the method _renderCustomRange should return new created field
            // return stub object to add to view.fieldRanges
            sinon.stub(view, '_renderCustomRange').callsFake(function(key) {
                var customType, customIndex;
                if(key.substring(0, 26) == 'custom_without_probability') {
                    customType = 'custom_without_probability';
                    customIndex = key.substring(27);
                } else if(key.substring(0, 6) == 'custom') {
                    customType = 'custom';
                    customIndex = key.substring(7);
                }
                if(customType) {
                    return {
                        name: key,
                        label: key,
                        customType: customType,
                        customIndex: customIndex,
                        $: function() {
                            var $el = $('<p/>');
                            $el.on = $.noop;
                            $el.trigger = $.noop;
                            $el.html = $.noop;
                            return $el;
                        }
                    };
                }
                return null;
            });

            ranges = {
                include: {
                    min: 85,
                    max: 100
                },
                upside: {
                    min: 70,
                    max: 84
                },
                exclude: {
                    min: 0,
                    max: 69
                }
            };

            customBucketOptions = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude'
            };

            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    label: 'include',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>']
                },
                upside: {
                    name: 'upside',
                    label: 'upside',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>']
                },
                exclude: {
                    name: 'exclude',
                    label: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>'],
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {}
                        }
                    }
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: customBucketOptions
            });
        });

        it("add custom field with probability", function() {
            _.each(['custom_1', 'custom_2'], function(name) {
                view.addCustomRange({
                    currentTarget: '<a class="btn addCustomRange" href="javascript:void(0)" data-type="custom" '
                        + 'data-category="show_custom_buckets">'
                });

                var bucketOptions = view.model.get('show_custom_buckets_options'),
                    bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketRanges[name].max).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].min).not.toBeUndefined(' -- bucketRanges[' + name + '].min');

                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');

                if(name == 'custom_1') {
                    expect(bucketRanges[name].max).toBe(69);
                    expect(bucketRanges[name].min).toBe(68);
                } else {
                    expect(bucketRanges[name].max).toBe(67);
                    expect(bucketRanges[name].min).toBe(66);
                }
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });

        it("add custom field without probability", function() {
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.addCustomRange({
                    currentTarget: '<a class="btn addCustomRange" href="javascript:void(0)" '
                        + 'data-type="custom_without_probability" data-category="show_custom_buckets">'
                });

                var bucketOptions = view.model.get('show_custom_buckets_options'),
                    bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketRanges[name].max).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].min).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');

                expect(bucketRanges[name].max).toBe(0);
                expect(bucketRanges[name].min).toBe(0);
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });

        it('adds accessibility for events', function() {
            sinon.stub(app.accessibility, 'run');
            var target = '<a class="btn addCustomRange" href="javascript:void(0);" '
                    + 'data-type="custom_without_probability" data-category="show_custom_buckets">';
            view.addCustomRange({currentTarget: target});
            expect(app.accessibility.run).toHaveBeenCalled();
        });
    });

    describe('removeCustomRange()', function() {
        var ranges,
            customBucketOptions;

        beforeEach(function() {
            sinon.stub(view, 'connectSliders').callsFake(function() {});
            sinon.stub(view, 'updateTitle').callsFake(function() {});

            ranges = {
                include: {
                    min: 85,
                    max: 100
                },
                upside: {
                    min: 70,
                    max: 84
                },
                custom_1: {
                    min: 68,
                    max: 69
                },
                custom_2: {
                    min: 66,
                    max: 67
                },
                exclude: {
                    min: 0,
                    max: 65
                },
                custom_without_probability_1: {
                    min: 0,
                    max: 0
                },
                custom_without_probability_2: {
                    min: 0,
                    max: 0
                }
            };

            customBucketOptions = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude',
                custom_1: 'custom_1',
                custom_2: 'custom_2',
                custom_without_probability_1: 'custom_without_probability_1',
                custom_without_probability_2: 'custom_without_probability_2'
            };

            // each item of view.fieldRanges must be View.field object in this case stub remove method of View.field
            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                upside: {
                    name: 'upside',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                custom_1: {
                    name: 'custom_1',
                    customType: 'custom',
                    customIndex: 1,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                custom_2: {
                    name: 'custom_2',
                    customType: 'custom',
                    customIndex: 2,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                exclude: {
                    name: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                custom_without_probability_1: {
                    name: 'custom_without_probability_1',
                    customType: 'custom_without_probability',
                    customIndex: 1,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                custom_without_probability_2: {
                    name: 'custom_without_probability_2',
                    customType: 'custom_without_probability',
                    customIndex: 2,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: customBucketOptions
            });
        });

        it("remove default custom field", function() {
            var result;
            _.each(['include', 'upside', 'exclude'], function(name) {
                result = view.removeCustomRange({
                    currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="'+name+'" '
                        + 'data-category="show_custom_buckets">'
                });
                expect(result).toBeFalsy();
            });
        });

        it("remove custom field with probability", function() {
            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_1" '
                    + 'data-category="show_custom_buckets">'
            });

            var bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_1']).toBeUndefined(' -- custom_1');
            expect(view.fieldRanges['show_custom_buckets']['custom_2']).not.toBeUndefined(' -- custom_2');
            expect(bucketRanges.custom_1).toBeUndefined(' -- bucketRanges.custom_1');
            expect(bucketRanges.custom_2).not.toBeUndefined(' -- bucketRanges.custom_2');

            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_2" '
                    + 'data-category="show_custom_buckets">'
            });

            bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_2']).toBeUndefined();
            expect(bucketRanges.custom_2).toBeUndefined(' -- custom_2');
        });

        it("remove custom field without probability", function() {
            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" '
                    + 'data-key="custom_without_probability_1" data-category="show_custom_buckets">'
            });

            var bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_1'])
                .toBeUndefined(' -- bucketRanges.custom_without_probability_1');

            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2'])
                .not.toBeUndefined(' -- bucketRanges.custom_without_probability_2');

            expect(bucketRanges.custom_without_probability_1).toBeUndefined();
            expect(bucketRanges.custom_without_probability_2).not.toBeUndefined();

            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" '
                    + 'data-key="custom_without_probability_2" data-category="show_custom_buckets">'
            });

            bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2']).toBeUndefined();
            expect(bucketRanges.custom_without_probability_2)
                .toBeUndefined(' -- bucketRanges.custom_without_probability_2');
        });
    });

    describe('updateCustomRangeLabel()', function() {
        var customBucketOptions,
            ranges,
            newName;

        beforeEach(function() {
            customBucketOptions = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude',
                custom_1: 'custom_1',
                custom_2: 'custom_2',
                custom_without_probability_1: 'custom_without_probability_1',
                custom_without_probability_2: 'custom_without_probability_2'
            };

            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    customType: 'custom_default',
                    customIndex: 0
                },
                upside: {
                    name: 'upside',
                    customType: 'custom_default',
                    customIndex: 0
                },
                custom_1: {
                    name: 'custom_1',
                    customType: 'custom',
                    customIndex: 1
                },
                custom_2: {
                    name: 'custom_2',
                    customType: 'custom',
                    customIndex: 2
                },
                exclude: {
                    name: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0
                },
                custom_without_probability_1: {
                    name: 'custom_without_probability_1',
                    customType: 'custom_without_probability',
                    customIndex: 1
                },
                custom_without_probability_2: {
                    name: 'custom_without_probability_2',
                    customType: 'custom_without_probability',
                    customIndex: 2
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: customBucketOptions
            });
        });

        afterEach(function() {

        });

        it("rename default custom field", function() {
            _.each(['include', 'upside', 'exclude'], function(name) {
                newName = name + '_CUSTOM';
                view.updateCustomRangeLabel({
                    target: '<input type="text" value="' + newName + '" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'
                });

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(newName);
            });
        });

        it("rename custom field with probability", function() {
            _.each(['custom_1', 'custom_2'], function(name) {
                newName = name + '_CUSTOM';
                view.updateCustomRangeLabel({
                    target: '<input type="text" value="' + newName + '" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'});

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(newName);
            });
        });

        it("rename custom field without probability", function() {
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                newName = name + '_CUSTOM_WITHOUT_PR';
                view.updateCustomRangeLabel({ target: '<input type="text" value="' + newName + '" '
                    + 'data-key="' + name + '" data-category="show_custom_buckets">'});

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(newName);
            });
        });
    });

    describe('updateCustomRangeIncludeInTotal()', function() {
        var ranges,
            bucketRanges;

        beforeEach(function() {
            sinon.stub(view, 'updateTitle').callsFake(function() {});
            view.includedCommitStages = [];

            ranges = {
                include: {
                    min: 85,
                    max: 100,
                    in_included_total: true
                },
                upside: {
                    min: 70,
                    max: 84,
                    in_included_total:false
                },
                custom_1: {
                    min: 68,
                    max: 69,
                    in_included_total:false
                },
                custom_2: {
                    min: 66,
                    max: 67,
                    in_included_total:false
                },
                exclude: {
                    min: 0,
                    max: 65,
                    in_included_total:false
                },
                custom_without_probability_1: {
                    min: 0,
                    max: 0,
                    in_included_total:false
                },
                custom_without_probability_2: {
                    min: 0,
                    max: 0,
                    in_included_total:false
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                commit_stages_included: ['include']
            });
        });

        it("test updateCustomRangeIncludeInTotal method", function() {
            _.each(['include', 'upside', 'custom_1', 'custom_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');

                expect(bucketRanges[name].in_included_total).toBe(true);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');

                expect(bucketRanges[name].in_included_total).toBe(false);
            });

            _.each(['exclude', 'custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');

                expect(bucketRanges[name].in_included_total).toBe(false);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="' + name + '" '
                        + 'data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total)
                    .not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });
    });
});
