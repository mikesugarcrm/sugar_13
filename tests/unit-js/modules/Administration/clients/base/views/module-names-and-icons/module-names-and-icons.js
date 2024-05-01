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
describe('Administration.Views.ModuleNamesAndIcons', function() {
    let app;
    let view;
    let moduleName = 'Administration';
    let viewName = 'module-names-and-icons';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', viewName, moduleName);

        let meta = {
            panels: [
                [
                    {
                        name: 'panel_header'
                    }
                ]
            ]
        };

        let model = app.data.createBean(moduleName);
        let context = app.context.getContext();
        context.set('model', model);

        sinon.stub(Backbone.history, 'getFragment').callsFake(function() {
            return 'Administration/module-names-and-icons';
        });

        sinon.stub(app.lang, 'getLanguage').callsFake(function() {
            return 'en_us';
        });

        sinon.stub(app.api, 'call');

        view = SugarTest.createView('base', moduleName, viewName, meta, context, true);
    });

    afterEach(function() {
        view.dispose();
        sinon.restore();
    });

    describe('beforeRouteChange', function() {
        beforeEach(function() {
            const models = [
                {id: 'id1'},
                {id: 'id2'},
            ];
            view.collection = new Backbone.Collection();
            models.forEach(function(model) {
                view.collection.add(new Backbone.Model(model));
            });
            app.routing.start();
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show').callsFake(function() {
                return {
                    onConfirm: $.noop,
                    onCancel: $.noop
                };
            });

            sinon.stub(app.router, 'navigate');
        });
        afterEach(function() {
            app.routing.stop();
        });
        it('should show leave_confirmation alert if any models have changes', function() {
            view.collection.models[1].set('id', 'newId');
            view.beforeRouteChange();
            expect(app.alert.show).toHaveBeenCalledWith('leave_confirmation');
        });
        it('should return true when there are no changes to the collection', function() {
            sinon.stub(_, 'some')
                .returns(false);
            expect(view.beforeRouteChange()).toBe(true);
        });
        it('should return false when there are changes to the collection', function() {
            sinon.stub(_, 'some')
                .returns(true);
            expect(view.beforeRouteChange()).toBe(false);
        });
    });

    describe('fetchModules', function() {
        let url;
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            url = 'testUrl';

            sinon.stub(view, '_getConfigURL').callsFake(function() {
                return 'testUrl';
            });
        });
        afterEach(function() {
            url = null;
        });
        it('should show loading alert', function() {
            let setDisabledStub = sinon.stub();
            sinon.stub(view, 'getField')
                .withArgs('save_button')
                .returns({setDisabled: setDisabledStub});
            view.fetchModules();
            expect(app.alert.show).toHaveBeenCalledWith('module-names-and-icons-loading', {
                level: 'process',
                title: app.lang.get('LBL_LOADING'),
            });
        });

        it('should call module names and icons api', function() {
            view.fetchModules();
            expect(app.api.call).toHaveBeenCalledWith('read', url);
        });
    });

    describe('saveConfig', function() {
        let setDisabledStub;

        beforeEach(function() {
            setDisabledStub = sinon.stub();

            sinon.stub(view, 'getField')
                .withArgs('save_button')
                .returns({setDisabled: setDisabledStub});
            sinon.stub(view, 'triggerBefore')
                .withArgs('save')
                .returns(true);
            sinon.stub(view, '_saveConfig');
            sinon.stub(view, 'validateCollection');
            sinon.stub(view, '_showErrorAlert');
        });

        it('should call _saveConfig function if the validation passes', function() {
            view.validateCollection.returns(true);
            view.saveConfig();
            expect(view._saveConfig).toHaveBeenCalled();
        });

        it('should display an error if the validation fails', function() {
            view.validateCollection.returns(false);
            view.saveConfig();
            expect(view._showErrorAlert).toHaveBeenCalled();
            expect(view._saveConfig).not.toHaveBeenCalled();
        });
    });

    describe('_showErrorAlert', function() {
        let setDisabledStub;
        let err;
        beforeEach(function() {
            setDisabledStub = sinon.stub();
            sinon.stub(view, 'getField')
                .withArgs('save_button')
                .returns({setDisabled: setDisabledStub});
            sinon.stub(app.alert, 'show');
            err = {
                error: 'Error',
                message: 'Error message for alert'
            };
        });
        afterEach(function() {
            err = null;
        });
        it('should show error alert', function() {
            view._showErrorAlert(err);
            expect(app.alert.show).toHaveBeenCalledWith('module-names-and-icons-warning', {
                level: 'error',
                title: app.lang.get('LBL_ERROR'),
                messages: err.message,
            });
        });
    });

    describe('_saveConfig', function() {
        let url;
        let configAtributes;
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            url = 'testUrl';

            configAtributes = {
                'changedModules': [
                    {
                        module_color: 'coral',
                        module_icon: 'sicon-opportunity-lg',
                        module_key: 'Opportunities',
                        module_name: 'Opportunities',
                        module_plural: 'Deals',
                        module_singular: 'Deal',
                    },
                ],
            };

            sinon.stub(view, '_getConfigURL').callsFake(function() {
                return 'testUrl';
            });
            sinon.stub(view, '_getSaveConfigAttributes').callsFake(function() {
                return configAtributes;
            });
        });
        afterEach(function() {
            url = null;
        });
        it('should show loading alert', function() {
            let setDisabledStub = sinon.stub();
            sinon.stub(view, 'getField')
                .withArgs('save_button')
                .returns({setDisabled: setDisabledStub});
            view._saveConfig();
            expect(app.alert.show).toHaveBeenCalledWith('module-names-and-icons-save', {
                level: 'process',
                title: app.lang.get('LBL_SAVING'),
                autoClose: false
            });
        });

        it('should call module names and icons api', function() {
            view._saveConfig();
            expect(app.api.call).toHaveBeenCalledWith('update', url);
        });
    });

    describe('_getConfigURL', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return `${view.module}/module-names-and-icons/${view.model.get('language_selection')}`;
            });
        });

        it('should return the config url', function() {
            expect(view._getConfigURL())
                .toBe(`${view.module}/module-names-and-icons/${view.model.get('language_selection')}`);
        });
    });

    describe('showSavedConfirmation', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show').callsFake(function() {
                return {
                    getCloseSelector: function() {
                        return {
                            on: function() {}
                        };
                    }
                };
            });
            sinon.stub(app.accessibility, 'run');
        });

        it('should dismiss rename-modules-save alert', function() {
            view.showSavedConfirmation();
            expect(app.alert.dismiss).toHaveBeenCalledWith('module-names-and-icons-save');
        });
        it('should show module_config_success alert', function() {
            view.showSavedConfirmation();
            expect(app.alert.show).toHaveBeenCalledWith('module_config_success');
        });
    });

    describe('cancelConfig', function() {
        it('should cancel changes and close drawer', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop,
            };
            sinon.stub(app.drawer, 'count').returns(1);
            sinon.stub(app.drawer, 'close');
            view.cancelConfig();
            expect(app.drawer.close).toHaveBeenCalledWith(view.context, view.context.get('model'));
            app.drawer = null;
        });
    });
});
