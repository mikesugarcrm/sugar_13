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
describe('includes.javascript.pmse.ui.expression_builder2', function() {
    describe('init', function() {
        it('should persist the name attribute in the local variable', function() {
            var params = {
                name: 'evn_criteria'
            };
            var control = new ExpressionControl(params);
            expect(control._name).toEqual(params.name);
        });
    });

    describe('_createTimespanPanel', function() {
        var params;
        var control;
        var ecParams;
        var businessHoursOption;
        var datetimeField;
        var fieldType;
        var datetimeParams;
        var constantPanelCfg;
        var timespanPanel;
        var oldAttributes;

        beforeEach(function() {
            params = {
                actionType: 'changeField'
            };
            control = new UpdaterField(params);
            ecParams = {
                parent: control,
                className: 'updateritem-panel',
                decimalSeparator: '.',
                numberGroupingSeparator: ',',
                dateFormat: 'YYYY/MM/DD',
                timeFormat: 'H:i',
            };
            businessHoursOption = {
                label: translate('LBL_PMSE_EXPCONTROL_CONSTANTS_TIMESPAN_BUSINESS_HOURS'),
                value: 'bh',
                id: 'bhOption'
            };
            fieldType = 'Datetime';
            datetimeParams = {
                fieldType: 'Datetime'
            };
            datetimeField = new DateUpdaterItem(datetimeParams);
            datetimeField.setFieldType(fieldType);
            datetimeField._datePanel = new ExpressionControl(ecParams);
            datetimeField._datePanel._createMainPanel();
            constantPanelCfg = {
                datetime: true,
                timespan: true,
                businessHours: {
                    show: true,
                    targetModuleBC: false,
                    selectedModuleBC: ''
                }
            };
            datetimeField._datePanel._constantPanels.timespan = null;

            // Save the user attribute data to restore it later, since these tests change Business Centers ACL data
            oldAttributes = App.user.attributes;
            App.user.attributes = {};
        });

        afterEach(function() {
            // Restore the user attributes to the original value
            App.user.attributes = oldAttributes;
            sinon.restore();
        });

        it('should not show a business hours option if the user does not have access to BusinessCenters', function() {
            var unitsOptions;

            // Mock the user not having access to the BusinessCenters module
            App.user.attributes.acl = {
                BusinessCenters: {
                    fields: [],
                    access: 'no',
                    hash: 'mockHash'
                }
            };

            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Check the timespan panel's units dropdown to make sure the business hours option isn't in there
            unitsOptions = timespanPanel._items.get(1)._options.asArray();
            for (var i = 0; i < unitsOptions.length; i++) {
                expect(unitsOptions[i].value).not.toEqual('bh');
            }
        });

        it('should show the business hours option if the user does have access to BusinessCenters', function() {
            var unitsOptions;
            var businessHoursShown = false;

            // In this test suite, the user's ACLs don't block access to BusinessCenters by default, so we don't need
            // to mock the ACL data for this test

            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Check the timespan panel's units dropdown to make sure the business hours option is in there
            unitsOptions = timespanPanel._items.get(1)._options.asArray();
            for (var i = 0; i < unitsOptions.length; i++) {
                businessHoursShown = businessHoursShown || unitsOptions[i].value === 'bh';
            }
            expect(businessHoursShown).toBe(true);
        });

        it('should include the option for the target module BC if targetModuleBC flag is true', function() {
            constantPanelCfg.businessHours.targetModuleBC = true;
            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Mock the user changing the value of the time units to "business hours"
            timespanPanel._items.get(1).setValue('bh');
            timespanPanel._items.get(1).onChange(timespanPanel._items.get(1), 'bh', 'y');

            // The business center dropdown should have one option with the value 'target_module_bc'
            expect(timespanPanel._items.get(2)._options.get(0)).not.toEqual(null);
            expect(timespanPanel._items.get(2)._options.get(0).value).toEqual('target_module_bc');

            expect(timespanPanel._items.get(2)._options.get(1)).toEqual(null);
        });

        it('should include the option for the selected module BC if selectedModuleBC flag is true', function() {
            constantPanelCfg.businessHours.selectedModuleBC = 'Cases';
            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Mock the user changing the value of the time units from "years" to "business hours"
            timespanPanel._items.get(1).setValue('bh');
            timespanPanel._items.get(1).onChange(timespanPanel._items.get(1), 'bh', 'y');

            // The business center dropdown should have one option with the value 'filter_module_bc'
            expect(timespanPanel._items.get(2)._options.get(0)).not.toEqual(null);
            expect(timespanPanel._items.get(2)._options.get(0).value).toEqual('filter_module_bc');

            expect(timespanPanel._items.get(2)._options.get(1)).toEqual(null);
        });

        it('should include both business center variable options if both BC flags are true', function() {
            constantPanelCfg.businessHours.targetModuleBC = true;
            constantPanelCfg.businessHours.selectedModuleBC = 'Cases';
            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Mock the user changing the value of the time units from "years" to "business hours"
            timespanPanel._items.get(1).setValue('bh');
            timespanPanel._items.get(1).onChange(timespanPanel._items.get(1), 'bh', 'y');

            // The business center dropdown should have two variable options with the values "target_module_bc"
            // and "filter_module_bc"
            expect(timespanPanel._items.get(2)._options.get(0)).not.toEqual(null);
            expect(timespanPanel._items.get(2)._options.get(0).value).toEqual('target_module_bc');
            expect(timespanPanel._items.get(2)._options.get(1)).not.toEqual(null);
            expect(timespanPanel._items.get(2)._options.get(1).value).toEqual('filter_module_bc');
        });

        it('should not include any business center variable options if both BC flags are false', function() {
            constantPanelCfg.businessHours.targetModuleBC = false;
            constantPanelCfg.businessHours.selectedModuleBC = '';
            datetimeField._datePanel.setConstantPanel(constantPanelCfg);
            timespanPanel = datetimeField._datePanel._constantPanels.timespan;

            // Mock the user changing the value of the time units to "business hours"
            timespanPanel._items.get(1).setValue('bh');
            timespanPanel._items.get(1).onChange(timespanPanel._items.get(1), 'bh', 'y');

            // The business center dropdown should not have any options
            expect(timespanPanel._items.get(2)._options.get(0)).toEqual(null);
        });
    });

    describe('_createCurrencyPanel', function() {
        var params;
        var control;
        var ecParams;
        var field;
        var datetimeParams;
        var constantPanelCfg;
        var currencyPanel;

        beforeEach(function() {
            params = {
                actionType: 'changeField'
            };
            control = new UpdaterField(params);
            ecParams = {
                parent: control,
                className: 'updateritem-panel',
                decimalSeparator: '.',
                numberGroupingSeparator: ',',
                dateFormat: 'YYYY/MM/DD',
                timeFormat: 'H:i',
                currencies: [{id: '-99', iso: 'USD', name: 'US Dollars', rate: 1, preferred: true, symbol: '$'},
                    {id: 'cc7e8e3c-e7a7-11ea-a2d9-acde48001122',
                        iso: 'EUR',
                        name: 'Euro',
                        rate: 0.9,
                        preferred: false,
                        symbol: '€'}]
            };
            field = new DropdownUpdaterItem(datetimeParams);
            field.panel = new ExpressionControl(ecParams);
            field.panel._createMainPanel();
            constantPanelCfg = {
                currency: true,
            };
        });

        it('should include currencies in dropdown menu', function() {
            field.panel.setConstantPanel(constantPanelCfg);
            currencyPanel = field.panel._constantPanels.currency;
            expect(currencyPanel._items.get(0)._options.get(0).iso).toEqual('USD');
            expect(currencyPanel._items.get(0)._options.get(1).iso).toEqual('EUR');
        });

    });
});
