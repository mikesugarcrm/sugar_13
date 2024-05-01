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
describe('Base.Layout.Subpanel', function() {
    var layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'panel');
        SugarTest.loadComponent('base', 'layout', 'subpanel');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        var testMeta, testLayout, testParams;

        beforeEach(function() {
            testMeta = {
                components: [],
                last_state: {
                    id: 'jasmin-test'
                }
            };
            testParams = {
                def: {
                    'override_subpanel_list_view': 'jasmine_test'
                }
            };
            var context = app.context.getContext();
            context.set({
                module: 'Accounts',
                layout: 'subpanel'
            });
            context.prepare();
            context.parent = app.context.getContext();
            testLayout = SugarTest.createLayout('base', 'Accounts', 'subpanel', testMeta, context, false, testParams);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('will set dataView variable and attribute on context to jasmine_test', function() {
            expect(testLayout.dataView).toEqual('jasmine_test');
            expect(testLayout.context.get('dataView')).toEqual('jasmine_test');
        });
    });
});
