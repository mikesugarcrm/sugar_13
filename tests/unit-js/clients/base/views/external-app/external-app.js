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
describe('Base.View.ExternalApp', function() {
    var view;
    var options;
    var app = null;
    var context = null;
    var layout;

    beforeEach(function() {
        var meta = {};

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        window.singleSpa = {
            start: sinon.stub(),
            mountRootParcel: sinon.stub()
        };

        options = {
            context: context,
            meta: {
                srn: 'some-srn',
                env: {
                    testKey: 'test val'
                }
            },
            layout: {
                cid: 'w92'
            }
        };

        layout = SugarTest.createLayout('base', 'Accounts', 'tabbed-layout', meta);
        view = SugarTest.createView('base', 'Accounts', 'external-app', options.meta, options.context, false, layout);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should check if singleSpa Start is called', function() {
            view.initialize(options);
            expect(window.singleSpa.start).toHaveBeenCalled();
        });

        it('should set allowApp to be true if it starts undefined', function() {
            view.allowApp = undefined;
            view.initialize(options);

            expect(view.allowApp).toBeTruthy();
        });

        it('should set allowApp to be false if it starts false', function() {
            view.allowApp = false;
            view.initialize(options);

            expect(view.allowApp).toBeFalsy();
        });

        it('should set extraParcelParams is meta.env is set', function() {
            view.initialize(options);

            expect(view.extraParcelParams).toEqual({
                testKey: 'test val'
            });
        });

        it('should call _onSugarAppLoad if not in a tabbed-layout', function() {
            options.layout.type = 'dashboard';
            sinon.stub(view, '_onSugarAppLoad').callsFake(function() {});
            view.initialize(options);

            expect(view._onSugarAppLoad).toHaveBeenCalled();
        });

        it('should call _onSugarAppLoad if in a tabbed-layout', function() {
            options.layout.type = 'tabbed-layout';
            sinon.stub(view, '_onSugarAppLoad').callsFake(function() {});
            sinon.stub(view.context, 'on').callsFake(function() {});
            view.initialize(options);

            expect(view._onSugarAppLoad).not.toHaveBeenCalled();
            expect(view.context.on).toHaveBeenCalledWith('sugarApp:w92:load:some-srn');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.stub(view, '_mountApp');
            view.render();
        });

        it('should set rendered to true', function() {
            expect(view.rendered).toBeTruthy();
        });

        it('should call _mountApp', function() {
            expect(view._mountApp).toHaveBeenCalled();
        });
    });

    describe('displayError', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get').callsFake(function() {});
            sinon.stub(view.$el, 'empty').callsFake(function() {});
            sinon.stub(view, 'template').callsFake(function() {});
            sinon.stub(view.$el, 'append').callsFake(function() {});

            view.errorCode = 'test1';
            view.displayError();
        });

        it('should call app.lang.get with the errorCode', function() {
            expect(app.lang.get).toHaveBeenCalledWith('LBL_SUGAR_APPS_DASHLET_CATALOG_ERROR', null, {
                errorCode: 'test1'
            });
        });

        it('should empty the $el', function() {
            expect(view.$el.empty).toHaveBeenCalled();
        });

        it('should call the template to add to the $el', function() {
            expect(view.template).toHaveBeenCalledWith(view);
        });

        it('should add the template to the $el', function() {
            expect(view.$el.append).toHaveBeenCalled();
        });
    });

    describe('_mountApp', function() {
        describe('when app is not mounted', function() {
            beforeEach(function() {
                sinon.stub(view.el, 'appendChild');

                view.mounted = false;
                view.parcelLib = true;

                view._mountApp();
            });

            it('should call view.appendChild', function() {
                expect(view.el.appendChild).toHaveBeenCalled();
            });

            it('should call singleSpa.mountRootParcel', function() {
                expect(window.singleSpa.mountRootParcel).toHaveBeenCalled();
            });

        });

        describe('when app is mounted', function() {
            beforeEach(function() {
                view.mounted = true;
                view.parcelApp = {
                    update: sinon.stub()
                };

                view._mountApp();
            });

            it('should call view.parcelApp.update', function() {
                expect(view.parcelApp.update).toHaveBeenCalled();
            });
        });
    });

    describe('_dispose', function() {
        var unmountStub;

        beforeEach(function() {
            unmountStub = sinon.stub();
            view.parcelApp = {
                unmount: unmountStub
            };
            view.sugarAppStore = {};

            sinon.stub(view, '_super');
            view._dispose();
        });

        it('should call view.parcelApp.unmount', function() {
            expect(unmountStub).toHaveBeenCalled();
        });
    });
});
