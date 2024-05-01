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
describe('Base.View.OmnichannelButton', function() {
    var view;
    var sandbox;
    var app = SUGAR.App;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('omnichannel-button', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-button');
        app.config.awsConnectInstanceName = 'instance1';
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('_renderHTML()', function() {
        using('different values for general and config availability', [
            {
                isAvailable: true,
                configAvailable: true,
                buttonExpected: true,
                kebabExpected: true,
            }, {
                isAvailable: true,
                configAvailable: false,
                buttonExpected: true,
                kebabExpected: false,
            }, {
                isAvailable: false,
                configAvailable: true,
                buttonExpected: false,
                kebabExpected: false,
            }, {
                isAvailable: false,
                configAvailable: false,
                buttonExpected: false,
                kebabExpected: false,
            }
        ], function(values) {
            it('should display the main button and kebab as expected', function() {
                sandbox.stub(view, '_isAvailable').returns(values.isAvailable);
                sandbox.stub(view, '_configAvailable').returns(values.configAvailable);
                view.render();
                expect(!!view.$('[data-action=omnichannel]').length).toEqual(values.buttonExpected);
                expect(!!view.$('.config-menu.dropdown').length).toEqual(values.kebabExpected);
            });
        });
    });

    describe('_isAvailable', function() {
        using('different licenses, auth states, and connect states', [
            {
                licenses: ['SUGAR_SERVE'],
                authenticated: true,
                instanceName: 'connectInstance',
                expected: true
            }, {
                licenses: ['SUGAR_SERVE', 'SUGAR_SELL'],
                authenticated: true,
                instanceName: 'connectInstance',
                expected: true
            }, {
                licenses: ['SUGAR_SELL'],
                authenticated: true,
                instanceName: 'connectInstance',
                expected: true
            }, {
                licenses: ['CURRENT'],
                authenticated: true,
                instanceName: 'connectInstance',
                expected: false
            }, {
                licenses: ['SUGAR_SERVE'],
                authenticated: false,
                instanceName: 'connectInstance',
                expected: false
            }, {
                licenses: ['SUGAR_SERVE'],
                authenticated: true,
                instanceName: '',
                expected: false
            }, {
                licenses: ['SUGAR_SERVE'],
                authenticated: true,
                instanceName: undefined,
                expected: false
            }
        ], function(values) {
            it('should return true only if a sell/serve user is authenticated in an instance with ' +
                'connect enabled', function() {
                sandbox.stub(app.user, 'get')
                    .returns(values.licenses)
                    .withArgs('cookie_consent').returns(true)
                    .withArgs('show_wizard').returns(false);
                sandbox.stub(app.api, 'isAuthenticated').returns(values.authenticated);
                app.config.awsConnectInstanceName = values.instanceName;
                var actual = view._isAvailable();
                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('_configAvailable', function() {
        using('combinations of licenses and admin status', [
            {
                licenses: ['SUGAR_SERVE'],
                type: 'admin',
                expected: true
            }, {
                licenses: ['SUGAR_SERVE', 'SUGAR_SELL'],
                type: 'admin',
                expected: true
            }, {
                licenses: ['SUGAR_SELL'],
                type: 'admin',
                expected: true
            }, {
                licenses: ['SUGAR_SERVE'],
                type: 'group',
                expected: false
            }, {
                licenses: ['SUGAR_SERVE'],
                type: '',
                expected: false
            }, {
                licenses: ['CURRENT'],
                type: 'admin',
                expected: false
            },
        ], function(values) {
            it('should only be available to Serve and Sell Admins', function() {
                sandbox.stub(app.user, 'get');
                app.user.get.withArgs('licenses').returns(values.licenses);
                app.user.get.withArgs('type').returns(values.type);
                var actual = view._configAvailable();
                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('setStatus()', function() {
        beforeEach(function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return true;
            });
            sandbox.stub(app.user, 'get')
                .returns(['SUGAR_SERVE'])
                .withArgs('show_wizard').returns(false);
            view.render();
        });

        it('should change status to active-session', function() {
            view.setStatus('active-session');
            expect(view.$('.btn').attr('class')).toBe('btn active-session');
            expect(view.status).toBe('active-session');
        });

        it('should change status to logged-out', function() {
            view.setStatus('logged-out');
            expect(view.$('.btn').attr('class')).toBe('btn logged-out');
            expect(view.status).toBe('logged-out');
        });

        it('should change status to logged-in', function() {
            view.setStatus('logged-in');
            expect(view.$('.btn').attr('class')).toBe('btn logged-in');
            expect(view.status).toBe('logged-in');
        });
    });

    describe('notifyUser', function() {
        beforeEach(function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return true;
            });
            sandbox.stub(app.user, 'get')
                .returns(['SUGAR_SERVE'])
                .withArgs('show_wizard').returns(false);
            view.render();
        });

        it('should add notify class to button', function() {
            sandbox.stub(view, '_getConsole').returns({
                isOpen: function() { return false; }
            });
            view._notifyUser();
            expect(view.$('.btn').hasClass('notification-pulse')).toBe(true);
        });
    });

    describe('_clearNotifications', function() {
        beforeEach(function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return true;
            });
            sandbox.stub(app.user, 'get').callsFake(function() {
                return ['SUGAR_SERVE'];
            });
            view.render();
        });

        it('should remove notification-pulse class', function() {
            view.$('.btn').addClass('notification-pulse');
            view._clearNotifications();
            expect(view.$('.btn').hasClass('notification-pulse')).toBe(false);
        });
    });
});
