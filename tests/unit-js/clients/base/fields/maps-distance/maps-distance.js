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

describe('Base.Fields.MapsDistance', function() {
    var app;
    var fieldType = 'maps-distance';
    var field;

    beforeEach(function() {
        app = SugarTest.app;

        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', fieldType);

        SugarTest.app.data.declareModels();
    });

    afterEach(function() {
        delete app.view.fields.BaseMapsDistanceField;
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        field = null;
        meta = null;
    });

    describe('initialize()', function() {
        it('should properly set the field parameters', function() {
            field = SugarTest.createField({
                name: 'testfield',
                type: fieldType,
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base',
            });

            var totalUnitTypesNo = 2;
            var totalCountriesNo = 94;

            expect(Object.keys(field._unitTypes).length).toEqual(totalUnitTypesNo);
            expect(Object.keys(field._countries).length).toEqual(totalCountriesNo);
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'testfield',
                type: fieldType,
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base',
            });
        });

        afterEach(function() {
            field = null;
        });

        it('should properly render the select2 controls', function() {
            field.setMode('edit');
            field.render();

            var $functions = field.$('[data-fieldname=countries]');
            expect($functions.length).toEqual(1);
            expect($functions.data('select2')).toBeDefined();

            var $fields = field.$('[data-fieldname=unitType]');
            expect($fields.length).toEqual(1);
            expect($fields.data('select2')).toBeDefined();
        });
    });

    describe('_dipose()', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'testfield',
                type: fieldType,
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base',
            });
        });

        afterEach(function() {
            field = null;
        });

        it('should properly dispose the select2 controls', function() {
            field.setMode('edit');
            field.render();
            field.dispose();

            expect(field._select2).toEqual({});
        });
    });
});
