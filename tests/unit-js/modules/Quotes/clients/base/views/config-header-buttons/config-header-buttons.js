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
describe('Quotes.View.ConfigHeaderButtons', function() {
    var app;
    var view;
    var quotesFieldsMeta;

    beforeEach(function() {
        app = SugarTest.app;
        quotesFieldsMeta = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        sinon.stub(app.metadata, 'getModule')
            .withArgs('Quotes', 'fields').returns(quotesFieldsMeta);

        view = SugarTest.createView('base', 'Quotes', 'config-header-buttons', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        quotesFieldsMeta = null;
    });

    describe('_getSaveConfigAttributes()', function() {
        var result;

        beforeEach(function() {
            view.model.set({
                footer_rows: [{
                    name: 'aaa',
                    syncedType: 'testSyncedType1',
                    type: 'testType2',
                    syncedCssClass: 'testSyncedCssClass1',
                    css_class: 'testCssClass2'
                }, {
                    name: 'bbb',
                    type: 'testType2',
                    css_class: 'testCssClass2'
                }, {
                    name: 'ccc',
                    default: '1'
                }, {
                    name: 'discount'
                }],
                footer_rows_related_fields: ['bbb'],
                worksheet_columns: [{
                    name: 'aaa'
                }],
                worksheet_columns_related_fields: ['bbb']
            });
        });

        afterEach(function() {
            result = null;
        });

        describe('with worksheet_columns', function() {
            beforeEach(function() {
                result = view._getSaveConfigAttributes();
            });

            it('should add line_num to worksheet_columns', function() {
                expect(result.worksheet_columns).toEqual([{
                    name: 'line_num',
                    label: null,
                    widthClass: 'cell-xsmall',
                    css_class: 'line_num text-center',
                    type: 'line-num',
                    readonly: true
                }, {
                    name: 'aaa'
                }]);
            });

            it('should add fields to worksheet_columns_related_fields', function() {
                expect(result.worksheet_columns_related_fields).toEqual([
                    'bbb',
                    'service_duration_value',
                    'service_duration_unit',
                    'catalog_service_duration_value',
                    'catalog_service_duration_unit',
                    'subtotal',
                    'description',
                    'currency_id',
                    'base_rate',
                    'account_id',
                    'quote_id',
                    'name',
                    'position',
                    'product_template_id',
                    'product_template_name'
                ]);
            });
        });

        describe('with footer_rows', function() {
            beforeEach(function() {
                result = view._getSaveConfigAttributes();
            });

            it('should set from synced values first', function() {
                expect(result.footer_rows[0]).toEqual({
                    name: 'aaa',
                    type: 'testSyncedType1',
                    css_class: 'testSyncedCssClass1'
                });
            });

            it('should set from type and css_class if synced values do not exist', function() {
                expect(result.footer_rows[1]).toEqual({
                    name: 'bbb',
                    type: 'testType2',
                    css_class: 'testCssClass2'
                });
            });

            it('should set default if it exists', function() {
                expect(result.footer_rows[2]).toEqual({
                    name: 'ccc',
                    default: '1'
                });
            });

            it('should set type to quote-footer-currency if it is not a rollup field', function() {
                expect(result.footer_rows[3]).toEqual({
                    name: 'discount',
                    type: 'quote-footer-currency',
                    default: '0.00',
                    css_class: 'quote-footer-currency'
                });
            });
        });
    });
});
