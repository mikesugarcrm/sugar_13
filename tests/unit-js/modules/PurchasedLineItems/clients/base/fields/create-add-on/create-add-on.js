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
describe('create-add-on field', function() {
    var app;
    var field;
    var moduleName = 'PurchasedLineItems';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'create-add-on', moduleName);
        field = SugarTest.createField('base', 'create_add_on_button', 'create-add-on', 'edit', {
            'type': 'create-add-on',
            'acl_action': 'view'
        }, moduleName, null, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        field = null;
        sinon.restore();
    });

    describe('_getAddOnRelatedFieldValues', function() {
        var templateBean;
        var pliModel;
        var callback;

        beforeEach(function() {
            pliModel = app.data.createBean('PurchasedLineItems');
            pliModel.set('product_template_id', '1111');
            pliModel.set('product_template_name', 'fakeTemplateName');
            pliModel.set('service_end_date', '2020-01-01');

            // Stub the fetch to mock fetching related-related Product Template data
            templateBean = app.data.createBean('ProductTemplates', {id: '8765-4321'});
            sinon.stub(templateBean, 'fetch').callsFake(function(callbacks) {
                callbacks.success({
                    get: function(fieldName) {
                        return this[fieldName];
                    },
                    'category_name': 'fakeCategory',
                    'weight': 1234
                });
            });
            sinon.stub(app.data, 'createBean').returns(templateBean);
            sinon.stub(app.metadata, 'getModule').returns({
                'add_on_to_name': {
                    'copyFromPurchasedLineItem': {
                        'product_template_id': 'product_template_id',
                        'product_template_name': 'product_template_name',
                        'service_end_date': 'service_end_date'
                    },
                    'copyFromProductTemplate': {
                        'category_name': 'category_name',
                        'weight': 'weight'
                    }
                }
            });
        });

        it('should get related fields from both the PLI and the Product Template', function() {
            callback = function(rliData) {
                expect(rliData.product_template_id).toEqual('1111');
                expect(rliData.product_template_name).toEqual('fakeTemplateName');
                expect(rliData.service_end_date).toEqual('2020-01-01');
                expect(rliData.category_name).toEqual('fakeCategory');
                expect(rliData.weight).toEqual(1234);
            };
            field._getAddOnRelatedFieldValues(pliModel, {}, callback);
        });
    });

});
