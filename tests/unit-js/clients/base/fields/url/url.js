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
describe("Url field", function() {

    var app, field, fieldName;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate("url", "field", "base", "detail");
        SugarTest.testMetadata.set();
        fieldName = "url";
        field = SugarTest.createField("base", fieldName, "url", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    describe("url widget", function() {
        it("should add http if missing on format and leave https and http alone", function() {
            var completeURL = "http://www.google.com";
            var completeHttpsURL = "https://www.google.com";
            var incompURL = "www.google.com";
            expect(field.format(completeURL)).toEqual(completeURL);
            expect(field.format(completeHttpsURL)).toEqual(completeHttpsURL);
            expect(field.format(incompURL)).toEqual(completeURL);
            expect(field.format('ftp:/ftp.example.edu/')).toEqual('ftp:/ftp.example.edu/');
        });
        it("should add the target window from the field definition to the anchor tag", function(){
            field.model.set(fieldName, "http://www.google.com");
            delete field.def.link_target;  //Default should be  _blank if link_target undefined
            field.render();
            expect(field.$('a').attr('target')).toEqual("_blank");
            field.def.link_target = '_blank';
            field.render();
            expect(field.$('a').attr('target')).toEqual("_blank");
            field.def.link_target = '_self';
            field.render();
            expect(field.$('a').attr('target')).toEqual("_self");
        });
        it("should support setting CSS class on HTML anchor", function(){
            field.model.set(fieldName, "http://www.google.com");
            field.def.css_class = "test";
            field.render();
            expect(field.$el.find('a')).toExist();
            expect(field.getFieldElement().is(".test")).toBe(true);
        });
        it("should support URLs with schemes other than HTTP/HTTPS", function(){
            expect(field.format("ftp://ftp.example.edu/")).toEqual("ftp://ftp.example.edu/");
            expect(field.format("mms://ftp.example.edu/")).toEqual("mms://ftp.example.edu/");
        });
    });
});
