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
describe('Base.Email', function() {

    var app, field, model, mock_addr;
    var module = 'Accounts';
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit-email-field');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();

        mock_addr =  [
            {
                email_address_id: '4e571e68-4e28-11e8-9911-3c15c2d582c6',
                email_address: "test1@test.com",
                primary_address: true
            },
            {
                email_address_id: '4e2b1c14-4e28-11e8-8a41-3c15c2d582c6',
                email_address: "test2@test.com",
                primary_address: false,
                opt_out: true
            }
        ];

        model = app.data.createBean(module, {email: app.utils.deepCopy(mock_addr)});
        model.fields = {
            email1: {
                required: true
            }
        };
        field = SugarTest.createField("base","email", "email", "edit", undefined, undefined, model);

        field.render();

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        field = null;
    });

    describe("initial rendering", function() {
        it("should display two email addresses and a field to add a new address", function() {
            expect(field.$('.existingAddress').length).toBe(2);
            expect(field.$('.newEmail').length).toBe(1);
        });
        it("should set first email as the primary address", function() {
            expect(field.$('[data-emailproperty=primary_address]').eq(0).hasClass('active')).toBe(true);
        });
    });

    describe("adding an email address", function() {
        it("should add email addresses on the model when there is change on the input", function() {
            var emails;

            field.$('.newEmail')
                .val("test3@test.com")
                .trigger('change');

            emails = model.get('email');
            expect(emails[2]).toBeDefined();
            expect(emails[2].email_address).toBe("test3@test.com");
        });
        it("should add email addresses when add email button is clicked", function() {
            var emails;

            field.$('.newEmail').val("test3@test.com");
            field.$('.addEmail').click();

            emails = model.get('email');
            expect(emails[2]).toBeDefined();
            expect(emails[2].email_address).toBe("test3@test.com");
        });
        it("should clear out the new email field", function(){
            var newEmailField = field.$('.newEmail')
                .val("test3@test.com")
                .change();

            expect(newEmailField.val()).toBe('');
        });
        it("should not allow duplicates", function(){
            field.$('.newEmail')
                .val('test2@test.com')
                .trigger('change');

            expect(model.get('email').length).toBe(2);
        });
        it("should make the email address primary if it is the only address", function(){
            var emails;

            model.clear();
            field.render();
            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
            expect(field.$('[data-emailproperty=primary_address]').hasClass('active')).toBe(true);
        });
        it("should not make the email address primary if there are existing email addresses", function(){
            var emails;

            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(3);
            expect(emails[2].primary_address).toBe(false);
            expect(field.$('[data-emailproperty=primary_address]').eq(2).hasClass('active')).toBe(false);
        });
        it('should default invalid_email to false or undefined', function() {
            var emails;

            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(3);
            expect(emails[2].invalid_email).toBeFalsy();
            expect(field.$('[data-emailproperty=invalid_email]').eq(2).hasClass('active')).toBe(false);
        });

        using('opt_out_config', [true, false], function(config) {
            it('should default opt_out to the value of the config', function() {
                var emails;

                app.config.newEmailAddressesOptedOut = config;

                field.$('.newEmail').val('foo@test.com').trigger('change');

                emails = model.get('email');
                expect(emails.length).toBe(3);
                expect(emails[2].opt_out).toBe(config);
                expect(field.$('[data-emailproperty=opt_out]').eq(2).hasClass('active')).toBe(config);
            });
        });
    });

    describe("updating an email address", function() {
        it("should update email addresses on the model", function() {
            var emails;

            field.$('input')
                .first()
                .val("testChanged@test.com")
                .trigger('change');

            emails = model.get('email');
            expect(emails[0].email_address).toBe("testChanged@test.com");
        });
        it("should delete empty email address field", function(){
            var emails;

            field.$('.existingAddress')
                .first()
                .val('')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].email_address).toBe('test2@test.com');
        });
        it("should make the first email address primary if primary email address is emptied", function(){
            var emails;

            field.$('.existingAddress')
                .first()
                .val('')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
        });
    });

    describe("removing an email address", function() {
        it("should delete email addresses on the model", function() {
            var emails = model.get('email');
            expect(emails.length).toBe(2);

            field.$('.removeEmail')
                .first()
                .trigger('click');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].email_address).toBe('test2@test.com')
        });
        it("should select another primary e-mail address if the primary is deleted", function(){
            var emails = model.get('email');
            expect(emails.length).toBe(2);
            expect(emails[0].primary_address).toBe(true);
            expect(emails[1].primary_address).toBe(false);

            field.$('.removeEmail')
                .first()
                .trigger('click');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
            expect(field.$('[data-emailproperty=primary_address]').hasClass('active')).toBe(true);
        });
    });

    describe("updating email properties", function() {
        it("should update opt_out when opt out button is toggled", function() {
            expect(model.get('email')[0].opt_out).toBeUndefined();

            field.$('[data-emailproperty=opt_out]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].opt_out).toBe(true);

            field.$('[data-emailproperty=opt_out]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].opt_out).toBe(false);
        });
        it("should update invalid_email when invalid button is toggled", function() {
            expect(model.get('email')[0].invalid_email).toBeUndefined();

            field.$('[data-emailproperty=invalid_email]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].invalid_email).toBe(true);

            field.$('[data-emailproperty=invalid_email]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].invalid_email).toBe(false);
        });
        it("should update primary_address only when non-primary email address is clicked", function() {
            expect(model.get('email')[0].primary_address).toBe(true);

            field.$('[data-emailproperty=primary_address]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].primary_address).toBe(true);

            field.$('[data-emailproperty=primary_address]')
                .last()
                .trigger('click');

            expect(model.get('email')[0].primary_address).toBe(false);
            expect(model.get('email')[1].primary_address).toBe(true);
        });
    });

    describe("changing the model", function() {
        it("should update the view with the updated values in detail mode", function() {
            var newValue = app.utils.deepCopy(mock_addr);
            newValue[0].email_address = 'foo@test.com';
            newValue[1].opt_out = false;

            field.setMode('detail');

            expect(field.$('a').eq(0).text().trim()).toBe('test1@test.com');
            expect(field.$('a').eq(1).text().trim()).toBe('test2@test.com');

            field.model.set('email', newValue);

            expect(field.$('a').eq(0).text().trim()).toBe('foo@test.com');
            expect(field.$('a').eq(1).text().trim()).toBe('test2@test.com');
        });
        it("should not render the view with the updated values in edit mode", function() {
            var newValue = app.utils.deepCopy(mock_addr);
            newValue[0].email_address = 'foo@test.com';
            newValue[1].opt_out = false;

            expect(field.$('input').eq(0).val()).toBe('test1@test.com');
            expect(field.$('[data-emailproperty=opt_out]').eq(1).hasClass('active')).toBe(true);

            field.model.set('email', newValue);

            expect(field.$('input').eq(0).val()).toBe('test1@test.com');
            expect(field.$('[data-emailproperty=opt_out]').eq(1).hasClass('active')).toBe(true);
        });

        using('different modes', ['detail', 'list'], function(mode) {
            it('should have the ban icon when an opted out email address is rendered', function() {
                var newValue = app.utils.deepCopy(mock_addr);
                newValue[0].opt_out = true;
                newValue[1].opt_out = false;

                field.setMode(mode);

                expect(field.$('a').eq(0).hasClass('opt-out')).toBe(false);
                expect(field.$('a').eq(1).hasClass('opt-out')).toBe(true);

                field.model.set('email', newValue);

                expect(field.$('a').eq(0).hasClass('opt-out')).toBe(true);
                expect(field.$('a').eq(1).hasClass('opt-out')).toBe(false);
            });
        });
    });

    describe("decorating error", function() {
        it("should decorate each invalid email fields", function(){
            var $inputs = field.$('input');
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({email: ["test2@test.com"]});
            expect(field.$('.add-on').length).toEqual(1);
            // on touch devices this will never pass
            //expect(field.$('.add-on').data('original-title')).toEqual('ERROR_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(1);
        });
        it("should decorate the first field if there isn't any primary address set", function(){
            var $inputs = field.$('input');
            var emails = model.get('email');
            emails[0].primary_address = false;
            emails[1].primary_address = false;
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({primaryEmail: true});
            expect(field.$('.add-on').length).toEqual(1);
            // on touch devices this will never pass
            //expect(field.$('.add-on').data('original-title')).toEqual('ERROR_PRIMARY_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(0);
        });
    });

    describe("format and unformat", function() {
        it("should create flag email strings", function() {
            var testAddresses =[
                {
                    email_address: "test1@test.com",
                    primary_address: true
                },
                {
                    email_address: "test2@test.com",
                    primary_address: true,
                    opt_out: true
                }
            ];;
            field.addFlagLabels(testAddresses);
            expect(testAddresses[0].flagLabel).toEqual("LBL_EMAIL_PRIMARY");
            expect(testAddresses[1].flagLabel).toEqual("LBL_EMAIL_PRIMARY, LBL_EMAIL_OPT_OUT");
        });

        it("should make an email address a link when metadata allows for links and the address is not opted out or invalid", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       false,
                        invalid_email: false
                    }
                ],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeTruthy();
            expect(actual[1].hasAnchor).toBeTruthy();
        });

        it("should not make an email address a link when metadata doesn't allow for links", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       false,
                        invalid_email: false
                    }
                ],
                actual;

            field.def.emailLink = false;
            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
            expect(actual[1].hasAnchor).toBeFalsy();
        });

        it('should make an email address a link when the address is opted out', function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       true,
                    invalid_email: false
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeTruthy();
        });

        it("should not make an email address a link when the address is invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       false,
                    invalid_email: true
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is opted out and invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       true,
                    invalid_email: true
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should convert a string representing an email address into an array containing one object", function() {
            var expected = {
                email_address: 'foo@bar.com',
                primary_address: true,
                hasAnchor: true,
                soleEmail: true,
                flagLabel: 'LBL_EMAIL_PRIMARY',
                confirmation_url: ''
            };

            var actual = field.format(expected.email_address);
            expect(actual.length).toBe(1);
            expect(actual[0]).toEqual(expected);
        });

        it("should still work when model value is not already set on edit in list view (SP-604)", function() {
            var expected = "abc@abc.com",
                emails = "abc@abc.com",
                actual;

            field.view.action = "list";
            field.model.set({email : ""});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

            field.model.set({email : undefined});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

        });

        it("should empty string model value as an empty list of e-mails (MAR-667)", function() {
            var actual;

            field.model.set("");
            actual = field.format("");
            expect(actual).toEqual("");

        });

        it("should return only a single primary email address as the value in the list view", function() {
            field = SugarTest.createField("base","email", "email", "list");
            field.render();

            var new_email_address = 'test@blah.co',
                new_assigned_email = field.unformat(new_email_address),
                expected = new_email_address,
                actual;

            actual = (_.find(new_assigned_email, function(email){
                return email.primary_address;
            })).email_address;
            expect(actual).toBe(expected);
        });

        it('should return empty array when value is an empty array', function() {
            var value = [];
            var actual = field.format(value);
            expect(actual.length).toBe(value.length);
        });

        it('should have email address, id, primary_address and flagLabel when passed as an object', function() {
            var value = {email_address: 'a@a.com', id: 'email_id', primary_address: false};
            var actual = field.format(value);
            var expectedAttributes = {
                email_address: 'a@a.com',
                email_address_id: 'email_id',
                flagLabel: '',
                primary_address: false,
                soleEmail: true,
            };

            expect(actual[0].email_address).toBe(expectedAttributes.email_address);
            expect(actual[0].email_address_id).toBe(expectedAttributes.email_address_id);
            expect(actual[0].flagLabel).toBe(expectedAttributes.flagLabel);
            expect(actual[0].primary_address).toBe(expectedAttributes.primary_address);
            expect(actual[0].soleEmail).toBe(expectedAttributes.soleEmail);
        });
    });

    describe('when required', function() {
        it('field def will have required as true', function() {
            expect(field.def.required).toBeTruthy();
        });

        it('should add the placeholder when all addresses are removed', function() {
            sandbox.stub(field, 'decorateRequired');

            // Remove the two email addresses.
            field.$('.removeEmail').first().click();
            field.$('.removeEmail').first().click();

            expect(field.decorateRequired).toHaveBeenCalledOnce();
        });

        it('field will remove the required placeholder after add has been called', function() {
            // FIXME: The placeholder should not be there because we already
            // have 2 email addresses created with the field. We remove it here
            // for the test, but it needs to be done in the code.
            var $el = field._getNewEmailField();
            var label = app.lang.get('LBL_REQUIRED_FIELD', this.module);
            $el.prop('placeholder', $el.prop('placeholder').replace('(' + label + ') ', ''));

            // Remove the 2 email adresses to display to add the placeholder.
            field.$('.removeEmail').first().click();
            field.$('.removeEmail').first().click();
            // make sure we have the LBL_REQUIRED_FIELD in the placeholder
            expect($el.prop('placeholder')).toContain('LBL_REQUIRED_FIELD');
            // set the value and add it
            $el.val('test@test.com');
            field.$('.addEmail').first().click();
            // make sure we don't have the LBL_REQUIRED_FIELD in the place holder
            expect(field._getNewEmailField().prop('placeholder')).not.toContain('LBL_REQUIRED_FIELD');

        });


    });

    describe('copying the confirmation link', function() {
        var field2;

        beforeEach(function() {
            field.$el.appendTo('body');

            field2 = SugarTest.createField('base', 'email', 'email', 'detail', null, module, model);
            field2.render();
            field2.$el.appendTo('body');
        });

        afterEach(function() {
            field.$el.remove();
            field2.$el.remove();
            field2.dispose();
        });

        it('should not have a copy confirmation link button if the mode is not detail', function() {
            var buttons = field.$('button[data-clipboard="enabled"]');

            expect(buttons.length).toBe(0);
        });

        it('should only have a copy confirmation link button if the email address is opted out', function() {
            var buttons = field2.$('button[data-clipboard="enabled"]');
            var clipboardText = buttons.data('clipboard-text');

            expect(buttons.length).toBe(1);
            expect(clipboardText).toMatch(
                /^.*\?entryPoint=ConfirmEmailAddress&email_address_id=4e2b1c14-4e28-11e8-8a41-3c15c2d582c6$/
            );
        });

        it('should update the email address when the copy confirmation link button is clicked', function() {
            var call;
            var bean = app.data.createBean('EmailAddresses', {id: '4e2b1c14-4e28-11e8-8a41-3c15c2d582c6'});

            sandbox.stub(app.alert, 'show');
            sandbox.stub(bean, 'save');
            sandbox.stub(app.data, 'createBean')
                .withArgs('EmailAddresses', {id: '4e2b1c14-4e28-11e8-8a41-3c15c2d582c6'})
                .returns(bean);

            // Stub the copy command.
            sandbox.stub(document, 'execCommand').returns(true);

            field2.$('button[data-clipboard="enabled"]').click();

            expect(bean.get('confirmation_requested_on')).not.toBeEmpty();
            expect(bean.save).toHaveBeenCalledOnce();

            call = app.alert.show.getCall(0);
            expect(app.alert.show).toHaveBeenCalledOnce();
            expect(call.args[0]).toBe('clipboard');
            expect(call.args[1].level).toBe('success');
        });
    });
});
