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
describe('Attachments Plugin', function() {
    var app;
    var sandbox;
    var plugin;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        SugarTest.loadPlugin('Attachments');
        plugin = app.plugins.plugins.field.Attachments;
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('uploadFile', function() {
        it('should call callbacks', function() {
            var successStub = sandbox.stub();
            plugin._handleFileUploadSuccess = successStub;
            var completeStub = sandbox.stub();
            plugin._handleFileUploadComplete = completeStub;
            var errorStub = sandbox.stub();
            plugin._handleFileUploadError = errorStub;

            sandbox.stub(app.data, 'createBean').returns({
                uploadFile: function(inputFile, fieldName, callbacks, options) {
                    if (callbacks.success) {
                        callbacks.success({});
                    }
                    if (callbacks.complete) {
                        callbacks.complete({});
                    }
                    if (callbacks.error) {
                        callbacks.error({});
                    }
                }
            });

            plugin.uploadFile(null, null, null);
            expect(successStub).toHaveBeenCalled();
            expect(completeStub).toHaveBeenCalled();
            expect(errorStub).toHaveBeenCalled();
        });
    });

    describe('addUploadedFileToCollection', function() {
        it('should add file to collection', function() {
            var collection = new app.BeanCollection();
            var addStub = sandbox.stub(collection, 'add');
            var file = new app.Bean();
            plugin.addUploadedFileToCollection(collection, file);
            expect(addStub).toHaveBeenCalledWith(file, {merge: true});
        });
    });

    describe('getUploadedFileBean', function() {
        it('should get default attrs', function() {
            var data = {test: 'test'};
            var getStub = sandbox.stub().returns(data);
            plugin.getUploadedFileBeanDefaultAttributes = getStub;
            var createStub = sandbox.stub(app.data, 'createBean');
            plugin.getUploadedFileBean(data, null);
            expect(getStub).toHaveBeenCalledWith(data);
            expect(createStub).toHaveBeenCalledWith('Notes', data);
        });
        it('should not get default attrs', function() {
            var attrs = {test: 'test'};
            var getStub = sandbox.stub();
            plugin.getUploadedFileBeanDefaultAttributes = getStub;
            var createStub = sandbox.stub(app.data, 'createBean');
            plugin.getUploadedFileBean(null, attrs);
            expect(getStub).not.toHaveBeenCalled();
            expect(createStub).toHaveBeenCalledWith('Notes', attrs);
        });
    });

    describe('getUploadedFileBeanDefaultAttributes', function() {
        var dataProvider = [
            {
                message: 'Should return empty',
                input: {
                    record: {}
                },
                expected: {
                }
            },
            {
                message: 'Return is not empty',
                input: {
                    record: {
                        id: 'id',
                        filename: 'name.txt',
                        file_mime_type: 'text/plain',
                        file_size: 123,
                        file_ext: 'txt'
                    }
                },
                expected: {
                    _link: 'attachments',
                    name: 'name.txt',
                    filename_guid: 'id',
                    filename: 'name.txt',
                    file_mime_type: 'text/plain',
                    file_size: 123,
                    file_ext: 'txt'
                }
            }
        ];
        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var result = plugin.getUploadedFileBeanDefaultAttributes(data.input);
                expect(result).toEqual(data.expected);
            });
        }, this);
    });
});
