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
describe('View.Views.Base.CjAsADashletView', function() {
    let app;
    let view;
    let model;
    let context;
    let layout;
    let initOptions;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean('Accounts');
        SugarTest.loadComponent('base', 'view', 'dri-customer-journey-dashlet');
        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        layout = SugarTest.createLayout(
            'base',
            '',
            'base',
            null,
            context
        );
        view = SugarTest.createView(
            'base',
            '',
            'dri-customer-journey-dashlet',
            null,
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();
        model = null;
        context = null;
        layout = null;
        view = null;
        app = null;
        initOptions = null;
    });

    describe('initialize', function() {
        it('should call the initialize function and initialze some properties', function() {
            sinon.stub(view, '_initProperties');
            view.initialize(initOptions);
            expect(view._initProperties).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('_initProperties', function() {
        it('should call the _initProperties function which initializes the properties that are checked', function() {
            view.chartData = 'focus';
            view.isFetching = true;
            view.selected = 'Accounts';
            view._initProperties();
            expect(view.chartData).not.toBe('focus');
            expect(view.isFetching).toBe(false);
            expect(view.selected).toBe(null);
        });
    });

    describe('bindDataChange', function() {
        it('should listen to model properties and binds functions with them', function() {
            sinon.stub(view, 'listenTo');
            view.bindDataChange();
            expect(view.listenTo).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('bindDataChange');
        });
    });

    describe('loadData', function() {
        it('should initialize some propertise to call app.api.buuildurl to make app.api.call to loadData', function() {
            sinon.stub(app.api, 'buildURL').returns('www.github.com');
            sinon.stub(app.api, 'call');
            view.loadData(initOptions);
            expect(view.isFetching).toBe(true);
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('setActiveCycle', function() {
        it('should call the setActiveCycle function to set incoming id after validations', function() {
            let id = '99';
            sinon.stub(view, 'loadData');
            view.setActiveCycle(id);
            expect(view.selected).toBe(id);
            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('loadChartDataSuccess', function() {
        it('should call the loadChartDataSuccess function to render chart on successfully loading data', function() {
            let data = {
                id: '5',
                stages: 'Multiple',
            } ;
            sinon.stub(view.layout, 'getComponent').returns(true);
            view.loadChartDataSuccess(data);
            expect(view.selected).toEqual('5');
            expect(view.layout.getComponent).toHaveBeenCalled();
            expect(view.chartData.attributes.rawChartData.values).toBe(data.stages);
        });
    });

    describe('loadChartDataComplete', function() {
        it('should call the loadChartDataComplete function on loading data from api', function() {
            let opts = {
                complete: function() {
                    return true;
                }
            };
            view.isFetching = true;
            view.loadChartDataComplete(opts);
            expect(view.isFetching).toBe(false);
        });
    });

    describe('_dispose', function() {
        it('should call the stopListening function properly', function() {
            sinon.stub(view, 'stopListening');
            view._dispose();
            expect(view.stopListening).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
