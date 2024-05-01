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
describe('DRI_Workflows.Base.View.ConfigureModulesContentView', function() {
    let app;
    let view;
    let context;
    let initOptions;
    let meta;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.prepare();
        context.parent = app.context.getContext();

        layout = SugarTest.createLayout(
            'base',
            'DRI_Workflows',
            'base',
            null,
            context
        );

        meta = {
            'header_label': 'LBL_CONFIGURE_RECORDVIEW_DISPLAY_TITLE',
            'fields': [
                {
                    'name': 'enabled_modules',
                    'label': 'LBL_ENABLED_MODULES',
                    'type': 'enum',
                },
            ],
        };

        view = SugarTest.createView(
            'base',
            'DRI_Workflows',
            'configure-modules-content',
            meta,
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
            meta: meta,
        };
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', () => {
        it('should set the header label', () => {
            view.initialize(initOptions);
            expect(view.headerLaber).toBe('LBL_CONFIGURE_RECORDVIEW_DISPLAY_TITLE');

            initOptions.meta.header_label = 'LBL_TEST';
            view.initialize(initOptions);
            expect(view.headerLaber).toBe('LBL_TEST');
        });
    });

    describe('_render', () => {
        it('should set the dispaly settings in module', () => {
            view.displaySettings = {
                'test': 'value',
            };

            let getFieldName = sinon.stub(view, 'getFieldName').callsFake(function(field) {
                return field;
            });

            view._render();

            expect(getFieldName).toHaveBeenCalled();
            expect(view.model.get('test')).toEqual('value');
        });
    });

    describe('prepareFieldsMeta', () => {
        it('should set the fields meta', () => {

            view.prepareFieldsMeta({
                'test': 'value',
            });

            expect(view.fieldsMeta[0].name).toEqual('test_display_field');
        });
    });

    describe('getFieldName', () => {
        using('values', [
            {
                input: '',
                expected: undefined
            },
            {
                input: 'test',
                expected: 'test_display_field'
            },
            {
                input: 'Test',
                expected: 'test_display_field'
            },
        ],
            (value) => {
                it('should get the field name', () => {
                    expect(view.getFieldName(value.input)).toEqual(value.expected);
                });
            });
    });

    describe('loadData', () => {
        it('should set data from Config', () => {
            app.config.customer_journey = {
                enabled_modules: 'test',
                recordview_display_settings: {'test': 'value'}
            };
            view.loadData();
            expect(view.displaySettings).toEqual({'test': 'value'});

        });
    });
});
