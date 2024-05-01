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

describe('DRI_SubWorkflow_Templates.Views.CreateNodupecheck', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'create-nodupecheck';
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
        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
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

    describe('initialize', () => {
        it('should call the initialize function and initialze some properties', () => {
            view.enableDuplicateCheck = true;
            view.initialize(initOptions);
            expect(view._super).toHaveBeenCalledWith('initialize');
            expect(view.enableDuplicateCheck).toBe(false);
        });
    });
});
