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
describe('Users.Base.View.Massupdate', function() {
    let app;
    let layout;
    let view;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'layout', 'list');
        view = SugarTest.createView('base', 'Users', 'massupdate', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
    });

    describe('checkFieldAvailability', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').returns(true);
        });

        it('should return false for IDM fields in IDM mode', function() {
            app.config.idmModeEnabled = true;
            let result = view.checkFieldAvailability({
                idm_mode_disabled: true
            });
            expect(result).toBeFalsy();
        });

        it('should return true for IDM fields in non-IDM mode', function() {
            app.config.idmModeEnabled = false;
            let result = view.checkFieldAvailability({
                idm_mode_disabled: true
            });
            expect(result).toBeTruthy();
        });

        it('should return false for user_preference fields', function() {
            let result = view.checkFieldAvailability({
                user_preference: true
            });
            expect(result).toBeFalsy();
        });
    });
});
