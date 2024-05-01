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

describe('DRI_Workflow_Templates.Base.View.TemplateImportView', function() {
    let app;
    let view;
    let model;
    let context;
    let initOptions;
    let meta;

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean('DRI_Workflow_Templates');
        context = new app.Context();

        context.set('model', model);
        context.prepare();

        context.parent = app.context.getContext();
        layout = SugarTest.createLayout(
            'base',
            'DRI_Workflow_Templates',
            'base',
            null,
            context
        );
        meta = {
            'header_label': 'LBL_CONFIGURE_RECORDVIEW_DISPLAY_TITLE',
            'fields': [
                {
                    'name': 'enabled_modules',
                    'label': 'LBL_ENABLED_MODULES',
                    'type': 'enum',
                },
            ],
        };
        view = SugarTest.createView(
            'base',
            'DRI_Workflow_Templates',
            'template-import',
            meta,
            context,
            true,
            layout,
            true
        );
        initOptions = {
            context: context,
            meta: meta,
        };
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        delete app.router;
        view.dispose();
        layout.dispose();
        model = null;
        layout = null;
        context = null;
    });

    describe('initialize', () => {
        it('should call the initialize function and initialze some properties', () => {
            sinon.stub(view, '_super');
            sinon.stub(view, 'listenTo').returns(true);
            view.initialize(initOptions);
            expect(view._super).toHaveBeenCalledWith('initialize');
            expect(view.listenTo).toHaveBeenCalled();
        });
    });

    describe('importTemplate', () => {
        it('should show alert', () => {
            sinon.stub(_, 'isEmpty').returns(true);
            sinon.stub(view, 'showAlert').returns(true);
            view.importTemplate();
            expect(view.showAlert).toHaveBeenCalled();
        });
        it('should show alert', () => {
            sinon.stub(_, 'isEmpty').returns(false);
            sinon.stub(app.lang, 'get').returns('Test');
            sinon.stub(view, 'showAlert').returns(true);
            sinon.stub(view.model, 'uploadFile').returns(true);
            view.importTemplate();
            expect(view.showAlert).toHaveBeenCalled();
            expect(view.model.uploadFile).toHaveBeenCalled();
        });
    });

    describe('uploadFileError', () => {
        it('should  handle error callback for uploadFile', () => {
            sinon.stub(app.alert, 'dismiss').returns(true);
            sinon.stub(view, 'showAlert').returns(true);
            view.importTemplate();
            expect(view.showAlert).toHaveBeenCalled();
        });
    });

    describe('importTemplateSuccess', () => {
        beforeEach(function() {
            sinon.stub(app.alert, 'dismiss').returns(true);
        });
        it('should show confirmation alert', () => {
            sinon.stub(app.alert, 'show').returns(true);
            let data = {
                update: true,
                record: {
                    id: 12,
                },
            };
            view.importTemplateSuccess(data);
            expect(app.alert.show).toHaveBeenCalled();
            expect(app.alert.dismiss).toHaveBeenCalledWith('upload');
        });
        it('should show duplicate alert', () => {
            sinon.stub(view, 'showAlert').returns('duplicate');
            let data = {
                duplicate: true,
            };
            view.importTemplateSuccess(data);
            expect(view.showAlert).toHaveBeenCalled();
        });
        it('should neither show confirmation alert nor duplicate alert and call doImport function', () => {
            sinon.stub(view, 'doImport');
            view.importTemplateSuccess({});
            expect(view.doImport).toHaveBeenCalled();
        });
    });

    describe('doImport', () => {
        beforeEach(function() {
            sinon.stub(view, 'showAlert');
            sinon.stub(view.model, 'uploadFile');
            sinon.stub(app.lang, 'get').returns('error 404');
        });
        afterEach(function() {
            sinon.restore();
        });
        it('should import the Smart Guide Template File (.json)', () => {
            let projectFile = {
                val: () => 'true',
            };
            sinon.stub(view, '$').returns(projectFile);
            view.doImport();
            expect(view.$).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
            expect(view.showAlert).toHaveBeenCalled();
            expect(view.model.uploadFile).toHaveBeenCalled();
        });
        it('should not import the Smart Guide Template File (.json)', () => {
            let projectFile = {
                val: () => false,
            };
            sinon.stub(view, '$').returns(projectFile);
            view.doImport();
            expect(view.$).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
            expect(view.showAlert).toHaveBeenCalled();
            expect(view.model.uploadFile).not.toHaveBeenCalled();
        });
    });

    describe('doImportSuccess', () => {
        it('should Handle success callback for doImport', () => {
            let data = {
                record: {
                    deleted: 1,
                },
            };
            app.router = {
                buildRoute: sinon.stub(),
                navigate: sinon.stub(),
            };
            sinon.stub(view, 'showAlert');
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.lang, 'get');
            view.doImportSuccess(data);
            expect(view.showAlert).toHaveBeenCalled();
            expect(app.alert.dismiss).toHaveBeenCalledWith('upload');
            expect(app.lang.get).toHaveBeenCalled();
        });

        it('should Handle success callback for doImport', () => {
            let data = {
                record: {
                    deleted: 0,
                },
            };
            app.router = {
                buildRoute: sinon.stub(),
                navigate: sinon.stub(),
            };
            sinon.stub(view, 'showAlert');
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(app.lang, 'get');
            view.doImportSuccess(data);
            expect(view.showAlert).toHaveBeenCalled();
            expect(app.alert.dismiss).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('_renderField', () => {
        it('should Set up the file field to edit mode', () => {
            let field = {
                name: 'template_import',
                setMode: sinon.stub(),
            };
            sinon.stub(view, '_super');
            view._renderField(field);
            expect(view._super).toHaveBeenCalledWith('_renderField');
        });
    });

    describe('showAlert', () => {
        it('should shows alert to the user', () => {
            let name = 'cj-select-to';
            let level = 'process';
            let message = 'success';
            let autoClose = true;
            let autoCloseDelay = 9;
            sinon.stub(app.alert, 'show');
            sinon.stub(app.utils, 'isTruthy').returns(true);
            view.showAlert(name, level, message, autoClose, autoCloseDelay);
            expect(app.alert.show).toHaveBeenCalledWith(name);
            expect(app.utils.isTruthy).toHaveBeenCalled();
        });
    });
});
