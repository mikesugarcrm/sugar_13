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
describe('Base.Field.Fullname', function() {
    var app, fieldDef;

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            'name': 'full_name',
            'type': 'fullname',
            'fields': [
                'first_name',
                'last_name',
                'salutation'
            ]
        };
        sinon.stub(app.user, 'getPreference')
            .withArgs('default_locale_name_format').returns('s f l');
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

        using('available formats', [{
            format: 'f s l',
            expected: ['first_name', 'salutation', 'last_name']
        },{
            format: 's f l',
            expected: ['salutation', 'first_name', 'last_name']
        },{
            format: 'f l',
            expected: ['first_name', 'last_name']
        },{
            format: 's l',
            expected: ['salutation', 'last_name']
        },{
            format: 'l, f',
            expected: ['last_name', 'first_name']
        },{
            format: 's l, f',
            expected: ['salutation', 'last_name', 'first_name']
        },{
            format: 'l s f',
            expected: ['last_name', 'salutation', 'first_name']
        },{
            format: 'l f s',
            expected: ['last_name', 'first_name', 'salutation']
        }], function(value) {
            it('Should sort the dependant fields in order of the user preference.', function() {
                app.user.getPreference.restore();
                sinon.stub(app.user, 'getPreference')
                    .withArgs('default_locale_name_format').returns(value.format);
                var field = SugarTest.createField('base', 'full_name', 'fullname', 'detail', fieldDef, 'Contacts');
                _.each(value.expected, function(name, index) {
                    expect(field.def.fields[index].name).toBe(name);
                });

                field.dispose();
            });
        });

        using('valid values',
            [
                {'hasLink' : true, 'id': 12345, 'href': '#Contacts/12345' },
                {'hasLink' : false, 'id': 12345, 'href': undefined}
            ], function(value) {
                it('should build this.href depending on the def.link property', function() {
                    fieldDef.link = value.hasLink;
                    var field = SugarTest.createField('base', 'full_name', 'fullname', 'detail', fieldDef, 'Contacts');
                    field.model.set('id', value.id);

                    field.initialize(field.options);
                    expect(field.href).toEqual(value.href);
                    field.dispose();
                });
            });

        it('should not build this.href if model does not have access to view record', function() {
            var field = SugarTest.createField('base', 'full_name', 'fullname', 'detail', fieldDef, 'Contacts');
            fieldDef.link = true;
            field.model.set('id', 12345);
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function() {
                return false;
            });

            field.initialize(field.options);
            expect(field.href).toBeUndefined();
            field.dispose();
        });
    });

    describe('render', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('list', 'view', 'base');
            SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'detail');
            SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'list');
            SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'edit');
            SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'record-detail');
            SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'recordlist-edit');
            SugarTest.testMetadata.set();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
        });

        it('should render with different templates', function() {
            var nameParts = {
                first_name: 'firstName',
                last_name: 'lastName',
                salutation: 'Mr.'
            };
            var fullName = nameParts.salutation + ' ' + nameParts.first_name + ' ' + nameParts.last_name;
            var model = new app.data.createBean('Contacts', {
                id: 'test-contact',
                full_name: fullName,
                first_name: nameParts.first_name,
                last_name: nameParts.last_name,
                salutation: nameParts.salutation
            });
            model.fields = {name: {fields: ['last_name', 'first_name', 'salutation']}};

            //record view detail
            var field = SugarTest.createField('base', 'full_name', 'fullname', 'detail', fieldDef, 'Contacts', model);
            var template = app.template.getField('fullname', 'detail', 'Contacts');
            field.render();
            expect(field.template(field)).toEqual(template(field));

            //record view edit
            template = app.template.getField('fullname', 'edit', 'Contacts');
            field.setMode('edit');
            expect(field.template(field)).toEqual(template(field));
            field.dispose();

            //list view detail
            field = SugarTest.createField('base', 'full_name', 'fullname', 'list', fieldDef, 'Contacts', model);
            template = app.template.getField('fullname', 'list', 'Contacts');
            field.render();
            expect(field.template(field)).toEqual(template(field));

            //list view edit
            field.view.name = 'recordlist';
            template = app.template.getField('fullname', 'recordlist-edit', 'Contacts');
            field.setMode('edit');
            expect(field.template(field)).toEqual(template(field));

            field.dispose();
        });
    });

    describe('bindDataChange', function() {
        it('should update the Full Name when First Name or Last Name changes', function() {
            var nameParts = {
                first_name: 'firstName',
                last_name: 'lastName',
                salutation: 'Mr.'
            };
            var fullName = nameParts.salutation + ' ' + nameParts.first_name + ' ' + nameParts.last_name;
            var model = new app.data.createBean('Contacts', {
                id: 'test-contact',
                full_name: fullName,
                first_name: nameParts.first_name,
                last_name: nameParts.last_name,
                salutation: nameParts.salutation
            });
            model.fields = {name: {fields: ['last_name', 'first_name', 'salutation']}};

            var field = SugarTest.createField('base', 'full_name', 'fullname', 'edit', fieldDef, 'Contacts', model);
            field.model.module = 'Contacts';
            field.render();

            expect(field.value).toBe('Mr. firstName lastName');

            field.model.set('first_name', 'FIRST');
            expect(field.model.get('full_name')).toBe('Mr. FIRST lastName');

            field.model.set('last_name', 'LAST');
            expect(field.model.get('full_name')).toBe('Mr. FIRST LAST');

            field.model.set('salutation', 'Dr.');
            expect(field.model.get('full_name')).toBe('Dr. FIRST LAST');

            field.setMode('detail');

            field.model.set('first_name', 'first');
            expect(field.model.get('full_name')).toBe('Dr. first LAST');

            field.dispose();
        });
    });
});
