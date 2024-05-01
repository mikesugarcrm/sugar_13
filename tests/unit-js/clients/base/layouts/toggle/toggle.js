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
describe("Base.Layout.Toggle", function() {
    var app, layout, defaultMeta, viewA, viewB, viewC,
        moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        viewA = app.view.createView({name: 'view-a', el: '<div data-name="view-a"></div>'});
        viewB = app.view.createView({name: 'view-b', el: '<div data-name="view-b"></div>'});
        viewC = app.view.createView({name: 'view-c', el: '<div data-name="view-c"></div>'});
        sinon.stub(viewA, 'render');
        sinon.stub(viewB, 'render');
        sinon.stub(viewC, 'render');
        sinon.spy(viewA, 'dispose');
        sinon.spy(viewB, 'dispose');
        sinon.spy(viewC, 'dispose');
        SugarTest.addComponent('base', 'view', 'view-a', viewA);
        SugarTest.addComponent('base', 'view', 'view-b', viewB);
        SugarTest.addComponent('base', 'view', 'view-c', viewC);
        SugarTest.testMetadata.set();
        defaultMeta = {
            "default_toggle": 'view-c',
            "available_toggles": {
                'view-a': {},
                'view-c': {}
            },
            "components": [
                {"view":"view-a"},
                {"view":"view-b"},
                {"view":"view-c"}
            ]
        };
        layout = SugarTest.createLayout("base", moduleName, "toggle", defaultMeta);
    });

    afterEach(function() {
        app.cache.cutAll();
        viewA.dispose();
        viewB.dispose();
        viewC.dispose();
        layout.dispose();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it("should render view-b then view-c, but defer rendering of view-a", function(){
        expect(viewA.render.callCount).toBe(0);
        expect(viewB.render.callCount).toBe(1); //not a toggle view
        expect(viewC.render.callCount).toBe(1); //default view to be rendered
        expect(layout._components.length).toBe(2);
        expect(layout.$('div').first().data('name')).toEqual('view-b');
        expect(layout.$('div').slice(1).data('name')).toEqual('view-c');
        expect(layout.$('[data-name="view-a"]').length).toBe(0);
    });

    it("should call dispose on all views, even though view-a isn't in layout's _components", function(){
        layout.dispose();
        expect(viewA.dispose.callCount).toBe(1);
        expect(viewB.dispose.callCount).toBe(1);
        expect(viewC.dispose.callCount).toBe(1);
    });

    it("should render view-a and hide view-c when showcomponent trigger is fired for view-a", function(){
        layout.trigger('toggle:showcomponent', 'view-a');
        expect(viewA.render.callCount).toBe(1);
        expect(layout._components.length).toBe(3);
        expect(layout.$('[data-name="view-a"]').length).toBe(1);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(true);
    });

    it("should toggle between views each time showComponent is called but render is only called once", function(){
        layout.showComponent('view-a');
        expect(layout.$('[data-name="view-a"]').hasClass('hide')).toBe(false);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(true);
        layout.showComponent('view-c');
        expect(layout.$('[data-name="view-a"]').hasClass('hide')).toBe(true);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(false);
        expect(viewA.render.callCount).toBe(1);
        expect(viewB.render.callCount).toBe(1);
    });
});
