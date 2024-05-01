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

describe('DRI_SubWorkflow_Templates.Views.Create', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'create';
    let module = 'DRI_SubWorkflow_Templates';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        context = app.context.getContext({
            module: module,
            model: model,
            create: true
        });
        context.prepare(true);

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', viewName);

        layout = SugarTest.createLayout(
            'base',
            module,
            viewName,
            {},
            null,
            false
        );
        view = SugarTest.createView(
            'base',
            module,
            viewName,
            null,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();

        layout = null;
        context = null;
    });

    describe('setSortOrder', function() {
        using('input', [
            {
                id: undefined,
                success: true,
                sort_order: 7,
                expected: undefined,
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                success: true,
                sort_order: 7,
                expected: 8,
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                success: false,
                sort_order: 5,
                expected: 1,
            },
        ],

        function(input) {
            it('sort order should be as input expected', function() {
                sinon.stub(app.api, 'buildURL');
                sinon.stub(app.api, 'call').callsFake(function(method, url, parameter, callbacks) {
                    if (input.success) {
                        callbacks.success({sort_order: input.sort_order});
                    } else {
                        callbacks.error();
                    }
                });
                view.model.set('dri_workflow_template_id', input.id);
                view.setSortOrder();

                expect(view.model.get('sort_order')).toBe(input.expected);
            });
        });
    });
});
