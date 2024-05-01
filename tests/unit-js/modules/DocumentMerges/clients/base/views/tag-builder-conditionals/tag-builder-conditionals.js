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
describe('DocumentMerges.View.TagBuilderConditionals', function() {
    var app;
    var sinonSandbox;
    var view;
    var mockEvent;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-conditionals', 'DocumentMerges');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-conditionals', null, null, true);
        var mainModule = 'Accounts';
        var fieldDefs = [
            {
                name: 'a',
                type: 'name',
            },
            {
                name: 'b',
                type: 'date',
            },
            {
                name: 'c',
                type: 'varchar',
            },
            {
                name: 'd',
                type: 'link',
                module: 'Contacts'
            },
        ];
        SugarTest.testMetadata.updateModuleMetadata(mainModule, {fields: fieldDefs});
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            // initializeDropDownsStub = sinonSandbox.stub(view, 'initializeDropDowns');
            // hideCustomOptionsStub = sinonSandbox.stub(view, 'hideCustomOptions');
        });
        it('should initialize dropdowns and hide options', function() {
            expect(view.conditionalTag).toBeDefined();
        });
    });
});
