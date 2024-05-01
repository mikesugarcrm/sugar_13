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
describe('Base.Layout.OmnichannelDashboard', function() {
    var dashboard;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard');
        SugarTest.loadComponent('base', 'layout', 'omnichannel-dashboard-config');
        dashboard = SugarTest.createLayout('base', 'layout',
            'omnichannel-dashboard-config', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        delete dashboard._components;
        dashboard.dispose();
    });

    describe('_getDashboardComponents', function() {
        it('should append dashboard-fab to components list', function() {
            var initialComponents = [{
                view: 'fake'
            }];
            var expectedComponents = [{
                view: 'fake'
            }, {
                view: 'dashboard-fab',
                loadModule: 'Dashboards'
            }];
            sinon.stub(dashboard, '_super').returns(initialComponents);
            var components = dashboard._getDashboardComponents();
            expect(components).toEqual(expectedComponents);
        });
    });

    describe('_setDummyModels', function() {
        it('should add empty models to each tab in the moduleTabIndex', function() {
            dashboard.moduleTabIndex = {
                'Contacts': 1,
                'Cases': 2,
                'Accounts': 3
            };
            var createSpy = sinon.spy(app.data, 'createBean');
            var setModelSpy = sinon.spy(dashboard, 'setModel');
            dashboard._setDummyModels();
            _.each(dashboard.moduleTabIndex, function(tabIndex, module) {
                expect(createSpy).toHaveBeenCalledWith(module);
                expect(setModelSpy).toHaveBeenCalledWith(tabIndex);
                var model = dashboard.tabModels[tabIndex];
                expect(model.dataFetched).toEqual(true);
            });
        });
    });

    describe('restoreTabToDefault', function() {
        it('should call the appropriate api to restore tab to default on confirmation', function() {
            alertMock = sinon.stub(app.alert, 'show');
            alertMock.yieldsTo('onConfirm');

            var id = '123';
            var tabIndex = 0;
            var params = {
                dashboard: 'omnichannel',
                tab_index: tabIndex
            };
            var url = app.api.buildURL('Dashboards', 'restore-tab-metadata', {id: id}, params);

            sinon.stub(dashboard, '_getOmniDashboardBeanId').returns(id);
            sinon.stub(app.api, 'call');

            dashboard.restoreTabToDefault(tabIndex);
            expect(app.alert.show).toHaveBeenCalledWith('restore_default_confirmation');

            expect(app.api.call).toHaveBeenCalledWith(
                'update',
                url,
                null
            );
        });
    });

    describe('_getOmniDashboardBeanId', function() {
        using('different components',[
            {
                components: [
                    {
                        type: 'not-dashboard',
                        model: new Backbone.Model({
                            view_name: 'record'
                        })
                    }
                ],
                expected: ''
            },
            {
                components: [
                    {
                        type: 'dashboard',
                        model: new Backbone.Model({
                            view_name: 'omnichannel',
                            id: '123'
                        })
                    }
                ],
                expected: '123'
            }
        ], function(value) {
            it('should get the bean id of the omnichannel dashboard component', function() {
                dashboard._components = value.components;

                var actual = dashboard._getOmniDashboardBeanId();

                expect(actual).toEqual(value.expected);
            });
        });
    });
});
