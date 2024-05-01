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
describe('BaseEmailAttachmentsField', function() {
    var app;
    var clock;
    var context;
    var field;
    var model;
    var sandbox;
    var timestamp;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();

        // Use a fake timer because there appears to be a bug with PhantomJS
        // that causes the first call to `new Date()` to return the Epoch and
        // every subsequent call to return the correct date.
        // `SUGAR.Api#buildFileURL` uses `new Date()` to add a cache buster to
        // the URL when `cleanCache: true` is passed as an option. The value of
        // that query string parameter is very unreliable when comparing
        // strings, due to the aforementioned bug. The fake timer works around
        // the bug when a specific date is created for the start of the timer.
        timestamp = (new Date(2016, 7, 22, 0, 26, 17)).getTime();
        clock = sinon.useFakeTimers(timestamp);
    });

    afterEach(function() {
        clock.restore();
        sandbox.restore();

        field.model.off();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('editing a draft', function() {
        var attachments;

        beforeEach(function() {
            var data = [{
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            }, {
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'jpg'
            }];

            // Set the next offset now to avoid potential pagination from the
            // CollectionFieldLoadAll plugin on sync.
            attachments = model.get('attachments_collection');
            attachments.next_offset = {
                attachments: -1
            };

            // Act as if the model was retrieved from the server.
            model.set('id', _.uniqueId());
            model.set('attachments_collection', data);
            model.trigger('sync');

            field = SugarTest.createField({
                name: 'attachments_collection',
                type: 'email-attachments',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context
            });
            field.render();
        });

        it('should format the value', function() {
            var $file = $('<input/>', {value: 'quote.pdf'});
            var urlEndpoint = '/file/filename?force_download=1&' + timestamp + '=1&platform=base';
            var expected;

            // Add some attachments.
            attachments.add([{
                _module: 'Notes',
                _link: 'attachments',
                filename_guid: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            }, {
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'pdf'
            }, {
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'pdf'
            }]);

            // Remove a previously linked attachment.
            attachments.remove(attachments.at(1));

            // Remove one of the new attachments, before it is linked.
            attachments.remove(attachments.at(2));

            // Don't allow the success callback to be called for the request.
            sandbox.stub(app.api, 'file');

            sandbox.stub(field, '_getFileInput').returns($file);
            field.$('input[type=file]').change();

            expected = [{
                cid: attachments.at(0).cid,
                _module: 'Notes',
                _link: 'attachments',
                id: attachments.at(0).get('id'),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(0).get('id') + urlEndpoint
            }, {
                cid: attachments.at(1).cid,
                _module: 'Notes',
                _link: 'attachments',
                filename_guid: attachments.at(1).get('filename_guid'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_ext: 'pdf',
                file_url: null
            }, {
                cid: attachments.at(2).cid,
                _module: 'Notes',
                _link: 'attachments',
                id: attachments.at(2).get('id'),
                upload_id: attachments.at(2).get('upload_id'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_source: 'DocumentRevisions',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(2).get('id') + urlEndpoint
            }, {
                cid: field._placeholders.at(0).cid,
                name: field._placeholders.at(0).get('name'),
                file_size: '0K',
                file_url: null
            }];

            expect(field.getFormattedValue()).toEqual(expected);
            expect(field.tooltip).toBe('Disclosure Agreement.pdf, quote.pdf, quote.pdf, quote.pdf');
        });

        it('should not allow the dropdown to open', function() {
            var event = $.Event('select2-opening');
            sandbox.spy(event, 'preventDefault');

            field.$(field.fieldTag).trigger(event);
            expect(event.preventDefault).toHaveBeenCalled();
        });

        describe('uploading an attachment', function() {
            it('should open the file dialog', function() {
                var $file = {
                    click: sandbox.stub()
                };

                sandbox.stub(field, '_getFileInput').returns($file);
                field.view.trigger('email_attachments:file');
                expect($file.click).toHaveBeenCalled();
            });

            it('should add an uploaded file', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var id = _.uniqueId();
                var attachment;
                var json;

                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file').callsFake(function(method, data, $files, callbacks, options) {
                    var response = {
                        filename: {
                            guid: fileName
                        },
                        record: {
                            id: id,
                            deleted: false,
                            file_mime_type: 'application/pdf',
                            file_size: 158589,
                            filename: fileName,
                            file_ext: 'pdf'
                        }
                    };

                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    // The uploaded attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the uploaded
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe(fileName);

                    callbacks.success(response);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                // The file input field should be cleared.
                expect($file.val()).toEqual('');

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The uploaded attachment should now exist.
                expect(attachments.length).toBe(3);

                attachment = attachments.at(2);
                expect(attachment.get('id')).toBeUndefined();
                expect(attachment.get('filename_guid')).toBe(id);
                expect(attachment.get('name')).toBe(fileName);
                expect(attachment.get('filename')).toBe(fileName);
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_size')).toBe(158589);
                expect(attachment.get('file_ext')).toBe('pdf');
                expect(attachment.get('file_source')).toBeUndefined();

                json = model.toJSON();
                expect(json.attachments.create).toEqual([{
                    _link: 'attachments',
                    filename_guid: id,
                    name: fileName,
                    filename: fileName,
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }]);
                expect(json.attachments.add.length).toBe(0);
                expect(json.attachments.delete.length).toBe(0);
            });

            it('should alert the user when the uploaded file is too large', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var error = {error: 'request_too_large'};
                var json;

                sandbox.spy(app.alert, 'show');
                sandbox.spy(app.api, 'defaultErrorHandler');
                sandbox.spy(app.lang, 'get');
                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file').callsFake(function(method, data, $files, callbacks, options) {
                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    // The uploaded attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the uploaded
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe(fileName);

                    callbacks.error(error);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                expect(error.handled).toBe(true);
                expect(app.alert.show).toHaveBeenCalled();
                expect(app.lang.get).toHaveBeenCalledWith('ERROR_MAX_FILESIZE_EXCEEDED');
                expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);

                // The file input field should be cleared.
                expect($file.val()).toEqual('');

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The uploaded attachment is not added due to the error.
                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();
            });
        });

        describe('attaching a document', function() {
            it("should add a document's file", function() {
                var doc;
                var attachment;
                var json;

                app.data.declareModel('Documents', {});
                doc = app.data.createBean('Documents', {
                    id: _.uniqueId(),
                    name: 'Contract.pdf',
                    filename: 'Contract.pdf'
                });
                sandbox.stub(doc, 'fetch').callsFake(function(options) {
                    // The document attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the document
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe('Contract.pdf');

                    doc.set({
                        document_revision_id: _.uniqueId(),
                        name: 'Contract.pdf',
                        filename: 'Contract.pdf',
                        latest_revision_file_mime_type: 'application/pdf',
                        latest_revision_file_size: 158589,
                        latest_revision_file_ext: 'pdf'
                    });

                    options.success(doc);
                    options.complete();
                });

                field.view.trigger('email_attachments:document', doc);

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The document attachment should now exist.
                expect(attachments.length).toBe(3);

                attachment = attachments.at(2);
                expect(attachment.get('id')).toBeUndefined();
                expect(attachment.get('upload_id')).toBe(doc.get('document_revision_id'));
                expect(attachment.get('name')).toBe('Contract.pdf');
                expect(attachment.get('filename')).toBe('Contract.pdf');
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_size')).toBe(158589);
                expect(attachment.get('file_ext')).toBe('pdf');
                expect(attachment.get('file_source')).toBe('DocumentRevisions');

                json = model.toJSON();
                expect(json.attachments.create).toEqual([{
                    _link: 'attachments',
                    upload_id: doc.get('document_revision_id'),
                    name: 'Contract.pdf',
                    filename: 'Contract.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf',
                    file_source: 'DocumentRevisions'
                }]);
                expect(json.attachments.add.length).toBe(0);
                expect(json.attachments.delete.length).toBe(0);
            });
        });

        describe('removing an attachment', function() {
            it('should remove a new attachment', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var json;

                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file').callsFake(function(method, data, $files, callbacks, options) {
                    var response = {
                        filename: {
                            guid: fileName
                        },
                        record: {
                            id: _.uniqueId(),
                            deleted: false,
                            file_mime_type: 'application/pdf',
                            file_size: 158589,
                            filename: fileName,
                            file_ext: 'pdf'
                        }
                    };

                    callbacks.success(response);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                // The uploaded attachment should now exist.
                expect(attachments.length).toBe(3);

                json = model.toJSON();
                expect(json.attachments.create).toEqual([{
                    _link: 'attachments',
                    filename_guid: attachments.at(2).get('filename_guid'),
                    name: fileName,
                    filename: fileName,
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }]);
                expect(json.attachments.delete.length).toBe(0);

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: attachments.at(2).cid}));

                // The uploaded attachment should no longer exist.
                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();
            });

            it('should remove an existing attachment', function() {
                var json;
                var id = attachments.at(0).get('id');

                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: attachments.at(0).cid}));

                expect(attachments.length).toBe(1);

                json = model.toJSON();
                expect(json.attachments.create.length).toBe(0);
                expect(json.attachments.add.length).toBe(0);
                expect(json.attachments.delete).toEqual([id]);
            });

            it('should remove a placeholder attachment', function() {
                var $file = $('<input/>', {value: 'quote.pdf'});
                var placeholder;

                // Don't allow the success callback to be called for the request.
                sandbox.stub(app.api, 'file');

                sandbox.stub(field, '_getFileInput').returns($file);
                field.$('input[type=file]').change();
                placeholder = field._placeholders.at(0);

                expect(field._placeholders.length).toBe(1);
                expect(placeholder.get('name')).toBe('quote.pdf');

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: placeholder.cid}));

                expect(field._placeholders.length).toBe(0);
                expect(field._requests[placeholder.cid]).toBeUndefined();
            });
        });

        describe('reporting if the max aggregate file size limit has been exceeded', function() {
            var spyOver;
            var spyUnder;

            beforeEach(function() {
                app.config.maxAggregateEmailAttachmentsBytes = 10000000;
                spyOver = sandbox.spy();
                spyUnder = sandbox.spy();
            });

            it('should trigger the over_max_total_bytes event', function() {
                field.model.on('attachments_collection:over_max_total_bytes', spyOver);
                field.model.on('attachments_collection:under_max_total_bytes', spyUnder);

                attachments.add([{
                    _module: 'Notes',
                    _link: 'attachments',
                    filename_guid: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 4000000,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    _link: 'attachments',
                    upload_id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: '4000000',
                    file_source: 'DocumentRevisions',
                    file_ext: 'jpg'
                }, {
                    _module: 'Notes',
                    _link: 'attachments',
                    filename_guid: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 4000000,
                    file_ext: 'pdf'
                }]);

                expect(spyOver).toHaveBeenCalledOnce();
                expect(spyUnder).not.toHaveBeenCalled();
                expect(spyOver.firstCall.args[0]).toBe(12317178);
                expect(spyOver.firstCall.args[1]).toBe(app.config.maxAggregateEmailAttachmentsBytes);
                expect(spyOver.firstCall.args[2]).toBe(-2317178);
            });

            it('should trigger the under_max_total_bytes event', function() {
                field.model.on('attachments_collection:over_max_total_bytes', spyOver);
                field.model.on('attachments_collection:under_max_total_bytes', spyUnder);

                attachments.add([{
                    _module: 'Notes',
                    _link: 'attachments',
                    filename_guid: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 4000000,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    _link: 'attachments',
                    upload_id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: '4000000',
                    file_source: 'DocumentRevisions',
                    file_ext: 'jpg'
                }]);

                expect(spyOver).not.toHaveBeenCalled();
                expect(spyUnder).toHaveBeenCalledOnce();
                expect(spyUnder.firstCall.args[0]).toBe(8317178);
                expect(spyUnder.firstCall.args[1]).toBe(app.config.maxAggregateEmailAttachmentsBytes);
                expect(spyUnder.firstCall.args[2]).toBe(1682822);
            });

            using('unknown file sizes', ['', null, undefined, 0, NaN], function(fileSize) {
                it('should treat an unknown file size as 0 bytes', function() {
                    // Remove any existing attachments to clearly show that the
                    // total file size is not changed.
                    attachments.reset();

                    field.model.on('attachments_collection:over_max_total_bytes', spyOver);
                    field.model.on('attachments_collection:under_max_total_bytes', spyUnder);

                    // Add an attachment without a file size.
                    attachments.add([{
                        _module: 'Notes',
                        _link: 'attachments',
                        filename_guid: _.uniqueId(),
                        name: 'Disclosure Agreement.pdf',
                        filename: 'Disclosure Agreement.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: fileSize,
                        file_ext: 'pdf'
                    }]);

                    // The attachment should have been added with 0 bytes as its size.
                    expect(attachments.length).toBe(1);
                    expect(spyOver).not.toHaveBeenCalled();
                    expect(spyUnder).toHaveBeenCalledOnce();
                    expect(spyUnder.firstCall.args[0]).toBe(0);
                    expect(spyUnder.firstCall.args[1]).toBe(app.config.maxAggregateEmailAttachmentsBytes);
                    expect(spyUnder.firstCall.args[2]).toBe(app.config.maxAggregateEmailAttachmentsBytes);
                });
            });

            it(
                'should trigger the under_max_total_bytes event when removing an attachment brings the total under max',
                function() {
                    attachments.add([{
                        _module: 'Notes',
                        _link: 'attachments',
                        filename_guid: _.uniqueId(),
                        name: 'Disclosure Agreement.pdf',
                        filename: 'Disclosure Agreement.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: 4000000,
                        file_ext: 'pdf'
                    }, {
                        _module: 'Notes',
                        _link: 'attachments',
                        upload_id: _.uniqueId(),
                        name: 'logo.jpg',
                        filename: 'logo.jpg',
                        file_mime_type: 'image/jpg',
                        file_size: '4000000',
                        file_source: 'DocumentRevisions',
                        file_ext: 'jpg'
                    }, {
                        _module: 'Notes',
                        _link: 'attachments',
                        filename_guid: _.uniqueId(),
                        name: 'NDA.pdf',
                        filename: 'NDA.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: 4000000,
                        file_ext: 'pdf'
                    }]);

                    field.model.on('attachments_collection:over_max_total_bytes', spyOver);
                    field.model.on('attachments_collection:under_max_total_bytes', spyUnder);

                    attachments.remove(attachments.at(4));

                    expect(spyOver).not.toHaveBeenCalled();
                    expect(spyUnder).toHaveBeenCalledOnce();
                    expect(spyUnder.firstCall.args[0]).toBe(8317178);
                    expect(spyUnder.firstCall.args[1]).toBe(app.config.maxAggregateEmailAttachmentsBytes);
                    expect(spyUnder.firstCall.args[2]).toBe(1682822);
                }
            );
        });
    });

    describe('viewing an archived email', function() {
        var attachments;

        beforeEach(function() {
            var data = [{
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                upload_id: '',
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: '',
                file_ext: 'pdf'
            }, {
                _module: 'Notes',
                _link: 'attachments',
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'jpg'
            }];

            // Set the next offset now to avoid potential pagination from the
            // CollectionFieldLoadAll plugin on sync.
            attachments = model.get('attachments_collection');
            attachments.next_offset = {
                attachments: -1
            };

            // Act as if the model was retrieved from the server.
            model.set('id', _.uniqueId());
            model.set('attachments_collection', data);
            model.trigger('sync');

            field = SugarTest.createField({
                name: 'attachments_collection',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });
        });

        it('should format the value', function() {
            var urlEndpoint = '/file/filename?force_download=1&' + timestamp + '=1&platform=base';
            var expected = [{
                cid: attachments.at(0).cid,
                _module: 'Notes',
                _link: 'attachments',
                id: attachments.at(0).get('id'),
                upload_id: '',
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_source: '',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(0).get('id') + urlEndpoint
            }, {
                cid: attachments.at(1).cid,
                _module: 'Notes',
                _link: 'attachments',
                id: attachments.at(1).get('id'),
                upload_id: attachments.at(1).get('upload_id'),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: '159K',
                file_source: 'DocumentRevisions',
                file_ext: 'jpg',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(1).get('id') + urlEndpoint
            }];

            expect(field.getFormattedValue()).toEqual(expected);
            expect(field.tooltip).toBe('Disclosure Agreement.pdf, logo.jpg');
        });

        it('should download an attachment', function() {
            var url = app.api.serverUrl +
                '/Notes/' +
                attachments.at(0).get('id') +
                '/file/filename?force_download=1&' +
                timestamp +
                '=1&platform=base';

            sandbox.stub(app.api, 'fileDownload');

            field.render();
            field.$('[data-action=download]').first().click();

            expect(app.api.fileDownload).toHaveBeenCalledWith(url);
        });
    });

    describe('checking if the field is empty', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'attachments_collection',
                type: 'email-attachments',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context
            });
            field.render();
        });

        it('should return true', function() {
            expect(field.isEmpty()).toBe(true);
        });

        it('should return false', function() {
            model.get('attachments_collection').add({
                _module: 'Notes',
                _link: 'attachments',
                filename_guid: _.uniqueId(),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            });

            expect(field.isEmpty()).toBe(false);
        });

        it('should return false when there is a placeholder', function() {
            var $file = $('<input/>', {value: 'quote.pdf'});
            var flag;

            // Don't allow the success callback to be called for the request.
            sandbox.stub(field, '_getFileInput').returns($file);
            sandbox.stub(app.api, 'file').callsFake(function() {
                flag = true;
            });

            runs(function() {
                flag = false;
                field.$('input[type=file]').change();
            });

            waitsFor(function() {
                return flag;
            }, 'The file to be uploaded', 100);

            runs(function() {
                expect(field.isEmpty()).toBe(false);
            });
        });
    });

    describe('rendering in disabled mode', function() {
        it('should disable the select2 element', function() {
            field = SugarTest.createField({
                name: 'attachments_collection',
                type: 'email-attachments',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context
            });

            field.render();
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(false);

            field.setDisabled();
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(true);
        });
    });
});
