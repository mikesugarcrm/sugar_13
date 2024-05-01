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
describe('includes.javascript.pmse.ui.updater_field', function() {
    var control;

    describe('init', function() {
        it('should persist the actionType attribute in the local variable', function() {
            var params = {
                actionType: 'changeField'
            };
            control = new UpdaterField(params);
            expect(control.actionType).toEqual(params.actionType);
        });
    });

    describe('_getPanelTypeFilter', function() {
        var ecParams = {
            parent: control,
            className: 'updateritem-panel',
            decimalSeparator: '.',
            numberGroupingSeparator: ',',
            dateFormat: 'YYYY/MM/DD',
            timeFormat: 'H:i',
        };

        it('should set the right typeFilter for date type', function() {
            var fieldType = 'Date';
            var dateParams = {
                fieldType: fieldType
            };
            var dateField = new DateUpdaterItem(dateParams);
            dateField.setFieldType(fieldType);
            dateField._datePanel = new ExpressionControl(ecParams);
            var panelType = control._getPanelTypeFilter(control, dateField, fieldType, {});

            expect(panelType).toEqual('Date');
        });

        it('should set the right typeFilter for datetime type', function() {
            var fieldType = 'Datetime';
            var datetimeParams = {
                fieldType: fieldType
            };
            var datetimeField = new DateUpdaterItem(datetimeParams);
            datetimeField.setFieldType(fieldType);
            datetimeField._datePanel = new ExpressionControl(ecParams);
            var panelType = control._getPanelTypeFilter(control, datetimeField, fieldType, {});

            expect(panelType).toEqual(['Date', 'Datetime']);
        });
    });

    describe('DateUpdaterItem', function() {
        describe('init', function() {
            var dateUpdaterItemMock;
            it('should correctly set the flags to include business center variable options if true', function() {
                dateUpdaterItemMock = new DateUpdaterItem({
                    options: {
                        businessHours: {
                            show: true,
                            targetModuleBC: true,
                            selectedModuleBC: 'Cases'
                        }
                    }
                });
                expect(dateUpdaterItemMock._businessHours.targetModuleBC).toBe(true);
                expect(dateUpdaterItemMock._businessHours.selectedModuleBC).toBe('Cases');
            });
            it('should correctly set the flags to not include business center variable options if false', function() {
                dateUpdaterItemMock = new DateUpdaterItem({
                    options: {
                        businessHours: {
                            show: true,
                            targetModuleBC: false,
                            selectedModuleBC: ''
                        }
                    }
                });
                expect(dateUpdaterItemMock._businessHours.targetModuleBC).toBe(false);
                expect(dateUpdaterItemMock._businessHours.selectedModuleBC).toBe('');
            });
        });
    });
});
