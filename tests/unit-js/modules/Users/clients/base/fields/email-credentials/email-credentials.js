
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
describe('Base.Users.EmailCredentials', function() {
    let app;
    let field;

    beforeEach(function() {
        app = SugarTest.app;

        sinon.stub(app.api, 'call');

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField(
            'base',
            'mail_credentials',
            'email-credentials',
            'detail',
            {},
            'Users',
            null,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
    });

    describe('_sendTestEmailClicked', function() {
        let modalEl;

        beforeEach(function() {
            modalEl = {
                addClass: sinon.stub()
            };

            field.model.set('mail_credentials', {
                mail_smtpserver: 'fake.smtp.com',
                mail_authtype: '',
                mail_smtptype: 'google',
                mail_smtpuser: 'fakeUserName',
                mail_smtppass: 'fakePassword!',
                eapm_id: '',
                authorized_account: ''
            });
        });

        it('should call the testUserOverride endpoint with the correct arguments', function() {
            // From address comes from the primary email
            field.model.set('email', [
                {
                    email_address: 'user_email@example.com',
                    primary_address: true
                }
            ]);

            // User ID comes from the model
            field.model.set('id', 'fakeID123');

            // Full name comes from the model
            field.model.set('full_name', 'Fake Name');

            // To address comes from the input field
            // Modal shoudl be hidden
            sinon.stub(field, '$')
                .withArgs('input.test-address').returns({
                    val: function() {
                        return 'fake_to_address@example.com';
                    }
                })
                .withArgs('.test-email-dialog').returns(modalEl);

            field._sendTestEmailClicked();

            expect(modalEl.addClass).toHaveBeenCalledWith('hide');
            expect(app.api.call).toHaveBeenCalledWith('create', jasmine.any(String), jasmine.objectContaining({
                user_id: 'fakeID123',
                name: 'Fake Name',
                from_address: 'user_email@example.com',
                to_address: 'fake_to_address@example.com',
                mail_smtpuser: 'fakeUserName',
                mail_smtppass: 'fakePassword!',
                eapm_id: ''
            }), jasmine.any(Object));
        });
    });

    describe('_getFromAddress', function() {
        it('should get the "from" address to use in an email test', function() {
            field.model.set('email', [
                {
                    email_address: 'not_the_right_one@example.com',
                    primary_address: false
                },
                {
                    email_address: 'the_right_one@example.com',
                    primary_address: true
                }
            ]);

            expect(field._getFromAddress()).toEqual('the_right_one@example.com');
        });
    });
});
