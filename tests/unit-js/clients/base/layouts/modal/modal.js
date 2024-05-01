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
xdescribe("Base.Layout.Modal", function() {

    var app, view, context, layout, parent, actual;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        actual = null;
        SugarTest.loadComponent("base", "layout", "modal");
        SugarTest.loadComponent("base", "view", "modal-header");
        SugarTest.loadComponent("base", "view", "modal-confirm");
        parent = app.view.createLayout({type: 'base'});
        if (!$.fn.modal) {
            $.fn.modal = function(options) {};
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        $.fn.modal = null;
        layout.context = null;
        layout.dispose();
        parent.dispose();
        layout = null;
        parent = null;
        actual = null;
    });

    it("should delegate triggers at contruction time", function(){
        var expected = 'app:layout:modal:open1',
            options = {
                'showEvent' : expected
            };
        var spy = sinon.spy(parent, "on");
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(spy).toHaveBeenCalledOnce();
        expect(spy.args[0][0]).toEqual(expected);
        spy.restore();
    });

    it("should delegate multiple trigger names for showevent", function(){
        var expected = ['editpopup', 'detailpopup'],
            options = {
                'showEvent' : expected
            };

        sinon.spy(parent, "on");
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(parent.on.calledWith('editpopup')).toBe(true);
        expect(parent.on.calledWith('detailpopup')).toBe(true);

    });

    it("should build proper modal dom elements", function(){
        var expected = 'app:layout:modal:open',
            options = {
                'showEvent' : expected
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(layout.$(".modal").length).toEqual(1);
        expect(layout.$(".modal-body").length).toEqual(0);
        var comp = {
            el: 'foo container'
        }
        layout._placeComponent(comp, { layout: 'popup-list', bodyComponent: true});
        expect(layout.$(".modal-body").length).toEqual(1);
        expect(layout.$(".modal-body").html()).toEqual('foo container');
    });


    it("should create dynamic components at the event trigger time", function(){
        let expectedLayout = 'list';
        let options = {
            showEvent: 'app:layout:modal:open',
            callback: sinon.stub()
        };
        let expectedModule = 'Accounts';
        let components = {
            components: [
                {
                    view: expectedLayout,
                    context: {
                        module: expectedModule
                    }
                }
            ]
        };

        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        let stub = sinon.stub(layout, 'loadData').callsFake(function() {
            var objectToFetch = context.get("modelId") ? context.get("model") : context.get("collection");
            objectToFetch.dataFetched = false;
        });

        //Add one layout component
        parent.trigger('app:layout:modal:open', components, stub);
        var actualLayout = layout._components[layout._components.length-1].options.name,
            actualModule = layout._components[layout._initComponentSize].module,
            actualContext = (layout.getBodyComponents())[0].context.parent;

        actualContext.trigger('modal:callback');
        expect(stub).toHaveBeenCalled();


        /*expect(layout._components.length).toEqual(layout._initComponentSize + 1);
        expect(actualLayout).toEqual(expectedLayout);
        expect(actualModule).toEqual(expectedModule);
        var actualContext = (layout.getBodyComponents())[0].context.parent;
        expect(_.has(actualContext._callbacks, 'modal:callback')).toBe(true);
        expect(_.has(actualContext._callbacks, 'modal:close')).toBe(true);
        expect(_.has(actualContext._callbacks, 'modal:undefined')).toBe(false);

        //Add two components
        var previousLayout = expectedLayout,
            expectedComponent = [ {view: 'test-list'}, {view: 'test'} ];
        parent.trigger('app:layout:modal:open', {
            components: expectedComponent,
            context: { module: expectedModule }
        });
        //it should clean out the previous components and append only new components
        var expected = undefined, //empty
            actual = _.find(layout._components, function(component) {
                return (component.options.name == previousLayout);
            }),
            actualComponent = layout.getBodyComponents();
        expect(actual).toBe(expected);
        expect(actualComponent.length).toEqual(expectedComponent.length);*/
        stub.restore();
    });

    it("should create a simple modal dialog", function(){
        var options = {
                'showEvent' : 'app:layout:modal:open'
            },
            expectedMessage = 'blah',
            expectedTitle = 'foo boo',
            confirmDialog = {
                'message' : expectedMessage,
                'title' : expectedTitle
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        parent.trigger('app:layout:modal:open', confirmDialog);
        var actualTitle = layout.getComponent("modal-header").title,
            actualMessage = _.first(layout.getBodyComponents()).message,
            expectedModalName = "modal-confirm",
            actualModalName = _.first(layout.getBodyComponents()).name;
        expect(actualTitle).toEqual(expectedTitle);
        expect(actualMessage).toEqual(expectedMessage);
        expect(actualModalName).toEqual(expectedModalName);
    });

    it("should adjust the modal width size", function() {
        var options = {
                'showEvent' : 'app:layout:modal:open'
            },
            confirmDialog = {
                'message' : 'blah',
                'title' : 'foo boo'
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        layout.show(_.extend({width: 4}, confirmDialog));
        expect(layout.$(".modal").width()).toBe(4);

        layout.show(_.extend({width: 5}, confirmDialog));
        expect(layout.$(".modal").width()).toBe(5);
        expect(layout.$(".modal").width()).not.toBe(4);

        layout.show(_.extend({}, confirmDialog));
        expect(layout.$(".modal").width()).not.toBe(5);
        expect(layout.$(".modal").width()).not.toBe(4);
    });


    it("should invoke before/after while modal is showing and hiding", function() {
        var options = {
                'showEvent' : 'app:layout:modal:open',
                'components' : [ { view: 'blah' }]
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        layout.triggerBefore = function(event) {
            sinon.stub();
        };

        layout.trigger = function(event) {
            sinon.stub();
        };
        var showOptions = {'blah' : 'yeahhh'};
        sinon.spy(layout, "triggerBefore");
        sinon.spy(layout, "trigger");

        layout.show({options: showOptions});
        expect(layout.triggerBefore.calledWith('show')).toBe(true);
        expect(layout.triggerBefore.calledWith('hide')).toBe(false);
        layout.hide();
        expect(layout.triggerBefore.calledWith('hide')).toBe(true);
    });
});
