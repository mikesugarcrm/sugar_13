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
describe('Base.Field.CJ_Forms.SelectToEmailAddress', function() {
    let model;
    let app;
    let fieldName = 'select_to_email_address';
    let module = 'CJ_Forms';

    function createField(model) {
        return SugarTest.createField('base', fieldName, 'select-to-email-address', 'detail', {
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
            sinon.stub(field.model, 'on');
            field.bindDataChange();
            expect(field.model.on).toHaveBeenCalled();
        });
    });

    describe('_getAvailableModulesApiURL', function() {
        using('input', [
            {
                parent_type: 'CJ_Forms',
                parent_id: '123',
                url: 'www.google.com',
            },
            {
                parent_type: 'CJ_Forms',
                parent_id: '456',
                url: 'www.github.com',
            },
        ],

        function(input) {
            it('should call the _getAvailableModulesApiURL function', function() {
                sinon.stub(app.api, 'buildURL').returns(input.url);
                field.model.set({
                    parent_id: input.parent_id,
                    parent_type: input.parent_type,
                });
                let result = field._getAvailableModulesApiURL();
                expect(result).toBe(input.url);
            });
        });
    });
});
