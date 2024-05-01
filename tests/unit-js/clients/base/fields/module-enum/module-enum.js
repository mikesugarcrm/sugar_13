
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
describe('Base.ModuleEnumField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        field = SugarTest.createField(
            'base',
            'calendar_module',
            'module-enum',
            'edit',
            {},
            module,
            model,
            context,
            false
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize()', function() {
        it('should be set as a multiselect field when used for filter component', function() {
            var options = {
                view: {
                    name: 'filter-rows'
                },
                def: {
                    name: 'calendar_module',
                    type: 'module-enum'
                }
            };
            field.initialize(options);

            expect(options.def.isMultiSelect).toBe(true);
        });

        it('should set a list with deny modules', function() {
            var options = {
                view: {
                    name: 'filter-rows'
                },
                def: {
                    name: 'calendar_module',
                    type: 'module-enum'
                }
            };
            field.initialize(options);

            expect(field.denyModules instanceof Array).toBe(true);
            expect(field.denyModules).toContain('Calendar');
        });
    });
});
