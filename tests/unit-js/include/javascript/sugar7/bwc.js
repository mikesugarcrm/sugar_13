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
describe("sugar7.extensions.bwc", function() {
    var app, module, id, action, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.createSandbox();
        app = SugarTest.app;
        module = "Foo";
        action = "EditView";
        id = '12345';
    });
    afterEach(function() {
        sinonSandbox.restore();
        module = null;
        action = null;
        id = null;
    });
    it("should have a login method", function() {
        var stub = sinon.stub(app.api, 'call');
        app.bwc.login('path/to/foo');
        expect(stub.called).toBe(true);
        expect(stub.args[0][0]).toEqual('create');
        expect(stub.args[0][1].match(/oauth2.bwc.login/)).not.toEqual(null);
        stub.restore();
    });
    it("should build a bwc route given module, action, id", function() {
        var expected, actual;
        expected = "bwc/index.php?module=" + module + "&action=" + action + "&record=" +id;
        actual = app.bwc.buildRoute(module, id, action);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module (no action or id provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module and id (no action provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=DetailView&record=" + id;
        actual = app.bwc.buildRoute(module, id);
        expect(actual).toEqual(expected);
    });
    it("should build bwc for module and action (no id) respecting caller's choices unless DetailView", function() {
        var actual, expected;
        // action could be a list view or whatever and we should respect wishes in this case
        // module=Quotes&action=ListView
        // module=Quotes&action=EditView (which goes to Create)
        expected = "bwc/index.php?module=" + module + "&action=" + action;
        actual = app.bwc.buildRoute(module, null, action);
        expect(actual).toEqual(expected);

        // But! If they're asking for action DetailView, with no id, we DO force
        // to action=index since detail with no id just doesn't make sense
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module, null, 'DetailView');
        expect(actual).toEqual(expected);
    });

    describe('_handleRelatedRecordSpecialCases', function() {
        var parentModel;
        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin',
                full_name: 'John Smith'
            });
            parentModel._syncedAttributes = {};
            parentModel.getSynced = function() {
                return parentModel._syncedAttributes;
            };
            parentModel.module = 'Contacts';
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should handle Contacts with meetings link special case', function() {
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "meetings");
            expect(params['parent_type']).toBe('Accounts');
            expect(params['parent_id']).toBe(parentModel.get('account_id'));
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
            expect(params['parent_name']).toBe(parentModel.get('account_name'));
            expect(params['contact_id']).toBe(parentModel.get('id'));
            expect(params['contact_name']).toBe(parentModel.get('full_name'));
        });

        it('should handle Contacts with calls link special case', function() {
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "calls");
            expect(params['parent_type']).toBe('Accounts');
            expect(params['parent_id']).toBe(parentModel.get('account_id'));
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
            expect(params['parent_name']).toBe(parentModel.get('account_name'));
            expect(params['contact_id']).toBe(parentModel.get('id'));
            expect(params['contact_name']).toBe(parentModel.get('full_name'));
        });

        //Fix for SP-1600: Account information is not populated during Quote creation via Opportunity Quote Subpanel
        it('should handle Opportunities with quotes link special case', function() {
            parentModel.module = 'Opportunities';
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "quotes");
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
        });

        it('will add account_id and account_name when link equals quotes regardless of module', function() {
            parentModel.module = 'Yahoo';
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "quotes");
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
        });

        it('will add contact_id when module is contacts', function() {
            parentModel.module = 'Contacts';
            parentModel.set('id', 'my_contact_id');
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "quotes");
            expect(params['contact_id']).toBe(parentModel.get('id'));
        });

        it('will add contact_id when parentModel contains contact_id', function() {
            parentModel.module = 'Yahoo';
            parentModel.set('contact_id', 'my_contact_id');
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, "quotes");
            expect(params['contact_id']).toBe(parentModel.get('contact_id'));
        });

        it('will use the syncedAttributes value', function() {
            parentModel.module = 'Opportunities';
            parentModel._syncedAttributes = {
                account_id: 'my_test_account_id',
                account_name: 'my_test_account_name'
            };
            var params = app.bwc._handleRelatedRecordSpecialCases({}, parentModel, 'quotes');
            expect(params['account_id']).toBe(parentModel._syncedAttributes.account_id);
            expect(params['account_name']).toBe(parentModel._syncedAttributes.account_name);
        });
    });

    describe('_createRelatedRecordUrlParams', function() {
        var parentModel, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            });
            parentModel._syncedAttributes = {};
            parentModel.getSynced = function() {
                return parentModel._syncedAttributes;
            };
            relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields').callsFake(function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });

        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields in URL when creating a new BWC record', function() {
            var params = app.bwc._createRelatedRecordUrlParams(parentModel, "test");
            expect(params['product_template_id']).toBe(parentModel.get('id'));
            expect(params['product_template_name']).toBe(parentModel.get('name'));
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
            expect(params['user_name']).toBe(parentModel.get('assigned_user_name'));
        });

    });

    describe('shareRecord', function() {
        beforeEach(function() {
            sinonSandbox.stub(app.template, 'getView').callsFake(function() {
                return $.noop;
            });
            sinonSandbox.stub(app.bwc, '_launchExternalEmail');
            SugarTest.loadPlugin('EmailClientLaunch');
            SugarTest.loadComponent('base', 'field', 'button');
            SugarTest.loadComponent('base', 'field', 'rowaction');
            SugarTest.loadComponent('base', 'field', 'emailaction');
            SugarTest.loadComponent('base', 'field', 'shareaction');
            sinonSandbox.stub(app.utils, 'openEmailCreateDrawer');
        });

        it('should launch external email client if preferred', function() {
            sinonSandbox.stub(app.user, 'getPreference').callsFake(function() {
                return {type: 'mailto'};
            });
            app.bwc.shareRecord(module, id, 'name123');
            expect(app.bwc._launchExternalEmail).toHaveBeenCalledOnce();
            expect(app.utils.openEmailCreateDrawer).not.toHaveBeenCalled();
        });

        it('should launch internal email client if preferred', function() {
            sinonSandbox.stub(app.user, 'getPreference').callsFake(function() {
                return {type: 'sugar'};
            });
            app.bwc.shareRecord(module, id, 'name123');
            expect(app.bwc._launchExternalEmail).not.toHaveBeenCalled();
            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledOnce();
        });
    });

    describe('getAction', function() {
        it('should convert Sidecar actions to BWC actions', function() {
            expect(app.bwc.getAction('create')).toEqual('EditView');
            expect(app.bwc.getAction('edit')).toEqual('EditView');
            expect(app.bwc.getAction('detail')).toEqual('DetailView');
        });

        it('should return the action itself if no specific BWC action exists', function() {
            expect(app.bwc.getAction('some-random-action')).toEqual('some-random-action');
        });
    });
});
