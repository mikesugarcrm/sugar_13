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
describe("ListEditable Plugin", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
    });

    it('should have custom templates for list-edit and list-disabled', function() {
        app.view.declareComponent("field", "testListEditField", null, {
            plugins: ["ListEditable"]
        }, true, "base");
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };

        field = SugarTest.createField({
            loadJsFile: false,
            type: "testListEditField",
            viewName: "edit",
            fieldDef: def
        });

        expect(field.view.action).not.toBe('list');
        field._loadTemplate();
        expect(field.tplName).toBe('edit');

        field.view.action = 'list';
        field._loadTemplate();
        expect(field.tplName).toBe('list-edit');

        field.setDisabled(true);
        expect(field.tplName).toBe('list-disabled');

        field.view.action = 'edit';
        field.setDisabled(false);
        expect(field.tplName).toBe('edit');
    });

});
