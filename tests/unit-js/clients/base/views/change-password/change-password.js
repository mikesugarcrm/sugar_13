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
describe('View.Views.Base.ChangePasswordView', function() {
    let app;
    let view;

    beforeEach(function() {
        app = SugarTest.app;
        app.config.passwordsetting = {
            minpwdlength: 6,
            onelower: true
        };
        app.config.honeypot_on = true;

        let meta = {
            fields: [
                {
                    name: 'current_password',
                    type: 'password',
                    required: true,
                },
                {
                    name: 'new_password',
                    type: 'password',
                    required: true,
                },
                {
                    name: 'new_password_confirm',
                    type: 'password',
                    required: true,
                },
                {
                    name: 'name_field',
                    type: 'text'
                },
            ]
        };
        view = SugarTest.createView('base', null, 'change-password', meta);
    });

    afterEach(function() {
        sinon.restore();
        app.view.reset();
    });

    describe('_initPasswordRequirements', function() {
        it('should only add requirements if they are set in config', function() {
            view._initPasswordRequirements();
            expect(view._requirements.minpwdlength).not.toBeUndefined();
            expect(view._requirements.onelower).not.toBeUndefined();
            expect(view._requirements.maxpwdlength).toBeUndefined();
        });
    });

    describe('_validatePasswords', function() {
        it('should return false if the password and confirm password do not match', function() {
            expect(view._validatePasswords('password1', 'password2')).toEqual(false);
        });

        it('should return false if the password does not match the requirements', function() {
            // Password is long enough, but has no lowercase letters
            expect(view._validatePasswords('PASSWORD1', 'PASSWORD1')).toEqual(false);

            // Password has lowercase letters, but is not long enough
            expect(view._validatePasswords('pass1', 'pass1')).toEqual(false);
        });

        it('should return true if all password conditions are satisfied', function() {
            expect(view._validatePasswords('password1', 'password1')).toEqual(true);
        });
    });

    describe('_updatePassword', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'updatePassword');
        });

        it('should not update the password if the honeypot is touched', function() {
            view.model.set('name_field', 'My name');
            view._updatePassword('currentPassword1', 'newPassword1');
            expect(app.api.updatePassword).not.toHaveBeenCalled();
        });

        it('should call the password update API with the updated password value', function() {
            view._updatePassword('currentPassword1', 'newPassword1');
            expect(app.api.updatePassword).toHaveBeenCalledWith('currentPassword1', 'newPassword1');
        });
    });
});
