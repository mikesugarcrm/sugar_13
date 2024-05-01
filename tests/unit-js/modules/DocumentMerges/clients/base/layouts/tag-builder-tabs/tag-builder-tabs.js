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
describe('DocumentMerges.View.TagBuilderTabs', function() {
    var app;
    var sinonSandbox;
    var tbtLayout;

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

        SugarTest.testMetadata.addLayoutDefinition('tag-builder-tabs', {
            'components': [
                {
                    view: 'tag-builder-relationships'
                },
                {
                    view: 'tag-builder-fields'
                },
                {
                    view: 'tag-builder-options'
                },
                {
                    view: 'tag-builder-directives'
                },
                {
                    view: 'tag-builder-conditionals'
                },
                {
                    view: 'tag-builder-formulas'
                },
            ],
        });

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        tbtLayout = SugarTest.createLayout('base', 'DocumentMerges', 'tag-builder-tabs', null, null, true);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        tbtLayout.dispose();
        tbtLayout = null;
    });

    describe('switchTab', function() {
        it('should switch between tabs', function() {
            tbtLayout.switchTab({
                'preventDefault': function() {},
                'target': {'dataset': {'target': 'tab2'}}
            });
            expect(tbtLayout.$('.tab-content #tab2')).not.toHaveClass('hide');
        });
    });
});
