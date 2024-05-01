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

describe('Base.View.ExternalAppDashlet', function() {
    var view;
    var options;
    var app;
    var context;
    var layout;
    var module;
    let getAvailableServicesStub;

    beforeEach(function() {
        var meta = {};

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        module = 'Contacts';
        window.singleSpa = {
            start: sinon.stub(),
            mountRootParcel: sinon.stub()
        };

        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent('base', 'view', 'external-app');

        options = {
            context: context,
            meta: {
                srn: 'some-srn',
                env: {
                    testKey: 'test val'
                }
            },
            module: module,
            layout: {
                cid: 'w92'
            }
        };

        layout = SugarTest.createLayout('base', module, 'dashboard', meta);
        view = SugarTest.createView(
            'base',
            'Contacts',
            'external-app-dashlet',
            options.meta,
            options.context,
            false,
            layout
        );
        getAvailableServicesStub = sinon.stub(view, '_getAvailableServices').callsFake(function() {
            return [{
                view: {
                    name: 'svc1',
                    src: 'test1'
                }
            }, {
                view: {
                    name: 'svc2',
                    src: 'test2'
                }
            }];
        });
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set services', function() {
            view.initialize(options);

            expect(view.services.length).toBe(2);
        });

        describe('if not in config mode', function() {
            beforeEach(function() {
                options.meta.config = false;
            });

            it('should check _checkCatalogAccess', function() {
                sinon.stub(view, '_checkCatalogAccess').callsFake(function() {
                    return true;
                });

                view.initialize(options);

                expect(view.allowApp).toBeTruthy();
            });

            it('should set errorCode if check _checkCatalogAccess is false', function() {
                sinon.stub(view, '_checkCatalogAccess').callsFake(function() {
                    return false;
                });

                view.initialize(options);

                expect(view.errorCode).toBe('CAT-404');
            });
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, '_super');
            sinon.stub(view, '_getLayoutName').callsFake(function() {
                return 'record-dashlet';
            });
            sinon.stub(app.events, 'on');
        });

        it('should do nothing in config', function() {
            view.meta.config = true;
            view.bindDataChange();

            expect(view._super).not.toHaveBeenCalled();
        });

        it('should do nothing if allowApp is true', function() {
            view.allowApp = true;
            view.bindDataChange();

            expect(app.events.on).not.toHaveBeenCalled();
        });

        it('should set an event listener and delay if allowApp is false', function() {
            view.allowApp = false;
            view.bindDataChange();

            expect(app.events.on).toHaveBeenCalledWith(
                'sugarApp:Contacts:record-dashlet:updated',
                view._checkMeta,
                view
            );
            expect(_.delay).toHaveBeenCalled();
        });
    });

    describe('initDashlet()', function() {
        var srcField;
        var services;

        beforeEach(function() {
            srcField = {
                name: 'src',
                type: 'enum'
            };
            view.dashletConfig = {
                panels: [{
                    fields: [srcField]
                }]
            };
            services = [{
                view: {
                    name: 'test1',
                    src: 'https://test1'
                }
            }, {
                view: {
                    name: 'test2',
                    src: 'https://test2'
                }
            }];
            view.services = services;
            sinon.spy(view.settings, 'on');
            sinon.stub(view, 'setAppUrlTitle').callsFake(function() {});
            view.meta.config = true;

            view.initDashlet();
        });

        afterEach(function() {
            srcField = null;
        });

        it('should set options on the src field with services', function() {
            expect(srcField.options).toEqual({
                'https://test1': 'test1',
                'https://test2': 'test2'
            });
        });

        it('should build services object with services', function() {
            expect(view.servicesObj).toEqual({
                'https://test1': {
                    name: 'test1',
                    src: 'https://test1'
                },
                'https://test2': {
                    name: 'test2',
                    src: 'https://test2'
                }
            });
        });

        it('should set a change event listener on settings', function() {
            expect(view.settings.on).toHaveBeenCalledWith('change');
        });

        it('should call setAppUrlTitle when settings src changes', function() {
            view.settings.set('src', 'test');

            expect(view.setAppUrlTitle).toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            sinon.stub(app.view.View.prototype.render, 'call').callsFake(function() {});
            sinon.stub(view, 'setAppUrlTitle').callsFake(function() {});
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, 'displayError').callsFake(function() {});
        });

        describe('in config mode', function() {
            beforeEach(function() {
                view.meta.config = true;
                view.render();
            });

            it('should call app.view.View.prototype.render.call', function() {
                expect(app.view.View.prototype.render.call).toHaveBeenCalled();
            });

            it('should call setAppUrlTitle', function() {
                expect(view.setAppUrlTitle).toHaveBeenCalled();
            });
        });

        describe('not in config mode', function() {
            beforeEach(function() {
                view.meta.config = false;
            });

            it('should call _super if allowApp is true', function() {
                view.allowApp = true;
                view.render();

                expect(view._super).toHaveBeenCalled();
            });

            it('should call displayError if allowApp is false', function() {
                view.allowApp = false;
                view.render();

                expect(view.displayError).toHaveBeenCalled();
            });
        });
    });

    describe('setAppUrlTitle()', function() {
        var servicesObj;

        beforeEach(function() {
            view.settings.set({
                src: 'https://test1'
            });
            servicesObj = {
                'https://test1': {
                    name: 'test1 name',
                    src: 'https://test1'
                },
                'https://test2': {
                    name: 'test2 name',
                    src: 'https://test2'
                }
            };
            view.servicesObj = servicesObj;
            sinon.stub(view, '_render').callsFake(function() {});

            view.setAppUrlTitle();
        });

        afterEach(function() {
            servicesObj = null;
        });

        it('should set the settings label with currentService name', function() {
            expect(view.settings.get('label')).toBe('test1 name');
        });

        it('should call _render', function() {
            expect(view._render).toHaveBeenCalled();
        });
    });

    describe('loadData()', function() {
        var completeFn;

        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, '_onSugarAppLoad').callsFake(function() {});
            completeFn = sinon.stub();
        });

        afterEach(function() {
            completeFn = null;
        });

        it('should call _super loadData if no complete fn is passed in', function() {
            view.loadData();

            expect(view._super).toHaveBeenCalledWith('loadData');
        });

        it('should call _onSugarAppLoad if complete fn is passed in and no parcelApp', function() {
            view.loadData({
                complete: completeFn
            });

            expect(view._onSugarAppLoad).toHaveBeenCalled();
        });

        it('should call complete fn if passed in', function() {
            view.loadData({
                complete: completeFn
            });

            expect(completeFn).toHaveBeenCalled();
        });
    });

    describe('_getLayoutName()', function() {
        it('should return record-dashlet on record views', function() {
            app.controller.context.set('layout', 'record');

            expect(view._getLayoutName()).toBe('record-dashlet');
        });

        it('should return list-dashlet on list views', function() {
            app.controller.context.set('layout', 'records');

            expect(view._getLayoutName()).toBe('list-dashlet');
        });
    });

    describe('_checkMeta()', function() {
        beforeEach(function() {
            sinon.stub(view.$el, 'empty');
            sinon.stub(view, '_onSugarAppLoad');
            sinon.stub(view, 'render');
        });

        it('should do nothing if allowApp is false', function() {
            sinon.stub(view, '_checkCatalogAccess').callsFake(function() {
                return false;
            });
            view._checkMeta();

            expect(view.$el.empty).not.toHaveBeenCalled();
            expect(view._onSugarAppLoad).not.toHaveBeenCalled();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should load the app and render if allowApp is true', function() {
            sinon.stub(view, '_checkCatalogAccess').callsFake(function() {
                return true;
            });
            view._checkMeta();

            expect(view.$el.empty).toHaveBeenCalled();
            expect(view._onSugarAppLoad).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('_failedToLoadScript()', function() {
        var addClassStub;
        var removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.stub();
            removeClassStub = sinon.stub();
            sinon.stub(app.events, 'off');
            sinon.stub(view, '_getLayoutName').callsFake(function() {
                return 'record-dashlet';
            });
            sinon.stub(view, '$')
                .withArgs('.loading-label').returns({
                    addClass: addClassStub
                })
                .withArgs('.error-msg').returns({
                    removeClass: removeClassStub
                });

            view._failedToLoadScript();
        });

        afterEach(function() {
            addClassStub = null;
            removeClassStub = null;
        });

        it('should unset the app.events listener', function() {
            expect(app.events.off).toHaveBeenCalledWith('sugarApp:Contacts:record-dashlet:updated');
        });

        it('should hide the loading label', function() {
            expect(addClassStub).toHaveBeenCalledWith('hide');
        });

        it('should show the error message', function() {
            expect(removeClassStub).toHaveBeenCalledWith('hide');
        });
    });

    describe('_getAvailableServices()', function() {
        var options;

        beforeEach(function() {
            getAvailableServicesStub.restore();
            options = {
                module: 'Contacts'
            };
            sinon.stub(app.metadata, 'getLayout').callsFake(function() {
                return {
                    components: []
                };
            });
        });

        afterEach(function() {
            options = null;
        });

        it('should call getLayout with list-dashlet', function() {
            sinon.stub(app.controller.context, 'get').callsFake(function() {
                return 'records';
            });
            view._getAvailableServices(options);

            expect(app.metadata.getLayout).toHaveBeenCalledWith('Contacts', 'list-dashlet');
        });

        it('should call getLayout with record-dashlet', function() {
            sinon.stub(app.controller.context, 'get').callsFake(function() {
                return 'record';
            });
            view._getAvailableServices(options);

            expect(app.metadata.getLayout).toHaveBeenCalledWith('Contacts', 'record-dashlet');
        });
    });

    describe('_checkCatalogAccess()', function() {
        var options;
        var result;

        beforeEach(function() {
            options = {
                meta: {
                    src: 'test1'
                }
            };
            getAvailableServicesStub.restore();
        });

        afterEach(function() {
            options = null;
            result = null;
        });

        it('should return true if this service exists', function() {
            sinon.stub(view, '_getAvailableServices').callsFake(function() {
                return [{
                    view: {
                        src: 'test1'
                    }
                }];
            });
            result = view._checkCatalogAccess(options);

            expect(result).toBeTruthy();
        });

        it('should return false if this service does not exist', function() {
            sinon.stub(view, '_getAvailableServices').callsFake(function() {
                return [];
            });
            result = view._checkCatalogAccess(options);

            expect(result).toBeFalsy();
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.stub(view, '_super');
            sinon.stub(view, '_getLayoutName').callsFake(function() {
                return 'list-dashlet';
            });
            sinon.stub(app.events, 'off');
            sinon.spy(window, 'clearTimeout');
            view.dashletSetTimeout = 123;

            view._dispose();
        });

        it('should clear any dashletSetTimeout', function() {
            expect(window.clearTimeout).toHaveBeenCalledWith(view.dashletSetTimeout);
        });

        it('should clear app.events listener', function() {
            expect(app.events.off).toHaveBeenCalledWith('sugarApp:Contacts:list-dashlet:updated');
        });

        it('should clear any dashletSetTimeout', function() {
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
