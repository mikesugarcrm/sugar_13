
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

describe("ForecastWorksheets.Field.Parent", function () {

    var app, field, buildRouteStub, moduleName = 'ForecastWorksheets', _oRouter;
    var createListViewAvatarStub;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'relate');
        SugarTest.loadComponent('base', 'field', 'parent');

        var fieldDef = {
            "name": "parent_name",
            "rname": "name",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
            "link": "accounts",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };

        // Workaround because router not defined yet
        _oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function(){}};
        buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute').callsFake(function(module, id, action, params) {
            return module+'/'+id;
        });

        field = SugarTest.createField("base", "parent", 'parent', 'list', fieldDef, moduleName, null, null, true);
        createListViewAvatarStub = sinon.stub(field, '_createListViewAvatar');
    });

    afterEach(function() {
        buildRouteStub.restore();
        createListViewAvatarStub.restore();
        SugarTest.app.router = _oRouter;
        field = null;
        app = null;
    });

    it('field.options.viewName should undefined', function() {
        field.model = new Backbone.Model({'parent_deleted': 0});
        field.render();
        expect(_.isUndefined(field.options.viewName)).toBeTruthy();
    });

    it('field.options.viewName should equal deleted', function() {
        field.model = new Backbone.Model({'parent_deleted': 1});
        field.render();
        expect(_.isUndefined(field.options.viewName)).toBeFalsy();
        expect(field.options.viewName).toEqual('deleted');
        expect(field.deleted_value).not.toBeEmpty();
    });
});
