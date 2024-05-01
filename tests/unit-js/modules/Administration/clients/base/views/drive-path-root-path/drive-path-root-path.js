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
describe('Administration.View.DrivePathRootPath', function() {
    var app = SUGAR.App;
    var sinonSandbox;
    var view;
    var viewName = 'drive-path-root-path';
    var module = 'Administration';
    var meta;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'layout', 'drive-path-content', module);
        SugarTest.loadComponent('base', 'view', viewName, module);

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', null, module);

        context = app.context.getContext();

        context.prepare();
        app.drawer = {
            open: sinon.stub()
        };

        SugarTest.testMetadata.set();

        sinonSandbox = sinon.createSandbox();

        parentLayout = SugarTest.createLayout('base', module, 'drive-path-content', null, null, true);
        view = SugarTest.createView('base', module, viewName, null, context, true, parentLayout);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var loadRootPathStub;
        beforeEach(function() {
            loadRootPathStub = sinonSandbox.stub(view, 'loadRootPath');

            view.initialize({context: context});
        });

        it('should call loadRootPath', function() {
            expect(loadRootPathStub).toHaveBeenCalled();
        });
    });

    describe('loadRootPath', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinonSandbox.stub(app.api, 'call');

            view.loadRootPath();
        });

        it('should load root path', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('_renderRootPath', function() {
        var renderStub;
        var data;

        beforeEach(function() {
            renderStub = sinonSandbox.stub(view, 'render');
            data = {
                records: null
            };

            view._renderRootPath(data);
        });

        it('should rootPath to be empty', function() {
            expect(view.rootPath).toEqual({path: ''});
        });

        it('should call render', function() {
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('selectRootPath', function() {
        var mockEvent;

        beforeEach(function() {
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('button');
            mockEvent.target.classList = ['selectRootPath'];

            view.selectRootPath(mockEvent);
        });

        it('should open drawer', function() {
            expect(app.drawer.open).toHaveBeenCalled();
        });
    });
});
