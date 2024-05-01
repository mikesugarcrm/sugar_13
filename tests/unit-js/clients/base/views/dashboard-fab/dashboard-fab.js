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

describe('Base.View.DashboardFab', function() {
    var app;
    var view;
    var metadata = {
        icon: 'fab-icon',
        buttons: [{
            name: 'add_dashlet_button',
            type: 'button',
            icon: 'add-dashlet-icon',
            label: 'LBL_ADD_DASHLET_BUTTON',
            acl_action: 'edit',
            showOn: 'view'
        }]
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'dashboard-fab');

        var layout = {
            model: new Backbone.Model()
        };

        view = SugarTest.createView('base', null, 'dashboard-fab', {}, null, null, layout);
        view._setButtons = $.noop;
        view.setButtonStates = $.noop;
        view.layout.model.set('metadata', metadata);
        view.layout.closestComponent = sinon.stub().returns({});
        app.routing.start();
    });

    afterEach(function() {
        sinon.restore();

        app.router.stop();

        app.cache.cutAll();
        app.view.reset();
        view.layout = null;
        view.context = null;
        view.dispose();
        view = null;
    });

    describe('metadata override', function() {
        var options;

        beforeEach(function() {
            options = {
                meta: {},
                context: {},
                platform: 'base',
                type: 'dashboard-fab',
                name: 'dashboard-fab',
            };
        });
        it('should not change the metadata', function() {
            var oldOptions = view.overrideOptions(options);
            expect(oldOptions.meta).toEqual({});
        });

        it('should override the initial metadata', function() {
            var parentModel = app.data.createBean();
            parentModel.set('module', 'Accounts');
            options.context = {
                parent: parentModel
            };
            sinon.stub(app.metadata, 'getView')
                .withArgs('Accounts', 'dashboard-fab', undefined).returns(metadata);

            var newOptions = view.overrideOptions(options);
            expect(newOptions.meta).toEqual(metadata);
        });
    });

    describe('render', function() {
        var showStub;
        var hideStub;

        beforeEach(function() {
            showStub = sinon.stub(view.$el, 'show');
            hideStub = sinon.stub(view.$el, 'hide');
        });

        it('should trigger the toggle main button logic', function() {
            var setButtonsSpy = sinon.spy(view, '_setButtons');
            var toggleSpy = sinon.spy(view, 'toggleMainButton');
            view.render();
            expect(setButtonsSpy).toHaveBeenCalled();
            expect(toggleSpy).toHaveBeenCalled();
        });

        it('should hide the main button if there are no inner buttons', function() {
            view.toggleMainButton();
            expect(showStub).not.toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });

        it('should show the main button if there are any visible inner buttons', function() {
            view.buttons = [{
                type: 'button',
                name: 'add_dashlet_button',
                isVisible: function() {
                    return true;
                }
            }];

            sinon.stub(view, 'checkButtonVisibilityOnBrowser').callsFake(function() {return true;});

            view.toggleMainButton();
            expect(showStub).toHaveBeenCalled();
            expect(hideStub).not.toHaveBeenCalled();
        });

        it('should hide the main button if the inner buttons are not visible on the browser', function() {
            view.buttons = [{
                type: 'button',
                name: 'add_dashlet_button',
                isVisible: function() {
                    return true;
                }
            }];

            sinon.stub(view, 'checkButtonVisibilityOnBrowser').callsFake(function() {return false;});

            view.toggleMainButton();
            expect(showStub).not.toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });

        it('should hide the main button if there are no visible inner buttons', function() {
            view.buttons = [{
                type: 'button',
                name: 'add_dashlet_button',
                isVisible: function() {
                    return false;
                }
            }];

            view.toggleMainButton();
            expect(showStub).not.toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('togglePinPosition', function() {
        it('should pin to top', function() {
            var langStub = sinon.stub(app.lang, 'get');
            view.togglePinPosition();
            expect(view.pinnedTo).toEqual('top');
        });

        it('should pin again to bottom', function() {
            var langStub = sinon.stub(app.lang, 'get');
            view.togglePinPosition();
            view.togglePinPosition();
            expect(view.pinnedTo).toEqual('bottom');
        });
    });

    describe('duplicateClicked', function() {
        var oldName;
        var prefill;
        var unsetStub;
        var saveStub;
        var appLangStub;

        beforeEach(function() {
            oldName = 'oldName';

            // create bean stub so that we can stub its method
            prefill = app.data.createBean('Dashboards', {name: oldName, module: 'Dashboards'});
            prefill.set('id', 'new_id');
            sinon.stub(app.data, 'createBean').withArgs('Dashboards').returns(prefill);

            unsetStub = sinon.stub(prefill, 'unset');
            saveStub = sinon.stub(prefill, 'save');
            sinon.stub(prefill, 'copy');
            appLangStub = sinon.stub(app.lang, 'get');

            appLangStub.withArgs(oldName, 'Accounts').returns(oldName);
            appLangStub.withArgs(oldName, 'Home').returns(oldName);
            appLangStub.withArgs('LBL_COPY_OF', 'Dashboards', {name: oldName}).returns('Copy of oldName');
        });

        afterEach(function() {
            prefill = null;
        });

        it('should save the new RHS Dashboard model and navigate to it', function() {
            var contextBro = new app.Context();
            contextBro.set('collection', app.data.createBeanCollection('Dashboards', [view.model]));
            prefill.set('dashboard_module', 'Accounts');
            view.context.parent = {
                getChildContext: function() {
                    return contextBro;
                },
                get: function() {
                    return 'record';
                }
            };
            view.layout = {
                navigateLayout: sinon.stub()
            };

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsToOn('success', view);

            view.duplicateClicked();

            expect(unsetStub.lastCall.args[0]).toEqual({
                id: void 0,
                assigned_user_id: void 0,
                assigned_user_name: void 0,
                team_name: void 0,
                default_dashboard: void 0
            });
            expect(view.layout.navigateLayout).toHaveBeenCalledWith('new_id');
            expect(contextBro.get('collection').length).toEqual(2);

            contextBro = null;
        });

        it('should save the new Home Dashboard model and navigate to it', function() {
            var navigateStub = sinon.stub(app.router, 'navigate');

            prefill.set('dashboard_module', 'Home');
            sinon.stub(app.router, 'buildRoute')
                .withArgs(view.module, prefill.get('id')).returns('NewModelRoute');

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsToOn('success', view);

            view.duplicateClicked();

            expect(navigateStub).toHaveBeenCalledWith('NewModelRoute', {trigger: true});
        });

        it('should show an error alert when saving fails', function() {
            var alertStub = sinon.stub(app.alert, 'show');
            prefill.set('dashboard_module', 'Home');

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsTo('error');

            view.duplicateClicked();

            expect(alertStub).toHaveBeenCalled();
        });
    });

    describe('editModuleTabsClicked', function() {
        it('should open drawer', function() {
            app.drawer = {
                open: function() { }
            };
            sinon.stub(app.drawer, 'open');
            view.editModuleTabsClicked();
            expect(app.drawer.open).toHaveBeenCalled();
        });
    });

    describe('deleteDashboard', function() {
        var alertStub;
        var destroyStub;

        beforeEach(function() {
            alertStub = sinon.stub(app.alert, 'show');
            destroyStub = sinon.stub(view.model, 'destroy');
        });

        it('should navigate to fallback Home dashboard after deleting current one', function() {
            sinon.stub(app.router, 'buildRoute')
                .withArgs('Home').returns('HomeRoute');
            var navigateStub = sinon.stub(app.router, 'navigate');
            view.module = 'Home';

            alertStub.yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('success', view, []);

            view.deleteDashboard();

            expect(navigateStub).toHaveBeenCalledWith('HomeRoute', {trigger: true});
        });

        it('should navigate to fallback RHS dashboard layout after deleting current one', function() {
            var dashboardList = [
                view.model,
                app.data.createBean('Dashboards', {name: 'Dashboard B'})
            ];
            var contextBro = new app.Context({
                module: 'Home'
            });
            contextBro.set('collection', app.data.createBeanCollection('Dashboards', dashboardList));
            view.context.parent = {
                getChildContext: function() {
                    return contextBro;
                },
                get: function() {
                    return 'record';
                }
            };
            view.layout = {
                navigateLayout: sinon.stub()
            };

            alertStub.yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('success', view, []);

            view.deleteDashboard();

            expect(_.findWhere(contextBro.get('collection'), view.model)).toBeUndefined();
            expect(view.layout.navigateLayout).toHaveBeenCalledWith('list');
        });

        it('should show an error alert when deletion fails', function() {
            alertStub.withArgs('delete_confirmation').yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('error', view, []);

            view.deleteDashboard();

            expect(alertStub.lastCall.args[0]).toEqual('error_while_save');
        });
    });

    describe('_getDashboardBeanId', function() {
        var component;
        var res;
        it('should return model id', function() {
            component = {
                model: {
                    get: sinon.stub().returns('testId')
                }
            };

            expect(view._getDashboardBeanId(component)).toEqual('testId');
        });

        it('should return blank string if model is not defined', function() {
            expect(view._getDashboardBeanId({})).toEqual('');
        });
    });

    describe('restoreDashboardDashlets', function() {
        var component;
        beforeEach(function() {
            component = view.closestComponent('dashboard');
            component.context = {
                trigger: sinon.stub()
            };
        });

        it('should trigger dashboard:restore-dashboard:clicked event', function() {
            view.restoreDashboardDashlets(0);

            expect(component.context.trigger).toHaveBeenCalled();
        });
    });
});
