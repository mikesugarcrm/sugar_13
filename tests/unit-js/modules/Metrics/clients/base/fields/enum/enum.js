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
describe('Metrics.Fields.Enum', function() {
    var app;
    var field;
    var fieldName = 'test_enum';
    var model;
    var module = 'Metrics';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        SugarTest.loadComponent('base', 'field', 'enum');

        field = SugarTest.createField(
            'base',
            fieldName,
            'enum',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        field.items = {
            'New': 'New',
            'Duplicate': 'Duplicate',
        };
    });

    afterEach(function() {
        sinon.restore();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(field, 'populateOrderByValues');
        });

        it('should set the order-by values for order-by enum fields', function() {
            field.initialize({
                def: {
                    name: 'order_by_primary'
                }
            });
            expect(field.populateOrderByValues).toHaveBeenCalled();
        });

        it('should not affect non-order-by enum fields', function() {
            field.initialize({
                def: {
                    name: 'non-order-by-field'
                }
            });
            expect(field.populateOrderByValues).not.toHaveBeenCalled();
        });
    });

    describe('populateOrderByValues', function() {
        beforeEach(function() {
            model.attributes.tabContent = {
                sortFields: {
                    'field1': 'Field 1',
                    'field2': 'Field 2',
                    'field3': 'Field 3'
                }
            };
        });

        it('should populate the order-by enums with the correct fields list', function() {
            field.items = {};
            field.populateOrderByValues();
            expect(field.items).toEqual({
                '': '',
                'field1': 'Field 1',
                'field2': 'Field 2',
                'field3': 'Field 3'
            });
        });
    });
});
