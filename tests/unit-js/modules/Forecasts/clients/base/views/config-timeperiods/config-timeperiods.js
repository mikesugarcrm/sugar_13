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
describe('Forecasts.View.ConfigTimeperiods', function() {
    var app,
        context,
        options,
        meta,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var cfgModel = new Backbone.Model({
            is_setup: 1,
            timeperiod_start_date: '2014-01-01'
        });

        context.set({
            model: cfgModel,
            module: 'Forecasts'
        });

        meta = {
            label: 'testLabel',
            panels: [{
                fields: [{
                    name: 'timeperiod_start_date',
                    click_to_edit: undefined
                }]
            }]
        };

        sinon.stub(app.user, 'getPreference').callsFake(function(pref) {
            if (pref == 'datepref') {
                return 'MM/DD/YYYY';
            }
        });

        options = {
            context: context,
            meta: meta
        };

        // load the parent config-panel view
        SugarTest.createView('base', null, 'config-panel', meta, context);
        view = SugarTest.createView('base', 'Forecasts', 'config-timeperiods', meta, null, true);
    });

    afterEach(function() {
        app.user.getPreference.restore();
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set timeperiod_start_date click_to_edit = true', function() {
            view.initialize(options);
            var tpField = _.find(view.meta.panels[0].fields, function(field) {
                return field.name === 'timeperiod_start_date';
            });
            expect(tpField.click_to_edit).toBeTruthy();
        });

        it('should set this.tpStartDate', function() {
            view.initialize(options);
            expect(view.tpStartDate).toEqual(app.date('2014-01-01'));
        });
    });

    describe('_updateTitleValues()', function() {
        it('should set this.titleSelectedValues', function() {
            view.initialize(options);

            sinon.stub(view.tpStartDate, 'formatUser').callsFake(function() {
                return app.date(view.tpStartDate, 'MM/DD/YYYY').format('MM/DD/YYYY');
            });

            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('01/01/2014');
        });
    });

    describe('checkFiscalYearField()', function() {
        beforeEach(function() {
            sinon.stub(view, 'addFiscalYearField').callsFake(function() {});
            sinon.stub(view, 'removeFiscalYearField').callsFake(function() {});
        });

        describe('if tp start date is 01/01/yyyy', function() {
            it('should call removeFiscalYearField if this.fiscalYearField has rendered', function() {
                options.context.get('model').set('timeperiod_start_date', '2014-01-01');
                view.initialize(options);
                // setting to true instead of creating a field
                view.fiscalYearField = true;
                view.checkFiscalYearField();
                expect(view.addFiscalYearField).not.toHaveBeenCalled(' -- addFiscalYearField');
                expect(view.removeFiscalYearField).toHaveBeenCalled(' -- removeFiscalYearField');
            });

            it('should not call removeFiscalYearField if this.fiscalYearField is undefined', function() {
                options.context.get('model').set('timeperiod_start_date', '2014-01-01');
                view.initialize(options);
                view.checkFiscalYearField();
                expect(view.addFiscalYearField).not.toHaveBeenCalled(' -- addFiscalYearField');
                expect(view.removeFiscalYearField).not.toHaveBeenCalled(' -- removeFiscalYearField');
            });
        });

        it('if tp start date is not 01/01/yyyy, call addFiscalYearField', function() {
            options.context.get('model').set('timeperiod_start_date', '2014-02-02');
            view.initialize(options);
            view.checkFiscalYearField();
            expect(view.addFiscalYearField).toHaveBeenCalled(' -- addFiscalYearField');
            expect(view.removeFiscalYearField).not.toHaveBeenCalled(' -- removeFiscalYearField');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, 'checkFiscalYearField').callsFake(function() {});
            sinon.stub(view, 'updateTitle').callsFake(function() {});
        });

        it('if timeperiod_start_date changes on the model, update', function() {
            view.initialize(options);
            view.bindDataChange();

            sinon.stub(app, 'date').callsFake(function(date) {
                return {
                    formatUser: function() {
                        return date;
                    }
                };
            });

            view.model.set('timeperiod_start_date', '2014-02-02');
            expect(view.checkFiscalYearField).toHaveBeenCalled(' -- checkFiscalYearField');
            expect(view.updateTitle).toHaveBeenCalled(' -- updateTitle');
        });
    });

    describe('_setUpTimeperiodConfigField()', function() {
        beforeEach(function() {
            sinon.stub(view, '_setUpTimeperiodShowField').callsFake(function() {});
            sinon.stub(view, '_setUpTimeperiodIntervalBind').callsFake(function() {});
        });

        it('should call _setUpTimeperiodShowField() when field.name == timeperiod_shown_forward', function() {
            view._setUpTimeperiodConfigField({name: 'timeperiod_shown_forward'});
            expect(view._setUpTimeperiodShowField).toHaveBeenCalled();
        });

        it('should call _setUpTimeperiodShowField() when field.name == timeperiod_shown_backward', function() {
            view._setUpTimeperiodConfigField({name: 'timeperiod_shown_backward'});
            expect(view._setUpTimeperiodShowField).toHaveBeenCalled();
        });

        it('should call _setUpTimeperiodIntervalBind() when field.name == timeperiod_interval', function() {
            view._setUpTimeperiodConfigField({name: 'timeperiod_interval'});
            expect(view._setUpTimeperiodIntervalBind).toHaveBeenCalled();
        });
    });

    describe('_setUpTimeperiodIntervalBind()', function() {
        var intervalField;
        beforeEach(function() {
            view.model = new Backbone.Model({
                timeperiod_interval: '',
                timeperiod_leaf_interval: '',
                get: function() {
                    return {};
                },
                set: function() {}
            });
            intervalField = {
                name: 'timeperiod_interval',
                def: {
                    options: {}
                }
            };
            intervalField = view._setUpTimeperiodIntervalBind(intervalField);
            intervalField.model = view.model;
        });

        afterEach(function() {
            intervalField = null;
        });

        it('should add the event handlers to update the selections for the field', function() {
            expect(intervalField.events['change input']).toBeDefined();
            expect(intervalField.events['change input']).toEqual('_updateIntervals');
            expect(intervalField._updateIntervals).toBeDefined();
        });

        it('should check that the method to select the interval and default the leaf was called', function() {
            testIntervalMethodStub = sinon.stub(intervalField, '_updateIntervals').callsFake(function() {return '';});
            intervalField._updateIntervals({});
            expect(testIntervalMethodStub).toHaveBeenCalled();
        });

        it('should check that the method to select the interval and default the leaf set the model correctly',
            function() {
                spyOn($.fn, 'val').andReturn('Annual');
                intervalField._updateIntervals({target: 'timeperiod_interval'}, {selected: 'Annual'});
                expect(view.model.get('timeperiod_interval')).toEqual('Annual');
                expect(view.model.get('timeperiod_leaf_interval')).toEqual('Quarter');
            }
        );
    });
});
