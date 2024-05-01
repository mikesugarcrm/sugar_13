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
describe("Email Text field", function() {
    var app, model, stubEmailAddress, field, sinonSandbox,
        primaryEmail = 'primary@email.com',
        nonPrimaryEmail = 'nonprimary@email.com';

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base", "email-text", "email-text", "edit");
        sinonSandbox = sinon.createSandbox();
        stubEmailAddress =  [{
                email_address: primaryEmail,
                primary_address: "1"
            }, {
                email_address: nonPrimaryEmail,
                primary_address: "0",
                opt_out: "1"
            }];
        model = field.model;

        model.set({email:_.clone(stubEmailAddress)});
    });
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field  = null;
        sinonSandbox.restore();
    });

    describe("format email address", function() {
        it("should return unadulterated value if not an array", function() {
            var actual = field.format('foo');
            expect(actual).toEqual('foo');
            actual = field.format('');
            expect(actual).toEqual('');
            actual = field.format(null);
            expect(actual).toEqual(null);
            actual = field.format(undefined);
            expect(actual).toEqual(undefined);
        });
        it("should return primary_address email if value is an array", function() {
            var email = model.get('email');
            var actual = field.format(email);
            expect(actual).toEqual(primaryEmail);
        });
        it("should return empty string if email has no primary_address", function() {
            var actual = field.format([{
                email_address: nonPrimaryEmail,
                primary_address: "0",
                opt_out: "1"
            }]);
            expect(actual).not.toEqual(primaryEmail);
            expect(actual).toEqual('');
        });
    });
    describe("unformat email address", function() {
        var setModelStub, triggerOnModelStub;
        beforeEach(function() {
            setModelStub = sinonSandbox.stub(model, 'set');
            triggerOnModelStub = sinonSandbox.stub(model, 'trigger');
        });
        it("should return email with primary_address mutated to value passed in", function() {
            var newemail = 'newPrimary@email.com';
            var actual = field.unformat(newemail);
            expect(actual).toContain({
                email_address: newemail,
                primary_address: "1"
            });
            expect(actual).toContain({
                email_address: nonPrimaryEmail,
                primary_address: "0",
                opt_out: "1"
            });
            // Secondary check that model set and event triggered
            expect(setModelStub).toHaveBeenCalled();
            expect(triggerOnModelStub).toHaveBeenCalled();
        });
        it("should only change if primary_address is different", function() {
            var actual = field.unformat(primaryEmail);//same as already set on primary
            expect(actual).toEqual(model.get('email'));
            // Secondary check that model NOT set; and event NOT triggered
            expect(setModelStub).not.toHaveBeenCalled();
            expect(triggerOnModelStub).not.toHaveBeenCalled();
        });
        it("should return email array with one email when no email in model", function() {
            field.model = new Backbone.Model({email: []});
            setModelStub = sinonSandbox.stub(field.model, 'set');
            triggerOnModelStub = sinonSandbox.stub(field.model, 'trigger');
            var actual = field.unformat(primaryEmail);//same as already set on primary
            expect(actual[0].email_address).toEqual(primaryEmail);
            expect(actual.length).toEqual(1);
            // Secondary check that model set and event triggered
            expect(setModelStub).toHaveBeenCalled();
            expect(triggerOnModelStub).toHaveBeenCalled();
        });
    });

});
