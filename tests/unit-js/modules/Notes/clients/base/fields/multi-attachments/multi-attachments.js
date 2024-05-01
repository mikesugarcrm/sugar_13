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
describe('modules.notes.clients.base.fields.multi-attachments', function() {
    var app;
    var field;
    var sandbox;
    var module = 'Notes';
    var model;
    var fieldName = 'attachment_list';
    var fieldType = 'multi-attachments';
    var fieldDef = {
        name: 'attachment_list',
        type: 'multi-attachments',
        link: 'attachments',
        module: 'Notes',
        modulefield: 'filename'
    };

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', fieldType, module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.testMetadata.set();

        field = SugarTest.createField('base', fieldName, fieldType, 'detail',
            fieldDef, module, model, null, true
        );

        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    describe('format', function() {
        using('different values for pill Added and hasAttachment', [
            {pillAdded: false, hasAttachment: false, expectCalled: false, expected: []},
            {pillAdded: false, hasAttachment: true, expectCalled: true, expected: ['added']},
            {pillAdded: true, hasAttachment: false, expectCalled: false, expected: []},
            {pillAdded: true, hasAttachment: true, expectCalled: false, expected: []}
        ], function(values) {
            it('should add pill if not added, and hasAttachment', function() {
                sandbox.stub(field, '_modelHasFileAttachment').returns(values.hasAttachment);
                sandbox.stub(field, '_getPillFromFile').returns('added');
                sandbox.stub(field, '_singleImagePill').returns(true);
                sandbox.stub(field, '_pillAdded').returns(values.pillAdded);
                var actual = field.format([]);
                expect(field._getPillFromFile.called).toEqual(values.expectCalled);
                expect(actual).toEqual(values.expected);
                expect(field._modelHasFileAttachment.called).toEqual(!values.pillAdded);
                expect(field._singleImagePill).toHaveBeenCalled();
            });
        });
    });

    describe('_pillAdded', function() {
        using('different contents and modelIds', [
            {contents: [{id: 1}, {id: 2}], modelId: 1, expected: true},
            {contents: [{id: 4}, {id: 2}], modelId: 1, expected: false},
            {contents: [], modelId: 1, expected: false}
        ], function(values) {
            it('should return true if model ID is in contents', function() {
                field.model.id = values.modelId;
                var actual = field._pillAdded(values.contents);
                expect(actual).toEqual(values.expected);
            });
        });
    })

    describe('_modelHasFileAttachment', function() {
        using('different field and mimetype combinations', [
            {filename: 'name', file_mime_type: 'mimetype', expected: true},
            {filename: 'name', file_mime_type: null, expected: false},
            {filename: null, file_mime_type: 'mimetype', expected: false},
            {filename: 'name', file_mime_type: null, expected: false},
        ], function(values) {
            it('should return true if file_name and mime_type are set', function() {
                sandbox.stub(field, 'format');
                field.model.attributes = values;
                var actual = field._modelHasFileAttachment();
                expect(actual).toEqual(values.expected);
            })
        });
    });

    describe('_getPillFromFile', function() {
        using('different model attributes', [
            {filename: 'name.pdf', file_mime_type: 'application/pdf', isImage: false, ext: '.pdf'},
            {filename: 'name.jpg', file_mime_type: 'image/jpeg', isImage: true, ext: '.jpg'},
        ], function(values) {
            it('should create an pill object based on model attributes', function() {
                field.model.attributes = {
                    filename: values.filename,
                    file_mime_type: values.file_mime_type,
                    id: 'id'
                };
                var actual = field._getPillFromFile();
                var expected = {
                    id: 'id',
                    filename: values.filename,
                    file_mime_type: values.file_mime_type
                }
                expect(actual).toEqual(expected);
            });
        });
    });

    describe('removeAttachment', function() {
        using('different event and IDs', [
            {val: 1, id: 1, expectRemoveLegacy: true},
            {val: 1, id: 2, expectRemoveLegacy: false}
        ], function(values) {
            it('should remove legacy attachment if evt and model ids match', function() {
                sandbox.stub(field, '_removeLegacyAttachment');
                sandbox.stub(field, 'render');
                sandbox.stub(field, '_super');
                field.pillAdded = true;
                field.model.set('id', values.id);
                field.removeAttachment({val: values.val});
                expect(field._removeLegacyAttachment.called).toEqual(values.expectRemoveLegacy);
                expect(field._super.called).not.toEqual(values.expectRemoveLegacy);
                expect(field.pillAdded).not.toEqual(values.expectRemoveLegacy);
            });
        });
    });

    describe('_removeLegacyAttachment', function() {
        it('should remove legacy attachment', function() {
            sandbox.stub(field, 'render');
            sandbox.stub(field, 'format');
            field.model.set('id', 'id1');
            field.model.set('filename', 'name1');
            field.model.set('attachment_list', {models: [
                app.data.createBean('Notes', {id: 'id1'}),
                app.data.createBean('Notes', {id: 'id2'})
            ]});
            field._removeLegacyAttachment();
            expect(field.model.get('attachment_list').models.length).toEqual(1);
            expect(field.model.get('attachment_list').models[0].get('id')).toEqual('id2');
            expect(field.model.get('filename')).toEqual('');
        });
    });

    describe('_isImage', function() {
        using('different mime types', [
            {mimeType: 'image/jpeg', expected: true},
            {mimeType: 'notimage/xml', expected: false},
        ], function(values) {
            it('should return true if the mimetype matches one on our list', function() {
                var actual = field._isImage(values.mimeType);
                expect(actual).toEqual(values.expected);
            });
        });
    });
});
