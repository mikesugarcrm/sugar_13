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
describe('Administration.View.DrivePathSelect', function() {
    var app = SUGAR.App;
    var sinonSandbox;
    var view;
    var viewName = 'drive-path-select';
    var module = 'Administration';
    var meta;
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'layout', 'drive-path-select', module);
        SugarTest.loadComponent('base', 'view', viewName, module);
        SugarTest.loadComponent('base', 'view', 'drive-path-buttons', module);

        SugarTest.loadHandlebarsTemplate('drive-path-select', 'view', 'base', null, module);

        SugarTest.testMetadata.addLayoutDefinition('drive-path-select', {
            'components': [
                {
                    'view': 'drive-path-buttons'
                },
                {
                    'view': 'drive-path-select'
                },
            ],
        });

        context = app.context.getContext();
        context.set({
            isRoot: false,
            pathModule: 'Accounts',
            parentId: '1'
        });

        context.prepare();
        SugarTest.testMetadata.set();

        sinonSandbox = sinon.createSandbox();
        sinon.stub(app.lang, 'getAppString').withArgs('LBL_MY_FILES').returns('My files');

        parentLayout = SugarTest.createLayout('base', module, 'drive-path-select', null, null, true);

        view = SugarTest.createView('base', module, viewName, meta, context, true, parentLayout);
        selectButtonsView = SugarTest.createView('base', module, 'drive-path-buttons', meta, null, true, parentLayout);
    });

    afterEach(function() {
        sinon.restore();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var loadFoldersStub;
        beforeEach(function() {
            loadFoldersStub = sinonSandbox.stub(view, 'loadFolders');
        });

        it('should return default folder path', function() {
            expect(view.currentPathFolders).toEqual([{name: 'My files', folderId: 'root'}]);
            expect(view.currentPathFolders).toBeDefined();
        });

        it('should call loadFolders', function() {
            view.initialize({context: context});
            expect(view.loadFolders).toHaveBeenCalled();
        });
    });

    describe('loadFolders', function() {
        var apiMock;
        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            view.loadFolders();
        });

        it('should call for folder list', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('intoFolder', function() {
        var loadFoldersStub;
        var mockEvent;

        beforeEach(function() {
            loadFoldersStub = sinonSandbox.stub(view, 'loadFolders');
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('a');
            mockEvent.target.dataset.id = '1';
            mockEvent.target.dataset.name = 'Accounts';
            selectButtonsView.sharedWithMe = true;

            view.intoFolder(mockEvent);
        });

        it('should check if parent id is current folder id', function() {
            expect(view.parentId).toEqual('root');
        });

        it('should call loadFodlers', function() {
            expect(loadFoldersStub).toHaveBeenCalled();
        });
    });

    describe('setFolder', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('a');
            mockEvent.target.dataset.id = '1';
            mockEvent.target.dataset.name = 'Accounts';

            view.setFolder(mockEvent);
        });

        it('should check if parent id is current folder id', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
