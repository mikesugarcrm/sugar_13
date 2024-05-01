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
describe('Base.Layout.ConfigDrawer', function() {
    var app,
        context,
        layout,
        options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        sinon.stub(app.controller.context, 'get').callsFake(function() {
            return 'Opportunities'
        });

        sinon.stub(app.user, 'getAcls').callsFake(function() {
            return {
                Opportunities: {}
            }
        });

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                config: {
                    testSetting: 'testSetting'
                }
            }
        });

        options = {
            context: context
        };

        layout = SugarTest.createLayout('base', 'Opportunities', 'config-drawer', {}, context);
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
    });

    describe('initialize()', function() {
        var loadConfigSpy,
            blockModuleSpy;
        beforeEach(function() {
            loadConfigSpy = sinon.spy(layout, 'loadConfig');
            blockModuleSpy = sinon.spy(layout, 'blockModule');
        });

        afterEach(function() {
            options = null;
        });

        describe('checkAccess true', function() {
            beforeEach(function() {
                sinon.stub(layout, 'checkAccess').callsFake(function() {
                    return true;
                });
            });

            it('should clear anything on the context model', function() {
                options.context.get('model').set('trashSetting', 'blah');
                layout.initialize(options);

                expect(layout.context.get('model').has('trashSetting'))
                    .toBeFalsy('config-drawer context model should not have Module attributes in it');
            });

            it('should only load module metadata config attributes to the context model', function() {
                layout.initialize(options);
                expect(layout.context.get('model').get('testSetting')).toBe('testSetting');
            });

            it('should call loadConfig', function() {
                layout.initialize(options);
                expect(loadConfigSpy).toHaveBeenCalled('loadConfig should have been called');
                expect(blockModuleSpy).not.toHaveBeenCalled('blockModule should not have been called');
            });
        });
    });

    describe('_render', function() {
        var blockModuleSpy;
        beforeEach(function() {
            blockModuleSpy = sinon.spy(layout, 'blockModule');
        });

        afterEach(function() {
            options = null;
        });
        describe('checkAccess false', function() {
            it('should call blockModule', function() {
                sinon.stub(layout, 'checkAccess').callsFake(function() {
                    return false;
                });
                sinon.stub(layout, 'displayNoAccessAlert').callsFake(function() {});

                layout._render();
                expect(blockModuleSpy).toHaveBeenCalled('blockModule should have been called');
            });
        });

        describe('checkAccess false', function() {
            it('should call blockModule', function() {
                sinon.stub(layout, 'checkAccess').callsFake(function() {
                    return true;
                });
                sinon.stub(layout, '_super').callsFake(function() {});

                layout._render();
                expect(layout._super).toHaveBeenCalledWith('_render');
            });
        });
    });

    describe('loadConfig()', function() {
        var superSpy;

        beforeEach(function() {
            superSpy = sinon.spy(layout, '_super');
        });

        it('should call initialize() and loadData()', function() {
            layout.loadConfig(options);
            expect(superSpy).toHaveBeenCalledWith('loadData');
        });
    });

    describe('checkAccess()', function() {
        it('returns true when all 4 checks return true', function() {
            sinon.stub(layout, '_checkConfigMetadata').callsFake(function() { return true; });
            sinon.stub(layout, '_checkUserAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleConfig').callsFake(function() { return true; });
            expect(layout.checkAccess()).toBeTruthy();
        });

        it('returns false if _checkConfigMetadata returns false', function() {
            sinon.stub(layout, '_checkConfigMetadata').callsFake(function() { return false; });
            sinon.stub(layout, '_checkUserAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleConfig').callsFake(function() { return true; });
            expect(layout.checkAccess()).toBeFalsy();
        });

        it('returns false if _checkUserAccess returns false', function() {
            sinon.stub(layout, '_checkConfigMetadata').callsFake(function() { return true; });
            sinon.stub(layout, '_checkUserAccess').callsFake(function() { return false; });
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleConfig').callsFake(function() { return true; });
            expect(layout.checkAccess()).toBeFalsy();
        });

        it('returns false if _checkModuleAccess returns false', function() {
            sinon.stub(layout, '_checkConfigMetadata').callsFake(function() { return true; });
            sinon.stub(layout, '_checkUserAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() { return false; });
            sinon.stub(layout, '_checkModuleConfig').callsFake(function() { return true; });
            expect(layout.checkAccess()).toBeFalsy();
        });

        it('returns false if _checkModuleConfig returns false', function() {
            sinon.stub(layout, '_checkConfigMetadata').callsFake(function() { return true; });
            sinon.stub(layout, '_checkUserAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleAccess').callsFake(function() { return true; });
            sinon.stub(layout, '_checkModuleConfig').callsFake(function() { return false; });
            expect(layout.checkAccess()).toBeFalsy();
        });
    });

    describe('_checkConfigMetadata()', function() {
        it('returns true if the module has config metadata', function() {
            expect(layout._checkConfigMetadata()).toBeTruthy();
        });

        it('returns false if the module does not have config metadata', function() {
            app.metadata.getModule.restore();
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return []
            });
            expect(layout._checkConfigMetadata()).toBeFalsy();
        });
    });

    describe('_checkUserAccess()', function() {
        it('returns true if the user has access to the module', function() {
            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('returns false if the user does not have access to the module', function() {
            app.user.getAcls.restore();
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    Opportunities: {
                        access: 'no'
                    }
                }
            });
            expect(layout._checkUserAccess()).toBeFalsy();
        });
    });

    describe('_checkModuleAccess()', function() {
        it('returns true by default', function() {
            expect(layout._checkModuleAccess()).toBeTruthy();
        });
    });

    describe('_checkModuleConfig()', function() {
        it('returns true by default', function() {
            expect(layout._checkModuleConfig()).toBeTruthy();
        });
    });

    describe('blockModule()', function() {
        var noAccessSpy;

        beforeEach(function() {
            noAccessSpy = sinon.stub(layout, 'displayNoAccessAlert').callsFake(function() {});
            layout.accessUserOK = true;
            layout.accessModuleOK = true;
            layout.accessConfigOK = true;
        });

        it('should set alert message to user access message when accessUserOK is false', function() {
            layout.accessUserOK = false;
            layout.blockModule();
            expect(noAccessSpy).toHaveBeenCalledWith('LBL_CONFIG_BLOCKED_TITLE', 'LBL_CONFIG_BLOCKED_DESC_USER_ACCESS');
        });

        it('should set alert message to module access message when accessModuleOK is false', function() {
            layout.accessModuleOK = false;
            layout.blockModule();
            expect(noAccessSpy).toHaveBeenCalledWith('LBL_CONFIG_BLOCKED_TITLE', 'LBL_CONFIG_BLOCKED_DESC_MODULE_ACCESS');
        });

        it('should set alert message to config access message when accessConfigOK is false', function() {
            layout.accessConfigOK = false;
            layout.blockModule();
            expect(noAccessSpy).toHaveBeenCalledWith('LBL_CONFIG_BLOCKED_TITLE', 'LBL_CONFIG_BLOCKED_DESC_CONFIG_ACCESS');
        });
    });

    describe('displayNoAccessAlert()', function() {
        it('should call app.alert.show', function() {
            var alertShowStub = sinon.stub(app.alert, 'show').callsFake(function() {
                return {
                    getCloseSelector: function() {
                        return {
                            on: function() {}
                        };
                    }
                };
            });
            app.drawer = {
                close: function() {}
            };
            sinon.stub(app.drawer, 'close');
            sinon.stub(app.accessibility, 'run').callsFake(function() {});
            layout.displayNoAccessAlert('test', 'test');
            expect(alertShowStub).toHaveBeenCalled();
            expect(app.drawer.close).toHaveBeenCalled();

            app.drawer = undefined;
        });
    });
});
