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
describe("file field", function() {

    var app, field, model;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","testfile", "file", "detail", {});
        model = field.model;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    describe("file", function() {

        it("should format an array", function() {
            var inputValue = [
                {name:'filename1.jpg', 'uri': '/path/to/rest'},
                {name:'filename2.jpg', 'uri': '/path/to/rest'},
                {name:'filename3.jpg', 'uri': '/path/to/rest'}
            ];
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'},
                {name:'filename2.jpg', 'url': '/path/to/rest'},
                {name:'filename3.jpg', 'url': '/path/to/rest'}
            ];
            var formattedValue = field.format(inputValue);
            expect(formattedValue).toEqual(expectedValue);
        });


        it("should format a string", function() {
            var inputValue = 'filename1.jpg';
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'}
            ];
            var formattedValue = field.format(inputValue);
            expect(formattedValue[0].name).toEqual(expectedValue[0].name);
            expect(formattedValue[0].url).not.toEqual(expectedValue[0].url);
        });

        it('should not display image only if mime type is not image', function() {
            var inputValue = [
                {name: 'filename1.jpg', 'uri': '/path/to/rest'}
            ];
            field.model.set('file_mime_type', 'document/txt');
            expect(field._isImage(field.model.get('file_mime_type'))).toBe(false);
            var expectedValue = [
                {
                    'name': 'filename1.jpg',
                    'url': '/path/to/rest'
                }
            ];
            var formattedValue = field.format(inputValue);
            expect(formattedValue).toEqual(expectedValue);
        });

        it('should display image only if mime type is an image', function() {
            var inputValue = 'filename1.jpg',
                mime_type = 'image/jpeg';
            //verify the mime type is an image
            expect(field._isImage(mime_type)).toBe(true);
            field.model.set('file_mime_type', mime_type);
            var expectedValue = [
                {
                    'name': 'filename1.jpg',
                    'url': '/path/to/rest',
                    'mimeType': 'image'
                }
            ];
            sinon.stub(app.api, 'buildFileURL').callsFake(function() {
                return expectedValue[0].url;
            });
            var formattedValue = field.format(inputValue);
            expect(formattedValue).toEqual(expectedValue);
        });
    });

    describe('_handleFileChanged', function() {
        beforeEach(function() {
            sinon.stub(field, '_updateDom');
        });

        it('should update the field value when the model value changes', function() {
            field.model.set({
                doc_type: 'Google',
                testfile: 'testfilename'
            });
            field.model.set('testfile', 'testfilename');

            expect(field.value[0].name).toEqual('testfilename');
            expect(field.value[0].docType).toEqual('Google');
            expect(field.value[0].url).toBeDefined();
            expect(field.value[0].url).not.toEqual('');
        });
    });

    describe('_handleDocTypeChanged', function() {
        beforeEach(function() {
            sinon.stub(field, '_updateDom');
        });

        it('should set externalApi to false for Sugar type documents', function() {
            field.externalApi = 'Google';
            field.externalApiDirectionFrom = true;
            field.model.set('doc_type', 'Sugar');
            field._handleDocTypeChanged();
            expect(field.externalApi).toEqual(false);
            expect(field.externalApiDirectionFrom).toEqual(false);
        });

        it('should set externalApi to the external API name for non-Sugar type documents', function() {
            field.docTypeField = 'doc_type';
            field.model.set('doc_type', 'Google');
            field._handleDocTypeChanged();
            expect(field.externalApi).toEqual('Google');
        });
    });
});
