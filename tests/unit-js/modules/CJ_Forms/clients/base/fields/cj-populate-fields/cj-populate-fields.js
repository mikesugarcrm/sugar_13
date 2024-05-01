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
describe('Base.Field.CJ_Forms.CjPopulateFields', function() {
    let field;
    let fieldType = 'cj-populate-fields';
    let app;
    let model;
    let fieldName = 'cj_populate_fields';
    let module = 'CJ_Forms';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = createField(model);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('getModuleName', function() {
        it('should return the module name for the relationship', function() {
            field.model.set('relationship', [
                {
                    'module': 'Accounts',
                },
                {
                    'module': 'Contacts',
                },
                {
                    'module': 'DRI_Workflow_Templates',
                },
            ]);
            expect(field.getModuleName()).toEqual('DRI_Workflow_Templates');
        });
    });
});
