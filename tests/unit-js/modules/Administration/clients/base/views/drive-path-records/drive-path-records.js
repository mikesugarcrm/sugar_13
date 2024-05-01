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
describe('Administration.View.DrivePathRecords', function() {
    var app = SUGAR.App;
    var sinonSandbox;
    var view;
    var viewName = 'drive-path-records';
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
        rootPathView = SugarTest.createView('base', module, 'drive-path-root-path', null, null, true, parentLayout);
    });

    afterEach(function() {
        sinonSandbox.restore();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var loadPathsStub;
        var getModuleListStub;
        beforeEach(function() {
            loadPathsStub = sinonSandbox.stub(view, 'loadPaths');
            getModuleListStub = sinonSandbox.stub(view, 'getModuleList');

            view.initialize({context: context});
        });

        it('should call loadPaths', function() {
            expect(loadPathsStub).toHaveBeenCalled();
        });

        it('should call getModuleList', function() {
            expect(getModuleListStub).toHaveBeenCalled();
        });
    });

    describe('loadPaths', function() {
        var loadPathsStub;

        beforeEach(function() {
            loadPathsStub = sinonSandbox.stub(view, 'loadPaths');

            view.loadPaths();
        });

        it('should call loadPaths', function() {
            expect(loadPathsStub).toHaveBeenCalled();
        });
    });

    describe('_renderPaths', function() {
        var renderStub;

        beforeEach(function() {
            var data = {
                records: []
            };
            sinon.stub(app.lang, 'getAppString').withArgs('LBL_MY_FILES').returns('My files');
            renderStub = sinonSandbox.stub(view, 'render');

            view._renderPaths(data);
        });

        it('should check if paths exist', function() {
            expect(view.paths).toEqual([{
                path: '',
                pathDisplay: 'My files',
            }]);
        });

        it('should call render', function() {
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('_render', function() {
        var initDropdownsStub;

        beforeEach(function() {
            initDropdownsStub = sinonSandbox.stub(view, 'initDropdowns');

            view._render();
        });

        it('should call render', function() {
            expect(initDropdownsStub).toHaveBeenCalled();
        });
    });

    describe('getModuleList', function() {
        beforeEach(function() {
            view.getModuleList();
        });

        it('should call render', function() {
            expect(view.modules.length).toNotEqual(0);
        });
    });

    describe('removePath', function() {
        var apiMock;
        var mockEvent;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('select');
            mockEvent.target.classList = ['moduleList'];

            view.removePath(mockEvent);
        });

        it('should remove path', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
