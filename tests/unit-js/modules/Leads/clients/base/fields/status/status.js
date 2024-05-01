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
describe('Leads Status Field', function() {
    var app, field, model, options;

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean('Leads');
        options = {
            'New': 'New',
            'Converted': 'Converted',
            'Dead': 'Dead'
        };

        SugarTest.loadComponent('base', 'field', 'enum');
        field = SugarTest.createField({
            name: 'status',
            type: 'status',
            viewName: 'detail',
            module: 'Leads',
            model: model,
            loadFromModule: true
        });
        sinon.stub(field, 'loadEnumOptions');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    it('should filter out "Converted" as an option if value is not already Converted', function() {
        var newOptions;
        field.model.set('status', 'New');
        newOptions = field._filterOptions(options);
        expect(newOptions.Converted).toBeUndefined();
    });

    it('should not filter out "Converted" as an option if value is already Converted', function() {
        var newOptions;
        field.model.set('status', 'Converted');
        newOptions = field._filterOptions(options);
        expect(newOptions.Converted).not.toBeUndefined();
    });

    it('should not filter out "Converted" as an option if value on model is not set yet', function() {
        var newOptions;
        field.model.unset('status');
        newOptions = field._filterOptions(options);
        expect(newOptions.Converted).not.toBeUndefined();
    });
});
