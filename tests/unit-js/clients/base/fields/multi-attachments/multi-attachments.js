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
describe('clients.base.fields.multi-attachments', function() {
	var app, field, sandbox,
        module = 'KBContents',
        model, 
        apiCallStub,
        fieldName = 'attachments',
        fieldType = 'multi-attachments',
        fieldDef = {
        	'name': 'attachment_list',
            'type': 'multi-attachments',
            'link': 'attachments',
            'field' : 'attachments',
            'module': 'Notes',
            'modulefield': 'filename'
        };

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', fieldType);
        SugarTest.loadPlugin('DragdropAttachments', 'Attachments');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'selection-partial');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        apiCallStub = sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks) {
            if (callbacks && callbacks.success)
                callbacks.success({});
        });
        model = app.data.createBean(module);
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        apiCallStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        delete app.plugins.plugins.field['DragdropAttachments', 'Attachments'];
    });

    it('should not rendered download all attachments button when attachments empty', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null
        );
        field.render();
        expect(field.$('[data-action="download-all"]').length).toEqual(0);
    });

    it('should render when model gets data', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail',
            fieldDef, module, model, null
        );
        field.render();
        var renderStub = sandbox.stub(field, 'render');
        model.set(fieldName, {});
        expect(renderStub).toHaveBeenCalled();
    });

    it('should rendered download all attachments button when attachments not empty', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null
        );
        field.render();
        model.set('attachments', [{
            id: 'testAttach1',
            name: 'testAttach1',
            filename: 'testAttach1.txt',
            file_mime_type: 'text/plain'
        }]);
        expect(field.$('[data-action="download-all"]').length).toEqual(1);
    });

    it('format should return valid value', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null
        );
        field.render();

        var result = field.format([{   
            id: 'testAttach1',
            name: 'testAttach1.jpg',
            file_mime_type: 'image'
        },
        {   
            id: 'testAttach2',
            name: 'testAttach2'
        }]);

        expect(result.length).toEqual(2);
        expect(_.first(result).mimeType).toEqual('image');
        expect(_.last(result).mimeType).toEqual('application/octet-stream');
    });

    it('should be called setSelect2Node during render', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null
        );
        var setSelect2Node = sandbox.spy(field, 'setSelect2Node');
        field.render();
        expect(setSelect2Node).toHaveBeenCalled();
    });

    it('should be called setSelect2Node on bind DOM change', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null
        );
        field.render();
        var setSelect2Node = sandbox.spy(field, 'setSelect2Node');
        field.bindDomChange();
        expect(setSelect2Node).toHaveBeenCalled();
    });

    it('should be able to get DOM element using getFileNode function', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null
        );
        field.render();
        expect(field.getFileNode().length).toEqual(1);
    });

    it('should add the event handlers to upload a file', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit',
            fieldDef, module, model, null
        );
        field.render();

        var event = 'change ' + field.fileInputSelector + '[data-type=fileinput]';

        expect(field.events[event]).toBeDefined();
        expect(field.events[event]).toEqual('_uploadFile');
    });

    it('should be able to download all files as archive from server', function() {
        var apiDownloadCallStub = sandbox.stub(app.api, 'fileDownload').callsFake(function(url, callbacks) {
            if (callbacks && callbacks.success)
                callbacks.success({});
        });

        model.set('attachments', [{   
            id: 'testAttach1',
            name: 'testAttach1',
            filename: 'testAttach1.txt',
            file_mime_type: 'text/plain'
        }]);

        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null
        );
        field.render();
        field.$('[data-action="download-all"]').click();
        expect(apiDownloadCallStub).toHaveBeenCalled();
    });

    it('should be able to update `Select2` data from model', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null
        );
        field.render();
        model.set('attachments', [{   
            id: 'testAttach1',
            name: 'testAttach1',
            filename: 'testAttach1.txt',
            file_mime_type: 'text/plain'
        }]);
        var sel2Data = field.$node.select2('data');
        model.set('attachments', [{   
            id: 'testAttach2',
            name: 'testAttach2',
            filename: 'testAttach2.txt',
            file_mime_type: 'text/plain'
        }]);
        field.refreshFromModel();
        expect(sel2Data).not.toEqual(field.$node.select2('data'));
    });

    describe('_handleFileUploadError', function() {
        var error;
        beforeEach(function() {
            field = SugarTest.createField('base', fieldName, fieldType, 'edit',
                fieldDef, module, model, null
            );
            sandbox.stub(field, '_toggleUploading');
            sandbox.stub(app.api, 'defaultErrorHandler');
            sandbox.stub(app.alert, 'show');
            sandbox.spy(app.lang, 'get');
        });

        describe('Request Entity Too Large', function() {
            beforeEach(function() {
                error = {
                    code: 'request_too_large',
                    status: 413,
                    handled: false
                };
                field._handleFileUploadError(error);
            });

            it('should alert the user when the uploaded file is too large', function() {
                expect(error.handled).toBeTruthy();
                expect(app.alert.show).toHaveBeenCalled();
                expect(app.lang.get).toHaveBeenCalledWith('ERROR_MAX_FILESIZE_EXCEEDED');
            });

            it('should call _toggleuploading with false', function() {
                expect(field._toggleUploading).toHaveBeenCalledWith(false);
            });
        });

        describe('Internal Server Error', function() {
            beforeEach(function() {
                error = {
                    code: 'fatal_error',
                    status: 500,
                    handled: false
                };
                field._handleFileUploadError(error);
            });

            it('should call the default error handler', function() {
                expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);
            });
        });
    });
});
