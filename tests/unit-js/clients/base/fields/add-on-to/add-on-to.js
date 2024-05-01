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
describe('Base.Field.AddOnTo', function() {
    var field;
    var fieldDef;
    var fieldModel;
    var app;

    beforeEach(function() {
        app = SugarTest.app;

        fieldDef = {
            'name': 'add_on_to_name',
            'rname': 'name',
            'id_name': 'add_on_to_id',
            'vname': 'LBL_ADD_ON_TO',
            'type': 'add-on-to',
            'save': true,
            'link': 'pli_addons_link',
            'isnull': 'true',
            'table': 'purchased_line_items',
            'module': 'PurchasedLineItems',
            'source': 'non-db',
            'copyFromPurchasedLineItem': {
                'product_template_id': 'product_template_id',
                'product_template_name': 'product_template_name',
                'service_end_date': 'service_end_date'
            },
            'copyFromProductTemplate': {
                'template_test_field': 'rli_test_template_field'
            }
        };

        fieldModel = new Backbone.Model({
            id: 'test',
        });

        field = SugarTest.createField('base', 'add_on_to_name', 'add-on-to',
            'detail', fieldDef);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('getFilterOptions', function() {
        beforeEach(function() {
            app.drawer = app.drawer || {};
            app.drawer.open = app.drawer.open || $.noop;
            openStub = sinon.stub(app.drawer, 'open');

            field.model.fields = {
                account_id: {
                    name: 'account_id'
                },
                account_name: {
                    name: 'account_name',
                    id_name: 'account_id'
                }
            };

            field.model.set('account_id', '1234-5678');
            field.model.set('account_name', 'The related Account');
        });

        it('should return the proper add_on_to_name filter options', function() {
            var filterOptions = field.getFilterOptions();
            expect(filterOptions).toBeDefined();
            expect(filterOptions.initial_filter).toEqual('add_on_plis');
            expect(filterOptions.initial_filter_label).toEqual('LBL_PLI_ADDONS');
            expect(filterOptions.filter_populate).toEqual(
                {
                    account_id: ['1234-5678']
                }
            );
        });
    });

    describe('getSearchFields', function() {
        it('should include the PLI search fields specified in the field definition', function() {
            var result = field.getSearchFields();
            expect(result).toContain('product_template_id');
            expect(result).toContain('product_template_name');
            expect(result).toContain('service_end_date');
        });
    });

    describe('_updateAddOnToRelatedFields', function() {
        var templateBean;

        beforeEach(function() {
            // Stub the fetch to mock fetching related-related Product Template data
            templateBean = app.data.createBean('ProductTemplates', {id: '1234-5678'});
            sinon.stub(templateBean, 'fetch').callsFake(function(callbacks) {
                callbacks.success({
                    get: function(fieldName) {
                        return this[fieldName];
                    },
                    'template_test_field': 'valueFromTemplate'
                });
            });
            sinon.stub(app.data, 'createBean').returns(templateBean);
        });

        it('should not set related values from the PLI that are not listed in copyFromPurchasedLineItem', function() {
            field._updateAddOnToRelatedFields({
                'not_a_listed_field': 'thisShouldNotBeSet'
            });
            expect(field.model.get('not_a_listed_field')).toBe(undefined);
        });

        it('should set the related values from the PLI alone if the PLI does not have a Product Template', function() {
            field._updateAddOnToRelatedFields({
                'product_template_id': null,
                'product_template_name': null,
                'service_end_date': '2020-01-01'
            });
            expect(field.model.get('service_end_date')).toBe('2020-01-01');
        });

        it('should set the related values from both the PLI and the PLIs Product Template if it has one', function() {
            field._updateAddOnToRelatedFields({
                'product_template_id': '1234-5678',
                'product_template_name': 'fakeTemplateName',
                'service_end_date': '2020-01-01'
            });
            expect(field.model.get('product_template_id')).toBe('1234-5678');
            expect(field.model.get('product_template_name')).toBe('fakeTemplateName');
            expect(field.model.get('service_end_date')).toBe('2020-01-01');
            expect(field.model.get('rli_test_template_field')).toBe('valueFromTemplate');
        });
    });
});
