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
describe('Dashboards.Base.Field.Name', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        metadata = {
            'fields': {
                'dashboard_module': {
                    'name': 'dashboard_module',
                    'type': 'enum',
                    'dbType': 'varchar'
                }
            }
        };
        app.data.declareModel('Dashboards', metadata);
        model = app.data.createBean('Dashboards', {
            dashboard_module: 'Home',
            default_dashboard: false
        });
        field = SugarTest.createField({
            name: 'test_field',
            type: 'name',
            viewName: 'testViewName',
            fieldDef: {},
            module: 'Dashboards',
            model: model,
            loadFromModule: true
        });
    });

    afterEach(function() {
        model = null;
        app.cache.cutAll();
        app.view.reset();
        field.dispose();
        SugarTest.testMetadata.dispose();
        sinon.restore();
    });

    describe('format', function() {
        it('should get translation from dashboard_module', function() {
            var val = 'TEST_VALUE';
            var correctRetValue = 'correct';
            sinon.stub(app.lang, 'get').callsFake(function(value, module) {
                var obj = {
                    Home: {
                        TEST_VALUE: correctRetValue
                    }
                };
                return obj[module] ? obj[module][value] : '';
            });

            var ret = field.format(val);
            expect(ret).toEqual(correctRetValue);
        });
    });
});
