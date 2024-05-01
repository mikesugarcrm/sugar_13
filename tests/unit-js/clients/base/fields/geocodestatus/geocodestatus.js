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
describe('Base.Field.Geocodestatus', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'test_c', 'geocodestatus', 'record', {}, 'Accounts');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        Handlebars.templates = {};
    });

    describe('_beforeInit', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
        });

        it('should properly decode the options', function() {
            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';

            field._beforeInit({
                status: 'LBL_MAPS_GEOCODED',
            });

            expect(field._status).toEqual('LBL_MAPS_GEOCODED');
        });

        it('should properly set the status if no options are given', function() {
            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';

            field._beforeInit();

            expect(field._status).toEqual('LBL_MAPS_NOT_GEOCODED');
        });
    });

    describe('saveStatus', function() {
        beforeEach(function() {
            field.model = new Backbone.Model();
            sinon.spy(field.model, 'set');
            sinon.stub(field.model, 'save');
        });

        it('should set a valid geocode status on the model', function() {
            field._status = 'LBL_MAPS_NOT_GEOCODED';
            expect(field.model.get('test_c')).toEqual(undefined);
            field.saveStatus();
            expect(field.model.get('test_c')).toEqual('NOT_GEOCODED');
        });

        it('should fall back to the failed geocode status on the model for invalid status', function() {
            field._status = 'LBL_NOT_A_REAL_STATUS';
            expect(field.model.get('test_c')).toEqual(undefined);
            field.saveStatus();
            expect(field.model.get('test_c')).toEqual('FAILED');
        });

        it('should save the model after setting the geocode status', function() {
            field.saveStatus();
            expect(field.model.set).toHaveBeenCalledBefore(field.model.save);
        });
    });
});
