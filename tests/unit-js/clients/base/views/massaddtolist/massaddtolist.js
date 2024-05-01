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
describe("Base.View.Massaddtolist", function() {

    var view, app, layout, router,
        convertedFieldDef = {
            'name': 'prospect_lists_name',
            'id_name': 'prospect_lists_id',
            'type': 'relate',
            'relationship': 'prospect_list_contacts',
            'module': 'ProspectLists',
            'source': 'non-db',
            'vname': 'LBL_PROSPECT_LIST',
            'label': 'LBL_PROSPECT_LIST',
            'required': true
        };

    beforeEach(function() {
        app = SugarTest.app;
        let stub = sinon.stub(app.metadata, 'getModule').returns({
            'fields': [{
                'name': 'prospect_lists',
                'type': 'link',
                'relationship': 'prospect_list_contacts',
                'module': 'ProspectLists',
                'source': 'non-db',
                'vname': 'LBL_PROSPECT_LIST'
            }]
        });
        SugarTest.loadComponent('base', 'view', 'massupdate');
        layout = SugarTest.createLayout('base', 'Contacts', 'list');
        view = SugarTest.createView("base", "Contacts", "massaddtolist", null, null, null, layout);
        stub.restore();
        view.model = new Backbone.Model();

        //mock out buildRoute - save existing router so we can put it back
        router = app.router;
        app.router = {
            buildRoute: sinon.stub()
        };
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.model = null;
        view = null;
        layout.dispose();
        app.router = router;
    });

    it("should generate its field from appropriate list field in metadata", function() {
        var expected = convertedFieldDef,
            actual = view.addToListField;

        expect(actual).toEqual(expected);
    });

    it("should set the default option to the list field", function(){
        view.setDefault();

        var expected = convertedFieldDef,
            actual = view.defaultOption;

        expect(actual).toEqual(expected);
    });

    it("should build the appropriate attributes array for the api", function() {
        view.model.set('prospect_lists_name', 'Foo');
        view.model.set('prospect_lists_id', '123');

        var actual = view.getAttributes(),
            expected = {
                'prospect_lists': ['123']
            };

        expect(actual).toEqual(expected);
    });

    it("should hide the view if appropriate list field is not found in the metadata", function() {
        delete view.addToListField; //simulate field not found
        view.visible = true;
        view.render();
        expect(view.visible).toBe(false);
    });

    it("should generate appropriate success messages if entire result set selected", function() {
        var massUpdateModel = {
            getAttributes: function() {
                return {
                    massupdate_params: {
                        entire: true
                    }
                }
            }
        };
        var successMessages = view.buildSaveSuccessMessages(massUpdateModel);
        expect(successMessages['done']).toBe('TPL_MASS_ADD_TO_LIST_SUCCESS');
        expect(successMessages['queued']).toBe('TPL_MASS_ADD_TO_LIST_QUEUED');
    });

    it("should generate appropriate success messages if a subset selected", function() {
        var massUpdateModel = {
            getAttributes: function() {
                return {
                    massupdate_params: {
                        entire: false,
                        uid: [
                            '123',
                            '456'
                        ]
                    }
                }
            }
        };
        var successMessages = view.buildSaveSuccessMessages(massUpdateModel);
        expect(successMessages['done']).toBe('TPL_MASS_ADD_TO_LIST_SUCCESS');
        expect(successMessages['queued']).toBe('TPL_MASS_ADD_TO_LIST_QUEUED');
    });

});
