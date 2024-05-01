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
describe('htmleditable_tinymce', function() {
    var field, sandbox, tinymce;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    describe('edit view', function() {
        beforeEach(function() {
            var $textarea = $('<textarea class="htmleditable"></textarea>');
            field = SugarTest.createField('base', 'html_email', 'htmleditable_tinymce', 'edit');
            sandbox.stub(field, '_getHtmlEditableField').callsFake(function() {
                return $textarea;
            });
            sandbox.stub(field, 'destroyTinyMCEEditor').callsFake($.noop());
            sandbox.stub(field, '_iframeHasBody').returns(false);
            tinymce = $.fn.tinymce;
            $.fn.tinymce = $.noop;
        });

        afterEach(function() {
            field.dispose();
            tinymce = $.fn.tinymce;
        });

        it('should render edit view not readonly view', function() {
            var edit = sandbox.spy(field, '_renderEdit'),
                view = sandbox.spy(field, '_renderView');
            sandbox.stub(field, 'initTinyMCEEditor').callsFake($.noop());
            field.render();

            expect(edit.called).toBeTruthy();
            expect(view.called).toBeFalsy();
        });

        it('should give access to TinyMCE config', function() {
            expect(field.getTinyMCEConfig()).toBeDefined();
        });

        it('should initialize TinyMCE editor when it doesn\'t exist', function() {
            var tinymceSpy = sandbox.spy($.fn, 'tinymce'),
                configSpy = sandbox.spy(field, 'getTinyMCEConfig');

            field.initTinyMCEEditor();

            expect(tinymceSpy.calledOnce).toBeTruthy();
            expect(configSpy.calledOnce).toBeTruthy();
        });

        it('should initialize TinyMCE editor with custom config options', function() {
            var tinymceSpy = sandbox.spy($.fn, 'tinymce'),
                configSpy = sandbox.spy(field, 'getTinyMCEConfig');

            field.initTinyMCEEditor({
                script_url: 'include/javascript/tiny_mce/tiny_mce.js',
                theme: 'advanced',
                skin: 'sugar7',
                plugins: 'style',
                entity_encoding: 'raw',
                theme_advanced_buttons1: 'code',
                theme_advanced_toolbar_location: 'top',
                theme_advanced_toolbar_align: 'left',
                theme_advanced_statusbar_location: 'bottom',
                theme_advanced_resizing: true,
                schema: 'html5'
            });

            expect(tinymceSpy.calledOnce).toBeTruthy();
            expect(configSpy.called).toBeTruthy();
        });

        it('should not initialize TinyMCE editor if it already exists', function() {
            var tinymceSpy = sinon.spy($.fn, 'tinymce');
            var configSpy = sinon.spy(field, 'getTinyMCEConfig');
            var config = field.getTinyMCEConfig();
            var called = 0;
            config.setup = function(){
                if(called < 1){
                    field.initTinyMCEEditor(config);
                    called++;
                } else {
                    expect(tinymceSpy.calledOnce).toBeTruthy();
                    expect(configSpy.calledOnce).toBeTruthy();
                    tinymceSpy.restore();
                    configSpy.restore();
                }
            };
            field.initTinyMCEEditor(config);
        });

        it('setting a value to the model should also set the editor with that value', function() {
            var expectedValue = 'foo',
                setEditorContentSpy;

            field.render();
            setEditorContentSpy = sandbox.spy(field, 'setEditorContent');
            sandbox.stub(field, 'handleEmbeddedImages');
            field.model.set(field.name, expectedValue);

            expect(setEditorContentSpy.withArgs(expectedValue).calledOnce).toBeTruthy();
        });
    });

    describe('readonly view', function() {
        beforeEach(function() {
            var $textarea = $('<iframe class="htmleditable" frameborder="0"></iframe>');
            field = SugarTest.createField('base', 'html_email', 'htmleditable_tinymce', 'detail');
            sandbox.stub(field, '_getHtmlEditableField').callsFake(function() {
                return $textarea;
            });
            sandbox.stub(field, '_iframeHasBody').returns(false);
            sandbox.stub(field, 'destroyTinyMCEEditor').callsFake($.noop());
        });

        afterEach(function() {
            field.dispose();
        });

        it('should render read view not edit view', function() {
            var edit = sandbox.spy(field, '_renderEdit'),
                view = sandbox.spy(field, '_renderView');

            field.render();

            expect(edit.called).toBeFalsy();
            expect(view.called).toBeTruthy();
        });

        it('should not return TinyMCE editor', function() {
            var tinymceSpy = sandbox.spy(field, 'initTinyMCEEditor');

            field.render();

            expect(tinymceSpy.called).toBeFalsy();
        });

        it('should set the value to the div if the model is changed', function() {
            var mock,
                expectedValue = 'foo';

            field.render();

            mock = sandbox.mock(field);
            mock.expects('setViewContent').once().withArgs(expectedValue);

            field.model.set(field.name, expectedValue);

            mock.verify();
        });
    });

    describe('readonly view from a preview pane', function() {
        var $textarea;
        var appendStub;

        beforeEach(function() {
            $textarea = $('<iframe class="htmleditable" frameborder="0"></iframe>');
            field = SugarTest.createField('base', 'html_email', 'htmleditable_tinymce', 'detail');

            $(document.body).append($textarea);
            appendStub = sandbox.stub();

            sandbox.stub($textarea, 'contents').callsFake(function() {
                return {
                    find: function(elem) {
                        if (elem === 'body') {
                            return {
                                length: 1,
                                html: $.noop
                            };
                        } else if (elem === 'link[rel="stylesheet"]') {
                            return {
                                each: $.noop
                            };
                        } else if (elem === 'head') {
                            return {
                                append: appendStub
                            };
                        }
                    }
                };
            });
            sandbox.stub(field, '_iframeHasBody').returns(true);
            sandbox.stub(field, '_getHtmlEditableField').callsFake(function() {
                return $textarea;
            });

            sandbox.stub(field, 'destroyTinyMCEEditor').callsFake($.noop());
        });

        afterEach(function() {
            $textarea.remove();
            appendStub = null;
            field.dispose();
        });

        it('should insert the tinyMCE CSS file into the iframe', function() {
            field.model.set(field.name, 'Test 123');
            field.render();

            expect(appendStub).toHaveBeenCalled();
        });
    });
});
