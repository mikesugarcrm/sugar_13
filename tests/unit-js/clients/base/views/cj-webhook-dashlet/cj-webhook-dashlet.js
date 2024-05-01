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
describe('View.Views.Base.CjWebhookDashletView', function() {
    let app;
    let view;
    let model;
    let context;
    let layout;
    let initOptions;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean('Accounts');
        SugarTest.loadComponent('base', 'view', 'cj-webhook-dashlet');
        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        context.parent.parent = app.context.getContext();
        layout = SugarTest.createLayout(
            'base',
            '',
            'base',
            null,
            context
        );
        view = SugarTest.createView(
            'base',
            '',
            'cj-webhook-dashlet',
            {
                buttons: [
                    {
                        type: 'dashletaction',
                        action: 'sendClicked'
                    },
                    {
                        dropdownbuttons: [
                            {
                                type: 'dashletaction',
                                action: 'editClicked',
                                label: 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                                name: 'edit_button',
                            }
                        ]
                    }
                ]
            },
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();
        model = null;
        context = null;
        layout = null;
        view = null;
    });

    describe('initialize', function() {
        it('should call the initialize', function() {
            view.initialize(initOptions);
            expect(view._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('_render', function() {
        it('check  wether the user has license or not if it have then allow to render', function() {
            sinon.stub(app.user, 'hasAutomateLicense').returns(true);
            sinon.stub(view.$('pre'), 'show');
            view.loaded = true;
            view._render();
            expect(view._super).toHaveBeenCalledWith('_render');
        });
        it('If user dont have license then simple return', function() {
            sinon.stub(app.user, 'hasAutomateLicense').returns(false);
            sinon.stub(view.$el, 'html');
            view._noAccessTemplate = sinon.stub();
            view._render();
            expect(view.$el.html).toHaveBeenCalled();
        });
    });

    describe('startLoading', function() {
        it('should call the startLoading', function() {
            let demoObj = {
                width: function() {
                    return 5;
                },
                height: function() {
                    return 5;
                },
            };
            sinon.stub(view.$el, 'parent').returns(demoObj);
            view.startLoading();
            expect(view.$el.parent).toHaveBeenCalled();
        });
    });

    describe('sendClicked', function() {
        it('should call the sendClicked', function() {
            sinon.stub(view, '_retrieveData');
            view.sendClicked();
            expect(view._retrieveData).toHaveBeenCalled();
        });
    });

    describe('_retrieveData', function() {
        it('should call the _retrieveData', function() {
            sinon.stub(view, 'startLoading');
            sinon.stub(app.api, 'buildURL').returns('www.github.com');
            sinon.stub(app.api, 'call');
            view._retrieveData();
            expect(view.startLoading).toHaveBeenCalled();
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('loadCompleted', function() {
        it('should call the loadCompleted', function() {
            let data = {};
            sinon.stub(app.template, 'get');
            sinon.stub(view, 'getJsonString');
            sinon.stub(view, 'render');
            view.loadCompleted(data);
            expect(app.template.get).toHaveBeenCalled();
            expect(view.getJsonString).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('loadError', function() {
        it('should call the loadError', function() {
            view.tplErrorMap = {};
            let error = {};
            sinon.stub(app.template, 'get').returns('Template');
            sinon.stub(view, 'render');
            view.loadError(error);
            expect(view.template).toBe('Template');
            expect(app.template.get).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('getJsonString', function() {
        it('should call the getJsonString', function() {
            let data = '[{"expType":"Accounts",' +
            '"expField":"phone_alternate"}]';
            let expectedResult = [{expType: 'Accounts', expField: 'phone_alternate'}];
            expect(view.getJsonString(data)).toBe(JSON.stringify(expectedResult,null,2));
        });
    });
});
