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
describe('PurchasedLineItems.Base.Fields.ServiceEnddateField', function() {
    var app;
    var field;
    var fieldDef;
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        model = new Backbone.Model({
            id: 'test'
        });
        fieldDef = {
            name: 'testField',
            type: 'service-enddate',
        };
        field = SugarTest.createField('base', 'service_end_date', 'service-enddate',
            'detail', fieldDef, 'PurchasedLineItems', model, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
        model = null;
    });

    describe('calculateEndDate', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.stub();
            field._super = stub;
            model.set = stub;
        });
        it('should set end date to service start date when the model is not service', function() {
            sinon.stub(model, 'get').callsFake(function(property) {
                return property === 'service_start_date' ? '2020-01-01' : false;
            });
            field.calculateEndDate();
            expect(field.model.set).toHaveBeenCalledWith(field.name, '2020-01-01');
        });
        it('should call super when the model is service', function() {
            sinon.stub(model, 'get').callsFake(function(property) {
                return property === 'service';
            });
            field.calculateEndDate();
            expect(field._super).toHaveBeenCalledWith('calculateEndDate');
        });
    });
});
