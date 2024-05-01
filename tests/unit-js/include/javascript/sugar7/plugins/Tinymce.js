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
describe('Plugins.Tinymce', function() {
    var module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        app, field, sinonSandbox;
    var view;
    var plugin;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        SugarTest.testMetadata.init();
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', fieldType);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate('file', 'field', 'base', 'detail', 'EmbeddedFiles');
        SugarTest.loadPlugin('Tinymce');
        plugin = app.plugins.plugins.view.Tinymce;
        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module);
        view = SugarTest.createView('base', 'Emails', 'create');
    });

    afterEach(function() {
        delete app.plugins.plugins.field.Tinymce;
        delete app.plugins.plugins.view.Tinymce;
        sinon.restore();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('onAttach', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldOnAttach').callsFake(function() {});
            sinon.stub(plugin, '_viewOnAttach').callsFake(function() {});
        });

        it('should call _fieldOnAttach when called with a Field component', function() {
            plugin.onAttach(field);

            expect(plugin._fieldOnAttach).toHaveBeenCalled();
            expect(plugin._viewOnAttach).not.toHaveBeenCalled();
        });

        it('should call _viewOnAttach when called with a View component', function() {
            plugin.onAttach(view);

            expect(plugin._fieldOnAttach).not.toHaveBeenCalled();
            expect(plugin._viewOnAttach).toHaveBeenCalled();
        });
    });

    describe('_viewOnAttach', function() {
        it('should call setupEditorResize on view render', function() {
            sinon.stub(plugin, 'setupEditorResize');
            plugin._viewOnAttach(view);
            view.trigger('render');

            expect(plugin.setupEditorResize).toHaveBeenCalled();
        });
    });

    describe('onDetach', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_fieldOnDetach').callsFake(function() {});
            sinon.stub(plugin, '_viewOnDetach').callsFake(function() {});
        });

        it('should call _fieldOnDetach when called with a Field component', function() {
            plugin.onDetach(field);

            expect(plugin._fieldOnDetach).toHaveBeenCalled();
            expect(plugin._viewOnDetach).not.toHaveBeenCalled();
        });

        it('should call _viewOnDetach when called with a View component', function() {
            plugin.onDetach(view);

            expect(plugin._fieldOnDetach).not.toHaveBeenCalled();
            expect(plugin._viewOnDetach).toHaveBeenCalled();
        });
    });

    describe('_viewOnDetach', function() {
        it('should detach resize event from window', function() {
            var offStub = sinon.stub(jQuery.fn, 'off');
            plugin._viewOnDetach(view);

            expect(offStub).toHaveBeenCalled();
        });
    });

    describe('_fieldOnDetach', function() {
        it('should remove embeddedInput', function() {
            plugin.$embeddedInput = {
                remove: sinon.stub()
            };
            plugin._fieldOnDetach(field);

            expect(plugin.$embeddedInput.remove).toHaveBeenCalled();
        });
    });

    describe('setupEditorResize', function() {
        beforeEach(function() {
            sinon.stub(plugin, '_resizeEditor');
            sinon.stub(jQuery.fn, 'on');
            plugin.listenTo = sinon.spy();
            plugin.on = sinon.stub();
            plugin.module = 'Emails';

            plugin.setupEditorResize();
        });

        it('should set up the listeners', function() {
            expect(plugin.listenTo).toHaveBeenCalledTwice();
            expect(plugin.on).toHaveBeenCalledTwice();
            expect($(window).on).toHaveBeenCalledOnce();
        });
    });

    it('Append input for embedded files on render.', function() {
        var name = 'testName';
        field.$embeddedInput = $('<input />', {name: name, type: 'file'});
        field.render();
        expect(field.$el.find('input[name=' + name + ']').length).toEqual(1);
    });

    it('Clear element on file type mismatching.', function() {
        tinymce.activeEditor = {
            windowManager: {
                alert: sinonSandbox.stub()
            }
        };
        var winObj = {};
        var fakeFileObj = {name: 'filename.txt', type: 'text/plain'};
        var clearFileSpy = sinonSandbox.spy(field, 'clearFileInput');

        sinonSandbox.stub(field, 'initTinyMCEEditor').callsFake($.noop());
        field.render();

        // The fake file is text, image required.
        // Need to replace `input` with `p`, because `FileList` attribute of `HTMLInputElement` is read-only.
        field.$embeddedInput = $('<p/>');
        field.$embeddedInput[0].files = [fakeFileObj];

        field.tinyMCEFileBrowseCallback('fakeName', 'fakeUrl', 'image', winObj);
        field.$embeddedInput.change();

        expect(clearFileSpy).toHaveBeenCalledOnce();
    });

    it('Create embedded file', function() {
        var saveStub = sinonSandbox.stub();
        sinonSandbox.stub(app.data, 'createBean').returns({
            save: saveStub
        });
        var blobInfo = {
            filename: function() {
                return 'a name';
            }
        };
        field.tinyMCEImagePasteCallback(blobInfo, '', '');
        expect(saveStub).toHaveBeenCalledOnce();
        expect(saveStub.firstCall.args[0].name).toBe('a name');
    });

    it('Save pasted image', function() {
        var uploadStub = sinonSandbox.stub();
        var blobInfo = {
            blob: function() {
                return 'some data';
            }
        };
        var model = {
            uploadFile: uploadStub
        };
        field._savePastedImage(blobInfo, model);
        expect(uploadStub).toHaveBeenCalledOnce();
        expect(uploadStub.firstCall.args[1][0].files[0]).toBe('some data');
    });

    it('Handle embedded images', function() {
        var removeStub = sinonSandbox.stub(field, '_removeImages');
        var parseStub = sinonSandbox.stub(field, '_parseImages').returns(['8e7fa198-221b-11ec-97e4-acde48002233']);
        field.newImages = ['9f7fa197-221b-11ec-97e4-acde48001122'];
        field.handleEmbeddedImages('some value');
        expect(removeStub).toHaveBeenCalledWith(['9f7fa197-221b-11ec-97e4-acde48001122']);
        expect(field.newImages).toEqual([]);
        expect(field.existingImages).toEqual(['8e7fa198-221b-11ec-97e4-acde48002233']);
    });

    it('Remove embedded images', function() {
        tinymce.activeEditor = {
            getContent: function() {
                return 'some value';
            }
        };
        var removeStub = sinonSandbox.stub(field, '_removeImages');
        var parseStub = sinonSandbox.stub(field, '_parseImages').returns(['8e7fa198-221b-11ec-97e4-acde48002233']);
        field.newImages = ['9f7fa197-221b-11ec-97e4-acde48001122'];
        field.existingImages = ['8e7fa198-221b-11ec-97e4-acde48002233'];
        field.handleImageSave();
        expect(removeStub).toHaveBeenCalledWith(['9f7fa197-221b-11ec-97e4-acde48001122']);
    });
});
