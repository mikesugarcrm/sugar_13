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
describe('pmse_Emails_Templates.Base.Views.emailtemplates-import', function() {
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
        view = SugarTest.createView('base', 'pmse_Emails_Templates', 'emailtemplates-import', null, context, true);
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
            sinon.stub(app.view.View.prototype, 'initialize');
            sinon.stub(view.context, 'off');
            sinon.stub(view.context, 'on');
        });

        it('should initialize the proper stuff', function() {
            var options = {};

            view.initialize(options);

            expect(view.context.off).toHaveBeenCalledWith('emailtemplates:import:finish', null, view);
            expect(view.context.on).toHaveBeenCalledWith('emailtemplates:import:finish',
                view.warnImportEmailTemplates, view);
            expect(app.view.View.prototype.initialize).toHaveBeenCalledWith(options);
        });
    });

    describe('_renderField', function() {

        beforeEach(function() {
            sinon.stub(app.view.View.prototype, '_renderField');
        });

        it('should call the parent render function', function() {
            var fakeField = {};

            view._renderField(fakeField);

            expect(app.view.View.prototype._renderField).toHaveBeenCalledWith(fakeField);
        });

        it('should set the field to edit mode when appropriate', function() {
            var fakeField = {
                name: 'emailtemplates_import',
                setMode: sinon.stub()
            };

            view._renderField(fakeField);

            expect(app.view.View.prototype._renderField).toHaveBeenCalledWith(fakeField);
            expect(fakeField.setMode).toHaveBeenCalledWith('edit');
        });
    });

    describe('warnImportEmailTemplates', function() {
        // Defined here to allow for customized returns/yields for each test.
        var alertMock;
        var cacheGetMock;

        beforeEach(function() {
            cacheGetMock = sinon.stub(app.cache, 'get');
            alertMock = sinon.stub(app.alert, 'show');

            sinon.stub(view, '_onWarnImportEmailTemplatesConfirm');
            sinon.stub(view, '_onWarnImportEmailTemplatesCancel');
            sinon.stub(view, 'importEmailTemplates');
            sinon.stub(app.lang, 'get').returns('a');
        });

        it('should warn the user if the user has not dismissed a warning already', function() {
            cacheGetMock.returns(true);
            var someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_IMPORT_CONFIRMATION')
            };

            view.warnImportEmailTemplates();

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-import-confirmation',
                jasmine.objectContaining(someAlertData));
        });

        it('should call _onWarnImportEmailTemplatesConfirm if user confirms the warning', function() {
            cacheGetMock.returns(true);
            alertMock.yieldsTo('onConfirm');

            var someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_IMPORT_CONFIRMATION')
            };

            view.warnImportEmailTemplates();

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-import-confirmation',
                jasmine.objectContaining(someAlertData));

            //Check that it is called on success here
            expect(view._onWarnImportEmailTemplatesConfirm).toHaveBeenCalled();
        });

        it('should call _onWarnImportEmailTemplatesCancel if user cancels the warning', function() {
            cacheGetMock.returns(true);
            alertMock.yieldsTo('onCancel');

            var someAlertData = {
                level: 'confirmation',
                messages: app.lang.get('LBL_PMSE_IMPORT_EXPORT_WARNING') +
                '<br/><br/>' + app.lang.get('LBL_PMSE_IMPORT_CONFIRMATION')
            };

            view.warnImportEmailTemplates();

            expect(app.alert.show).toHaveBeenCalledWith('emailtpl-import-confirmation',
                jasmine.objectContaining(someAlertData));

            //Check that it is called on success here
            expect(view._onWarnImportEmailTemplatesCancel).toHaveBeenCalled();
        });

        it('should call importEmailTemplates directly if the warning cache is set to false', function() {
            cacheGetMock.returns(false);
            view.warnImportEmailTemplates();

            expect(view.importEmailTemplates).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnImportEmailTemplatesConfirm', function() {

        beforeEach(function() {
            sinon.stub(app.cache, 'set');
            sinon.stub(view, 'importEmailTemplates');
        });

        it('should set the cache and import the template', function() {

            view._onWarnImportEmailTemplatesConfirm();

            expect(app.cache.set).toHaveBeenCalledWith('show_emailtpl_import_warning', false);
            expect(view.importEmailTemplates).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onWarnImportEmailTemplatesCancel', function() {

        beforeEach(function() {
            app.router = {goBack: sinon.stub()};
        });

        afterEach(function() {
            app.router = null;
        });

        it('should go back to the previous page', function() {

            view._onWarnImportEmailTemplatesCancel();

            expect(app.router.goBack).toHaveBeenCalled();
        });
    });

    describe('importEmailTemplates', function() {
        // Defined here because it is common to all tests.
        var fakeFile;
        // Defined here so tests can set return/yield values.
        var emptyStub;
        var importMock;

        beforeEach(function() {
            fakeFile = {
                val: sinon.stub()
            };

            emptyStub = sinon.stub(_, 'isEmpty').returns(false);
            importMock = sinon.stub(view.model, 'uploadFile');

            sinon.stub(app.lang, 'get').returns('a');
            sinon.stub(app.alert, 'show');
            sinon.stub(view, '_onImportEmailTemplatesError');
            sinon.stub(view, '_onImportEmailTemplatesSuccess');

            // jQuery is in the global namespace so it is a child of window.
            sinon.stub(window, '$').returns(fakeFile);
        });

        it('should bitch at the user if there is no file', function() {
            emptyStub.returns(true);

            view.importEmailTemplates();

            expect(app.alert.show).toHaveBeenCalledWith('error_validation_emailtemplates', {
                level: 'error',
                messages: app.lang.get('LBL_PMSE_EMAIL_TEMPLATES_EMPTY_WARNING', self.module),
                autoClose: false
            });
            expect(view.model.uploadFile).not.toHaveBeenCalled();
        });

        it('should show an upload diddly and upload the file if it exists', function() {
            emptyStub.returns(false);

            view.importEmailTemplates();

            expect(app.alert.show).toHaveBeenCalledWith('upload',
                {level: 'process', title: 'LBL_UPLOADING', autoclose: false});
            expect(view.model.uploadFile).toHaveBeenCalledWith('emailtemplates_import',
                fakeFile,
                // To allow for any kind of binding needed.
                jasmine.any(Object),
                {deleteIfFails: true, htmlJsonFormat: true});
        });

        it('should call _onImportEmailTemplatesSuccess when the import succeeds', function() {
            emptyStub.returns(false);
            importMock.yieldsTo('success');

            view.importEmailTemplates();

            expect(view.model.uploadFile).toHaveBeenCalled();
            expect(view._onImportEmailTemplatesSuccess).toHaveBeenCalled();
        });

        it('should call _onImportEmailTemplatesError when the import fails', function() {
            emptyStub.returns(false);
            importMock.yieldsTo('error');

            view.importEmailTemplates();

            expect(view.model.uploadFile).toHaveBeenCalled();
            expect(view._onImportEmailTemplatesError).toHaveBeenCalled();
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onImportEmailTemplatesSuccess', function() {

        beforeEach(function() {
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show');
            app.router = {goBack: sinon.stub()};
            sinon.stub(app.lang, 'get').returns('a');
        });

        afterEach(function() {
            app.router = null;
        });

        it('should remove the diddly, go back, and tell the user it succeeded', function() {

            view._onImportEmailTemplatesSuccess({});

            expect(app.alert.dismiss).toHaveBeenCalledWith('upload');
            expect(app.router.goBack).toHaveBeenCalled();
            expect(app.alert.show).toHaveBeenCalledWith('process-import-saved', {
                level: 'success',
                messages: app.lang.get('LBL_PMSE_EMAIL_TEMPLATES_IMPORT_SUCCESS', self.module),
                autoClose: true
            });
        });
    });

    // Function is @private so it does not show up properly here in IDE.
    describe('_onImportEmailTemplatesError', function() {

        beforeEach(function() {
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.alert, 'show');
        });

        it('should remove the diddly and tell the user it failed', function() {

            view._onImportEmailTemplatesError({error_message: 'ruhroh'});

            expect(app.alert.dismiss).toHaveBeenCalledWith('upload');
            expect(app.alert.show).toHaveBeenCalledWith('process-import-saved', {
                level: 'error',
                messages: 'ruhroh',
                autoClose: false
            });
        });
    });
});
