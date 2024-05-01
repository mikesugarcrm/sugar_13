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
describe('PurchasedLineItems.Base.Plugins.PurchaseAndServiceChangeHandler', function() {
    var app;
    var plugin;
    var moduleName = 'PurchasedLineItems';
    var model;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadFile(
            '../modules/PurchasedLineItems/clients/base/plugins',
            'PurchaseAndServiceChangeHandler',
            'js',
            function(data) {
                app.events.off('app:init');
                eval(data);
                app.events.trigger('app:init');
            }
        );
        plugin = app.plugins.plugins.view.PurchaseAndServiceChangeHandler;
        plugin.model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lorem ipsum dolor sit amet'
        });

        plugin._super = sinon.stub();
    });

    afterEach(function() {
        sinon.restore();
        plugin = null;
        model = null;
    });

    describe('bindDataChange()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.stub();
            plugin.model = {
                on: stub,
                off: stub
            };
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        it('should call _super bindDataChange method', function() {
            plugin.bindDataChange();

            expect(plugin._super).toHaveBeenCalledWith('bindDataChange');
        });

        it('should call bind handler for service and purchase_name change', function() {
            plugin.bindDataChange();

            expect(plugin.model.on).toHaveBeenCalledWith('change:service');
            expect(plugin.model.on).toHaveBeenCalledWith('change:purchase_name');
        });

    });

    describe('updateServiceDuration()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.stub();
            plugin.model = {
                set: stub,
                off: stub
            };
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        it(
            'when service is true and service_duration_unit is not set, should set service_duration_unit with year',
            function() {
                plugin.model.get = function(property) {
                    return property === 'service' ? true : '';
                };
                plugin._updateServiceDuration();

                expect(plugin.model.set).toHaveBeenCalledWith('service_duration_unit', 'year');
            }
        );

        it('when service is true and service_duration_unit is set, should not set service_duration_unit', function() {
            plugin.model.get = function(property) {
                return property === 'service_duration_unit' ? 'month' : true;
            };
            plugin._updateServiceDuration();

            expect(plugin.model.set).not.toHaveBeenCalled();
        });

        it(
            'set service duration to 1 and value to day when service is false',
            function() {
                plugin.model.get = function() {
                    return false;
                };
                plugin.handleServiceChange();

                expect(plugin.model.set).toHaveBeenCalledWith({
                    'service_duration_unit': 'day',
                    'service_duration_value': 1
                });
            }
        );
    });

    describe('updateStartEndDate()', function() {
        var stub;
        var calculateStub;
        var field;
        beforeEach(function() {
            stub = sinon.stub();
            calculateStub = sinon.stub();
            field = {
                calculateEndDate: calculateStub
            };
            plugin.model = {
                set: stub,
                off: stub
            };
            plugin.getField = function() {
                return field;
            };
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        describe('when model is service', function() {
            it('should calculate end date', function() {
                plugin.model.get = function(property) {
                    return property === 'service';
                };
                plugin._updateStartEndDate();
                expect(field.calculateEndDate).toHaveBeenCalled();
                expect(plugin.model.set).not.toHaveBeenCalled();
            });
        });

        describe('when model is not service', function() {
            it('should not calculate, and should set start and end dates', function() {
                plugin.model.get = function(property) {
                    return property === 'date_closed' ? '2020-01-01' : false;
                };
                plugin._updateStartEndDate();
                expect(field.calculateEndDate).not.toHaveBeenCalled();
                expect(plugin.model.set).toHaveBeenCalledWith({
                    'service_start_date': '2020-01-01',
                    'service_end_date': '2020-01-01'
                });
            });
        });
    });

    describe('handlePurchaseChange()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.stub();
            plugin.model = {
                set: stub,
                off: stub
            };

            sinon.stub(plugin, 'setProductAutoPopulateFields');
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        describe('when product template id is defined', function() {
            it('should call setProductAutoPopulateFields', function() {
                plugin.model.get = function() {
                    return {
                        product_template_id: 'test123'
                    };
                };
                plugin.model.fields = {
                    product_template_name: {
                        populate_list: {test: 'testList'}
                    }
                };
                plugin.handlePurchaseChange();

                expect(plugin.setProductAutoPopulateFields).toHaveBeenCalledWith('test123', {test: 'testList'});
            });
        });

        describe('when product template id is empty', function() {
            it('should not call setProductAutoPopulateFields', function() {
                plugin.model.get = function() {
                    return {
                        product_template_id: ''
                    };
                };
                plugin.handlePurchaseChange();

                expect(plugin.setProductAutoPopulateFields).not.toHaveBeenCalled();
            });
        });
    });

    describe('setProductAutoPopulateFields', function() {
        var stub;
        var populateList;
        var prodId;
        beforeEach(function() {
            populateList = {test: 'testList'};

            stub = sinon.stub();
            plugin.model = {
                set: stub,
                off: stub
            };
            sinon.stub(app.api, 'call');
        });

        afterEach(function() {
            stub = null;
            populateList = null;
            prodId = null;
            plugin.model = null;
        });

        it('should make an api call with ProductTemplates/prodTemplateId', function() {
            plugin.model.get = function() {
                return {
                    product_template_id: 'test123'
                };
            };
            prodId = plugin.model.get().product_template_id;
            plugin.setProductAutoPopulateFields(prodId, populateList);

            expect(app.api.call).toHaveBeenCalledWith();
        });

        it('should not make an api call when prodTemplateId is empty', function() {
            plugin.model.get = function() {
                return {
                    product_template_id: ''
                };
            };
            prodId = plugin.model.get().product_template_id;
            plugin.setProductAutoPopulateFields(prodId, populateList);

            expect(app.api.call).not.toHaveBeenCalledWith();
        });

    });
});
