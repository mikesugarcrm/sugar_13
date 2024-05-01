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
describe('View.Views.Base.MetricTabView', function() {
    let app;
    let view;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;

        layout = {
            layout: {
                layout: {
                    meta: {
                        metric_context: 'service_console',
                        metric_module: 'Cases'
                    }
                }
            },
            off: $.noop,
            getComponent: sinon.stub().returns({test: 'test'})
        };
        sinon.stub(app.user.lastState, 'get').returns({id: 'test'});
        view = SugarTest.createView('base', '', 'metric-tab',
            {'id': 'test'}, null, false, layout);
        sinon.stub(view, 'initialize').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        layout = null;
        view = null;
        app = null;
    });

    describe('setActiveMetric', function() {
        let metric;
        beforeEach(function() {
            view.context.metrics = [
                {id: 'test1'},
                {id: 'test2'}
            ];
            metric = {id: 'test1'};
            sinon.stub(view.model, 'set');
            sinon.stub(view, 'getActiveMetricKey');
            sinon.stub(app.user.lastState, 'set');
        });

        it('should set the user last state', function() {
            view.setActiveMetric(metric);

            expect(view.getActiveMetricKey).toHaveBeenCalled();
            expect(app.user.lastState.set).toHaveBeenCalled();
            expect(view.model.set).toHaveBeenCalledWith('active', 'test1');
        });
    });

    describe('checkAdminAccess', function() {
        using('different access levels', [
            {
                acls: {'test': {}},
                userType: 'admin',
                expected: true
            },
            {
                acls: {'test': {}},
                userType: 'test',
                expected: true
            },
            {
                acls: {'admin': {}},
                userType: 'test',
                expected: false
            },
            {
                acls: {'admin': {}},
                userType: 'admin',
                expected: true
            },
        ], function(values) {
            it('should return correct admin access', function() {
                sinon.stub(app.user, 'getAcls').returns({Metrics: values.acls});
                sinon.stub(app.user, 'get').returns(values.userType);

                if (values.expected) {
                    expect(view.checkAdminAccess()).toBeTruthy();
                } else {
                    expect(view.checkAdminAccess()).toBeFalsy();
                }
            });
        });
    });

    describe('deleteClicked', function() {
        beforeEach(function() {
            sinon.stub(app.view, 'createView').returns({warnDelete: sinon.stub()});
        });

        it('should not do anything if context metrics are not defined', function() {
            view.context = app.context.getContext();
            view.context.metrics = undefined;

            view.deleteClicked();
            expect(app.view.createView).not.toHaveBeenCalled();
        });

        it('should not call createView if metric attributes are not defined', function() {
            view.context = app.context.getContext();
            view.context.metrics = {test: 'test'};
            sinon.stub(app.data, 'createBean').returns({});
            sinon.stub(view, 'getMetricAttributes').returns({});

            view.deleteClicked();
            expect(view.getMetricAttributes).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(app.view.createView).not.toHaveBeenCalled();
        });

        it('should call createView if metric attributes are defined', function() {
            view.context = app.context.getContext();
            view.context.metrics = {test: 'test'};
            sinon.stub(app.data, 'createBean').returns({
                set: sinon.stub(),
                setSyncedAttributes: sinon.stub(),
            });
            sinon.stub(view, 'getMetricAttributes').returns({testAttr: 'testAttr'});

            view.deleteClicked();
            expect(view.getMetricAttributes).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(view.context.get('navigateBack')).toBeTruthy();
            expect(app.view.createView).toHaveBeenCalled();
            expect(app.view.createView().warnDelete).toHaveBeenCalled();
        });
    });

    describe('hideBtnClicked', function() {
        it('should trigger click:metric:hide event on the context', function() {
            view.context = app.context.getContext();
            sinon.stub(view.context, 'trigger');
            view.hideBtnClicked();
            expect(view.layout.getComponent).toHaveBeenCalledWith(view.name);
            expect(view.context.trigger).toHaveBeenCalledWith('click:metric:hide', {test: 'test'});
        });
    });
});
