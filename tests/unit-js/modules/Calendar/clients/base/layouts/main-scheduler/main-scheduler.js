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
describe('View.Layouts.Base.Calendar.MainSchedulerLayout', function() {
    var app;
    var layout;
    var model;
    var module = 'Calendar';
    var context;
    var collection;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'main-panel', module);
        SugarTest.loadComponent('base', 'view', 'scheduler', module);
        SugarTest.loadComponent('base', 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', 'tabbed-layout');
        SugarTest.loadComponent('base', 'layout', 'main-scheduler', module);

        SugarTest.declareData('base', module, true, true);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        app.Calendar = {
            utils: {
                buildUserKeyForStorage: function() {}
            }
        };

        model = app.data.createBean(module);

        context = new app.Context();

        collection = app.data.createBeanCollection('Calendar');
        context.set({
            module: module,
            model: model,
            collection: collection,
        });
        var meta = {};
        layout = SugarTest.createLayout(
            'base',
            module,
            'main-scheduler',
            meta,
            context,
            true,
            {}
        );
    });

    afterEach(function() {
        model = null;
        layout = null;
    });

    describe('initialize', function() {
        it('should set initial parameters', function() {
            layout.initialize({
                type: 'main-scheduler',
                module: module,
                context: context,
            });

            expect(layout.componentStorageKey).toEqual('main-panel');
        });
    });
});
