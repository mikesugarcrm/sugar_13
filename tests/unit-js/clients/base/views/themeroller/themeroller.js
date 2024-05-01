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
describe("Theme Roller View", function() {

    var app, view, themeApiStub;

    beforeEach(function() {
        if (!$.fn.colorpicker) {
            $.fn.colorpicker = function() {};
        }
        app = SugarTest.app;
        themeApiStub = sinon.stub(app.api, "call");
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "themeroller", null, context);
    });
    afterEach(function() {
        themeApiStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it("should get input values and store them in the context", function() {
        $('<input>').attr({type:"text", name:"a", value:"aaaa"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"b", value:"bbbb"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"c", value:"cccc"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"d", value:"dddd"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"e", value:"eeee"}).addClass("bgvar").appendTo(view.$el);

        view.previewTheme();
        expect(view.context.get('colors')).toEqual({
            a: "aaaa",
            b: "bbbb",
            c: "cccc",
            d: "dddd",
            e: '"eeee"'
        });
    });

    it("should make right api call", function() {
        var url, platform = 'portal';
        $('<input>').attr({type:"text", name:"a", value:"aaaa"}).appendTo(view.$el);

        //Describe loadTheme
        var showMessageSpy = sinon.stub(view, "showMessage");
        view.loadTheme();

        url = app.api.buildURL('theme', '', {}, {platform: platform, themeName: "default"});
        expect(themeApiStub.lastCall.args[0]).toEqual("read");
        expect(themeApiStub.lastCall.args[1]).toEqual(url);
        expect(themeApiStub.lastCall.args[2]).toEqual({});

        //Describe saveTheme
        view.saveTheme();
        url = app.api.buildURL('theme', '', {}, {});
        expect(themeApiStub.lastCall.args[0]).toEqual("create");
        expect(themeApiStub.lastCall.args[1]).toEqual(url);
        expect(themeApiStub.lastCall.args[2]).toEqual({a: "aaaa", platform: platform, themeName: "default"});

        //Describe resetTheme
        var alertStub = sinon.stub(app.alert, 'show').callsFake(function(key, args) {
           args.onConfirm();
        });
        view.resetTheme();
        expect(themeApiStub.lastCall.args[0]).toEqual("create");
        expect(themeApiStub.lastCall.args[1]).toEqual(url);
        expect(themeApiStub.lastCall.args[2]).toEqual({reset: true, platform: platform, themeName: "default"});

        //Restore stubs
        alertStub.restore();
        showMessageSpy.restore();
    });


    it("should parse less vars and add an @ to relate variables", function() {
        view.lessVars = {
            rel: [
                {"name": "TheVar", value: "@TheRelatedVar"}
            ]
        };
        view.parseLessVars();
        expect(view.lessVars.rel[0].relname).toEqual("TheRelatedVar");
    });
});
