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

describe('Base.Fields.FormulaBuilder', function() {
    var app;
    var field;
    var fieldType = 'formula-builder';
    var fieldModule = 'Accounts';
    var meta;
    var initOptions;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', fieldType);

        SugarTest.app.data.declareModels();

        meta = {
            'fields': {
                'name': 'name',
                'date_entered': 'date_entered',
                'date_modified': 'date_modified',
                'description': 'description'
            },
            'relateFields': {
                'Activities (Activity Stream)': {
                    'name': 'name',
                    'date_entered': 'date_entered',
                    'date_modified': 'date_modified',
                    'description': 'description'
                },
            },
            'relateModules': {
                'created_by_link': 'Users (Created by User)',
                'modified_user_link': 'Users (Modified by User)',
                'activities': 'Activities (Activity Stream)',
                'opportunities': 'Opportunities (Opportunity)'
            },
            'help': {
                'hourOfDay': '\u003Cb\u003EhourOfDay(Date d)\u003C\/b\u003E\u003Cbr\/\u003EReturns ' +
                    'the hour of the day (24 hour format) of a given date\/time.\u003Cbr\u003E\n ',
                'time': '\u003Cb\u003Etime(String s)\u003C\/b\u003E\u003Cbr\/\u003EReturns ' +
                    '\u003Ci\u003Es\u003C\/i\u003E as a time value.\u003Cbr\u003E\n ',
            },
            'rollupFields': {
                'Opportunities (Opportunity)': {
                    'best_case': 'Best Case',
                    'amount': 'Amount',
                },
            },
            'fieldsTypes': {
                'name': ['name', 'string'],
                'date_entered': ['date_entered', 'date'],
                'date_modified': ['date_modified', 'date'],
                'description': ['description', 'string'],
                'created_by_link': ['created_by_link', 'relate']
            }
        };

        view = new app.view.View({});

        initOptions = {
            targetModule: 'Accounts',
            formula: '"test"',
            callback: sinon.stub(),
            returnType: 'string',
            view: view,
            viewName: 'edit',
            viewDefs: {
                type: 'formula-builder'
            }
        };

        sinon.stub($.fn, 'select2').callsFake(function(options) {
            var select2 = sinon.stub().returns({
                options: options,
                onSelect: function() {
                }
            });
            $(this).data('select2', select2);
            return $(this);
        });
    });

    afterEach(function() {
        delete app.view.fields.BaseFormulaBuilderField;
        sinon.restore();
        if (field) {
            field.dispose();
        }
        if (view) {
            view.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
        meta = null;
    });

    describe('initialize()', function() {
        it('should properly set the field parameters and request the meta from the server', function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/formulaBuilder\/meta.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(meta)]);

            field = SugarTest.createField({
                name: 'testfield',
                type: 'formula-builder',
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base',
                viewDef: {
                    options: initOptions
                }
            });
            field.initialize(initOptions);

            SugarTest.server.respond();

            expect(field._module).toEqual(initOptions.targetModule);
            expect(field._formula).toEqual(initOptions.formula);
            expect(field._callback).toEqual(initOptions.callback);
            expect(field._returnType).toEqual(initOptions.returnType);
            expect(field._dropdowns.length).toEqual(7);
            expect(_.keys(field._functions).length > 0).toEqual(true);
            expect(_.keys(field._rollupModules).length > 0).toEqual(true);
            expect(field._fields).toEqual(meta.fields);
            expect(field._relatedFields).toEqual(meta.relateFields);
            expect(field._relatedModules).toEqual(meta.relateModules);
            expect(field._functionsHelp).toEqual(meta.help);
            expect(field._rollupFields).toEqual(meta.rollupFields);
            expect(field._fieldsType).toEqual(meta.fieldsTypes);
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/formulaBuilder\/meta.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(meta)]);

            field = SugarTest.createField({
                name: 'testfield',
                type: 'formula-builder',
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base'
            });

            field.initialize(initOptions);

            SugarTest.server.respond();
        });

        afterEach(function() {
            field = null;
        });

        it('should properly render the select2 controls', function() {
            field.setMode('edit');
            field.render();

            var $functions = field.find('a[data-select=functions]');
            expect($functions.length).toEqual(1);
            expect($functions.data('select2')).toBeDefined();

            var $fields = field.find('a[data-select=fields]');
            expect($fields.length).toEqual(1);
            expect($fields.data('select2')).toBeDefined();

            var $relatedModules = field.find('a[data-select=related-module]');
            expect($relatedModules.length).toEqual(1);
            expect($relatedModules.data('select2')).toBeDefined();

            var $relatedFields = field.find('a[data-select=related-fields]');
            expect($relatedFields.length).toEqual(1);
            expect($relatedFields.data('select2')).toBeDefined();

            var $rollupModules = field.find('a[data-select=rollup-module]');
            expect($rollupModules.length).toEqual(1);
            expect($rollupModules.data('select2')).toBeDefined();

            var $rollupFields = field.find('a[data-select=rollup-fields]');
            expect($rollupFields.length).toEqual(1);
            expect($rollupFields.data('select2')).toBeDefined();

            var $rollupFunctions = field.find('a[data-select=rollup-function]');
            expect($rollupFunctions.length).toEqual(1);
            expect($rollupFunctions.data('select2')).toBeDefined();
        });
    });

    describe('event listeners', function() {
        beforeEach(function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/formulaBuilder\/meta.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify(meta)]);

            field = SugarTest.createField({
                name: 'testfield',
                type: 'formula-builder',
                viewName: 'edit',
                loadFromModule: false,
                loadJsFile: false,
                client: 'base'
            });

            field.initialize(initOptions);

            SugarTest.server.respond();

            field.setMode('edit');
            field.render();
        });

        afterEach(function() {
            field = null;
        });

        it('should add a function to the formula when selected', function() {
            var $editor = field.$('textarea.formula-editor');
            field._addFunction({
                text: 'toString'
            });

            expect($editor.val().indexOf('toString(') >= 0).toEqual(true);
            expect(field._formula.indexOf('toString(') >= 0).toEqual(true);
        });

        it('should add a field to the formula when selected', function() {
            var $editor = field.$('textarea.formula-editor');
            field._addField({
                text: 'name'
            });

            expect($editor.val().indexOf('$name') >= 0).toEqual(true);
            expect(field._formula.indexOf('$name') >= 0).toEqual(true);
        });

        it('should add a related field to the formula when clicked', function() {
            field.find('a[data-select=related-module]').val('contacts');
            field.find('a[data-select=related-fields]').val('first_name');
            field.addRelatedField();

            var $editor = field.$('textarea.formula-editor');
            expect($editor.val().indexOf('related($contacts, "first_name")') >= 0).toEqual(true);
            expect(field._formula.indexOf('related($contacts, "first_name")') >= 0).toEqual(true);
        });

        it('should add a rollup field to the formula when clicked', function() {
            field.find('a[data-select=rollup-module]').val('contacts');
            field.find('a[data-select=rollup-fields]').val('income');
            field.find('a[data-select=rollup-function]').val('sum');
            field.addRollupField();

            var $editor = field.$('textarea.formula-editor');
            expect($editor.val().indexOf('sum($contacts,"income")') >= 0).toEqual(true);
            expect(field._formula.indexOf('sum($contacts,"income")') >= 0).toEqual(true);
        });
    });
});
