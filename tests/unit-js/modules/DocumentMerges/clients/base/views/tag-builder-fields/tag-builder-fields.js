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
describe('DocumentMerges.View.TagBuilderFields', function() {
    var app;
    var sinonSandbox;
    var view;
    var mockEvent;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-fields', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-fields', 'view', 'base', null, 'DocumentMerges');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-fields', null, null, true);

        var mainModule = 'Accounts';
        var fieldDefs = [
            {
                name: 'a',
                type: 'name',
            }, {
                name: 'b',
                type: 'date',
            }, {
                name: 'c',
                type: 'varchar',
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

    describe('refreshFields', function() {
        beforeEach(function() {
            renderStub = sinonSandbox.stub(view, 'render');
        });
        it('should retrieve fields for a module', function() {
            view.refreshFields(view.context, 'Accounts');
            expect(view.fieldsMeta.length).toBeGreaterThan(0);
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('showFieldOptions', function() {
        beforeEach(function() {
            sinon.spy(view.context, 'trigger');
        });
        it('should trigger the tag-builder-options:show event', function() {
            mockEvent = $.Event('click');
            mockEvent.currentTarget = document.createElement('button');

            view.showFieldOptions(mockEvent);
            expect(view.context.trigger).toHaveBeenCalledWith('tag-builder-options:show');
        });
    });
});
