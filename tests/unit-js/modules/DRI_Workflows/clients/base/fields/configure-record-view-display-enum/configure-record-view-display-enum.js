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

describe('View.Fields.Base.DRIWorkflows.ConfigureRecordViewDisplayEnumField', function() {
    let app;
    let field;
    let model;
    let module = 'DRI_Workflows';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'enum');

        model = app.data.createBean(module);
        field = SugarTest.createField(
            'base',
            'configure-record-view-display-enum',
            'configure-record-view-display-enum',
            'detail',
            {},
            module,
            model,
            null,
            true
        );
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        model.dispose();
        sinon.restore();
    });

    describe('initialize', function() {
        it('field type should be enum and dropdown class should be as expected', function() {
            expect(field.type).toBe('enum');
            expect(field.def.dropdown_class).toBe('cj-configure-recordview-display-content-field');
        });
    });

    describe('_sortResults', function() {
        it('field disableOptions function should be called', function() {
            sinon.stub(field, 'disableOptions');
            field._sortResults(['Zack', 'Alex', 'Sam']);

            expect(field.disableOptions).toHaveBeenCalled();
        });
    });

    describe('disableOptions', function() {
        it('tabs index should be disabled and _hasTabEnabledForView function should be called', function() {
            sinon.stub(field, '_hasTabEnabledForView').returns(false);
            let results = {
                'panel-top': {
                    'id': 'panel'
                },
                'panel-bottom': {
                    'id': 'panel'
                },
                'tab-first': {
                    'id': 'tab'
                },
                'tab-last': {
                    'id': 'tab'
                },
            };
            let response = field.disableOptions(results);

            expect(response['tab-first'].disabled).toBe(true);
            expect(response['panel-bottom'].disabled).toBe(undefined);
            expect(field._hasTabEnabledForView).toHaveBeenCalled();
        });
    });

    describe('_hasTabEnabledForView', function() {
        afterEach(function() {
            sinon.restore();
        });

        using('input', [
            {
                record_view: {},
                result: false
            },
            {
                record_view: {
                    panels: {
                        'cj_info': {
                            name: 'Sugar Automate Related fields',
                            newTab: false,
                        },
                    }
                },
                result: false,
            },
            {
                record_view: {
                    panels: [
                        {
                            name: 'Basic Information',
                            newTab: 'false',
                        },
                        {
                            name: 'Sugar Automate Related fields',
                            newTab: 'true',
                        },
                    ]
                },
                result: true,
            },
        ],

        function(input) {
            it('response from _hasTabEnabledForView function should match with input result', function() {
                sinon.stub(app.metadata, 'getView').returns(input.record_view);
                field.def.baseModule = 'Accounts';

                expect(field._hasTabEnabledForView()).toBe(input.result);
            });
        });
    });

    describe('_hasMetaPanels', function() {
        using('input', [
            {
                record_view: null,
                result: false
            },
            {
                record_view: {},
                result: false,
            },
            {
                record_view: {
                    panels: ['panel-top', 'panel-bottom']
                },
                result: true,
            },
        ],

        function(input) {
            it('response from _hasMetaPanels function should match with input result', function() {
                expect(field._hasMetaPanels(input.record_view)).toBe(input.result);
            });
        });
    });
});
