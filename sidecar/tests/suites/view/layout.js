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

const Context = require('../../../src/core/context');
const Field = require('../../../src/view/field');
const Layout = require('../../../src/view/layout');
const Template = require('../../../src/view/template');
const View = require('../../../src/view/view');

describe('View/Layout', function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('initComponents', function() {
        var catLayout, components1, components2;

        beforeEach(function() {
            catLayout = SugarTest.createComponent('Layout', {
                'type': 'cat',
                'module': 'Pets'
            });

            components1 = [
                {'view': {'type': 'view1'}},
                {'layout': {'type': 'layout1'}}
            ];

            components2 = [
                {'layout': {
                    'components': [{layout: {'type': 'layout3'}}],
                    'type': 'layout2',
                    'context': {'module': 'componentContextModule'}
                }}
            ];
        });

        afterEach(function() {
            catLayout.dispose();
        });

        it('should initialize and append metadata-defined components if no components are passed in', function() {
            catLayout.meta.components = components1;
            expect(catLayout._components.length).toEqual(0);

            catLayout.initComponents();

            expect(catLayout._components.length).toEqual(2);
            expect(catLayout._components[0].name).toEqual('view1');
            expect(catLayout._components[1].name).toEqual('layout1');
        });

        it('should avoid initializing child components if it is a lazy loaded layout', function() {
            var baseLayout = SugarTest.createComponent('Layout', {meta: {components: components1, lazy_loaded: true}});

            baseLayout.initComponents();

            expect(baseLayout._components.length).toEqual(0);
            expect(baseLayout._componentsMeta).toEqual(components1);
        });

        it('should initialize and append the components passed in', function() {
            expect(catLayout._components.length).toEqual(0);

            catLayout.initComponents(components1);

            expect(catLayout._components.length).toEqual(2);
            expect(catLayout._components[0].name).toEqual('view1');
            expect(catLayout._components[1].name).toEqual('layout1');
        });

        it('should initialize and append the components recursively', function() {
            catLayout.meta.components = components2;

            catLayout.initComponents();

            //check that it has recursively initialized layouts within catLayout
            expect(catLayout._components[0]._components.length).toEqual(1);
            expect(catLayout._components.length).toEqual(1);

            sinon.spy(catLayout._components[0], 'initComponents');

            //initComponents with a different set of components
            catLayout.initComponents(components1);
            expect(catLayout._components.length).toEqual(3);

            //check that `initComponents` of already initialized layouts are not
            //called again.
            expect(catLayout._components[0].initComponents).not.toHaveBeenCalled();

            //check that the child component inherited the parent context
            var parentComponent = catLayout.getComponent('layout2');
            var childComponent = parentComponent.getComponent('layout3');
            expect(childComponent.context).toEqual(parentComponent.context);
        });

        it('should use the specified context and module that were passed in when creating new components', function() {
            sinon.stub(catLayout, 'createComponentFromDef');
            sinon.stub(catLayout, 'addComponent');
            var fakeContext = sinon.mock({});

            catLayout.initComponents(components2, fakeContext, 'Animals');
            expect(catLayout.createComponentFromDef.lastCall.args[1]).toEqual(fakeContext);
            expect(catLayout.createComponentFromDef.lastCall.args[2]).toEqual('Animals');
        });

    });

    it("should use custom layout template", function() {
        app.metadata.set({
            modules:{
                Avengers:{
                    layouts:{
                        foo:{
                            controller:"({})",
                            templates: {
                                "foo": "Custom Layout Template"
                            }
                        }
                    }
                }
            }
        });
        var layout = SugarTest.createComponent("Layout", {
            type : "foo",
            module: "Avengers",
            meta : []
        });
        expect(layout.$el.html()).toEqual("Custom Layout Template");
    });
    it("should set a layout's label if meta.label provided", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "foo", module: "Bar",
            meta : {label: 'my_meta_label'}
        });
        expect(layout.label).toEqual('my_meta_label');
    });
    it("should set a layout's label if options.def.label provided", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "foo", module: "Bar", meta: {},
            def: { label: "my_options_def_label"}
        });
        expect(layout.label).toEqual('my_options_def_label');
    });
    it("should set a layout's label if options.label provided", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "foo", module: "Bar", meta: {},
            label: "my_options_label"
        });
        expect(layout.label).toEqual('my_options_label');
    });
    it("should fallback to empty string for layout's label", function() {
        var layout = SugarTest.createComponent("Layout", {type : "foo", module: "Bar", meta: {}});
        expect(layout.label).toEqual('');
    });
    it("should use load non-module specific layout templates", function() {
            app.metadata.set({
                layouts:{
                    myLayout1:{
                        controller:"({})",
                        templates: {
                            "myLayout1": "OOB Layout Template"
                        }
                    }
                }
            });
            var layout = SugarTest.createComponent("Layout", {
                type : "myLayout1",
                module: "Avengers",
                meta : []
            });
            expect(layout.$el.html()).toEqual("OOB Layout Template");
        });

    it("should get a component by name", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "edit",
            module: "Contacts"
        });

        layout.addComponent(SugarTest.createComponent("View", {
            type: "subedit"
        }));

        expect(layout._components.length).toEqual(2);

        var component = layout.getComponent("edit");
        expect(component).toBeDefined();
        expect(component.name).toEqual("edit");
        expect(component instanceof View).toBeTruthy();

        expect(layout.getComponent("foo")).toBeUndefined();

        layout.initComponents([{view: 'blah-list'}]);
        var actualBlahComponent = layout.getComponent("blah-list");
        expect(actualBlahComponent).toBeDefined();
        expect(actualBlahComponent.module).toBe(layout.module);
        expect(actualBlahComponent.context).toBe(layout.context);
    });

    it("should get a sub-layout by component name", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "edit",
            module: "Contacts"
        });

        layout.addComponent(SugarTest.createComponent("Layout", {
            type: "sublayout",
            meta: {}
        }));

        expect(layout._components.length).toEqual(2);

        var component = layout.getComponent("sublayout");
        expect(component).toBeDefined();
        expect(component.name).toEqual("sublayout");
        expect(component instanceof Layout).toBeTruthy();
    });

    it('should load a sublayout from inline definition', function() {
        let layout = SugarTest.createComponent('Layout', {
            type: 'simple',
            module: 'Contacts',
            meta: {
                components: [{
                    layout: {
                        type: 'simple',
                    },
                    xmeta: {
                        foo: 'bar'
                    }
                }]
            }
        });

        expect(layout._components.length).toEqual(1);
        expect(layout._components[0].meta.foo).toEqual('bar');
    });

    it('should load a view from inline definition', function() {
        let layout = SugarTest.createComponent('Layout', {
            type: 'simple',
            module: 'Contacts',
            meta: {
                components: [{
                    view: {
                        type: 'simple',
                        foo: 'bar'
                    }
                }]
            }
        });

        expect(layout._components.length).toEqual(1);
        expect(layout._components[0].meta.foo).toEqual('bar');
        expect(layout._components[0] instanceof View).toBeTruthy();
    });

    it("should get a sub-layout should have name from def type", function() {
        var layout = SugarTest.createComponent("Layout", {
            type : "edit",
            module: "Contacts"
        });

        layout.addComponent(SugarTest.createComponent("Layout", {
            type: "sublayout",
            meta: {}
        }));

        expect(layout._components.length).toEqual(2);

        var component = layout.getComponent("sublayout");
        expect(component).toBeDefined();
        expect(component.name).toEqual("sublayout");
        expect(component instanceof Layout).toBeTruthy();
    });

    // Please refer to: https://www.pivotaltracker.com/story/show/30426995
    xit("should dispose itself", function() {
        var model = app.data.createBean("Contacts");
        var collection = app.data.createBeanCollection("Contacts");
        var context = new Context({
            model: model,
            collection: collection
        });

        var layout = SugarTest.createComponent("Layout", {
            type: "edit",
            module: "Contacts",
            context: context
        });

        var view = layout._components[0];
        view.fallbackFieldTemplate = "edit";
        view.template = Template.get('edit');
        view.on("foo", function() {});

        // Fake bindDataChange
        collection.on("reset", view.render, view);

        // Different scope
        var obj = {
            handler: function() {}
        };
        model.on("change", obj.handler, obj);
        collection.on("reset", obj.handler, obj);

        layout.render();
        var fields = _.clone(view.fields);

        expect(_.isEmpty(model._callbacks)).toBeFalsy();
        expect(_.isEmpty(collection._callbacks)).toBeFalsy();
        expect(_.isEmpty(view._callbacks)).toBeFalsy();

        var spy = sinon.spy(Field.prototype, 'unbindDom');
        var spy2 = sinon.spy(app.view.Component.prototype, 'remove');

        layout.dispose();

        // Dispose shouldn't remove callbacks that are not scoped by components
        expect(_.keys( model._callbacks).length).toEqual(1);
        expect(_.keys( model._callbacks)[0]).toEqual("change");
        expect(_.keys(collection._callbacks).length).toEqual(1);
        expect(_.keys(collection._callbacks)[0]).toEqual("reset");

        // Check if layout is disposed
        expect(layout.disposed).toBeTruthy();
        expect(layout._components.length).toEqual(0);
        expect(layout.model).toBeNull();
        expect(layout.collection).toBeNull();
        expect(function() { layout.render(); }).toThrow();

        // Check if view is disposed
        expect(view.disposed).toBeTruthy();
        expect(_.isEmpty(view.fields)).toBeTruthy();
        expect(_.isEmpty(view._callbacks)).toBeTruthy();
        expect(view.model).toBeNull();
        expect(view.collection).toBeNull();
        expect(function() { view.render(); }).toThrow();

        // Check if fields are disposed
        expect(spy.callCount).toEqual(6); // for each field
        _.each(fields, function(field) {
            expect(field.disposed).toBeTruthy();
            expect(function() { field.render(); }).toThrow();
            expect(field.model).toBeNull();
            expect(field.collection).toBeNull();
        });

        expect(spy2.callCount).toEqual(8); // 6 fields + 1 layout + 1 view
    });

   describe('loading the template', function() {
        using('different cases where templates are defined or not', [
            // We get the template defined in the view's module first.
            {
                module: 'Accounts',
                moduleTpl: true,
                expectedTpl: 'moduleTpl'
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: false,
                expectedTpl: 'moduleTpl'
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: true,
                expectedTpl: 'moduleTpl'
            },
            // If the template is not defined in the
            // view's module and `loadModule` is not passed, we fallback to the
            // template in base.
            {
                module: 'Accounts',
                moduleTpl: false,
                expectedTpl: 'baseTpl'
            },
            // If the template is not defined in the view's
            // module, we fallback to the one defined in `loadModule` module.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: true,
                expectedTpl: 'loadModuleTpl'
            },
            // If the template in `loadModule` module
            // is undefined, we do NOT fallback to the one defined in base.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: false,
                expectedTpl: void 0
            },
        ], function(data) {
            it('should load the template from the correct module', function() {
                var layoutName = 'testLayout';
                var templates = {
                    moduleTpl: '<div>' + data.module + '</div>',
                    loadModuleTpl: '<div>' + data.loadModule + '</div>',
                    baseTpl: '<div>base</div>',
                    emptyTpl: app.template.empty()
                };

                sinon.stub(app.template, 'getLayout').callsFake(function(name, module) {
                   if (module === data.loadModule && data.loadModuleTpl) {
                       return function() {
                           return templates.loadModuleTpl;
                       }
                   } else if (module === data.module && data.moduleTpl) {
                       return function() {
                           return templates.moduleTpl;
                       }
                   } else if (!module) {
                       return function() {
                           return templates.baseTpl;
                       }
                   }
                });

                var layout = app.view.createLayout({name: layoutName, type: layoutName, module: data.module, loadModule: data.loadModule});
                if (_.isUndefined(data.expectedTpl)) {
                    expect(layout.template).toBeUndefined();
                } else {
                    expect(layout.template()).toEqual(templates[data.expectedTpl]);
                }
            });
        });
    });

    // TODO: Test Layout class: render method
    // TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

});
