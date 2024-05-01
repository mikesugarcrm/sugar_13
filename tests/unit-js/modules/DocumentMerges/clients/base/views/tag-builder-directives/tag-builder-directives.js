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
describe('DocumentMerges.View.TagBuilderDirectives', function() {
    var app;
    var sinonSandbox;
    var view;
    var mockEvent;
    var initializeDropDownsStub;
    var hideCustomDateStub;
    var initColorPickerStub;
    var mockchangeEvent;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-directives', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-directives', 'view', 'base', null, 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-directives', 'view', 'base', 'table', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-directives', 'view', 'base', 'list', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-directives', 'view', 'base', 'date', 'DocumentMerges');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-directives', null, null, true);

        var mainModule = 'Accounts';
        var fieldDefs = [
            {
                name: 'a',
                type: 'name',
            },
            {
                name: 'b',
                type: 'date',
            },
            {
                name: 'c',
                type: 'varchar',
            },
            {
                name: 'd',
                type: 'link',
                module: 'Contacts'
            },
        ];
        SugarTest.testMetadata.updateModuleMetadata(mainModule, {fields: fieldDefs});
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('render', function() {
        beforeEach(function() {
            initializeDropDownsStub = sinonSandbox.stub(view, 'initializeDropDowns');
            hideCustomDateStub = sinonSandbox.stub(view, 'hideCustomDate');
            initColorPickerStub = sinonSandbox.stub(view, 'initColorPicker');
        });
        it('should render selects, colorpicker and hide custom date', function() {
            view.render();
            expect(initializeDropDownsStub).toHaveBeenCalled();
            expect(hideCustomDateStub).toHaveBeenCalled();
            expect(initColorPickerStub).toHaveBeenCalled();
        });
    });

    describe('changeDirective', function() {
        beforeEach(function() {
            renderStub = sinonSandbox.stub(view, 'render');
            mockEvent = $.Event('change');
            mockEvent.target = document.createElement('button');
            mockEvent.target.value = 'table';
        });
        it('should change the directive view', function() {
            view.changeDirective(mockEvent);
            expect(view.currentDirective).toBe('table');
            expect(view.tag.getName()).toBe('table');
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('initTag', function() {
        it('should initialize a tag', function() {
            view.initTag();
            expect(view.tag).toBeDefined();
        });
    });

    describe('applyAttribute', function() {
        beforeEach(function() {
            initColorPickerStub = sinonSandbox.stub(view, 'initColorPicker');

            mockchangeEvent = $.Event('change');
            mockchangeEvent.target = document.createElement('button');
            mockchangeEvent.target.value = 'date';
            view.changeDirective(mockchangeEvent);

            mockEvent = $.Event('change');
            mockEvent.target = document.createElement('input');
            mockEvent.target.type = 'text';
            mockEvent.target.setAttribute('value', 'dd-mm');
            mockEvent.target.name = 'format';

            view.initTag();
            view.tag.setName('date');
            view.render();
        });
        it('should apply the attribute to the current tag', function() {
            view.applyAttribute(mockEvent);
            expect(view.$('.preview').html()).toBe(view.tag.getTagValue());
        });
    });

    describe('toggleDateOption', function() {
        beforeEach(function() {
            initColorPickerStub = sinonSandbox.stub(view, 'initColorPicker');
            mockEvent = $.Event('change');
            mockEvent.currentTarget = document.createElement('checkbox');
            mockEvent.currentTarget.checked = true;
            view.render();

            mockchangeEvent = $.Event('change');
            mockchangeEvent.target = document.createElement('button');
            mockchangeEvent.target.value = 'date';
            view.changeDirective(mockchangeEvent);
        });
        it('should toggle custom dates', function() {
            view.toggleDateOption(mockEvent);
            expect(view.$el.find('.customDate')).not.toHaveClass('hide');
        });
    });
});
