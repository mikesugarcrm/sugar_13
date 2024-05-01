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
//FIXME: SC-4097 will rewrite this file properly.
describe("Base.View.SelectionList", function () {
    var view, layout, app, moduleName;
    beforeEach(function () {
        moduleName = 'Accounts';
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.loadComponent('base', 'view', 'selection-headerpane');
        SugarTest.testMetadata.addViewDefinition('list', {
            "panels":[
                {
                    "name":"panel",
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        { name: "phone_work" },
                        { name: "email1" },
                        { name: "phone_office" },
                        { name: "full_name" }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', moduleName, 'selection-list');
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it("Should add the selection field at the first field", function () {
        expect(view.leftColumns.length).toBe(1);
        expect('selection').toBe(view.leftColumns[0].type);
    });


    it("Should pass only the model's attributes that the user has the ACL view control", function () {
        var exptected_billing_phone = "111-000-9321",
            model = new Backbone.Model({
            id: "1234",
            name: "bob",
            billing_address: "should be undefined",
            billing_phone: exptected_billing_phone});
        var aclMapping = {
                billing_address: false,
                billing_phone: true
            },
            aclStub = sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                return aclMapping[field];
            });
        var actualAttributes = {

        };
        app.drawer = {
            close: function(attributes) {
                actualAttributes = attributes;
            }
        };
        view.context.set("selection_model", model);

        expect(actualAttributes['id']).toBe("1234");
        expect(actualAttributes['value']).toBe("bob");
        expect(actualAttributes['billing_address']).toBeUndefined();
        expect(actualAttributes['billing_phone']).toBe(exptected_billing_phone);
        aclStub.restore();
        delete app.drawer;
    });

    it('should remove all links except rowactions', function() {
        var htmlBefore = '<a href="javascript:void(0)">unwrapped</a><a class="rowaction" href="">wrapped</a>';
        var htmlAfter = 'unwrapped<a class="rowaction" href="">wrapped</a>';

        view.$el = $('<div>' + htmlBefore + '</div>');
        view.template = function() { return view.$el.html(); }
        view.render();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    it('should add preview row action', function() {
        var hasPreview = _.some(view.rightColumns, function(column) {
            return (column.event === "list:preview:fire");
        });
        expect(hasPreview).toBe(true);
    });
});
