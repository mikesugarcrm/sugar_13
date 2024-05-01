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
describe('pmse_Emails_Templates.Base.Views.recordlist', function() {
    var app;
    var view;
    var model;
    var context;
    // Generic data for use in tests
    var genericObjects;
    var genericModel;
    var genericVerifyUrl;
    var genericStrings;

    beforeEach(function() {
        app = SugarTest.app;

        context = app.context.getContext();
        model = app.data.createBean('pmse_Emails_Templates');
        context.set('model', model);
        view = SugarTest.createView('base', 'pmse_Emails_Templates', 'recordlist', null, context, true);

        // Common data to use in test cases.
        genericObjects = [
            {goat: 'baa'},
            {cat: 'meow'}
        ];

        genericStrings = ['goat', 'poop'];

        genericModel = {
            module: 'goat',
            id: 'llama',
            get: sinon.stub().returns('toast')
        };

        genericVerifyUrl = app.api.buildURL(
            'pmse_Project',
            'verify',
            {id: genericModel.get('id')},
            {baseModule: view.module});
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
        var options;

        beforeEach(function() {
            options = genericObjects[1];
            sinon.stub(view, '_super');
            view.contextEvents = genericObjects[0];
        });

        afterEach(function() {
            view.contextEvents = null;
        });

        it('should add context events and call parent init function', function() {

            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
            expect(view.contextEvents).toEqual(jasmine.objectContaining({
                'list:editemailstemplates:fire': 'openEmailsTemplates',
                'list:exportemailstemplates:fire': 'warnExportEmailsTemplates',
                'list:deleteemailstemplates:fire': 'warnDeleteEmailsTemplates',
                'list:edit_emailstemplates:fire': 'warnEditEmailsTemplates'
            }));
        });

        it('should not remove existing context events', function() {

            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
            expect(view.contextEvents).toEqual(jasmine.objectContaining(genericObjects[0]));
        });
    });

    describe('openEmailsTemplates', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            sinon.stub(view, '_onDesignRecordVerify');
            view._modelToDesign = null;
        });

        it('should fire a request to the correct URL', function() {

            view.openEmailsTemplates(genericModel);
            expect(view._modelToDesign).toEqual(genericModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', genericVerifyUrl, null, jasmine.any(Object));
        });

        it('should call _onDesignRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.openEmailsTemplates(genericModel);

            expect(app.api.call).toHaveBeenCalled();
            expect(view._modelToDesign).toEqual(genericModel);
            //Check that it is called on success here
            expect(view._onDesignRecordVerify).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onDesignRecordVerify', function() {
        var alertMock;

        beforeEach(function() {
            view._modelToDesign = genericModel;

            alertMock = sinon.stub(app.alert, 'show');
            sinon.stub(app, 'navigate');
            sinon.stub(app.lang, 'get').returns('a');
        });

        afterEach(function() {
            view._modelToDesign = null;
            alertMock = null;
        });

        it('should not warn the user if the template is not in use', function() {

            view._onDesignRecordVerify(false);

            expect(app.navigate).toHaveBeenCalledWith(view.context, genericModel, 'layout/emailtemplates');
        });

        it('should warn the user that the template is currently in use', function() {

            view._onDesignRecordVerify(true);

            expect(app.navigate).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('business-rule-design-confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });

        it('should start the designer if user confirms alert', function() {
            alertMock.yieldsTo('onConfirm');

            view._onDesignRecordVerify(true);

            expect(app.navigate).toHaveBeenCalledWith(view.context, genericModel, 'layout/emailtemplates');
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnDesignActiveRecordConfirm', function() {

        beforeEach(function() {
            sinon.stub(app, 'navigate');
        });

        it('should navigate to the designer', function() {

            view._onWarnDesignActiveRecordConfirm(genericStrings[0]);

            expect(app.navigate).toHaveBeenCalledWith(view.context, genericStrings[0], 'layout/emailtemplates');
        });
    });

    describe('warnEditEmailsTemplates', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            sinon.stub(view, '_onEditRecordVerify');
            view._modelToEdit = null;
        });

        it('should fire a request to the correct URL', function() {

            view.warnEditEmailsTemplates(genericModel);
            expect(view._modelToEdit).toEqual(genericModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', genericVerifyUrl, null, jasmine.any(Object));
        });

        it('should call _onEditRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.warnEditEmailsTemplates(genericModel);

            expect(app.api.call).toHaveBeenCalled();
            expect(view._modelToEdit).toEqual(genericModel);
            //Check that it is called on success here
            expect(view._onEditRecordVerify).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onEditRecordVerify', function() {
        var alertMock;

        beforeEach(function() {
            view._modelToEdit = genericModel;

            alertMock = sinon.stub(app.alert, 'show');
            view.toggleRow = sinon.stub();
            view.resize = sinon.stub();
            sinon.stub(app.lang, 'get').returns('a');
            sinon.stub(view, '_onWarnEditActiveRecordConfirm');
        });

        afterEach(function() {
            view._modelToEdit = null;
            alertMock = null;
            view.toggleRow = null;
            view.resize = null;
        });

        it('should not warn the user if the template is not in use', function() {

            view._onEditRecordVerify(false);

            expect(view.toggleRow).toHaveBeenCalled();
            expect(view.resize).toHaveBeenCalled();
        });

        it('should warn the user that the template is currently in use', function() {

            view._onEditRecordVerify(true);

            expect(view.toggleRow).not.toHaveBeenCalled();
            expect(view.resize).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('business-rule-design-confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
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
            view.toggleRow = sinon.stub();
            view.resize = sinon.stub();
            view._modelToEdit = genericStrings[0];
        });

        afterEach(function() {
            view.toggleRow = null;
            view.resize = null;
            view._modelToEdit = null;
        });

        it('should start editing and clear the _modelToEdit', function() {

            view._onWarnEditActiveRecordConfirm();

            expect(view.toggleRow).toHaveBeenCalled();
            expect(view.resize).toHaveBeenCalled();
            expect(view._modelToEdit).toEqual(null);
        });
    });

    describe('warnDeleteEmailsTemplates', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            sinon.stub(view, '_onDeleteRecordVerify');
        });

        it('should fire a request to the correct URL and set the model to delete', function() {

            view.warnDeleteEmailsTemplates(genericModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', genericVerifyUrl, null, jasmine.any(Object));
            expect(view._modelToDelete).toEqual(genericModel);
        });

        it('should call _onDeleteRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.warnDeleteEmailsTemplates(genericModel);

            expect(app.api.call).toHaveBeenCalled();
            //Check that it is called on success here
            expect(view._onDeleteRecordVerify).toHaveBeenCalled();
        });
    });

    describe('_onDeleteRecordVerify', function() {

        beforeEach(function() {
            sinon.stub(view, '_warnDeleteInactiveRecord');
            sinon.stub(view, '_blockDeleteActiveRecord');
        });

        it('should call _warnDeleteInactiveRecord if the template is not in use', function() {

            view._onDeleteRecordVerify(false);

            expect(view._warnDeleteInactiveRecord).toHaveBeenCalled();
        });

        it('should call _blockDeleteActiveRecord if the template is in use', function() {

            view._onDeleteRecordVerify(true);

            expect(view._blockDeleteActiveRecord).toHaveBeenCalled();
        });
    });

    describe('_warnDeleteInactiveRecord', function() {
        var alertMock;
        var historyMock;

        beforeEach(function() {
            view._modelToDelete = genericModel;

            // Mocks that need to be configured.
            alertMock = sinon.stub(app.alert, 'show');
            historyMock = sinon.stub(Backbone.history, 'getFragment').returns(genericStrings[0]);
            // Regular mocks.
            app.router = {navigate: sinon.stub()};
            sinon.stub(view, '_onWarnDeleteInactiveRecordConfirm');
            sinon.stub(view, '_clearModelToDelete');
            view.getDeleteMessages = sinon.stub().returns({confirmation: genericStrings[0]});
            // This is used by the function, so set it to something we know.
            view._currentUrl = genericStrings[0];
        });

        afterEach(function() {
            view._modelToDelete = null;
            view._targetUrl = null;
            app.router = null;
            view.getDeleteMessages = null;
        });

        it('should warn the user and replace the url hash when url does not match', function() {
            // Is used to set view._targetUrl
            historyMock.returns('baa');

            view._warnDeleteInactiveRecord();

            expect(app.router.navigate).toHaveBeenCalledWith(view._currentUrl,
                {trigger: false, replace: true});
            expect(app.alert.show).toHaveBeenCalledWith('delete_confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });

        it('should warn the user and not replace the url when the url matches', function() {
            // Is used to set view._targetUrl
            historyMock.returns(genericStrings[0]);

            view._warnDeleteInactiveRecord();

            expect(app.router.navigate).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('delete_confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });

        it('should call _onWarnDeleteInactiveRecordConfirm if user confirms the confirmation', function() {
            alertMock.yieldsTo('onConfirm');

            view._warnDeleteInactiveRecord();

            expect(view._onWarnDeleteInactiveRecordConfirm).toHaveBeenCalled();
        });

        it('should call _clearModelToDelete if user cancels the confirmation', function() {
            alertMock.yieldsTo('onCancel');

            view._warnDeleteInactiveRecord();

            expect(view._clearModelToDelete).toHaveBeenCalled();
        });
    });

    describe('_blockDeleteActiveRecord', function() {
        var alertMock;

        beforeEach(function() {
            view._modelToDelete = genericModel;

            alertMock = sinon.stub(app.alert, 'show');
            alertMock = sinon.stub(view, '_clearModelToDelete');
            sinon.stub(app.lang, 'get').returns('a');
        });

        afterEach(function() {
            view._modelToDelete = null;
        });

        it('should block deletion of the active template and unset _modelToDelete', function() {
            view._onDeleteRecordVerify(true);

            expect(app.alert.show).toHaveBeenCalledWith('message-id',
                jasmine.objectContaining({level: 'warning', autoClose: false}));
            expect(view._clearModelToDelete).toHaveBeenCalled();
        });
    });

    describe('_onWarnDeleteInactiveRecordConfirm', function() {

        beforeEach(function() {
            view._modelToDelete = genericModel;

            view.deleteModel = sinon.stub();
        });

        afterEach(function() {
            view._modelToDelete = null;
            view.deleteModel = null;
        });

        it('should delete the model', function() {

            view._onWarnDeleteInactiveRecordConfirm();

            expect(view.deleteModel).toHaveBeenCalled();
        });
    });

    describe('_clearModelToDelete', function() {

        it('should unset the module to be deleted', function() {
            view._modelToDelete = genericStrings[0];

            view._clearModelToDelete();

            expect(view._moduleToDelete).toEqual(null);
        });
    });

    describe('warnExportEmailsTemplates', function() {
        // Defined here to allow for customized returns/yields for each test.
        var alertMock;
        var cacheGetMock;
        var someAlertData;

        beforeEach(function() {
            cacheGetMock = sinon.stub(app.cache, 'get');
            alertMock = sinon.stub(app.alert, 'show');

            sinon.stub(view, '_onWarnExportEmailsTemplatesConfirm');
            sinon.stub(view, 'exportEmailsTemplates');
            sinon.stub(app.lang, 'get').returns('a');

            someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_EXPORT_CONFIRMATION')
            };
        });

        it('should warn the user if the warning cache is not set to false', function() {
            cacheGetMock.returns(true);

            view.warnExportEmailsTemplates(genericObjects[0]);

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));
        });

        it('should call _onWarnExportEmailsTemplatesConfirm if user confirms the warning', function() {
            cacheGetMock.returns(true);
            alertMock.yieldsTo('onConfirm');

            view.warnExportEmailsTemplates(genericObjects[0]);

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));

            //Check that it is called on success here
            expect(view._onWarnExportEmailsTemplatesConfirm).toHaveBeenCalled();
        });

        it('should call exportEmailsTemplates directly if the warning cache is set to false', function() {
            cacheGetMock.returns(false);
            view.warnExportEmailsTemplates(genericObjects[0]);

            expect(view.exportEmailsTemplates).toHaveBeenCalledWith(genericObjects[0]);
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnExportEmailsTemplatesConfirm', function() {

        beforeEach(function() {
            sinon.stub(app.cache, 'set');
            sinon.stub(view, 'exportEmailsTemplates');
        });

        it('should set the cache and export the template', function() {

            view._onWarnExportEmailsTemplatesConfirm(genericObjects[0]);

            expect(app.cache.set).toHaveBeenCalledWith('show_emailtpl_export_warning', false);
            expect(view.exportEmailsTemplates).toHaveBeenCalledWith(genericObjects[0]);
        });
    });

    describe('exportEmailsTemplates', function() {
        var apiMock;
        // So tests can set the return value.
        var emptyStub;
        var downloadUrl;

        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').returns(genericStrings[0]);
            apiMock = sinon.stub(app.api, 'fileDownload');
            sinon.stub(app.logger, 'error');
            emptyStub = sinon.stub(_, 'isEmpty').returns(false);
            app.config.platform = genericStrings[0];
            sinon.stub(view, '_onExportEmailsTemplatesDownloadError');

            downloadUrl = app.api.buildURL(genericModel.module,
                'etemplate',
                {id: genericModel.id},
                {platform: app.config.platform});
        });

        it('should call the api to download the file', function() {

            view.exportEmailsTemplates(genericModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(downloadUrl, jasmine.any(Object), jasmine.any(Object));
        });

        it('should not download when the url is broken', function() {
            emptyStub.returns(true);
            view.exportEmailsTemplates(genericModel);

            expect(app.logger.error).toHaveBeenCalled();
            expect(app.api.fileDownload).not.toHaveBeenCalled();
        });

        it('should call _onExportEmailsTemplatesDownloadError when download fails', function() {
            apiMock.yieldsTo('error');

            view.exportEmailsTemplates(genericModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(downloadUrl, jasmine.any(Object), jasmine.any(Object));
            expect(view._onExportEmailsTemplatesDownloadError).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onExportEmailsTemplatesDownloadError', function() {

        beforeEach(function() {
            sinon.stub(app.error, 'handleHttpError');
        });

        it('should call handleHttpError', function() {

            view._onExportEmailsTemplatesDownloadError(genericStrings[0]);

            expect(app.error.handleHttpError).toHaveBeenCalledWith(genericStrings[0], {});
        });
    });
});
