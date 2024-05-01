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
describe('Base.Field.DRI_Workflow_Task_Templates.SelectToGuests', function() {
    let field;
    let model;
    let app;
    let fieldName = 'select-to-guests';
    let module = 'DRI_Workflow_Task_Templates';

    function createField(model) {
        return SugarTest.createField('base', fieldName, 'select-to-guests', 'detail', {
        }, module, model, null, true);
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        SugarTest.loadComponent('base', 'field', 'cj-select-to');
        field = createField(model);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('initialize', function() {
        it('should initialize the cj-select-to', function() {
            expect(field.type).toBe('cj-select-to');
        });
    });

    describe('bindDataChange', function() {
        it('should call the bindDataChange function', function() {
            sinon.stub(field, 'listenTo');
            field.bindDataChange();
            expect(field.listenTo).toHaveBeenCalled();
        });
    });

    describe('_getAvailableModulesApiURL', function() {
        using('input', [
            {
                id: '123',
                url: '../../../rest/v10/DRI_Workflow_Task_Templates/available-modules?template_id=123',
            },
            {
                id: '456',
                url: '../../../rest/v10/DRI_Workflow_Task_Templates/available-modules?template_id=456',
            },
        ],

        function(input) {
            it('should call the _getAvailableModulesApiURL function', function() {
                field.model.set({
                    dri_workflow_template_id: input.id,
                    url: input.url,
                });
                expect(field._getAvailableModulesApiURL()).toBe(field.model.get('url'));
            });
        });
    });
});
