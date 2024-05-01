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
describe('Quotes.Layout.ConfigDrawer', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
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
                    Quotes: {
                        developer: 'no',
                        admin: 'no'
                    }
                };
            });
            app.user.get.restore();
            sinon.stub(app.user, 'get').callsFake(function() {
                return 'admin';
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should allow access when user has Quotes Developer role', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Quotes: {
                        admin: 'no'
                    }
                };
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should not allow access when user is not a System Admin nor Developer', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Quotes: {
                        developer: 'no',
                        admin: 'no'
                    }
                };
            });

            expect(layout._checkModuleAccess()).toBeFalsy();
        });
    });

    describe('_checkConfigMetadata()', function() {
        it('should return true', function() {
            expect(layout._checkConfigMetadata()).toBeTruthy();
        });
    });

    describe('loadData()', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'call');
        });

        afterEach(function() {

        });

        it('should call app.api.call when _checkModuleAccess is true', function() {
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() {
                return true;
            });
            layout.loadData();

            expect(app.api.call).toHaveBeenCalled();
        });

        it('should not call app.api.call when _checkModuleAccess is false', function() {
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() {
                return false;
            });
            layout.loadData();

            expect(app.api.call).not.toHaveBeenCalled();
        });
    });

    describe('onConfigSuccess()', function() {
        var response;

        beforeEach(function() {
            response = {
                dependentFields: 'depFields',
                relatedFields: 'relFields'
            };

            layout.onConfigSuccess(response);
        });

        afterEach(function() {
            response = null;
        });

        it('should set dependentFields on the context', function() {
            expect(layout.context.get('dependentFields')).toBe(response.dependentFields);
        });

        it('should set relatedFields on the context', function() {
            expect(layout.context.get('relatedFields')).toBe(response.relatedFields);
        });
    });
});
