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
describe('Forecasts.Base.Views.ListHeaderpane', function() {
    let view;
    let layout;
    let context;
    let moduleName = 'Forecasts';
    let sbox = sinon.createSandbox();

    beforeEach(function() {
        let viewMeta = {
            datapoints: [
                {
                    name: 'quota',
                    label: 'LBL_QUOTA',
                    type: 'quotapoint'
                },
                {
                    name: 'worst_case',
                    label: 'LBL_WORST',
                    type: 'datapoint'
                }
            ]
        };

        sinon.stub(SugarTest.app.metadata, 'getModule').withArgs('Opportunities', 'config').returns({
            opps_view_by: 'Opportunities'
        });

        context = SugarTest.app.context.getContext();
        layout = {
            context: context,
            on: function() {},
            off: function() {}
        };
        view = SugarTest.createView('base', moduleName, 'list-headerpane', null, null, true, layout, true);
        view.saveDraftBtnField = {
            hide: _.noop,
            show: _.noop,
        };
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
        sbox.restore();
    });

    describe('when resetSelection is called', function() {
        beforeEach(function() {
            view.fields = [{
                name: 'selectedTimePeriod',
                render: function() {
                },
                dispose: function() {
                }
            }];
            sbox.spy(view.fields[0], 'render');
            sbox.stub(view.tpModel, 'set').callsFake(function() {
            });
            sbox.stub(view, 'dispose').callsFake(function() {
            });

            view.resetSelection();
        });

        it('should have called render', function() {
            expect(view.fields[0].render).toHaveBeenCalled();
        });

        it('should have called set on tpModel', function() {
            expect(view.tpModel.set).toHaveBeenCalled();
        });
    });

    describe('tpModel is changed', function() {
        let tpMapValues = {
            start: '2014-01-01',
            end: '2014-03-31'
        };
        beforeEach(function() {
            sbox.stub(view.context, 'trigger').callsFake(function(event, model, object) {
            });
            sbox.stub(view, 'getField').callsFake(function() {
                return {
                    tpTooltipMap: {
                        'test_1': tpMapValues
                    }
                };
            });
        });

        it('will trigger event with model and object', function() {
            let m = new Backbone.Model({selectedTimePeriod: 'test_1'});
            view.tpModel.trigger('change', m);

            expect(view.context.trigger).toHaveBeenCalled();
            expect(view.context.trigger).toHaveBeenCalledWith('forecasts:timeperiod:changed', m, tpMapValues);
        });
    });
});
