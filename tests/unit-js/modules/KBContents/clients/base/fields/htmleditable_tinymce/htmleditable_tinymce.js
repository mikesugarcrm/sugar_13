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
describe('modules.kbcontents.clients.base.fields.htmleditable_tinymce', function() {

    var app, field,
        module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        model;

    beforeEach(function() {
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
        field.tinyMCEFileBrowseCallback = sinon.stub();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    describe('setViewContent', function() {
        var value;
        var $element = $('<iframe class="kbdocument-body"></iframe>');

        beforeEach(function() {
            sinon.stub(field, '_super');
            sinon.stub(field, '_getHtmlEditableField').returns($element);
            sinon.stub($.fn, 'ready');
        });

        it('should not add css when value is empty', function() {
            value = '';
            field.setViewContent(value);
            expect(field._super).toHaveBeenCalledWith('setViewContent', ['']);
        });

        it('should add css when value is not empty', function() {
            value = '<p>test</p><ul><li>test</li></ul><p>test</p>';
            field.setViewContent(value);
            expect(field._super).toHaveBeenCalledWith('setViewContent',
                ['<div class="kbdocument-body"><p style="font-size: 14px; margin-top: 7.5px;">test</p>' +
                '<ul><li>test</li></ul><p>test</p></div>']);
        });
    });

    describe('updateBodyHeight', function() {
        var $element;

        beforeEach(function() {
            $element = $('<iframe class="kbdocument-body"></iframe>');
            $element.height(150);
        });

        using('different height values', [
            {
                contentHeight: 100,
                expected: 150
            },
            {
                contentHeight: 250,
                expected: 270
            }
        ], function(options) {
            it('should appropriately set KB body height', function() {
                sinon.stub(field, '_getContentHeight').returns(options.contentHeight);

                field.updateBodyHeight($element);

                expect($element.height()).toEqual(options.expected);
            });
        });

        it('should set KB body height to 60% of window height', function() {
            sinon.stub(field, '_getContentHeight').returns(1500);

            field.updateBodyHeight($element);

            expect($element.height()).toEqual(field.maxBodyHeight);
        });
    });

    describe('_dispose', function() {
        it('should call window.removeEventListener with resize and field.resizeWindowHandler', function() {
            sinon.stub(window, 'removeEventListener').callsFake(function() {});
            sinon.stub(field, '_super');
            field._dispose();

            expect(window.removeEventListener).toHaveBeenCalledWith('resize', field.resizeWindowHandler);
            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
