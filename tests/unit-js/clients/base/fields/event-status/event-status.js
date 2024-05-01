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
describe('View.Fields.Base.EventStatusField', function() {
    var app, field, items, module;

    module = 'Meetings';
    items = {
        Planned: 'Scheduled',
        Held: 'Held',
        'Not Held': 'Canceled',
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
        SugarTest.loadComponent('base', 'field', 'event-status');
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
        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'event-status', 'edit', undefined, module);
            field.items = items;
        });

        it('should be an enum', function() {
            field.action = 'edit';
            field.render();
            expect(field.$('input.select2').length).toBe(1);
        });
    });
});
