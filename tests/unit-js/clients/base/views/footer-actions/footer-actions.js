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
describe('Base.View.FooterActions', function () {
    var view,
        sandbox,
        app = SUGAR.App;

    beforeEach(function () {
        sandbox = sinon.createSandbox();
        sandbox.stub(app.shortcuts, 'registerGlobal');
        // doWhen needs to be stubed out so it doesn't continue to run
        // and possibly fail
        sandbox.stub(app.utils, 'doWhen');
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('footer-actions', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'footer-actions');
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Shortcuts button', function() {
        it('should display if shortcuts are enabled and if user is authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled').callsFake(function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).not.toBe(0);
        });

        it('should not display if shortcuts are disabled', function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled').callsFake(function() {
                return false;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });

        it('should not display if user is not authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated').callsFake(function() {
                return false;
            });
            sandbox.stub(app.shortcuts, 'isEnabled').callsFake(function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });
    });

    describe('Help button', function() {
        using('different layout names', [
            {
                layoutName: 'notBwc'
            },
            {
                layoutName: null
            }
        ], function(data) {
            it('should not open bwc help', function() {
                var bwcHelpClickedStub = sandbox.stub(view, 'bwcHelpClicked').returns();
                sandbox.stub(view, '_createHelpLayout').callsFake(function() {
                    view._helpLayout = {
                        toggle: $.noop
                    };
                });

                app.isSynced = true;
                view.layoutName = data.layoutName;
                view.helpButton = $('');
                view.help();

                expect(bwcHelpClickedStub.called).toBe(false);
            });
        });

        using('bwc layout names or the About module', ['bwc', 'about'], function(layoutName) {
            it('should open bwc help', function() {
                var bwcHelpClickedStub = sinon.stub(view, 'bwcHelpClicked').returns();

                app.isSynced = true;
                view.layoutName = layoutName;
                view.helpButton = $('<button></button>');
                view.help();
                expect(bwcHelpClickedStub).toHaveBeenCalled();
            });
        });

        it('should not open bwc help or create helpLayout if app is not synced', function() {
            var bwcHelpClickedStub = sandbox.stub(view, 'bwcHelpClicked');
            var createLayoutStub = sandbox.stub(view, '_createHelpLayout');

            app.isSynced = false;
            view.layoutName = 'notBwc';
            view.help();

            expect(bwcHelpClickedStub.called).toBe(false);
            expect(createLayoutStub.called).toBe(false);
        });

        it('should not open bwc help or create helpLayout if help button is disabled', function() {
            var bwcHelpClickedStub = sandbox.stub(view, 'bwcHelpClicked');
            var createLayoutStub = sandbox.stub(view, '_createHelpLayout');

            app.isSynced = true;
            view.layoutName = 'notBwc';
            view.helpButton = $('<button class="disabled"></button>');
            view.help();

            expect(bwcHelpClickedStub.called).toBe(false);
            expect(createLayoutStub.called).toBe(false);
        });

        it('should create helpLayout if it does not already exist in a non-bwc module', function() {
            var createLayoutStub = sandbox.stub(view, '_createHelpLayout').callsFake(function() {
                view._helpLayout = {
                    toggle: $.noop
                };
            });

            app.isSynced = true;
            view.layoutName = 'notBwc';
            view.helpButton = $('');
            view.help();

            expect(createLayoutStub.called).toBe(true);
        });

        it('should create helpLayout if it is disposed in a non-bwc module', function() {
            var createLayoutStub = sandbox.stub(view, '_createHelpLayout').callsFake(function() {
                view._helpLayout = {
                    toggle: $.noop
                };
            });

            app.isSynced = true;
            view.layoutName = 'notBwc';
            view.helpButton = $('');
            view._helpLayout = {
                disposed: true
            };
            view.help();

            expect(createLayoutStub.called).toBe(true);
        });
    });
});
