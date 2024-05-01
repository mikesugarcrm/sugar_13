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
describe('EAPM.View.Create', function() {
    var app;
    var view;
    var layout;
    var context;
    var sandbox;
    var url;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'layout', 'create');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({
            module: 'EAPM',
            create: true
        });
        context.prepare(true);
        model = context.get('model');

        sinon.stub(app.api, 'call');

        layout = SugarTest.createLayout('base', 'EAPM', 'create', {}, null, false);
        view = SugarTest.createView('base', 'EAPM', 'create', null, context, true, layout, true);

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();

        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('initialize', function() {
        beforeEach(function() {
            url = 'testUrl';
            sinon.stub(view, 'setupFields');
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return url;
            });
            sinon.stub(view, '_super');
            sinon.stub(view, '_registerEvents');
        });

        afterEach(function() {
            url = null;
            sinon.restore();
        });

        it('during the initialize should call the api', function() {
            expect(_.isEmpty(view.messageListenersToBeRemoved)).toBe(true);
            expect(_.isEqual(view.fullAPIList, {})).toBe(true);

            view.initialize({});

            expect(app.api.call).toHaveBeenCalledWith('read', url);
            expect(view._registerEvents).toHaveBeenCalledOnce();
        });
    });

    describe('setupFields', function() {
        beforeEach(function() {
            url = 'testUrl';
            sandbox.stub(app.api, 'buildURL').callsFake(function() {
                return url;
            });
            sandbox.stub(view, '_super');
            sandbox.stub(view, '_registerEvents');
        });

        afterEach(function() {
            url = null;
            sandbox.restore();
        });

        it('should set up fields correctly when no application type is selected', function() {
            view.fullAPIList = ['test'];

            var showHideFieldStub = sandbox.stub(view, '_showHideField');
            var setFieldRequiredStub = sandbox.stub(view, '_setFieldRequired');

            view.setupFields();

            expect(showHideFieldStub).toHaveBeenCalledWith('url', true);
            expect(showHideFieldStub).toHaveBeenCalledWith('name', true);
            expect(showHideFieldStub).toHaveBeenCalledWith('password', true);

            expect(setFieldRequiredStub).toHaveBeenCalledWith('url', true);
            expect(setFieldRequiredStub).toHaveBeenCalledWith('name', true);
            expect(setFieldRequiredStub).toHaveBeenCalledWith('password', true);
        });

        it('should set up fields correctly when no application type is selected', function() {
            var applicationType = 'mockApplicationType';
            view.model.set('application', applicationType);

            var showHideFieldStub = sandbox.stub(view, '_showHideField');
            var setFieldRequiredStub = sandbox.stub(view, '_setFieldRequired');

            view.fullAPIList = {
                [applicationType]: {
                    needsUrl: false,
                    authMethod: 'oauth2',
                },
            };

            view.setupFields();

            expect(showHideFieldStub).toHaveBeenCalledWith('url', false);
            expect(showHideFieldStub).toHaveBeenCalledWith('name', false);
            expect(showHideFieldStub).toHaveBeenCalledWith('password', false);

            expect(setFieldRequiredStub).toHaveBeenCalledWith('url', false);
            expect(setFieldRequiredStub).toHaveBeenCalledWith('name', false);
            expect(setFieldRequiredStub).toHaveBeenCalledWith('password', false);
        });
    });

    describe('save', function() {
        beforeEach(function() {
            url = 'testUrl';
            sandbox.stub(app.api, 'buildURL').callsFake(function() {
                return url;
            });
            sandbox.stub(view, '_super');
            sandbox.stub(view, '_registerEvents');
        });

        afterEach(function() {
            url = null;
            sandbox.restore();
        });

        it('should disable buttons and thrown an error', function() {
            var validateModelWaterfallStub = sandbox.stub(view, 'validateModelWaterfall');
            var startAuthProcessStub = sandbox.stub(view, '_startAuthProcess');
            var enableButtonsStub = sandbox.stub(view, 'enableButtons');
            var metadataSyncError = sandbox.stub(view, 'handleMetadataSyncError');
            var errorMeta = {
                status: 412,
                request: {
                    metadataRetry: false,
                    execute: function() {},
                },
            };

            validateModelWaterfallStub.yields();
            startAuthProcessStub.yields(errorMeta);

            view.save();

            waitsFor(function() {
                return enableButtonsStub.called;
            }, 'enableButtonsStub to be called', 1000);

            runs(function() {
                expect(metadataSyncError).toHaveBeenCalled();
            });
        });

        it('should disable buttons and start the auth process', function() {
            var validateModelWaterfallStub = sandbox.stub(view, 'validateModelWaterfall');
            var startAuthProcessStub = sandbox.stub(view, '_startAuthProcess');
            var enableButtonsStub = sandbox.stub(view, 'enableButtons');
            var metadataSyncError = sandbox.stub(view, 'handleMetadataSyncError');

            validateModelWaterfallStub.yields();
            startAuthProcessStub.yields(null);

            view.save();

            waitsFor(function() {
                return enableButtonsStub.called;
            }, 'enableButtonsStub to be called', 1000);

            runs(function() {
                expect(metadataSyncError).not.toHaveBeenCalled();
            });
        });
    });
});
