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
describe('DocumentMerges.Layout.TagBuilder', function() {
    var app;
    var sinonSandbox;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'tag-builder-tabs', 'DocumentMerges');

        SugarTest.loadComponent('base', 'view', 'tag-builder-module', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-options', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-relationships', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-fields', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-directives', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-conditionals', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'tag-builder-formulas', 'DocumentMerges');

        SugarTest.testMetadata.addLayoutDefinition('tag-builder', {
            'components': [
                {
                    'view': 'tag-builder-module',
                },
                {
                    'layout': 'tag-builder-tabs',
                },
            ],
        });

        SugarTest.testMetadata.addLayoutDefinition('tag-builder-tabs', {
            'components': [
                {
                    view: 'tag-builder-relationships',
                },
                {
                    view: 'tag-builder-fields',
                },
                {
                    view: 'tag-builder-options',
                },
            ],
        });

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();
        layout = SugarTest.createLayout('base', 'DocumentMerges', 'tag-builder', null, null, true);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        layout.dispose();
        layout = null;
    });

    describe('initialize', function() {
        it('should remove application headers and sidebar-nav on initialize', function() {
            expect($('#navbar').length).toBe(0);
            expect($('#sidebar-nav').length).toBe(0);
        });
    });

    describe('hideOptions', function() {
        it('should hide the options view and show the fields', function() {
            layout.context.trigger('change:currentModule', 'Accounts');
            layout.context.trigger('tag-builder-options:hide', {name: 'name', type: 'name'});
            expect(layout.getComponent('tag-builder-tabs')
                .getComponent('tag-builder-options').$el).toHaveClass('hide');
            expect(layout.getComponent('tag-builder-tabs')
                .getComponent('tag-builder-fields').$el).not.toHaveClass('hide');
        });
    });

    describe('showOptions', function() {
        it('should show the options view and hide the fields', function() {
            layout.context.trigger('change:currentModule', 'Accounts');
            layout.context.trigger('tag-builder-options:show', {name: 'name', type: 'name'});
            expect(layout.getComponent('tag-builder-tabs')
                .getComponent('tag-builder-options').$el).not.toHaveClass('hide');
            expect(layout.getComponent('tag-builder-tabs')
                .getComponent('tag-builder-fields').$el).toHaveClass('hide');
        });
    });
});
