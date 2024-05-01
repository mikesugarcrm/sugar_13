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
describe('View.Fields.Base.Meetings.EnumField', function() {
    var app, field, sandbox, createFieldProperties,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();
        createFieldProperties = {
            client: 'base',
            name: 'type',
            type: 'enum',
            viewName: 'edit',
            module: module,
            loadFromModule: true
        };
        sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                'foo': 'Foo',
                'bar': 'Bar',
                'baz': 'Baz'
            };
        });
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        app.view.reset();
    });

    it('should add selected value to list if in main app list string and it was not there initially', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo'
        };
        field.model.set('type', 'baz');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo',
            'baz': 'Baz'
        });
    });

    it('should not add selected value to list if not in main app list string', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo'
        };
        field.model.set('type', 'bap');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo'
        });
    });

    it('should not add additional value to list if it is already in the list', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo',
            'bar': 'Bar'
        };
        field.model.set('type', 'bar');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo',
            'bar': 'Bar'
        });
    });
});
