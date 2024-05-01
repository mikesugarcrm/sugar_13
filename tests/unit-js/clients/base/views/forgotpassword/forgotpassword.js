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
describe("Forgotpassword View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('forgotpassword', {
            "panels": [
                {
                    "fields": [
                        {
                            "name": "username"
                        },
                        {
                            "name": "email"
                        },
                        {
                            "name": "first_name"
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Forgotpassword", "forgotpassword");
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe("Declare password Bean", function() {
        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.username)).toBeDefined();
            expect(_.size(view.model.fields.password)).toBeDefined();
        });
    });
    it("should be able make and handle successful password reset requests", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);
        var renderSpy = sinon.stub(view, 'render');
        //setup fake REST end-point for enum
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*rest\/v10\/password\/request\/*/,
            [200, { 'Content-Type': 'application/json'}, JSON.stringify(true)]);

        view.forgotPassword();
        SugarTest.server.respond();

        expect(view._showResult).toBeTruthy();
        expect(view._showSuccess).toBeTruthy();
        expect(view.resultLabel).toEqual('LBL_PASSWORD_REQUEST_SENT');
    });

    it("should be able make and handle failed password reset requests", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);
        var renderSpy = sinon.stub(view, 'render');
        //setup fake REST end-point for enum
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*rest\/v10\/password\/request\/*/,
            [424, { 'Content-Type': 'application/json'}, JSON.stringify({message:'LBL_PASSWORD_REQUEST_ERROR'})]);

        view.forgotPassword();
        SugarTest.server.respond();

        expect(view._showResult).toBeTruthy();
        expect(view._showSuccess).toBeFalsy();
        expect(view.resultLabel).toEqual('LBL_PASSWORD_REQUEST_ERROR');
    });

    it("should be able to not make requests if honeypot filled", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);

        var validMock = sinon.mock(view.model,'doValidate', function(arg,callback){
            callback(true);
        });
        var ajaxSpy = sinon.spy(jQuery,'ajax');
        var oValue = SugarTest.app.config.honeypot_on;
        SugarTest.app.config.honeypot_on = true;
        view.$el.append('<input name="first_name" value="bob">');
        view.forgotPassword();

        expect(ajaxSpy.called).toBeFalsy();

        SugarTest.app.config.honeypot_on = oValue;
        validMock.restore();
        ajaxSpy.restore();
    });

    it("should be able to not make requests if honeypot filled", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);
        view.model.set('first_name','testName');

        var validMock = sinon.mock(view.model,'doValidate', function(arg,callback){
            callback(true);
        });
        var ajaxSpy = sinon.spy(jQuery,'ajax');
        var oValue = SugarTest.app.config.honeypot_on;
        SugarTest.app.config.honeypot_on = true;

        view.forgotPassword();

        expect(ajaxSpy.called).toBeFalsy();

        SugarTest.app.config.honeypot_on = oValue;
        validMock.restore();
        ajaxSpy.restore();
    });
})
;
