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
describe('DocumentMerges.View.TagBuilderRelationships', function() {
    var app;
    var sinonSandbox;
    var view;
    var mockEvent;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-relationships', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-relationships', 'view', 'base', null, 'DocumentMerges');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-relationships', null, null, true);

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

    describe('addToRelationshipStack', function() {
        beforeEach(function() {
            renderStub = sinonSandbox.stub(view, 'render');
        });
        it('should a relationship to the current relationship stack', function() {
            view.addToRelationshipStack(view.context, 'Accounts');
            expect(view.relationshipStack.length).toBeGreaterThan(0);
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('filterRelationships', function() {
        it('should retrieve relationship fields for a module', function() {
            var rels = view.filterRelationships('Accounts');
            expect(rels.length).toBeGreaterThan(0);
        });
    });

    describe('removeRelationship', function() {
        beforeEach(function() {
            renderStub = sinonSandbox.stub(view, 'render');
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('button');
            mockEvent.target['stack-index'] = 0;

            view.context.trigger('change:currentRelationshipsModule', 'Accounts');
        });
        it('should remove a relationship from the stack', function() {
            view.removeRelationship(mockEvent);
            expect(view.relationshipStack.length).toBe(0);
            expect(renderStub).toHaveBeenCalled();
        });
    });
});
