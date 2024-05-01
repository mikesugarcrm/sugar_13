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
describe('Base.Field.Actionbutton', function() {
    var app;
    var fieldDef;
    var field;
    var fixture;

    beforeEach(function() {
        app = SugarTest.app;

        fixture = SugarTest.loadFixture('actionbutton');

        fieldDef = fixture.encoded;
    });

    afterEach(function() {
        sinon.restore();
        Handlebars.templates = {};
    });

    describe('initialize', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
        });

        it('should properly decode the database definition', function() {
            field = SugarTest.createField('base', 'test_c', 'actionbutton', 'record', fieldDef, 'Accounts');

            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';
            field.initialize({
                def: fieldDef, view: field.view
            });

            expect(field.actionMeta).toEqual(fixture.decoded);
        });
    });

    describe('render', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'button-loading');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'button');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'detail-button-group');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'detail-button');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'detail-dropdown');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'detail');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'edit');
            SugarTest.loadHandlebarsTemplate('actionbutton', 'field', 'base', 'list');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'test_c', 'actionbutton', 'record', fieldDef, 'Accounts');

            // createField() does not set the view type on itself through the class constructor
            field.view.type = 'record';
            field.initialize({
                def: fieldDef, view: field.view
            });
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            field = null;
        });

        it('should properly render buttons as individual buttons', function() {
            field.render();

            var $buttons = field.$el.find('.actionbuttons.actionbuttons-button');
            expect($buttons.length).toEqual(4);
            expect($buttons.eq(0).attr('title')).toEqual('Create Tooltip');;
            expect($buttons.eq(0).html().indexOf('<i class="sicon sicon-plus">') >= 0).toEqual(true);
            expect($buttons.eq(0).text().trim()).toEqual('Create');

            expect($buttons.eq(1).attr('title')).toEqual('Assign Tooltip');;
            expect($buttons.eq(1).html().indexOf('<i class="sicon sicon-user">') >= 0).toEqual(true);
            expect($buttons.eq(1).text().trim()).toEqual('Assign');

            expect($buttons.eq(2).attr('title')).toEqual('Open Url Tooltip');;
            expect($buttons.eq(2).html().indexOf('<i class="sicon sicon-email">') >= 0).toEqual(true);
            expect($buttons.eq(2).text().trim()).toEqual('Open Url');

            expect($buttons.eq(3).attr('title')).toEqual('Update Tooltip');;
            expect($buttons.eq(3).html().indexOf('<i class="sicon sicon-settings">') >= 0).toEqual(true);
            expect($buttons.eq(3).text().trim()).toEqual('Update');
        });

        it('should properly render buttons as button group', function() {
            field.actionMeta.settings.type = 'buttonGroup';

            field.render();
            var $buttons = field.$el.find('.actionbuttons.actionbuttons-buttonGroup');
            expect($buttons.length).toEqual(4);
            expect($buttons.eq(0).attr('title')).toEqual('Create Tooltip');;
            expect($buttons.eq(0).html().indexOf('<i class="sicon sicon-plus">') >= 0).toEqual(true);
            expect($buttons.eq(0).text().trim()).toEqual('Create');

            expect($buttons.eq(1).attr('title')).toEqual('Assign Tooltip');;
            expect($buttons.eq(1).html().indexOf('<i class="sicon sicon-user">') >= 0).toEqual(true);
            expect($buttons.eq(1).text().trim()).toEqual('Assign');

            expect($buttons.eq(2).attr('title')).toEqual('Open Url Tooltip');;
            expect($buttons.eq(2).html().indexOf('<i class="sicon sicon-email">') >= 0).toEqual(true);
            expect($buttons.eq(2).text().trim()).toEqual('Open Url');

            expect($buttons.eq(3).attr('title')).toEqual('Update Tooltip');;
            expect($buttons.eq(3).html().indexOf('<i class="sicon sicon-settings">') >= 0).toEqual(true);
            expect($buttons.eq(3).text().trim()).toEqual('Update');
        });

        it('should properly render buttons as button dropdown', function() {
            field.actionMeta.settings.type = 'dropdown';

            field.render();
            var $primaryButton = field.$el.find('.actionbuttons.actionbuttons-dropdown');
            var $dropdown = field.$el.find('.actionbtn-toggle');
            var $buttons = field.$el.find('li a.actionbuttons');

            expect($primaryButton.length).toEqual(1);
            expect($primaryButton.attr('title')).toEqual('Create Tooltip');;
            expect($primaryButton.html().indexOf('<i class="sicon sicon-plus">') >= 0).toEqual(true);
            expect($primaryButton.text().trim()).toEqual('Create');

            expect($dropdown.html().indexOf('<span class="sicon sicon-caret-down">') >= 0).toEqual(true);

            expect($buttons.eq(0).html().indexOf('<i class="sicon sicon-user">') >= 0).toEqual(true);
            expect($buttons.eq(0).text().trim()).toEqual('Assign');

            expect($buttons.eq(1).html().indexOf('<i class="sicon sicon-email">') >= 0).toEqual(true);
            expect($buttons.eq(1).text().trim()).toEqual('Open Url');

            expect($buttons.eq(2).html().indexOf('<i class="sicon sicon-settings">') >= 0).toEqual(true);
            expect($buttons.eq(2).text().trim()).toEqual('Update');
        });
    });
});
