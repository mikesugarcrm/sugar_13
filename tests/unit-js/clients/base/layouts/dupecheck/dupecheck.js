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
describe("Base.Layout.DupeCheck", function() {
    var app, defaultMeta, defaultListView,
        moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent("base", "view", "selection-list");
        SugarTest.loadComponent("base", "view", "dupecheck-list");
        SugarTest.loadComponent("base", "view", "dupecheck-header");
        SugarTest.testMetadata.addViewDefinition('list', {
            "panels":[
                {
                    "name":"panel_header",
                    "fields":[
                        {
                            "name":"name",
                            "label":"",
                            "placeholder":"LBL_LIST_NAME"
                        },
                        {
                            "name":"status",
                            "label":"",
                            "placeholder":"LBL_LIST_STATUS"
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        defaultListView = "dupecheck-list";
        defaultMeta = {
            "type": "dupecheck",
            "components": [
                {"view":"dupecheck-header"},
                {"view":defaultListView, "name":"dupecheck-list"}
            ]
        };
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it("should have default list view type", function(){
        var layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        expect(layout._components[1].name).toEqual(defaultListView);
    });

    it("should be able to switch list view type", function(){
        var expectedListView, context, layout;

        //if you set dupelisttype on context, the list view will be overridden.
        expectedListView = 'dupecheck-list-select';
        context = app.context.getContext();
        context.set('dupelisttype', expectedListView);
        context.prepare();

        layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta, context);
        expect(layout._components[1].name).toEqual(expectedListView);
    });

});
