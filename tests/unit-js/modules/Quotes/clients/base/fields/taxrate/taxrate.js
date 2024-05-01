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
describe('Quotes.Base.Fields.Taxrate', function() {
    var field;
    var fieldDef;

    beforeEach(function() {
        fieldDef = {
            name: 'taxrate_name',
            type: 'taxrate',
            initial_filter: 'active_taxrates',
            filter_populate: {
                module: ['TaxRates']
            },
            populate_list: {
                id: 'taxrate_id',
                value: 'taxrate_value'
            }
        };

        field = SugarTest.createField('base', 'taxrate_name', 'taxrate',
            'detail', fieldDef, 'Quotes', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('bindDataChange()', function() {
        it('should be quote-data-actiondropdown', function() {
            sinon.stub(field, '_super').callsFake(function() {});
            sinon.stub(field.model, 'on').callsFake(function() {});
            field.bindDataChange();

            expect(field.model.on).toHaveBeenCalledWith('change:taxrate_value');
        });
    });

    describe('_onTaxRateChange()', function() {
        it('should set tax based on the taxrate value', function() {
            field.model.set('taxable_subtotal', '100');
            field._onTaxRateChange({}, '8.25');

            expect(field.model.get('tax')).toBe('8.25');
        });
    });

    describe('_onSelect2Change()', function() {
        var select2Plugin;
        var event;
        var taxRateModel;
        var taxRateCollection;

        beforeEach(function() {
            taxRateModel = new Backbone.Model({
                id: 'taxId1',
                name: 'taxName1',
                value: 'taxValue1'
            });
            taxRateModel.id = 'taxId1';

            taxRateCollection = new Backbone.Collection();
            taxRateCollection.add(taxRateModel);

            event = {
                val: 'taxId1'
            };

            select2Plugin = {
                context: taxRateCollection,
                selection: {
                    find: function() {
                        return {
                            text: function() {
                                return 'taxName1';
                            }
                        };
                    }
                }
            };

            sinon.stub($.fn, 'data').callsFake(function() {
                return select2Plugin;
            });
            sinon.stub(field, 'setValue').callsFake(function() {});

            field._onSelect2Change(event);
        });

        it('should call setValue with id, name, and value attributes', function() {
            expect(field.setValue).toHaveBeenCalledWith({
                id: 'taxId1',
                name: 'taxName1',
                value: 'taxValue1'
            });
        });
    });

    describe('setValue()', function() {
        var valueObj;

        beforeEach(function() {
            valueObj = {
                id: 'taxId1',
                name: 'taxName1',
                value: 'taxValue1'
            };

            field.updateRelatedFields = function() {};
            field.setValue(valueObj);
        });

        it('should set taxrate_name, taxrate_id, taxrate_value on model', function() {
            expect(field.model.get('taxrate_id')).toBe('taxId1');
            expect(field.model.get('taxrate_name')).toBe('taxName1');
            expect(field.model.get('taxrate_value')).toBe('taxValue1');
        });
    });
});
