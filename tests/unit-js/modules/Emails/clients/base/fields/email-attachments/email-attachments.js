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
describe('Emails.BaseEmailAttachmentsField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'email-attachments');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('adding attachments from a template', function() {
        var attachments;
        var template;

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
                context: context,
                loadFromModule: true
            });
            field.render();

            app.data.declareModel('EmailTemplates', {});
            template = app.data.createBean('EmailTemplates', {
                id: _.uniqueId(),
                name: 'We have quite the offer for you!'
            });
        });

        it('should not remove non-template attachments', function() {
            var json;
            var notes = app.data.createBeanCollection('Notes');

            // Add an uploaded file and an attachment from a document.
            // These are not yet linked.
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
                upload_id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'pdf'
            }]);

            sandbox.stub(app.data, 'createRelatedCollection').returns(notes);
            sandbox.stub(notes, 'fetch').callsFake(function(options) {
                // No placeholder attachment.
                expect(field._placeholders.length).toBe(0);

                // Pass the attachments linked to the template.
                notes.add([{
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_ext: 'jpg'
                }]);

                options.success(notes);
                // Pretend a real XMLHttpRequest object was created and
                // pass it to the complete callback.
                options.complete({uid: _.uniqueId()});
            });

            field.view.trigger('email_attachments:template', template);

            expect(attachments.length).toBe(7);

            json = model.toJSON();
            expect(json.attachments.create).toEqual([{
                _module: 'Notes',
                _link: 'attachments',
                filename_guid: attachments.at(2).get('filename_guid'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            }, {
                _module: 'Notes',
                _link: 'attachments',
                upload_id: attachments.at(3).get('upload_id'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf',
                file_source: 'DocumentRevisions'
            }, {
                _link: 'attachments',
                upload_id: attachments.at(4).get('upload_id'),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf',
                file_source: 'EmailTemplates'
            }, {
                _link: 'attachments',
                upload_id: attachments.at(5).get('upload_id'),
                name: 'NDA.pdf',
                filename: 'NDA.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf',
                file_source: 'EmailTemplates'
            }, {
                _link: 'attachments',
                upload_id: attachments.at(6).get('upload_id'),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: 158589,
                file_ext: 'jpg',
                file_source: 'EmailTemplates'
            }]);
            expect(json.attachments.add.length).toBe(0);
            expect(json.attachments.delete.length).toBe(0);
        });

        it('should continue to unlink attachments set to be removed', function() {
            var json;
            var id = attachments.at(0).get('id');
            var notes = app.data.createBeanCollection('Notes');

            // Set to unlink an attachment.
            attachments.remove(attachments.at(0));

            sandbox.stub(app.data, 'createRelatedCollection').returns(notes);
            sandbox.stub(notes, 'fetch').callsFake(function(options) {
                // No placeholder attachment.
                expect(field._placeholders.length).toBe(0);

                // Pass the attachments linked to the template.
                notes.add([{
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_ext: 'jpg'
                }]);

                options.success(notes);
                // Pretend a real XMLHttpRequest object was created and
                // pass it to the complete callback.
                options.complete({uid: _.uniqueId()});
            });

            field.view.trigger('email_attachments:template', template);

            expect(attachments.length).toBe(4);

            json = model.toJSON();
            expect(json.attachments.create).toEqual([{
                _link: 'attachments',
                upload_id: attachments.at(1).get('upload_id'),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf',
                file_source: 'EmailTemplates'
            }, {
                _link: 'attachments',
                upload_id: attachments.at(2).get('upload_id'),
                name: 'NDA.pdf',
                filename: 'NDA.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf',
                file_source: 'EmailTemplates'
            }, {
                _link: 'attachments',
                upload_id: attachments.at(3).get('upload_id'),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: 158589,
                file_ext: 'jpg',
                file_source: 'EmailTemplates'
            }]);
            expect(json.attachments.add.length).toBe(0);
            expect(json.attachments.delete).toEqual([id]);
        });

        it('should not remove placeholders attachments', function() {
            var $file = $('<input/>', {value: 'quote.pdf'});
            var notes = app.data.createBeanCollection('Notes');

            // Don't allow the success callback to be called for the request.
            sandbox.stub(app.api, 'file');

            sandbox.stub(field, '_getFileInput').returns($file);
            field.$('input[type=file]').change();

            expect(field._placeholders.length).toBe(1);
            expect(field._placeholders.at(0).get('name')).toBe('quote.pdf');

            sandbox.stub(app.data, 'createRelatedCollection').returns(notes);
            sandbox.stub(notes, 'fetch').callsFake(function(options) {
                // Should only have the placeholder attachment for the
                // uploaded file.
                expect(field._placeholders.length).toBe(1);
                expect(field._placeholders.at(0).get('name')).toBe('quote.pdf');

                // Pass the attachments linked to the template.
                notes.add([{
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    _module: 'Notes',
                    id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_ext: 'jpg'
                }]);

                options.success(notes);
                // Pretend a real XMLHttpRequest object was created and
                // pass it to the complete callback.
                options.complete({uid: _.uniqueId()});
            });

            field.view.trigger('email_attachments:template', template);

            // The template attachments should have been added.
            expect(attachments.length).toBe(5);

            // Should still have the placeholder attachment for the
            // uploaded file.
            expect(field._placeholders.length).toBe(1);
            expect(field._placeholders.at(0).get('name')).toBe('quote.pdf');
        });

        describe('the user changes templates twice in a single editing session', function() {
            it("should remove the first template's attachments", function() {
                var json;
                var notes = app.data.createBeanCollection('Notes');

                // Add a template attachment as if a template had been
                // selected before.
                attachments.add({
                    _module: 'Notes',
                    _link: 'attachments',
                    upload_id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf',
                    file_source: 'EmailTemplates'
                });

                expect(attachments.length).toBe(3);

                sandbox.stub(app.data, 'createRelatedCollection').returns(notes);
                sandbox.stub(notes, 'fetch').callsFake(function(options) {
                    // No placeholder attachment.
                    expect(field._placeholders.length).toBe(0);

                    // Pass the attachments linked to the template.
                    notes.add([{
                        _module: 'Notes',
                        id: _.uniqueId(),
                        name: 'NDA.pdf',
                        filename: 'NDA.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: 158589,
                        file_ext: 'pdf'
                    }, {
                        _module: 'Notes',
                        id: _.uniqueId(),
                        name: 'logo.jpg',
                        filename: 'logo.jpg',
                        file_mime_type: 'image/jpg',
                        file_size: 158589,
                        file_ext: 'jpg'
                    }]);

                    options.success(notes);
                    // Pretend a real XMLHttpRequest object was created and
                    // pass it to the complete callback.
                    options.complete({uid: _.uniqueId()});
                });

                field.view.trigger('email_attachments:template', template);

                expect(attachments.length).toBe(4);

                json = model.toJSON();
                expect(json.attachments.create).toEqual([{
                    _link: 'attachments',
                    upload_id: attachments.at(2).get('upload_id'),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf',
                    file_source: 'EmailTemplates'
                }, {
                    _link: 'attachments',
                    upload_id: attachments.at(3).get('upload_id'),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_ext: 'jpg',
                    file_source: 'EmailTemplates'
                }]);
                expect(json.attachments.add.length).toBe(0);
                expect(json.attachments.delete.length).toBe(0);
            });
        });

        describe('the user changes templates while editing an existing draft', function() {
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
                }, {
                    _module: 'Notes',
                    _link: 'attachments',
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: 'EmailTemplates',
                    file_ext: 'pdf'
                }];

                // Set the next offset now to avoid potential pagination from the
                // CollectionFieldLoadAll plugin on sync.
                attachments = model.get('attachments_collection');
                attachments.next_offset = {
                    attachments: -1
                };

                // Reset the attachments with an attachment linked from a
                // template. Again, we're acting as if the model was retrieved
                // from the server.
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

            it("should remove the previous template's attachments", function() {
                var json;
                var id = attachments.at(2).get('id');
                var notes = app.data.createBeanCollection('Notes');

                sandbox.stub(app.data, 'createRelatedCollection').returns(notes);
                sandbox.stub(notes, 'fetch').callsFake(function(options) {
                    // No placeholder attachment.
                    expect(field._placeholders.length).toBe(0);

                    // Pass the attachments linked to the template.
                    notes.add([{
                        _module: 'Notes',
                        id: _.uniqueId(),
                        name: 'Disclosure Agreement.pdf',
                        filename: 'Disclosure Agreement.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: 158589,
                        file_ext: 'pdf'
                    }, {
                        _module: 'Notes',
                        id: _.uniqueId(),
                        name: 'logo.jpg',
                        filename: 'logo.jpg',
                        file_mime_type: 'image/jpg',
                        file_size: 158589,
                        file_ext: 'jpg'
                    }]);

                    options.success(notes);
                    /// Pretend a real XMLHttpRequest object was created and
                    // pass it to the complete callback.
                    options.complete({uid: _.uniqueId()});
                });

                field.view.trigger('email_attachments:template', template);

                expect(attachments.length).toBe(4);

                json = model.toJSON();
                expect(json.attachments.create).toEqual([{
                    _link: 'attachments',
                    upload_id: attachments.at(2).get('upload_id'),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf',
                    file_source: 'EmailTemplates'
                }, {
                    _link: 'attachments',
                    upload_id: attachments.at(3).get('upload_id'),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_ext: 'jpg',
                    file_source: 'EmailTemplates'
                }]);
                expect(json.attachments.add.length).toBe(0);
                expect(json.attachments.delete).toEqual([id]);
            });
        });
    });
});
