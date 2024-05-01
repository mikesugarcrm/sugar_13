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
describe("Profile Actions", function() {

    var app, view, sinonSandbox, menuMeta;
    beforeEach(function() {
        var context;
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('profileactions', 'view', 'base');
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        sinon.stub(app.shortcuts, 'registerGlobal');
        view = SugarTest.createView("base","Accounts", "profileactions", null, context);
        sinonSandbox = sinon.createSandbox();
        menuMeta = [{
            acl_action: 'admin',
            label: 'LBL_ADMIN'
        },{
            acl_action: 'not_admin',
            acl_module: 'Accounts'
        }];
    });
    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        Handlebars.templates = {};
        view.dispose();
        view = null;
        menuMeta = null;
    });

    it('should show admin link together with normal link when user is an admin', function() {
        var stubAdmin = sinonSandbox.stub(app.acl, 'hasAccess').callsFake(function(action, module) {
            if (action === 'admin' && module === 'Administration') {
                return true;
            }
            if (module === 'Accounts') {
                return true;
            }
            return false;
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubAdmin).toHaveBeenCalled();
        expect(result.length).toEqual(2);
    });

    it('should show admin link together with normal link when user is a developer', function() {
        var stubDev = sinonSandbox.stub(app.acl, 'hasAccessToAny').callsFake(function(action) {
            return action === 'developer';
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubDev).toHaveBeenCalled();
        expect(result.length).toEqual(2);
    });

    it('should NOT show admin link when user is NOT an admin or a developer', function() {
        var notDev = sinonSandbox.stub(app.acl, 'hasAccessToAny').callsFake(function(action) {
            return false;
        });
        var notAdmin = sinonSandbox.stub(app.acl, 'hasAccess').callsFake(function(action, module) {
            return action === 'admin';
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(notDev).toHaveBeenCalled();
        expect(notAdmin).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });

    describe('Shortcuts button', function() {
        beforeEach(function() {
            sinon.spy(view, '$');
            sinon.stub(jQuery.fn, 'addClass');
            sinon.stub(app.api, 'isAuthenticated').returns(true);
            app.routing.start();
            app.drawer = {
                close: $.noop,
                open: $.noop,
                getActive: $.noop
            };
            sinon.stub(app.drawer, 'open').callsFake($.noop);
            sinon.stub(app.drawer, 'close').callsFake($.noop);
        });
        afterEach(function() {
            app.router.stop();
            sinon.restore();
        });
        it('should open shortcut drawer', function() {
            sinon.stub(app.drawer, 'getActive').returns(null);
            view.openShortcutsDrawer();
            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.drawer.close).not.toHaveBeenCalled();
        });
        it('should close shortcut drawer', function() {
            sinon.stub(app.drawer, 'getActive').returns({
                type: 'shortcuts'
            });
            view.openShortcutsDrawer();
            expect(app.drawer.open).not.toHaveBeenCalled();
            expect(app.drawer.close).toHaveBeenCalled();
        });
        it('should display if shortcuts are enabled and if user is authenticated', function() {
            sinon.stub(app.shortcuts, 'isEnabled').returns(true);
            view.render();

            expect(view.$).not.toHaveBeenCalledWith('.profileactions-shortcuts');
            expect(jQuery.fn.addClass).not.toHaveBeenCalledWith('hide');
        });
        it('should not display if shortcuts are enabled and if user is authenticated', function() {
            sinon.stub(app.shortcuts, 'isEnabled').returns(false);
            view.render();

            expect(view.$).toHaveBeenCalledWith('.profileactions-shortcuts');
            expect(jQuery.fn.addClass).toHaveBeenCalledWith('hide');
        });
    });

    describe('impersonation', function() {

        beforeEach(function() {
            app.config.tenant = 'srn:dev:iam:na:1234567890:tenant';
            menuMeta = [
                {label: 'LBL_LOGOUT'},
                {label: 'LBL_FINISH_IMPERSONATING'},
                {label: 'LBL_CHANGE_PASSWORD'},
            ];
        });

        it('should show log out and change password links in standart login session', function() {
            var result = view.filterAvailableMenu(menuMeta);
            expect(result.length).toEqual(2);
            expect(result[0].label).toEqual('LBL_LOGOUT');
            expect(result[1].label).toEqual('LBL_CHANGE_PASSWORD');
        });

        it('should show finish impersonating link in impersonating login session', function() {
            app.cache.set('ImpersonationFor', 'admin-user-id');

            var result = view.filterAvailableMenu(menuMeta);
            expect(result.length).toEqual(1);
            expect(result[0].label).toEqual('LBL_FINISH_IMPERSONATING');
        });

    });
});
