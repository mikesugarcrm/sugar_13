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
describe("radioenum field", function() {
    var app, field, fields = {}, stub_appListStrings,
        fieldName = 'test_radioenum',
        fieldType = 'radioenum',
        original, expected, actual;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'list');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'list-edit');
        SugarTest.testMetadata.set();
        sinon.stub(app.lang, 'getAppListStrings').returns({
            '': '',
            Defect: 'DefectValue',
            Feature: 'FeatureValue'
        });

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }

        //Because this controller depends on enum and listeditable controllers
        fields.enumField = SugarTest.createField("base", "test_enum", "enum", "edit", {options: "bugs_type_dom"});
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
        fields = {};
    });

    it("should call loadEnumOptions and set items during render", function() {
        var loadEnumSpy = sinon.spy(app.view.fields.BaseEnumField.prototype, "loadEnumOptions");
        var field = SugarTest.createField("base", fieldName, fieldType, "edit", {options: "bugs_type_dom"});
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual(app.lang.getAppListStrings());
    });

    it("format a list of radio buttons and have one selected on edit template", function() {
        var field = fields.radioenum = SugarTest.createField("base", fieldName, fieldType, "edit", {options: "bugs_type_dom"});

        original = 'Defect';
        expected = ' ' + 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        actual = field.$('input[type=radio]:checked').closest('label').text();
        expect(actual).toEqual(expected);
    });

    it("use enum controller to have option selected in Select2 widget on list edit template", function() {
        var field = fields.radioenum = SugarTest.createField("base", fieldName, fieldType, "edit", {options: "bugs_type_dom"});

        field.view.action = 'list';

        expected = 'Defect';
        field.model.set(fieldName, expected);
        field.render();
        actual = field.$('input').select2('val');
        expect(actual).toEqual(expected);
    });
});
