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

const Controller = require('../../../src/core/controller');
const Layout = require('../../../src/view/layout');
const View = require('../../../src/view/view');

describe('Core/Controller', function() {

    let app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata();
        this.controller = new Controller();
        this.sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        this.sandbox.restore();
    });

    describe('whether layout.loadData gets called is controlled by caller using skipFetch', function() {
        let params;
        let loadDataStub;
        let bindDataStub;

        beforeEach(function() {
            params = {
                module: 'Contacts',
                layout: 'list'
            };

            loadDataStub = this.sandbox.stub(Layout.prototype, 'loadData');
            bindDataStub = this.sandbox.stub(Layout.prototype, 'bindDataChange');
        });

        it('should call loadData by default', function() {
            this.controller.loadView(params);
            expect(loadDataStub).toHaveBeenCalled();
        });

        it('should NOT call loadData if skipFetch passed', function() {
            params.skipFetch = true;
            this.controller.loadView(params);
            expect(loadDataStub).not.toHaveBeenCalled();
        });
    });

    describe('when a route is matched', function() {
        it('should load the view properly', function() {
            var params = {
                module: 'Contacts',
                layout: 'list'
            };
            SugarTest.seedFakeServer();

            SugarTest.server.respondWith('GET', /.*\/rest\/v10\/Contacts.*/,
                [200, {'Content-Type': 'application/json'},
                    JSON.stringify(fixtures.api['rest/v10/contact'].GET.response)]);

            this.controller.loadView(params);
            SugarTest.server.respond();

            expect(this.controller.layout).toBeDefined();
            expect(this.controller.layout instanceof Backbone.View).toBeTruthy();
            expect(this.controller.context.get('collection')).toBeDefined();
            expect(this.controller.context.get('collection').models.length).toEqual(2);

        });
    });

    describe('when additional components', function() {
        var targetId = 'footer';
        var targetSelector = '#' + targetId;
        beforeEach(function() {
            sinon.spy(app.view, 'createView');
            sinon.spy(app.view, 'createLayout');
            this.controller.$el.append('<div id="' + targetId + '"></div>');
        });

        afterEach(function() {
            sinon.restore();
            app.additionalComponents = {};
        });

        it('should log an error and return if the target element is not in the DOM', function() {
            sinon.stub(app.logger, 'error');
            var components = {login: {target: '#header'}};

            this.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
        });

        it('should log an error if some of the components do not have a target', function() {
            sinon.stub(app.logger, 'error');
            var components = {login: {}};

            this.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
            expect(app.view.createView).not.toHaveBeenCalled();
        });

        it('should log an error and return if app.additionalComponents is already defined.', function() {
            sinon.stub(app.logger, 'error');
            var components = {login: {target: targetSelector, view: 'login'}};
            this.controller.loadAdditionalComponents(components);
            this.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
            expect(app.view.createView).not.toHaveBeenCalledTwice();
        });

        it('should create and render a view', function() {
            var components = {login: {target: targetSelector}};
            this.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.login instanceof View).toBeTruthy();
            expect(app.additionalComponents.login.name).toEqual('login');
            expect(app.view.createView).toHaveBeenCalled();
        });

        it('should create and render a layout', function() {
            var components = {header: {target: targetSelector, layout: 'header'}};
            this.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.header instanceof Layout).toBeTruthy();
            expect(app.additionalComponents.header.name).toEqual('header');
            expect(app.view.createLayout).toHaveBeenCalled();
        });

        it('should create and render a view with a name different from the add comp name', function() {
            var components = {login: {target: targetSelector, view: 'footer'}};
            this.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.login instanceof View).toBeTruthy();
            expect(app.additionalComponents.login.name).toEqual('footer');
        });

        it('should re-render them when app:sync:complete fires', function() {
            var components = {
                login: {
                    target: targetSelector
                },
                testlayout: {
                    target: targetSelector,
                    layout: 'header'
                }
            };
            this.controller.loadAdditionalComponents(components);
            var renderStub = sinon.stub(app.additionalComponents.login, 'render');

            app.router = app.router || {};
            app.router.reset = app.router.reset || function() {};
            sinon.stub(app.router, 'reset');

            app.events.trigger('app:sync:complete');

            expect(renderStub).toHaveBeenCalled();
        });
    });
});
