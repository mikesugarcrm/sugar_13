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

describe('View/Component', function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('constructor', function() {
        it('should delegate the events properly', function() {

            app.view.declareComponent('layout', 'class1', null, {
                events: function() {
                    return {
                        click: 'foo'
                    };
                },
                foo: sinon.spy()
            }, true, 'base');

            app.view.declareComponent('layout', 'class2', null, {
                events: {
                    click: 'bar'
                },
                bar: sinon.spy()
            }, true, 'base');

            var class1 = new app.view.layouts.BaseClass1Layout({});
            class1.$el.click();
            expect(class1.foo).toHaveBeenCalled();

            var class2 = new app.view.layouts.BaseClass2Layout({});
            class2.$el.click();
            expect(class2.bar).toHaveBeenCalled();

            class1.dispose();
            class2.dispose();
            delete app.view.layouts['BaseClass1Layout'];
            delete app.view.layouts['BaseClass2Layout'];
        });
    });

    it("should add a css class when specified", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: {
                css_class: "test"
            }
        });
        expect(layout.$el.hasClass("test")).toBeTruthy();
    });

    it("should add multiple css classes when specified", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: {
                css_class: "test1 test2"
            }
        });
        expect(layout.$el.hasClass("test1")).toBeTruthy();
        expect(layout.$el.hasClass("test2")).toBeTruthy();
    });

    it("should not add css classes when none are specified", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: []
        });
        expect(layout.$el.hasClass("test")).toBeFalsy();
        expect(layout.$el.hasClass("undefined")).toBeFalsy();
    });

    it('should log error if component disposed', function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree"
        });
        var spy = sinon.spy(layout, '_render');
        var stub = sinon.stub(app.logger, 'error');
        //Nominal case
        layout.render();
        expect(stub).not.toHaveBeenCalled();
        expect(spy).toHaveBeenCalled();
        stub.resetHistory();
        spy.resetHistory();
        //Disposed case
        layout.dispose();
        layout.render();
        expect(stub).toHaveBeenCalled();
        expect(spy).not.toHaveBeenCalled();
    });

    it("should not render when before render is false", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: []
        });

        var spy = sinon.spy(layout, "_render");
        layout.before("render", function() {
            return false;
        });
        layout.render();
        expect(spy).not.toHaveBeenCalled();
    });

    it("should hide and show the base element", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: {}
        });

        layout.hide();
        expect($(layout.$el).css("display") == "none").toEqual(true);
        expect($(layout.$el).hasClass("hide")).toEqual(true);
        expect(layout.isVisible()).toEqual(false);
        layout.show();
        expect($(layout.$el).css("display") != "none").toEqual(true);
        expect($(layout.$el).hasClass("hide")).toEqual(false);
        expect(layout.isVisible()).toEqual(true);
    });

    it('should return false for show if the event should not be triggered', function() {
        var layout = SugarTest.createComponent('Layout', {
            type: 'tree',
            module: 'Taxonomy',
            meta: {}
        });

        var stubTrigger = sinon.stub(layout, 'triggerBefore').callsFake(function() {
            return false;
        });

        var stubVisible = sinon.stub(layout, 'isVisible').callsFake(function() {
            return false;
        });

        expect(layout.show()).toEqual(false);
    });

    it('should return false for hide if the event should not be triggered', function() {
        var layout = SugarTest.createComponent('Layout', {
            type: 'tree',
            module: 'Taxonomy',
            meta: {}
        });

        var stubTrigger = sinon.stub(layout, 'triggerBefore').callsFake(function() {
            return false;
        });

        var stubVisible = sinon.stub(layout, 'isVisible').callsFake(function() {
            return true;
        });

        expect(layout.hide()).toEqual(false);
    });

    it('should return the correct placeholder', function() {
        var layout = SugarTest.createComponent('Layout', {
            type: 'tree',
            module: 'Taxonomy',
            meta: {},
        });

        expect(layout.getPlaceholder()).toEqual(new Handlebars.SafeString('<span cid="' + layout.cid + '"></span>'));
    });

    it('should remove this.layout and this.context events', function() {
        var parentLayout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: []
        });
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            layout: parentLayout,
            meta: []
        });
        var stubLayoutOff = sinon.spy(parentLayout, "off");
        var context = new Context({});
        var stubContextOff = sinon.spy(context, "off");
        layout.context = context;

        layout.dispose();

        expect(stubLayoutOff).toHaveBeenCalled();
        expect(stubContextOff).toHaveBeenCalled();
    });

    it("should null out this.el and this.$el once a component has been disposed", function() {
        var layout = SugarTest.createComponent("Layout", {
            type: "tree",
            module: "Taxonomy",
            meta: {}
        });

        expect(layout.el).not.toBeNull();
        expect(layout.$el).not.toBeNull();

        layout.dispose();

        expect(layout.el).toBeNull();
        expect(layout.$el).toBeNull();
    });

    it("should invoke the parent chain correctly", function() {
        app.view.declareComponent("layout", "class1", null, {
            foo: function() {
                return "foo";
            }
        }, true, "base");
        app.view.declareComponent("layout", "class2", null, {
            extendsFrom: 'Class1Layout',
            foo: function() {
                return this._super("foo") + "bar";
            }
        }, true, "base");
        app.view.declareComponent("layout", "class3", null, {
            extendsFrom: 'Class2Layout',
            foo: function() {
                return this._super("foo") + "baz";
            }
        }, true, "base");

        var testInstance = new app.view.layouts.BaseClass3Layout({});

        expect(testInstance.foo()).toEqual("foobarbaz");

        delete app.view.layouts['BaseClass1Layout'];
        delete app.view.layouts['BaseClass2Layout'];
        delete app.view.layouts['BaseClass3Layout'];
    });

    it('should not introduce loops when calling _super from other methods invoked through _super', function() {
        app.view.declareComponent('layout', 'myBaseLayout', null, {
            foo: function() {
                return this.bar() + ',FOO!';
            },
            bar: function() {
                return 'BAR!';
            }
        }, true, 'base');
        app.view.declareComponent('layout', 'class0', null, {
            extendsFrom: 'MyBaseLayoutLayout'
        }, true, 'base');
        app.view.declareComponent('layout', 'class1', null, {
            extendsFrom: 'Class0Layout',
            foo: function() {
                return this._super('foo') + ',fc1';
            },
            bar: function() {
                return this._super('bar') + ',bz1';
            }
        }, true, 'base');
        app.view.declareComponent('layout', 'class2', null, {
            extendsFrom: 'Class1Layout',
            foo: function() {
                return this._super('foo') + ',' + this.bar() + ',fc2';
            }
        }, true, 'base');
        app.view.declareComponent('layout', 'class3', null, {
            extendsFrom: 'Class2Layout'
        }, true, 'base');
        app.view.declareComponent('layout', 'class4', null, {
            extendsFrom: 'Class3Layout'
        }, true, 'base');
        app.view.declareComponent('layout', 'class5', null, {
            extendsFrom: 'Class4Layout',
            foo: function() {
                return this._super('foo') + ',fc5';
            }
        }, true, 'base');

        /**
         * Expected Callstack explanation:
         * this._super('foo') + ',fc5';
         * this._super('foo') + ',' + this.bar() + ',fc2' + ',fc5';
         * this._super('foo') + ',fc1' + ',' + this._super('bar') + ',bz1' + ',fc2' + ',fc5';
         * this.bar() + ',FOO!' + ',fc1' + ',' + 'BAR' + ',bz1' + ',fc2' + ',fc5';
         * this._super('bar') + ',bz1' + ',FOO!' + ',fc1' + ',' + 'BAR' + ',bz1' + ',fc2' + ',fc5';
         * 'BAR' + ',bz1' + ',FOO!' + ',fc1' + ',' +'BAR' + ',bz1' + ',fc2' + ',fc5';
         * 'BAR,bz1,FOO!,fc1,BAR,bz1,fc2,fc5'
         */

        var testInstance = new app.view.layouts.BaseClass5Layout({});
        expect(testInstance.foo()).toEqual('BAR!,bz1,FOO!,fc1,BAR!,bz1,fc2,fc5');

        delete app.view.layouts['BaseMyBaseLayoutLayout'];
        delete app.view.layouts['BaseClass0Layout'];
        delete app.view.layouts['BaseClass1Layout'];
        delete app.view.layouts['BaseClass2Layout'];
        delete app.view.layouts['BaseClass3Layout'];
        delete app.view.layouts['BaseClass4Layout'];
    });

    describe('closestComponent', function() {
        it('should traverse upwards and find the closest component', function() {
            var mainLayout1 = SugarTest.createComponent('Layout', {
                    type: 'a-layout'
                }),
                mainLayout2 = SugarTest.createComponent('Layout', {
                    type: 'another-layout'
                }),
                childLayout = SugarTest.createComponent('Layout', {
                    type: 'child-layout'
                });

            mainLayout1.addComponent(mainLayout2);
            mainLayout2.addComponent(childLayout);

            var view = SugarTest.createComponent('Layout', {
                type: 'the-view',
                meta: {}
            });

            childLayout.addComponent(view);
            view.fields = {};

            var field = SugarTest.createComponent('Field', {
                def: { name: 'the-field', type: 'varchar' },
                view: view
            });

            expect(field.closestComponent('the-view')).toEqual(field.view);
            expect(field.closestComponent('child-layout')).toEqual(childLayout);
            expect(field.closestComponent('another-layout')).toEqual(mainLayout2);
            expect(field.closestComponent('a-layout')).toEqual(mainLayout1);
            expect(field.closestComponent('not-exists')).toBeUndefined();

            expect(view.closestComponent('child-layout')).toEqual(childLayout);
            expect(view.closestComponent('another-layout')).toEqual(mainLayout2);
            expect(view.closestComponent('a-layout')).toEqual(mainLayout1);
            expect(view.closestComponent('not-exists')).toBeUndefined();

            expect(mainLayout2.closestComponent('a-layout')).toEqual(mainLayout1);
            expect(mainLayout2.closestComponent('not-exists')).toBeUndefined();

            expect(mainLayout1.closestComponent('a-layout')).toBeUndefined();
        });
    });
});
