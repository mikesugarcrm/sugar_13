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
describe('View.Fields.Base.Calls.LabelField', function() {
    var app;
    var field;
    var sandbox;
    var createFieldProperties;
    var module = 'Calls';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'label');
        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();
        createFieldProperties = {
            client: 'base',
            name: 'type',
            type: 'label',
            viewName: 'edit',
            module: module,
            loadFromModule: true
        };

    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    it('should return detail', function() {
        field = SugarTest.createField(createFieldProperties);

        expect(field._getFallbackTemplate()).toEqual('detail');
    });
});
