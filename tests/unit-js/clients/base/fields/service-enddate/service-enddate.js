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
describe('Base.Field.ServiceEnddate', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'service-enddate', 'service-enddate', 'view');
        sinon.stub(field, 'unformat').callsFake(function(date) {
            return app.date(date).format('YYYY-MM-DD');
        });
    });

    afterEach(function() {
        field.dispose();
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
    });

    it('should set up the dependency field names', function() {
        field.setFieldDependencyNames({
            def: {
                startDateFieldName: 'service_start_date'
            }
        });

        expect(field.startDateFieldName).toEqual('service_start_date');
        expect(field.durationUnitFieldName).toEqual('service_duration_unit');
        expect(field.durationValueFieldName).toEqual('service_duration_value');
    });

    it('should not allow the calculation of an end date', function() {
        sinon.spy(field, 'getMethodNames');
        sinon.spy(field, 'canCalculateEndDate');
        field.calculateEndDate(field.model);
        expect(field.getMethodNames).not.toHaveBeenCalled();
        expect(field.canCalculateEndDate).toHaveBeenCalled();
        expect(field.canCalculateEndDate(field.model)).toEqual(false);
    });

    it('should allow the calculation of an end date', function() {
        field.model.set(field.durationValueFieldName, '7');
        field.model.set(field.durationUnitFieldName, 'day');
        field.model.set(field.startDateFieldName, '2019-09-25');
        expect(field.canCalculateEndDate(field.model)).toEqual(true);
    });

    it('should get valid date methods for handling dates', function() {
        var methods;
        var date = new Date();

        field.model.set('service_duration_unit', 'day');
        methods = field.getMethodNames(field.model);
        expect(typeof date[methods.get]).toEqual('function');
        expect(typeof date[methods.set]).toEqual('function');

        field.model.set('service_duration_unit', 'month');
        methods = field.getMethodNames(field.model);
        expect(typeof date[methods.get]).toEqual('function');
        expect(typeof date[methods.set]).toEqual('function');

        field.model.set('service_duration_unit', 'year');
        methods = field.getMethodNames(field.model);
        expect(typeof date[methods.get]).toEqual('function');
        expect(typeof date[methods.set]).toEqual('function');
    });

    it('should add days to a date correctly', function() {
        field.model.set(field.startDateFieldName, '2019-09-25');

        field.model.set(field.durationValueFieldName, 1);
        field.model.set(field.durationUnitFieldName, 'day');
        expect(field.model.get(field.name)).toEqual('2019-09-25');

        field.model.set(field.durationValueFieldName, 7);
        field.model.set(field.durationUnitFieldName, 'day');
        expect(field.model.get(field.name)).toEqual('2019-10-01');
    });

    it('should add months to a date correctly', function() {
        field.model.set(field.startDateFieldName, '2019-06-30');
        field.model.set(field.durationValueFieldName, 1);
        field.model.set(field.durationUnitFieldName, 'month');
        expect(field.model.get(field.name)).toEqual('2019-07-29');

        field.model.set(field.durationValueFieldName, 3);
        field.model.set(field.durationUnitFieldName, 'month');
        expect(field.model.get(field.name)).toEqual('2019-09-29');
    });

    it('should add years to a date correctly', function() {
        field.model.set(field.startDateFieldName, '2019-09-30');
        field.model.set(field.durationValueFieldName, 1);
        field.model.set(field.durationUnitFieldName, 'year');
        expect(field.model.get(field.name)).toEqual('2020-09-29');

        field.model.set(field.durationValueFieldName, 3);
        field.model.set(field.durationUnitFieldName, 'year');
        expect(field.model.get(field.name)).toEqual('2022-09-29');
    });
});
