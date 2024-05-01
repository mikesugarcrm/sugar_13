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
describe('DocumentMerges.View.TagBuilderOptions', function() {
    var app;
    var sinonSandbox;
    var view;
    var mockEvent;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-options', 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-options', 'view', 'base', null, 'DocumentMerges');
        SugarTest.loadHandlebarsTemplate('tag-builder-options', 'view', 'base', 'collection', 'DocumentMerges');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-options', null, null, true);
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
            hideCustomOptionsStub = sinonSandbox.stub(view, 'hideCustomOptions');
        });
        it('should initialize dropdowns and hide options', function() {
            view.render();
            expect(initializeDropDownsStub).toHaveBeenCalled();
            expect(hideCustomOptionsStub).toHaveBeenCalled();
        });
    });

    describe('showOptions', function() {
        it('should initialize options for displaying', function() {
            view.showOptions({name: 'account_name', type: 'relate', module: 'Contacts'});
            expect(view.type).toBe('relate');
            expect(view.relateModule).toBe('Contacts');
        });
    });

    describe('initTag', function() {
        it('should initialize a tag', function() {
            view.type = 'collection';
            view.initTag();
            expect(view.tag).toBeDefined();
        });
    });

    describe('applyAttribute', function() {
        beforeEach(function() {
            mockEvent = $.Event('change');
            mockEvent.target = document.createElement('input');
            mockEvent.target.type = 'text';
            mockEvent.target.setAttribute('value', '23');
            mockEvent.target.name = 'max_num';

            sinon.stub(view, 'createCopyTable');

            view.type = 'collection';
            view.initTag();
            view.tag.setName('calls');
            view.render();
        });
        it('should apply the attribute to the current tag', function() {
            view.applyAttribute(mockEvent);
            expect(view.$('.preview').html()).toBe(view.tag.getTagValue());
        });
    });
});
