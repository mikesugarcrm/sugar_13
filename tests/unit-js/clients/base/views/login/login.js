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
describe("Login View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('login', {
            'panels': [
                {
                    'fields': [
                        {
                            'name': 'username',
                            'type': 'text',
                            'required': true
                        },
                        {
                            'name': 'password',
                            'type': 'password',
                            'required': true
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Login", "login");
        app = SUGAR.App;
    });

    afterEach(function() {
        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinon.restore();
        Handlebars.templates = {};
        view = null;
    });

    describe("Declare Login Bean", function() {

        //Internet Explorer
        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.username)).toBeDefined();
            expect(_.size(view.model.fields.password)).toBeDefined();
        });
    });

    describe('handle keypress', function() {
        it('should trigger login if `ENTER` key is pressed', function() {
            sinon.stub(view, 'login');
            var evt = $.Event('keypress', {keyCode: 13}),    //ENTER key
                evt2 = $.Event('keypress', {keyCode: 16});   //SHIFT key
            view.handleKeypress(evt);

            expect(view.login).toHaveBeenCalled();

            view.handleKeypress(evt2);

            expect(view.login.calledOnce).toBeTruthy();
        });
    });

    describe('postLogin', function() {
        beforeEach(function() {
            sinon.stub(app.user, 'get')
                .withArgs('show_wizard').returns(false)
                .withArgs('cookie_consent').returns(true);
        });

        it('should only refresh additional components when wizard is not shown', function() {
            sinon.spy(view, 'refreshAdditionalComponents');
            view.postLogin();

            expect(view.refreshAdditionalComponents).toHaveBeenCalled();

            app.user.get.withArgs('show_wizard').returns(true);
            view.postLogin();

            expect(view.refreshAdditionalComponents.calledOnce).toBeTruthy();
        });

        it('should only pop alert of different timezone if timezones do not match', function() {
            sinon.spy(app.alert, 'show');
            sinon.stub(Date.prototype, 'getTimezoneOffset').returns(420);
            sinon.stub(app.user, 'getPreference')
                .withArgs('tz_offset_sec').returns(420 * (-30));
            view.postLogin();

            expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.offsetProblem);

            app.user.getPreference.withArgs('tz_offset_sec').returns(420 * (-60));
            view.postLogin();

            expect(app.alert.show.calledOnce).toBeTruthy();
        });
    });

    describe('fields patching', function() {
        //FIXME: Enforce with `required` => false in metadata once it is implemented (SC-3106)
        it('should enforce that `username` and `password` fields are required', function() {
            _.each(view.meta.panels[0].fields, function(field) {
                expect(field.required).toEqual(true);
            });
        });
    });

    describe('logging in', function() {
        //FIXME: Login fields should trigger model change (SC-3106)
        it('should set the username and password in the model', function() {
            sinon.stub(view, '$')
                .withArgs('input[name=password]').returns({
                    val: function() {
                        return 'pass';
                    }
                })
                .withArgs('input[name=username]').returns({
                    val: function() {
                        return 'user';
                    }
                });
            sinon.stub(view.model, 'doValidate');

            view.login();

            expect(view.model.get('password')).toEqual('pass');
            expect(view.model.get('username')).toEqual('user');
        });

        it('should pass exact username and password to the API', function() {
            sinon.stub(view.model, 'doValidate').callsFake(function(fields, callback) {
                callback(true);
            });
            //FIXME: Use field values instead (SC-3106)
            sinon.stub(view, '$')
                .withArgs('input[name=password]').returns({
                    val: function() {
                        return 'pass';
                    }
                })
                .withArgs('input[name=username]').returns({
                    val: function() {
                        return 'user';
                    }
                });
            sinon.stub(app, 'login');

            view.login();

            expect(app.login).toHaveBeenCalledWith({password: 'pass', username: 'user'});
        });

        describe('successful login', function() {
            beforeEach(function() {
                sinon.stub(view.model, 'doValidate').callsFake(function(fields, callback) {
                    callback(true);
                });
                sinon.spy(app.alert, 'show');
                sinon.spy(app.alert, 'dismiss');
                sinon.stub(app, 'login').callsFake(function(credentials, info, callbacks) {
                    callbacks.success();
                    callbacks.complete({'xhr': {'status': 401}});
                });
            });

            it('should only show `loading...` alert while processing the login', function() {
                view.login();

                expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.login);
                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.login);
            });

            it('should dismiss login alerts upon successfully logging in', function() {
                view.login();

                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.needLogin);
                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.invalidGrant);
            });

            it('should handle post login events once successfully logged in', function() {
                sinon.stub(view, 'postLogin');
                view.login();
                app.events.trigger('app:sync:complete');
                expect(view.postLogin).toHaveBeenCalled();
            });
        });

        describe('unsuccessful login', function() {
            it('should not do anything if model is not valid', function() {
                sinon.stub(view.model, 'doValidate').callsFake(function(fields, callback) {
                    return callback(false);
                });
                sinon.spy(app.alert, 'show');

                view.login();

                expect(app.alert.show).not.toHaveBeenCalled();
            });
        });
    });

    describe('refreshAdditionalComponents', function() {
        it('should render each additional component', function() {
            var originalComponents = app.additionalComponents;
            app.additionalComponents = [
                {'render' : $.noop},
                {'render' : $.noop}
            ];
            sinon.spy(app.additionalComponents[0], 'render');
            sinon.spy(app.additionalComponents[1], 'render');

            view.refreshAdditionalComponents();

            expect(app.additionalComponents[0].render).toHaveBeenCalled();
            expect(app.additionalComponents[1].render).toHaveBeenCalled();

            app.additionalComponents = originalComponents;
        });
    });

    describe('render', function() {
        it('should set logoUrl as the one from metadata', function() {
            sinon.stub(app.metadata, 'getLogoUrl').callsFake(function() {
                return 'LOGO_URL';
            });

            view.render();

            expect(view.logoUrl).toEqual('LOGO_URL');
        });

        it('should render additional components', function() {
            sinon.spy(view, 'refreshAdditionalComponents');

            view.render();

            expect(view.refreshAdditionalComponents).toHaveBeenCalled();
        });

        it('should show `admin only` alert if `admin_only` is set in the config', function() {
            sinon.spy(app.alert, 'show');
            sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                return {'system_status': {'level': 'admin_only'}};
            });

            view.render();

            expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.adminOnly);
        });
    });

    describe('formatLanguageList', function() {
        let languageListStub;
        beforeEach(function() {
            languageListStub = sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                return {
                    '': '',
                    en_us: 'English (US)',
                    fr_FR: 'French',
                    it_it: 'Italiano',
                    nl_NL: 'Nederlands'
                };
            });
        });
        afterEach(function() {
            languageListStub.restore();
        });
        it('should format an array of language objects', function() {
            var expected = [
                {key: 'en_us', value: 'English (US)'},
                {key: 'fr_FR', value: 'French'},
                {key: 'it_it', value: 'Italiano'},
                {key: 'nl_NL', value: 'Nederlands'}
            ];
            expect(view.formatLanguageList()).toEqual(expected);
        });

    });
});
