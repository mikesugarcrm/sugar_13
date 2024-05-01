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
describe('View.Fields.Base.BadgeSelectField', function() {
    var app, field, items;

    items = {
        success: 'Success',
        important: 'Important',
        foo: 'Foo Moo'
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'list');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'badge-select');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('when the status field is in edit mode', function() {
        it('should be an enum', function() {
            field = SugarTest.createField('base', 'direction', 'badge-select', 'edit');
            field.items = items;
            field.action = 'edit';
            field.render();
            expect(field.$('input.select2').length).toBe(1);
        });
    });

    describe('when the status field is in detail mode', function() {
        var checkClasses = function(plain, success, important) {
            expect(field.$('.label').length).toBe(plain);
            expect(field.$('.label-success').length).toBe(success);
            expect(field.$('.label-important').length).toBe(important);
        };

        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'badge-select', 'detail');
            field.items = items;
        });

        using('detail modes', ['detail', 'list'], function(mode) {
            it('should be a bootstrap label', function() {
                field.action = mode;
                field.model.set('status', 'foo');
                field.render();
                checkClasses(1, 0, 0);
            });
        });

        it('should be a success bootstrap label when the status is success', function() {
            field.model.set('status', 'success');
            field.render();
            checkClasses(1, 1, 0);
        });

        it('should be an important bootstrap label when the status is important', function() {
            field.model.set('status', 'important');
            field.render();
            checkClasses(1, 0, 1);
        });
    });

});
