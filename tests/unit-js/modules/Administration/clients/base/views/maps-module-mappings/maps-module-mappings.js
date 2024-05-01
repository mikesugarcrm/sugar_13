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
describe('Administration.Views.MapsModuleMappingsView', function() {
    var app;
    var sandbox = sinon.createSandbox();
    var viewName = 'maps-module-mappings';
    var module = 'Administration';
    var testModelParams;
    var initOptions;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', viewName, module);
        app = SugarTest.app;

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: module,
            layout: viewName,
            model: new Backbone.Model(testModelParams)
        });

        context.prepare();
        context.parent = app.context.getContext();

        initOptions = {
            context: context,
            widgetModule: 'Accounts'
        };
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app = null;
    });

    describe('initialize()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);

            sandbox.spy(testView, '_beforeInit');

            testView.initialize(initOptions);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly call _beforeInit', function() {
            expect(testView._beforeInit.calledOnce).toEqual(true);
        });

        it('should properly set _mappingData property', function() {
            if (testView.meta) {
                expect(testView._mappingData).toEqual({
                    locality: {
                        label: 'LBL_ADDRESS_CITY',
                        id: 'locality'
                    },
                    countryRegion: {
                        label: 'LBL_ADDRESS_COUNTRY',
                        id: 'country-region'
                    },
                    addressLine: {
                        label: 'LBL_ADDRESS_STREET',
                        id: 'address-line'
                    },
                    postalCode: {
                        label: 'LBL_ADDRESS_POSTALCODE',
                        id: 'postal-code'
                    },
                    adminDistrict: {
                        label: 'LBL_ADDRESS_STATE',
                        id: 'admin-district'
                    },
                });
            }
        });
    });

    describe('mappingChanged()', function() {
        var testView;

        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView('base', 'Administration', viewName, {}, context, true, null, false);
            testView.initialize(initOptions);

            testView.context.safeRetrieveModulesData = function(module) {
                const _modulesData = this.model.get('maps_modulesData') || {};
                let modulesData = app.utils.deepCopy(_modulesData);

                if (_.isEmpty(modulesData)) {
                    modulesData[module] = {};
                }

                if (!_.has(modulesData, module)) {
                    modulesData[module] = {};
                }

                if (!_.has(modulesData[module], 'mappings')) {
                    modulesData[module].mappings = {};
                }

                if (!_.has(modulesData[module], 'settings')) {
                    modulesData[module].settings = {};
                }

                if (!_.has(modulesData[module], 'subpanelConfig')) {
                    modulesData[module].subpanelConfig = {};
                }

                return modulesData;
            }.bind(testView);
        });

        afterEach(function() {
            testView.dispose();
        });

        it('should properly store to model the changed mapping', function() {
            testView.mappingChanged({
                currentTarget: {
                    value: 'billing_address_city',
                    dataset: {
                        fieldname: 'country-region'
                    }
                }
            });

            var expectValue = testView.model.get('maps_modulesData').Accounts.mappings.countryRegion;

            expect(expectValue).toEqual('billing_address_city');
        });
    });
});
