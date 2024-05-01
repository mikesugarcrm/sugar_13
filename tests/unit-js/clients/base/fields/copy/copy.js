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
describe('copy field', function() {

    describe('edit view', function() {
        var app;
        var model;
        var field;

        beforeEach(function() {

            app = SugarTest.app;

            model = new Backbone.Model({
                id: 'not-new',
                int: 1234567890,
                float: 1234567.89,
                name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.',
                subject: 'Eum quem êrror éligéndi ne, eum ex mundi détracto?',
                description: 'No nobis laboràmus nec, pri id tritáni indoçtum.\n' +
                    'Ením euripidis eu usu. Êt dicô legímus eos, pêr tantas desêruisse effiçíàntur ut.\n' +
                    'Quôdsi ópõrteãt seá eu, êt luçilíús gloriàtur vis. Usu vivêndô appêteré gubergren éx, usu illud mollis insólens çu, êt usú solêt volúptua.',
                address_street: '1 Foo Way',
                address_city: 'Castro Valley',
                address_state: 'CA',
                address_postalcode: '94546',
                address_country: 'USA'
            });

            var fieldDef = {
                mapping: {
                    'address_street': 'name',
                    'float': 'address_street',
                    'int': 'float',
                    'address_postalcode': 'int',
                    'subject': 'address_postalcode',
                    'description': 'subject'
                }
            };
            field = SugarTest.createField('base', 'copy_from_master', 'copy', 'edit', fieldDef, undefined, model);
            sinon.stub(field, '_loadTemplate').callsFake(function() {
                this.template = function() {
                    return '{{#if def.sync}}<label><input type="checkbox"{{#if value}} checked{{/if}}/>{{label}}</label>{{else}}<button type="button" class="btn">{{label}}</button>{{/if}}';
                };
            });
        });

        afterEach(function() {
            app.cache.cutAll();
            app.view.reset();
            Handlebars.templates = {};
            model = null;
            sinon.restore();
            field.dispose();
            field = null;
        });

        it('should initialize all the private properties correctly', function() {

            field._initialValues = 'initial values must be initialized';
            field._fields = 'fields must be initialized';

            field.initialize(field.options);

            expect(field._initialValues).toEqual({});
            expect(field._fields).toEqual({});
        });

        it('should be able to copy values from and to any type when checked', function() {

            var prev = model.clone();

            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });
            field.$('input[type=checkbox]').prop('checked', true).trigger('change');
            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(source));
            });
        });

        it('should make target fields disabled if exists', function() {

            var stub = sinon.stub(field, 'getField').callsFake(function() {
                return SugarTest.createField('base', 'dummy', 'base', 'edit');
            });

            var disabled = sinon.spy(field, 'setDisabled');

            field.$('input[type=checkbox]').prop('checked', true).trigger('change');
            expect(disabled.calledOnce);

            stub.restore();
            disabled.restore();
        });

        it('should be able to restore values after unchecked', function() {

            var prev = model.clone();

            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });
            field.$('input[type=checkbox]').prop('checked', true).trigger('change');
            field.$('input[type=checkbox]').prop('checked', false).trigger('change');
            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });
        });

        it('should be able to restore module changed values after unchecked', function() {

            var prev = model.clone();

            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });

            var name = 'Edited name to be restored';
            field.model.set('name', name);
            expect(field.model.get('name')).toEqual(name);

            field.$('input[type=checkbox]').prop('checked', true).trigger('change');
            field.$('input[type=checkbox]').prop('checked', false).trigger('change');

            _.each(field.def.mapping, function(target, source) {
                if (target === 'name') {
                    return;
                }
                expect(field.model.get(target)).toEqual(prev.get(target));
            });

            expect(field.model.get('name')).toEqual(name);
        });

        it('should have sync enabled by default', function() {
            expect(field.def.sync).toBeTruthy();
        });

        it('should be able to keep values in sync', function() {

            var prev = model.clone();

            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });

            field.$('input[type=checkbox]').prop('checked', true).trigger('change');

            var value = 'Edited float value to sync with `address_street` and `name` fields';
            field.model.set('float', value);
            expect(field.model.get('float')).toEqual(value);
            expect(field.model.get('address_street')).toEqual(value);
            expect(field.model.get('name')).toEqual(value);
        });

        it('should be able to copy values without keep it in sync', function() {

            var prev = model.clone();

            field.def.sync = false;
            expect(field.def.sync).toBeFalsy();

            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });

            field.$('button').trigger('click');
            var value = 'Edited float value to sync with `address_street` and `name` fields';
            field.model.set('float', value);
            expect(field.model.get('float')).toEqual(value);

            _.each(field.def.mapping, function(target, source) {
                if (target === 'float') {
                    return;
                }
                expect(field.model.get(target)).toEqual(prev.get(source));
            });
        });

        it('should use format as internal sync mode', function() {
            expect(field.format(true)).toEqual(true);
            expect(field.format(null)).toEqual(field._inSync);
        });

        it('should return null on unformat', function() {
            expect(field.unformat()).toEqual(null);
        });

        it('should be able to default to `false` when using sync setup', function() {

            field.dispose();

            var prev = model.clone();

            var fieldDef = {
                'default': false,
                mapping: {
                    'address_street': 'name',
                    'float': 'address_street',
                    'int': 'float',
                    'address_postalcode': 'int',
                    'subject': 'address_postalcode',
                    'description': 'subject'
                }
            };
            field = SugarTest.createField('base', 'copy_from_master', 'copy', 'edit', fieldDef, undefined, model);
            field.render();

            _.each(field.def.mapping, function(target, source) {
                expect(field.model.get(target)).toEqual(prev.get(target));
            });

            expect(field.$('input[type=checkbox]').prop('checked')).toBeFalsy();
        });

        describe('hasAccess', function() {

            afterEach(function() {
                sinon.restore();
            });

            it('should return true for case: Original value (read) + Target value (edit)', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return (action === 'read' || action === 'edit');
                });
                expect(field.hasAccess()).toBeTruthy();
            });

            it('should return false for case: Original value (non-read) + Target value (edit)', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return action === 'edit';
                });
                expect(field.hasAccess()).toBeFalsy();
            });

            it('should return false for case: Original value (read) + Target value (non-edit)', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return action === 'read';
                });
                expect(field.hasAccess()).toBeFalsy();
            });

            it('should return false for case: Original value (non-read) + Target value (non-edit)', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return !(action === 'read' || action === 'edit');
                });
                expect(field.hasAccess()).toBeFalsy();
            });

            it('should return false for case: there is at least one pair of fields with access', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return ((action === 'read' && field === 'description') ||
                        (action === 'edit' && field === 'subject'));
                });
                expect(field.hasAccess()).toBeTruthy();
            });

            it('should return false for case: fields are not mapped one to other', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model, field) {
                    return ((action === 'read' && field === 'description') ||
                        (action === 'edit' && field === 'address_street'));
                });
                expect(field.hasAccess()).toBeFalsy();
            });
        });
    });
});
