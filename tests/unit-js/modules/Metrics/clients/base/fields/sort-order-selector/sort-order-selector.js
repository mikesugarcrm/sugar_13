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
describe('Metrics.Fields.SortOrderSelector', function() {
    var app;
    var field;
    var fieldName = 'order_by_primary_direction';
    var model;
    var module = 'Metrics';

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean(module);
        model.set({
            metric_module: 'Accounts',
            order_by_primary: 'next_renewal_date',
            order_by_primary_direction: 'asc',
            order_by_secondary: '',
            order_by_secondary_direction: 'desc',
            filter_def: [
                {
                    $owner: ''
                }
            ]
        });

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField({
            client: 'base',
            name: fieldName,
            type: 'sort-order-selector',
            fieldDef: {},
            viewName: 'edit',
            module: module,
            model: model,
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        sinon.restore();
    });

    describe('when a dependency field is specified', function() {
        beforeEach(function() {
            field.initialize({
                model: model,
                def: {
                    dependencyField: 'order_by_primary'
                }
            });
            field.bindDataChange();

            sinon.stub(field.$el, 'hide');
            sinon.stub(field.$el, 'show');
        });

        describe('and the dependency field value becomes empty', function() {
            var descendingButton;

            beforeEach(function() {
                model.set('order_by_primary', '');
            });

            it('should hide the sort-order-selector field', function() {
                expect(field.$el.hide).toHaveBeenCalled();
            });
        });

        describe('and the dependency field value is not empty', function() {
            beforeEach(function() {
                model.set('order_by_primary', 'industry');
            });

            it('should show the sort-order-selector field', function() {
                expect(field.$el.show).toHaveBeenCalled();
            });
        });
    });
});
