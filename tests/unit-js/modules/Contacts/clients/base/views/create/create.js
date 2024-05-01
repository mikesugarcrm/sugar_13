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
describe("Contacts Create View", function() {
    let moduleName = 'Contacts';
    let app;
    let viewName = 'create';
    let view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadFile('../modules/Contacts/clients/base/plugins', 'ContactsPortalMetadataFilter', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record', moduleName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": ["name"]
                },
                {
                    "name": "panel_body",
                    "label": "LBL_PANEL_2",
                    "columns": 1,
                    "labels": true,
                    "labelsOnTop": false,
                    "placeholders": true,
                    //Portal specific fields
                    "fields": ["portal_name", "portal_active"]
                },
                {
                    "name": "panel_hidden",
                    "hide": true,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": ["created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        //Fake portal is inactive
        sinon.stub(app.metadata, 'getServerInfo').returns({
            portal_active: false
        });
        view = SugarTest.createView("base", moduleName, viewName, null, null);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('Render', function() {
        it("Should not render portal fields if portal is disabled", function() {
            expect(_.size(view.meta.panels[0].fields)).toEqual(1);
            expect(_.size(view.meta.panels[1].fields)).toEqual(0);
            expect(_.size(view.meta.panels[2].fields)).toEqual(4);
        });
    });
});
