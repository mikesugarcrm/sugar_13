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
describe('pmse_Emails_Templates.Base.Views.dashlet-email', function() {
    var app;
    var view;
    //var viewMeta;
    var model;
    var context;

    beforeEach(function() {
        app = SugarTest.app;

        // TODO: Find out if a custom metadata object would be better to use
        // and if so how to do that.
        context = app.context.getContext();
        model = app.data.createBean('pmse_Emails_Templates');
        context.set('model', model);
        view = SugarTest.createView('base', 'pmse_Emails_Templates', 'dashlet-email', null, context, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('initialize', function() {

        beforeEach(function() {
            sinon.stub(view, '_super');
        });

        it('should initialize the proper stuff', function() {
            var options = {
                meta: {
                    toast: 'burnt'
                }
            };

            view.plugins = ['toast'];

            view.initialize(options);

            expect(options.meta.template).toEqual('tabbed-dashlet');
            expect(view.plugins).toEqual(['toast', 'LinkedModel']);
            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should make options.meta if it does not exist', function() {
            var emptyOptions = {};

            view.initialize(emptyOptions);

            expect(view._super).toHaveBeenCalled();
            expect(view.plugins).toEqual(['LinkedModel']);
            expect(emptyOptions.meta.template).toEqual('tabbed-dashlet');
        });

        it('should not add the LinkedModel plugin twice', function() {
            var stuff = {};
            view.plugins = ['LinkedModel'];

            view.initialize(stuff);

            expect(view._super).toHaveBeenCalled();
            expect(view.plugins).toEqual(['LinkedModel']);
            expect(stuff.meta.template).toEqual('tabbed-dashlet');
        });

    });

    describe('_initEvents', function() {

        beforeEach(function() {
            sinon.stub(view, '_super');
            sinon.stub(view, 'on');
        });

        it('should initialize the needed events', function() {
            view._initEvents();

            expect(view._super).toHaveBeenCalledWith('_initEvents');
            expect(view.on).toHaveBeenCalledWith('dashlet-email:edit:fire', view.editRecord, view);
            expect(view.on).toHaveBeenCalledWith('dashlet-email:delete-record:fire', view.deleteRecord, view);
            expect(view.on).toHaveBeenCalledWith('dashlet-email:enable-record:fire', view.enableRecord, view);
            expect(view.on).toHaveBeenCalledWith('dashlet-email:download:fire', view.warnExportEmailsTemplates, view);
            expect(view.on).toHaveBeenCalledWith('dashlet-email:description-record:fire', view.descriptionRecord, view);
            expect(view.on).toHaveBeenCalledWith('linked-model:create', view.loadData, view);
        });

    });

    // FIXME: Remove along with original function when SC-4775 is implemented.
    describe('_reloadData', function() {

        beforeEach(function() {
            sinon.stub(view.context, 'set');
            sinon.stub(view.context, 'reloadData');
        });

        it('should not skip fetching and reload the data', function() {
            view._reloadData();

            expect(view.context.set).toHaveBeenCalledWith('skipFetch', false);
            expect(view.context.reloadData).toHaveBeenCalled();
        });

    });

    describe('editRecord', function() {
        var fakeModel;
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            fakeModel = {get: sinon.stub().returns('toast')};
            sinon.stub(view, '_onEditRecordVerify');
        });

        it('should fire a request to the correct URL', function() {
            var testURL = app.api.buildURL(
                'pmse_Project',
                'verify',
                {id: fakeModel.get('id')},
                {baseModule: view.module});

            view.editRecord(fakeModel);
            expect(view._modelToEdit).toEqual(fakeModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', testURL, null, jasmine.any(Object));
        });

        it('should call _onEditRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.editRecord(fakeModel);

            expect(app.api.call).toHaveBeenCalled();
            expect(view._modelToEdit).toEqual(fakeModel);
            //Check that it is called on success here
            expect(view._onEditRecordVerify).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onEditRecordVerify', function() {
        var redirectLink;
        var alertMock;
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                module: 'goat',
                id: 'llama'
            };
            view._modelToEdit = fakeModel;
            redirectLink = fakeModel.module + '/' + fakeModel.id + '/layout/emailtemplates';
            alertMock = sinon.stub(app.alert, 'show');
            sinon.stub(view, '_onWarnEditActiveRecordConfirm');
            app.router = {navigate: sinon.stub()};
            sinon.stub(app.lang, 'get').returns('a');
        });

        afterEach(function() {
            app.router = null;
            view._modelToEdit = null;
        });

        it('should not warn the user if the template is not in use', function() {

            view._onEditRecordVerify(false);

            expect(app.router.navigate).toHaveBeenCalledWith(redirectLink, {trigger: true, replace: true});
        });

        it('should warn the user that the template is currently in use', function() {

            view._onEditRecordVerify(true);

            var staticAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_PROCESS_EMAIL_TEMPLATES_EDIT', model.module)
            };

            expect(app.router.navigate).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('email-templates-edit-confirmation',
                jasmine.objectContaining(staticAlertData));
        });

        it('should call _onWarnEditActiveRecordConfirm if user confirms alert', function() {
            alertMock.yieldsTo('onConfirm');

            view._onEditRecordVerify(true);

            expect(view._onWarnEditActiveRecordConfirm).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnEditActiveRecordConfirm', function() {

        beforeEach(function() {
            app.router = {navigate: sinon.stub()};
        });

        afterEach(function() {
            app.router = null;
        });

        it('should navigate the user', function() {
            var link = 'beepboopbeep';

            view._onWarnEditActiveRecordConfirm(link);

            expect(app.router.navigate).toHaveBeenCalledWith(link, {trigger: true, replace: true});
        });
    });

    describe('_onWarnEditActiveRecordCancel', function() {

        it('should unset the module to be edited', function() {
            view._modelToEdit = 'goat';

            view._onWarnEditActiveRecordCancel();

            expect(view._modelToEdit).toEqual(null);
        });
    });

    describe('warnExportEmailsTemplates', function() {
        // Defined here to allow for customized returns/yields for each test.
        var alertMock;
        var cacheGetMock;

        beforeEach(function() {
            cacheGetMock = sinon.stub(app.cache, 'get');
            alertMock = sinon.stub(app.alert, 'show');

            sinon.stub(view, '_onWarnExportEmailsTemplatesConfirm');
            sinon.stub(view, 'exportEmailsTemplates');
            sinon.stub(app.lang, 'get').returns('a');
        });

        it('should warn the user if the warning cache is not set to false', function() {
            cacheGetMock.returns(true);
            var someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_EXPORT_CONFIRMATION')
            };

            view.warnExportEmailsTemplates({a: 'b'});

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));
        });

        it('should call _onWarnExportEmailsTemplatesConfirm if user confirms the warning', function() {
            cacheGetMock.returns(true);
            alertMock.yieldsTo('onConfirm');

            var someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_EXPORT_CONFIRMATION')
            };

            view.warnExportEmailsTemplates({a: 'b'});

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));

            //Check that it is called on success here
            expect(view._onWarnExportEmailsTemplatesConfirm).toHaveBeenCalled();
        });

        it('should call exportEmailsTemplates directly if the warning cache is set to false', function() {
            cacheGetMock.returns(false);
            view.warnExportEmailsTemplates({a: 'b'});

            expect(view.exportEmailsTemplates).toHaveBeenCalledWith({a: 'b'});
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnExportEmailsTemplatesConfirm', function() {

        beforeEach(function() {
            sinon.stub(app.cache, 'set');
            sinon.stub(view, 'exportEmailsTemplates');
        });

        it('should set the cache and export the template', function() {

            view._onWarnExportEmailsTemplatesConfirm({a: 'b'});

            expect(app.cache.set).toHaveBeenCalledWith('show_emailtpl_export_warning', false);
            expect(view.exportEmailsTemplates).toHaveBeenCalledWith({a: 'b'});
        });
    });

    describe('exportEmailsTemplates', function() {
        var apiMock;

        var fakeModel = {
            module: 'dromedary',
            id: 'camel'
        };
        // So tests can set the return value.
        var emptyStub;

        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').returns('goat');
            apiMock = sinon.stub(app.api, 'fileDownload');
            sinon.stub(app.logger, 'error');
            emptyStub = sinon.stub(_, 'isEmpty').returns(false);
            app.config.platform = 'goat';
            sinon.stub(view, '_onExportEmailsTemplatesDownloadError');
        });

        it('should call the api to download the file', function() {
            var url = app.api.buildURL(fakeModel.module,
                'etemplate',
                {id: fakeModel.id},
                {platform: app.config.platform});

            view.exportEmailsTemplates(fakeModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(url, jasmine.any(Object), jasmine.any(Object));
        });

        it('should not download when the url is broken', function() {
            emptyStub.returns(true);
            view.exportEmailsTemplates(fakeModel);

            expect(app.logger.error).toHaveBeenCalled();
            expect(app.api.fileDownload).not.toHaveBeenCalled();
        });

        it('should call _onExportEmailsTemplatesDownloadError when download fails', function() {
            apiMock.yieldsTo('error');
            var url = app.api.buildURL(fakeModel.module,
                'etemplate',
                {id: fakeModel.id},
                {platform: app.config.platform});

            view.exportEmailsTemplates(fakeModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(url, jasmine.any(Object), jasmine.any(Object));
            expect(view._onExportEmailsTemplatesDownloadError).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onExportEmailsTemplatesDownloadError', function() {

        beforeEach(function() {
            sinon.stub(app.error, 'handleHttpError');
        });

        it('should call handleHttpError', function() {

            view._onExportEmailsTemplatesDownloadError('goat');

            expect(app.error.handleHttpError).toHaveBeenCalledWith('goat', {});
        });
    });

    //FIXME: Remove this test after the original function is removed.
    // Function is @private so it does not show up properly here in IDE.
    describe('_initTabs', function() {

        beforeEach(function() {
            sinon.stub(view, '_super');
        });

        it('should call super', function() {

            view._initTabs();

            expect(view._super).toHaveBeenCalledWith('_initTabs');
        });
    });

    describe('createRecord', function() {
        var fakeParams = {module: 'goat', link: 'blah'};

        beforeEach(function() {
            sinon.stub(view, 'createRelatedRecord');
            // Use .callsArg to simulate a callback
            app.drawer = {open: sinon.stub().callsArg(1)};
            sinon.stub(view, '_onCreateRecordDrawerClose');
        });

        it('should create the related record for non-self modules', function() {
            view.module = 'notEmails';
            view.createRecord(null, fakeParams);

            expect(view.createRelatedRecord).toHaveBeenCalledWith('goat', 'blah');
            expect(app.drawer.open).not.toHaveBeenCalled();
        });

        it('should not create related records for self and instead open the record', function() {
            view.module = 'pmse_Emails_Templates';
            view.createRecord(null, fakeParams);

            expect(view.createRelatedRecord).not.toHaveBeenCalled();
            expect(app.drawer.open).toHaveBeenCalled();
            // Assume open succeeded.
            expect(view._onCreateRecordDrawerClose).toHaveBeenCalled();
        });
    });

    describe('_onCreateRecordDrawerClose', function() {
        var isFunctionMock;

        beforeEach(function() {
            sinon.stub(view.context, 'resetLoadFlag');
            sinon.stub(view.context, 'set');
            sinon.stub(view.context, 'loadData');
            sinon.stub(view, 'loadData');
            isFunctionMock = sinon.stub(_, 'isFunction');
        });

        it('should call view.loadData if it is a function and reset the load flag', function() {
            isFunctionMock.returns(true);

            view._onCreateRecordDrawerClose('goat', true);

            expect(view.context.resetLoadFlag).toHaveBeenCalled();
            expect(view.context.set).toHaveBeenCalledWith('skipFetch', false);
            expect(view.loadData).toHaveBeenCalled();
            expect(view.context.loadData).not.toHaveBeenCalled();
        });

        it('should call view.context.loadData if view.loadData is not a function', function() {
            isFunctionMock.returns(false);

            view._onCreateRecordDrawerClose('goat', true);

            expect(view.context.resetLoadFlag).toHaveBeenCalled();
            expect(view.context.set).toHaveBeenCalledWith('skipFetch', false);
            expect(view.loadData).not.toHaveBeenCalled();
            expect(view.context.loadData).toHaveBeenCalled();
        });

        it('should do nothing if there is no model returned', function() {

            view._onCreateRecordDrawerClose('goat', false);

            expect(view.context.resetLoadFlag).not.toHaveBeenCalled();
            expect(view.context.set).not.toHaveBeenCalled();
            expect(view.loadData).not.toHaveBeenCalled();
            expect(view.context.loadData).not.toHaveBeenCalled();
        });
    });

    describe('importRecord', function() {

        beforeEach(function() {
            app.router = {navigate: sinon.stub()};
        });

        afterEach(function() {
            app.router = null;
        });

        it('should navigate to the record', function() {
            view.importRecord({}, {link: 'testGoat'});

            expect(app.router.navigate).toHaveBeenCalledWith('testGoat');
        });
    });

    describe('deleteRecord', function() {
        var fakeModel;
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            fakeModel = {get: sinon.stub().returns('toast')};
            sinon.stub(view, '_onDeleteRecordVerify');
        });

        it('should fire a request to the correct URL and set the model to delete', function() {
            var testURL = app.api.buildURL(
                'pmse_Project',
                'verify',
                {id: fakeModel.get('id')},
                {baseModule: view.module});

            view.deleteRecord(fakeModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', testURL, null, jasmine.any(Object));
            expect(view._modelToDelete).toEqual(fakeModel);
        });

        it('should call _onDeleteRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.deleteRecord(fakeModel);

            expect(app.api.call).toHaveBeenCalled();
            //Check that it is called on success here
            expect(view._onDeleteRecordVerify).toHaveBeenCalled();
        });
    });

    describe('_onDeleteRecordVerify', function() {
        var alertMock;
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                module: 'goat',
                id: 'llama'
            };
            view._modelToDelete = fakeModel;

            alertMock = sinon.stub(app.alert, 'show');
            sinon.stub(view, '_onWarnDeleteInactiveRecordConfirm');
            sinon.stub(view, '_onWarnDeleteInactiveRecordCancel');
            sinon.stub(app.lang, 'get').returns('a');
            sinon.stub(app.utils, 'formatString').returns('a');
        });

        afterEach(function() {
            view._modelToDelete = null;
        });

        it('should double check with the user when the template is not in use', function() {

            view._onDeleteRecordVerify(false);

            expect(app.alert.show).toHaveBeenCalledWith('delete_confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });

        it('should call _onWarnDeleteInactiveRecordConfirm if user confirms the confirmation', function() {
            alertMock.yieldsTo('onConfirm');

            view._onDeleteRecordVerify(false);

            expect(view._onWarnDeleteInactiveRecordConfirm).toHaveBeenCalled();
        });

        it('should call _onWarnDeleteInactiveRecordCancel if user cancels the confirmation', function() {
            alertMock.yieldsTo('onCancel');

            view._onDeleteRecordVerify(false);

            expect(view._onWarnDeleteInactiveRecordCancel).toHaveBeenCalled();
        });

        it('should explicitly warn the user if the template is already in use', function() {

            view._onDeleteRecordVerify(true);

            expect(app.alert.show).toHaveBeenCalledWith('message-id',
                jasmine.objectContaining({level: 'warning', autoClose: false}));
            expect(view._modelToDelete).toEqual(null);
        });
    });

    describe('_onWarnDeleteInactiveRecordConfirm', function() {
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                destroy: sinon.stub().yieldsTo('success')
            };
            view._modelToDelete = fakeModel;

            sinon.stub(view, '_getRemoveRecord');
        });

        afterEach(function() {
            view._modelToDelete = null;
        });

        it('should destroy the model and call the right callback on success', function() {

            view._onWarnDeleteInactiveRecordConfirm();

            expect(fakeModel.destroy).toHaveBeenCalledWith(jasmine.objectContaining({showAlerts: true}));
            expect(view._getRemoveRecord).toHaveBeenCalled();
        });
    });

    describe('_onWarnDeleteInactiveRecordCancel', function() {

        it('should unset the module to be deleted', function() {
            view._modelToDelete = 'goat';

            view._onWarnDeleteInactiveRecordCancel();

            expect(view._moduleToDelete).toEqual(null);
        });
    });

    describe('_getRemoveRecord', function() {
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                module: 'goat',
                id: 'llama'
            };
            view._modelToDelete = fakeModel;

            view.collection = {remove: sinon.stub()};
            sinon.stub(view, 'render');
            sinon.stub(view.context, 'trigger');
        });

        afterEach(function() {
            // view.dispose takes a big shit the fake collection is still around.
            view.collection = null;
        });

        it('should remove the model and refresh the dashlet', function() {
            view.disposed = false;

            view._getRemoveRecord(fakeModel);

            expect(view.collection.remove).toHaveBeenCalledWith(fakeModel);
            expect(view.render).toHaveBeenCalled();
            expect(view.context.trigger).toHaveBeenCalledWith('tabbed-dashlet:refresh', fakeModel.module);
        });

        it('should not do anything if the view is already disposed', function() {
            view.disposed = true;

            view._getRemoveRecord(fakeModel);

            expect(view.collection.remove).not.toHaveBeenCalled();
            expect(view.render).not.toHaveBeenCalled();
            expect(view.context.trigger).not.toHaveBeenCalled();
        });
    });

    describe('_refresh', function() {
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                module: 'goat',
                id: 'llama'
            };

            sinon.stub(view, '_refreshReturn');
            sinon.stub(app.alert, 'show');
        });

        it('should show a refresh diddly and return _refreshReturn', function() {
            var refreshReturnFunc = view._refresh(fakeModel, 'spoopy');

            expect(app.alert.show).toHaveBeenCalledWith('llama:refresh', {
                level: 'process',
                title: 'spoopy',
                autoclose: false
            });

            // Check that after whatever binding and stuff that the right function was returned.
            refreshReturnFunc();

            expect(view._refreshReturn).toHaveBeenCalled();
        });
    });

    describe('_refreshReturn', function() {
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                module: 'goat',
                id: 'llama'
            };

            view.layout = {reloadDashlet: sinon.stub()};
            sinon.stub(app.alert, 'dismiss');
        });

        afterEach(function() {
            view.layout = null;
        });

        it('should do what it did before I refactored it', function() {
            view._refreshReturn(fakeModel);

            expect(view.layout.reloadDashlet).toHaveBeenCalled();
            expect(app.alert.dismiss).toHaveBeenCalledWith('llama:refresh');
        });
    });

    describe('descriptionRecord', function() {
        var fakeModel;

        beforeEach(function() {
            fakeModel = {
                get: sinon.stub().returns('goat')
            };

            sinon.stub(app.lang, 'get').returns('hi');
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show');
        });

        it('should dismiss and show an alert', function() {
            view.descriptionRecord(fakeModel);

            expect(app.alert.dismiss).toHaveBeenCalledWith('message-id');
            expect(app.alert.show).toHaveBeenCalledWith('message-id', {
                level: 'info',
                title: 'hi',
                messages: '<br/>goat',
                autoClose: false
            });
        });
    });

    describe('_setRelativeTimeAvailable', function() {
        var fakeDiff;

        beforeEach(function() {
            fakeDiff = sinon.stub();
            sinon.stub(app, 'date').returns({diff: fakeDiff});
            // Re-setting value here because init might not happen.
            view.thresholdRelativeTime = 2;
        });

        it('should use relative time for < 2 days', function() {
            fakeDiff.returns(1.5);

            var rt = view._setRelativeTimeAvailable();

            expect(rt).toEqual(true);
        });

        it('should use relative time for = 2 days', function() {
            fakeDiff.returns(2.0);

            var rt = view._setRelativeTimeAvailable();

            expect(rt).toEqual(true);
        });

        it('should not use relative time for > 2 days', function() {
            fakeDiff.returns(2.3);

            var rt = view._setRelativeTimeAvailable();

            expect(rt).toEqual(false);
        });
    });

    describe('_renderHtml', function() {

        beforeEach(function() {
            sinon.stub(view, '_super');
            sinon.stub(view, '_renderItemHtml');
            view.collection = {models: [1, 2, 3, 4, 5]};
        });

        afterEach(function() {
            view.collection = null;
        });

        it('should skip rendering when meta.config is truthy', function() {
            view.meta.config = true;

            view._renderHtml();

            expect(view._super).toHaveBeenCalledWith('_renderHtml');
            expect(view._renderItemHtml).not.toHaveBeenCalled();
        });

        it('should render each emails template when there is no meta.config', function() {

            view._renderHtml();

            expect(view._super).toHaveBeenCalledWith('_renderHtml');
            _.each(view.collection.models, function(model) {
                expect(view._renderItemHtml).toHaveBeenCalledWith(model);
            });
        });
    });

    describe('_renderItemHtml', function() {
        var fakeModel;
        var modStringMock;

        beforeEach(function() {
            sinon.stub(view, '_setRelativeTimeAvailable').returns('blort');
            fakeModel = {
                get: sinon.stub().returns('toast'),
                attributes: {date_entered: 21},
                set: sinon.stub()
            };
            modStringMock = sinon.stub(app.lang, 'getModString').returns('goat');
        });

        it('should get the stuff needed to render', function() {

            view._renderItemHtml(fakeModel);

            expect(view._setRelativeTimeAvailable).toHaveBeenCalledWith(fakeModel.attributes.date_entered);
            expect(fakeModel.useRelativeTime).toEqual('blort');
            expect(fakeModel.set).toHaveBeenCalledWith('base_module_name', 'goat');
        });

        it('should set base module name to the module name if it is not set', function() {
            modStringMock.returns(undefined);

            view._renderItemHtml(fakeModel);

            expect(view._setRelativeTimeAvailable).toHaveBeenCalledWith(fakeModel.attributes.date_entered);
            expect(fakeModel.useRelativeTime).toEqual('blort');
            expect(fakeModel.set).toHaveBeenCalledWith('base_module_name', 'toast');
        });
    });
});
