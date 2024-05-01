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
describe('Sugar7 field extensions', function () {
    var app,
        field;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function () {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        if (field) {
            field.dispose();
        }
        field = null;
    });

    describe('fallback flow', function() {
        it('should fallback to the detail action if edit acl fails', function() {
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action) {
                return action !== 'edit';
            });
            field = SugarTest.createField('base', 'name', 'base', 'edit');
            field._loadTemplate();
            expect(field.action).toBe('detail');
        });

        it('should fallback to the noaccess if detail acl is failed and action is erased', function() {
            field = SugarTest.createField('base', 'account_name', 'text', 'erased');
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action) {
                return !_.contains(['edit', 'detail', 'list', 'admin'], action);
            });
            field._loadTemplate();
            expect(field.action).toBe('noaccess');
        });

        it('should fallback to the noaccess if all acl is failed', function() {
            field = SugarTest.createField('base', 'name', 'base', 'edit');
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action) {
                return !_.contains(['edit', 'detail', 'list', 'admin'], action);
            });
            sinon.stub(field, 'showNoData').callsFake(function() {
                return false;
            });
            field._loadTemplate();
            expect(field.action).toBe('noaccess');
        });

        it('should fallback to the noaccess if list acl fails', function() {
            field = SugarTest.createField('base', 'name', 'base', 'list');
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(action) {
                return !_.contains(['edit', 'detail', 'list', 'admin'], action);
            });
            sinon.stub(field, 'showNoData').callsFake(function() {
                return false;
            });
            field._loadTemplate();
            expect(field.action).toBe('noaccess');
        });

        it('must fallback to the nodata once showNoData is true', function() {
            field = SugarTest.createField('base', 'name', 'base', 'edit');
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function() {
                return true;
            });
            sinon.stub(field, 'showNoData').callsFake(function() {
                return true;
            });
            field._loadTemplate();
            expect(field.action).toBe('nodata');
        });
    });

    describe('nodata', function() {
        it('should show nodata if field is readonly and has no data', function() {
            field = SugarTest.createField('base', 'name', 'base', 'detail', {readonly: true});
            field.model = new Backbone.Model({_module: 'Accounts'});
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(true);
        });

        it('should not show nodata if the field is readonly, user does not have read access and has no data', function() {
            field = SugarTest.createField('base', 'name', 'base', 'detail', {readonly: true});
            field.model = new Backbone.Model({_module: 'Accounts', _acl: {fields: {name: { read: 'no'}}}});
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });

        it('should not show nodata if not readonly', function() {
            field = SugarTest.createField('base', 'name', 'base', 'detail', {readonly: false});
            field.model = new Backbone.Model({_module: 'Accounts'});
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });

        it('should not show nodata if readonly but fields have data', function() {
            var field = SugarTest.createField('base', 'name', 'base', 'detail', {readonly: true});
            field.model = new Backbone.Model();
            field.model.set('name', 'test');
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });

        it('should not show nodata if readonly, user does not have read access and has data', function() {
            var field = SugarTest.createField('base', 'name', 'base', 'detail', {readonly: true});
            field.model = new Backbone.Model({_module: 'Accounts', _acl: {fields: {name: { read: 'no'}}}});
            field.model.set('name', 'test');
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });
    });

    describe('decorating required fields', function () {

        it("should call decorateRequired only on required fields on edit mode", function () {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var spy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(spy.called).toBe(true);
            spy.resetHistory();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(spy.called).toBe(false);
            spy.resetHistory();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "detail", {required: true});
            field.render();
            expect(spy.called).toBe(false);
            spy.restore();
        });

        it("should call clearRequiredLabel prior to calling decorateRequired on a field", function () {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var clearSpy = sinon.spy(field, 'clearRequiredLabel');
            var reqSpy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(clearSpy.called).toBe(true);
            expect(reqSpy.called).toBe(true);
            expect(clearSpy.calledBefore(reqSpy)).toBe(true);

            clearSpy.restore();
            reqSpy.restore();
        });

        it("should allow a way to opt-out of calling decorateRequired so Required placeholder", function () {
            field = SugarTest.createField("base", "text", "base", "edit", {required: true});
            field.def.no_required_placeholder = true;
            var should = field._shouldRenderRequiredPlaceholder();
            expect(should).toBeFalsy();
            field.def.no_required_placeholder = undefined;
            should = field._shouldRenderRequiredPlaceholder();
            expect(should).toBeTruthy();
        });
    });

    describe('Edit mode css class', function () {
        var editClass = 'edit';
        var detailClass = 'detail';

        it('should render in detail mode without the edit class', function () {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });

        it('should render in edit mode with edit class', function () {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should add the edit class when toggled to edit mode', function () {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();

            field.setMode('edit');
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should remove the edit class when toggled from edit to detail mode', function () {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();

            field.setMode('detail');
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });

        describe('Disabled', function () {
            it('has both detail and disabled classes on set disabled', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
            });

            it('has both edit and disabled classes on mode change from detail to edit', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                field.setMode('edit');
                expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
                expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
            });

            it('loses the disabled class when re-enabled', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                field.setDisabled(false);
                expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
            });
        });
    });

    describe('Test _getFallbackTemplate method', function () {
        it('should return noaccess as name if viewName is noaccess', function() {
            field = SugarTest.createField('base', 'text', 'base', 'list', {});
            expect(field._getFallbackTemplate('noaccess')).toEqual('noaccess');
        });
    });

    describe('Erased field', function() {
        describe('by default', function() {
            beforeEach(function() {
                field = SugarTest.createField('base', 'phone_work', 'base', 'detail');
            });

            it('should change the field action to erased', function() {
                field.model.set('_erased_fields', ['phone_work']);
                field._setErasedFieldAction();
                expect(field.action).toEqual('erased');
            });

            it('should keep the field action unchanged', function() {
                field.model.set('_erased_fields', ['phone_home']);
                field.render();
                expect(field.action).toEqual('detail');
            });
        });

        it('should show related field as erased', function() {
            var fieldDef = {
                rname: 'name',
                link: 'accounts',
                module: 'Accounts'
            };

            var model = app.data.createBean('Contacts');
            model.set('accounts', {'_erased_fields': ['name']});
            field = SugarTest.createField('base', 'account_name', 'relate', 'list', fieldDef, 'Contacts', model);

            field._setErasedFieldAction();
            expect(field.action).toEqual('erased');
        });

        it('should mark a field with no license access', function() {
            var def = {
                'name': 'business_center_name',
                'type': 'relate',
                'module': 'Contacts',
            };
            var acls = {
                fields: {
                    'business_center_name': {
                        'license': 'no'
                    }
                }
            };

            var model = app.data.createBean('Contacts');
            model.set('_acl', acls);
            field = SugarTest.createField('base', 'business_center_name', 'relate', 'list', def, 'Contacts', model);
            field._markFieldLackingAccess();
            expect(field.action).toEqual('nolicense');
        });
    });

    describe('isFieldEmpty', function() {
        using('different model values', [
            {value: '', expected: true},
            {value: 'hello', expected: false},
            {value: [], expected: true},
            {value: ['hello'], expected: false},
            {value: true, expected: false},
            {value: false, expected: false},
            {value: 0, expected: false},
            {value: 1, expected: false},
            {value: 1.1, expected: false},
            {value: undefined, expected: true},
            {value: null, expected: true},
            {value: {}, expected: false},
            {value: {a: 'b'}, expected: false},

        ], function(provider) {
            it('should correctly identify empty fields', function() {
                field = SugarTest.createField('base', 'name', 'base', 'detail');
                field.model.set('name', provider.value);
                expect(field.isFieldEmpty()).toBe(provider.expected);
            });
        });

        it('should be empty if there is no model attribute', function() {
            field = SugarTest.createField('base', 'noAttribute', 'base', 'detail');
            field.name = 'noAttribute';
            expect(field.isFieldEmpty()).toBeTruthy();
        });
    });
});
