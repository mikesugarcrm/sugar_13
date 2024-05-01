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
describe('pmse_Emails_Templates.Base.Views.record', function() {
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
        view = SugarTest.createView('base', 'pmse_Emails_Templates', 'record', null, context, true);

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
            sinon.stub(view, '_super');
            sinon.stub(view.context, 'on');
            options = genericObjects[0];
        });

        it('should initialize the click handlers and call parent init function', function() {

            view.initialize(options);

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
            expect(view.context.on).toHaveBeenCalledWith('button:design_emailtemplates:click',
                view.designEmailTemplates, view);
            expect(view.context.on).toHaveBeenCalledWith('button:export_emailtemplates:click',
                view.warnExportEmailTemplates, view);
            expect(view.context.on).toHaveBeenCalledWith('button:delete_emailstemplates:click',
                view.warnDeleteEmailsTemplates, view);
            expect(view.context.on).toHaveBeenCalledWith('button:edit_emailstemplates:click',
                view.warnEditEmailTemplates, view);
        });
    });

    describe('_render', function() {
        var jMock;

        beforeEach(function() {
            jMock = {remove: sinon.stub()};
            sinon.stub(view, '$').returns(jMock);
            sinon.stub(view, '_super');
        });

        afterEach(function() {
            jMock = null;
        });

        it('should call parent render function and remove extra columns', function() {
            view._render();

            expect(view._super).toHaveBeenCalledWith('_render');
            // Not sure how to best test removing extra elements as selectors may change.
            // For now will just check that something was removed.
            expect(jMock.remove).toHaveBeenCalled();
        });
    });

    describe('designEmailTemplates', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            sinon.stub(view, '_onDesignRecordVerify');
            view._modelToDesign = null;
        });

        it('should fire a request to the correct URL', function() {

            view.designEmailTemplates(genericModel);
            expect(view._modelToDesign).toEqual(genericModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', genericVerifyUrl, null, jasmine.any(Object));
        });

        it('should call _onDesignRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.designEmailTemplates(genericModel);

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
            expect(app.alert.show).toHaveBeenCalledWith('email-templates-edit-confirmation',
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

        it('should navigate to the designer and clear the _modelToDesign', function() {

            view._onWarnDesignActiveRecordConfirm(genericStrings[0]);

            expect(app.navigate).toHaveBeenCalledWith(view.context, genericStrings[0], 'layout/emailtemplates');
            expect(view._modelToDesign).toEqual(null);
        });
    });

    describe('warnEditEmailTemplates', function() {
        var apiMock;

        beforeEach(function() {
            apiMock = sinon.stub(app.api, 'call');
            sinon.stub(view, '_onEditRecordVerify');
            view._modelToEdit = null;
        });

        it('should fire a request to the correct URL', function() {

            view.warnEditEmailTemplates(genericModel);
            expect(view._modelToEdit).toEqual(genericModel);

            // Using jasmine.any object because there are different ways to call/bind the callback;
            expect(app.api.call).toHaveBeenCalledWith('read', genericVerifyUrl, null, jasmine.any(Object));
        });

        it('should call _onEditRecordVerify on success', function() {
            apiMock.yieldsTo('success');

            view.warnEditEmailTemplates(genericModel);

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
            view.editClicked = sinon.stub();
            sinon.stub(app.lang, 'get').returns('a');
        });

        afterEach(function() {
            view._modelToEdit = null;
            alertMock = null;
            view.editClicked = null;
        });

        it('should not warn the user if the template is not in use', function() {

            view._onEditRecordVerify(false);

            expect(view.editClicked).toHaveBeenCalled();
        });

        it('should warn the user that the template is currently in use', function() {

            view._onEditRecordVerify(true);

            expect(view.editClicked).not.toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('email-templates-edit-confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });

        it('should start editing if user confirms alert', function() {
            alertMock.yieldsTo('onConfirm');

            view._onEditRecordVerify(true);

            expect(view.editClicked).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnEditActiveRecordConfirm', function() {

        beforeEach(function() {
            view.editClicked = sinon.stub();
            view._modelToEdit = genericStrings[0];
        });

        afterEach(function() {
            view.editClicked = null;
            view._modelToEdit = null;
        });

        it('should start editing and clear the _modelToEdit', function() {

            view._onWarnEditActiveRecordConfirm();

            expect(view.editClicked).toHaveBeenCalled();
            expect(view._modelToEdit).toEqual(null);
        });
    });

    describe('handleEdit', function() {
        // In case this is important and needs to be saved.
        var oldModel;

        beforeEach(function() {
            oldModel = view.model;
            view.model = genericModel;

            sinon.stub(view, 'warnEditEmailTemplates');
        });

        afterEach(function() {
            view.model = oldModel;
            oldModel = null;
            genericModel = null;
        });

        it('should call warnEditEmailTemplates with the model', function() {

            view.handleEdit({}, {});

            expect(view.warnEditEmailTemplates).toHaveBeenCalledWith(genericModel);
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
        var alertMock;

        beforeEach(function() {
            view._modelToDelete = genericModel;

            alertMock = sinon.stub(app.alert, 'show');
            sinon.stub(view, '_onWarnDeleteInactiveRecordConfirm');
            sinon.stub(view, '_clearModelToDelete');
            sinon.stub(app.utils, 'formatString').returns('a');
            sinon.stub(app.lang, 'get').returns('a');
            view.getDeleteMessages = sinon.stub().returns({confirmation: genericStrings[0]});
        });

        afterEach(function() {
            view._modelToDelete = null;
            view.getDeleteMessages = null;
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

        it('should call _clearModelToDelete if user cancels the confirmation', function() {
            alertMock.yieldsTo('onCancel');

            view._onDeleteRecordVerify(false);

            expect(view._clearModelToDelete).toHaveBeenCalled();
        });

        it('should block deletion if the template is already in use', function() {

            view._onDeleteRecordVerify(true);

            expect(app.alert.show).toHaveBeenCalledWith('message-id',
                jasmine.objectContaining({level: 'warning', autoClose: false}));
            expect(view._clearModelToDelete).toHaveBeenCalled();
        });
    });

    describe('_onWarnDeleteInactiveRecordConfirm', function() {
        var genericModel;

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

    describe('warnExportEmailTemplates', function() {
        // Defined here to allow for customized returns/yields for each test.
        var alertMock;
        var cacheGetMock;
        var someAlertData;

        beforeEach(function() {
            cacheGetMock = sinon.stub(app.cache, 'get');
            alertMock = sinon.stub(app.alert, 'show');

            sinon.stub(view, '_onWarnExportEmailTemplatesConfirm');
            sinon.stub(view, 'exportEmailTemplates');
            sinon.stub(app.lang, 'get').returns('a');

            someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_EXPORT_CONFIRMATION')
            };
        });

        it('should warn the user if the warning cache is not set to false', function() {
            cacheGetMock.returns(true);

            view.warnExportEmailTemplates({a: 'b'});

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));
        });

        it('should call _onWarnExportEmailTemplatesConfirm if user confirms the warning', function() {
            cacheGetMock.returns(true);
            alertMock.yieldsTo('onConfirm');

            view.warnExportEmailTemplates({a: 'b'});

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-export-confirmation',
                jasmine.objectContaining(someAlertData));

            //Check that it is called on success here
            expect(view._onWarnExportEmailTemplatesConfirm).toHaveBeenCalled();
        });

        it('should call exportEmailTemplates directly if the warning cache is set to false', function() {
            cacheGetMock.returns(false);
            view.warnExportEmailTemplates({a: 'b'});

            expect(view.exportEmailTemplates).toHaveBeenCalledWith({a: 'b'});
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnExportEmailTemplatesConfirm', function() {

        beforeEach(function() {
            sinon.stub(app.cache, 'set');
            sinon.stub(view, 'exportEmailTemplates');
        });

        it('should set the cache and export the template', function() {

            view._onWarnExportEmailTemplatesConfirm({a: 'b'});

            expect(app.cache.set).toHaveBeenCalledWith('show_emailtpl_export_warning', false);
            expect(view.exportEmailTemplates).toHaveBeenCalledWith({a: 'b'});
        });
    });

    describe('exportEmailTemplates', function() {
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
            sinon.stub(view, '_onExportEmailTemplatesDownloadError');

            downloadUrl = app.api.buildURL(genericModel.module,
                'etemplate',
                {id: genericModel.id},
                {platform: app.config.platform});
        });

        it('should call the api to download the file', function() {

            view.exportEmailTemplates(genericModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(downloadUrl, jasmine.any(Object), jasmine.any(Object));
        });

        it('should not download when the url is broken', function() {
            emptyStub.returns(true);
            view.exportEmailTemplates(genericModel);

            expect(app.logger.error).toHaveBeenCalled();
            expect(app.api.fileDownload).not.toHaveBeenCalled();
        });

        it('should call _onExportEmailTemplatesDownloadError when download fails', function() {
            apiMock.yieldsTo('error');

            view.exportEmailTemplates(genericModel);

            expect(app.logger.error).not.toHaveBeenCalled();
            // The first .any object is because there are multiple ways to bind the callbacks.
            // The second is because it wants an element and so is meaningless in a unit test.
            expect(app.api.fileDownload).toHaveBeenCalledWith(downloadUrl, jasmine.any(Object), jasmine.any(Object));
            expect(view._onExportEmailTemplatesDownloadError).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onExportEmailTemplatesDownloadError', function() {

        beforeEach(function() {
            sinon.stub(app.error, 'handleHttpError');
        });

        it('should call handleHttpError', function() {

            view._onExportEmailTemplatesDownloadError(genericStrings[0]);

            expect(app.error.handleHttpError).toHaveBeenCalledWith(genericStrings[0], {});
        });
    });
});
