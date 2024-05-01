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
describe('FilterOperatorManager', function() {
    let app;
    let filterOperatorManager;

    beforeEach(function() {
        app = {
            events: {
                on: jasmine.createSpy('on')
            },
            plugins: {
                register: jasmine.createSpy('register')
            },
            filterOperators: {
                DefaultOperator: function(options) {
                    this.options = options;
                    this.getUpdatedInput = jasmine.createSpy('getUpdatedInput');
                }
            },
            lang: {
                get: jasmine.createSpy('get')
            }
        };

        app = SugarTest.app;
        SugarTest.loadPlugin('FilterOperatorManager');
        filterOperatorManager = app.plugins.plugins.view.FilterOperatorManager;
    });

    describe('updateFilterInput', function() {
        beforeEach(function() {
            filterOperatorManager._filterData = {
                qualifier_name: 'between'
            };
            filterOperatorManager._fieldType = 'text';
            filterOperatorManager.getOperatorType =
                jasmine.createSpy('getOperatorType').andReturn('BetweenOperator');
            filterOperatorManager.createOperatorController =
                jasmine.createSpy('createOperatorController').andReturn({
                getUpdatedInput: jasmine.createSpy('getUpdatedInput')
            });

            filterOperatorManager.updateFilterInput();
        });

        it('should set loading to true', function() {
            expect(filterOperatorManager._loading).toBe(true);
        });

        it('should reset input values', function() {
            expect(filterOperatorManager._inputValue).toBe(false);
            expect(filterOperatorManager._inputValue1).toBe(false);
            expect(filterOperatorManager._inputValue2).toBe(false);
            expect(filterOperatorManager._inputValue3).toBe(false);
        });

        it('should create operatorController with correct arguments', function() {
            expect(filterOperatorManager.createOperatorController)
                .toHaveBeenCalledWith('BetweenOperator', 'between', 'text');
        });

        it('should call getUpdatedInput on operatorController', function() {
            expect(filterOperatorManager.operatorController.getUpdatedInput).toHaveBeenCalled();
        });
    });

    describe('getOperatorType', function() {
        it('should return the correct operator type based on qualifierName and fieldType', function() {
            expect(filterOperatorManager.getOperatorType('between', 'text')).toBe('BetweenOperator');
            expect(filterOperatorManager.getOperatorType('between_dates', 'date')).toBe('DateOperator');
            expect(filterOperatorManager.getOperatorType('between_datetimes', 'datetime')).toBe('DateOperator');
            expect(filterOperatorManager.getOperatorType('_n_days', 'text')).toBe('DateOperator');
            expect(filterOperatorManager.getOperatorType('empty', 'text')).toBe('EmptyOperator');
            expect(filterOperatorManager.getOperatorType('date', 'date')).toBe('DateOperator');
            expect(filterOperatorManager.getOperatorType('id', 'id')).toBe('RelateOperator');
            expect(filterOperatorManager.getOperatorType('username', 'username')).toBe('UsernameOperator');
            expect(filterOperatorManager.getOperatorType('enum', 'enum')).toBe('EnumOperator');
            expect(filterOperatorManager.getOperatorType('bool', 'bool')).toBe('BoolOperator');
            expect(filterOperatorManager.getOperatorType('unknown', 'text')).toBe('DefaultOperator');
        });
    });
});
