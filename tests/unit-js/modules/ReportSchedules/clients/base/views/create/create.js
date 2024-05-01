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

describe('ReportSchedules.Base.Views.Create', function() {
    var app;
    var view;
    var context;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        sandbox.stub(SugarTest.app.api, 'call');
        var parentModel = app.data.createBean('ReportSchedules');
        var userModel = new app.Bean();
        userModel.set({id: 'user_id'});
        parentModel.getRelatedCollection = function() { return {models: [userModel]}; };
        var currentModel = app.data.createBean('ReportSchedules');
        currentModel.set({id: 'rs_id'});
        context = new app.Context();
        context.set({
            model: currentModel
        });
        context.parent = new app.Bean();
        context.parent.set({model: parentModel});
        context.prepare();
        view = SugarTest.createView('base', 'ReportSchedules', 'create', null, context, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        app = null;
    });

    describe('copyExistingUsers()', function() {
        it('should copy existing users', function() {
            var url = 'ReportSchedules/rs_id/link/users/user_id';
            view.copyExistingUsers();
            expect(SugarTest.app.api.call.called).toBe(true);
            expect(SugarTest.app.api.call.getCall(0).args[2].requests[0].method).toEqual('POST');
            expect(SugarTest.app.api.call.getCall(0).args[2].requests[0].url).toMatch(url);
        });
    });

    describe('linkCurrentUser()', function() {
        beforeEach(function() {
            sandbox.stub(app.user, 'save').callsFake(function() {});
            sandbox.stub(app.data, 'createRelatedBean').callsFake(function() {
                return app.user;
            });
        });

        it('should link current user', function() {
            view.linkCurrentUser();
            expect(app.user.save).toHaveBeenCalledOnce();
        });
    });
});
