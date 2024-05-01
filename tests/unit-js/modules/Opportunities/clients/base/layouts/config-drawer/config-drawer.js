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
describe('Opportunities.Layout.ConfigDrawer', function() {
    var app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Opportunities', 'config-drawer', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
    });

    describe('_checkModuleAccess()', function() {
        beforeEach(function() {
            sinon.stub(app.user, 'get').callsFake(function() {
                return 'user';
            });
        });

        it('should allow access when user is System Admin', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Opportunities: {
                        developer: 'no',
                        admin: 'no'
                    }
                }
            });
            app.user.get.restore();
            sinon.stub(app.user, 'get').callsFake(function() {
                return 'admin';
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should allow access when user has Opportunities Developer role', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Opportunities: {
                        admin: 'no'
                    }
                }
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should not allow access when user is Opportunities Admin', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Opportunities: {
                        developer: 'no'
                    }
                }
            });

            expect(layout._checkModuleAccess()).toBeFalsy();
        });

        it('should not allow access when user is not a System Admin nor Developer', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Opportunities: {
                        developer: 'no',
                        admin: 'no'
                    }
                }
            });

            expect(layout._checkModuleAccess()).toBeFalsy();
        });
    });
});
