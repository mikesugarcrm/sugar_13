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
describe('Base.View.ConfigHeaderButtons', function() {
    var app;
    var view;
    var module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'Opportunities';

        sinon.stub(app.lang, 'getModuleName').withArgs(module, {plural: true}).returns('Opps');

        view = SugarTest.createView('base', module, 'config-header-buttons');
        app.routing.start();
    });

    afterEach(function() {
        sinon.restore();
        view = null;
        app.router.stop();
    });

    describe('initialize()', function() {
        it('will have custom module name in moduleLangObj', function() {
            expect(view.moduleLangObj.module).toBe('Opps');
        });
    });

    describe('saveConfig()', function() {
        var button;

        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.stub(button, 'setDisabled').withArgs(true).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('will disable the save button', function() {
            sinon.stub(view, '_saveConfig');
            view.saveConfig();

            expect(button.setDisabled).toHaveBeenCalledWith(true);
        });

        it('will not disable if beforeSave returns false', function() {
            sinon.stub(view, 'triggerBefore').returns(false);
            view.saveConfig();

            expect(button.setDisabled).not.toHaveBeenCalled();
        });
    });

    describe('_saveConfig()', function() {
        var button;

        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.stub(button, 'setDisabled').withArgs(false).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('on xhr error will enable the button', function() {
            sinon.stub(app.api, 'call').callsFake(function(method, url, data, callbacks) {
                callbacks.error({});
            });

            view._saveConfig();
            expect(button.setDisabled).toHaveBeenCalledWith(false);
        });
    });

    describe('cancelConfig()', function() {
        beforeEach(function() {
            sinon.stub(app.router, 'goBack').callsFake(function() {});
        });
        it('should close the drawer if there is one', function() {
            app.drawer = {
                close: $.noop,
                count: function() {
                    return 1;
                }
            };
            sinon.spy(app.drawer, 'close');
            view.cancelConfig();

            expect(app.drawer.close).toHaveBeenCalled();
            delete app.drawer;
        });

        it('should navigate to the module if there is no drawer', function() {
            app.drawer = {
                count: function() {
                    return 0;
                }
            };
            sinon.spy(app.router, 'navigate');
            view.cancelConfig();

            expect(app.router.navigate).toHaveBeenCalledWith(module, {trigger: true});
            delete app.drawer;
        });
    });

    describe('_getSaveConfigURL()', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return view.module + '/config';
            });
        });

        it('should return the config url', function() {
            expect(view._getSaveConfigURL()).toBe(view.module + '/config');
        });
    });

    describe('_getSaveConfigAttributes()', function() {
        beforeEach(function() {
            view.model.set({
                test: 'test1'
            });
        });

        it('should return the attributes to be saved', function() {
            expect(view._getSaveConfigAttributes()).toEqual({
                test: 'test1'
            });
        });
    });

    describe('_beforeSaveConfig()', function() {
        it('should return true', function() {
            expect(view._beforeSaveConfig()).toBeTruthy();
        });
    });

    describe('showSavedConfirmation()', function() {
        var onStub;

        beforeEach(function() {
            onStub = sinon.stub();
            sinon.stub(app.alert, 'show').callsFake(function() {
                return {
                    getCloseSelector: function() {
                        return {
                            on: onStub
                        };
                    }
                };
            });
            sinon.stub(app.accessibility, 'run');
        });

        afterEach(function() {
            onStub = null;
        });

        it('should get the close selector and listen for the click event', function() {
            view.showSavedConfirmation($.noop);

            expect(onStub).toHaveBeenCalledWith('click');
        });

        it('should call app.accessibility.run', function() {
            view.showSavedConfirmation($.noop);

            expect(app.accessibility.run).toHaveBeenCalled();
        });
    });

    describe('cancelConfig()', function() {
        beforeEach(function() {
            app.drawer = {
                close: $.noop,
                count: $.noop
            };
            sinon.stub(app.drawer, 'close');
            sinon.stub(app.router, 'navigate');
        });

        afterEach(function() {
            delete app.drawer;
        });

        it('should call app.drawer.close if inside a drawer', function() {
            sinon.stub(app.drawer, 'count').callsFake(function() {
                return 1;
            });
            view.cancelConfig();

            expect(app.drawer.close).toHaveBeenCalled();
        });

        it('should call app.router.navigate if not inside a drawer', function() {
            sinon.stub(app.drawer, 'count').callsFake(function() {
                return 0;
            });
            view.cancelConfig();

            expect(app.router.navigate).toHaveBeenCalled();
        });
    });

    describe('_beforeCancelConfig()', function() {
        it('should return true', function() {
            expect(view._beforeCancelConfig()).toBeTruthy();
        });
    });
});
