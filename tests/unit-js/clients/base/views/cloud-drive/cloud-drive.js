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
describe('Base.View.CloudDrive', function() {
    let app;
    let view;
    let context;
    let initPopoversStub;
    let hidePopoverStub;
    let disposeDropdownsStub;
    let disposePopoversStub;
    let viewName = 'cloud-drive';
    let sandbox = sinon.createSandbox();
    let module = 'Accounts';
    let meta;
    let model;
    let layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        app = SugarTest.app;

        model = app.data.createBean(module);

        context = new app.Context();

        context.set({
            module: module,
            model: model,
        });

        meta = {
            config: false
        };

        layout = SugarTest.createLayout(
            'base',
            module,
            'list',
            null,
            context
        );

        sinon.stub(app.api, 'call');

        view = SugarTest.createView(
            'base',
            null,
            viewName,
            meta,
            context,
            false,
            layout,
            true
        );

        initPopoversStub = sandbox.stub(view, 'initPopovers');
        hidePopoverStub = sandbox.stub(view, 'hidePopover').callsFake(function() {
            return true;
        });
        disposeDropdownsStub = sandbox.stub(view, 'disposeDropdowns');
        disposePopoversStub = sandbox.stub(view, 'disposePopovers');
        sinon.stub(app.controller.context, 'get').callsFake(function() {
            return 'record';
        });
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        view.dispose();
        app.view.reset();
        sinon.restore();
    });

    describe('loadFiles', function() {
        beforeEach(function() {
            view.folderId = '123';
            view.loadFiles();
        });

        it('should call cloud api', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('displayItems', function() {
        var renderStub;

        beforeEach(function() {
            renderStub = sandbox.stub(view,'render');
            var data = {
                files: [],
                nextPageToken: ''
            };

            view.displayItems(data);
        });

        it('should check if data has an array of files', function() {
            expect(view.files).toEqual([]);
        });

        it('should call render', function() {
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('getRootFolder', function() {
        beforeEach(function() {
            view.getRootFolder();
        });

        it('should call google api', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('intoFolder', function() {
        var mockEvent;
        var getParentStub;

        beforeEach(function() {
            getParentStub = sandbox.stub(view, 'getParent');
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('a');
            mockEvent.target.dataset.id = '1';
            mockEvent.target.dataset.name = 'Accounts';
            view.sharedWithMe = true;
            view.folderId = '123';
            view.parentIds = [];
            view.pathFolders = [];
            view.intoFolder(mockEvent);
        });

        it('should call getParent', function() {
            expect(getParentStub).toHaveBeenCalled();
        });
    });
});
