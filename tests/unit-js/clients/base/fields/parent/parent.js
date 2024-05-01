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
describe('Base.Field.Parent', function() {

    var app;
    var field;
    var parentRecord;
    var fieldDef;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        fieldDef = {
            "name": "parent_name",
            "rname": "name",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
            "link": "parent",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };

        SugarTest.loadComponent("base", "field", "relate");
        field = SugarTest.createField("base","parent_name", "parent", "edit", fieldDef);
        parentRecord = {
            type: 'Contacts',
            id: '111-222-33333',
            name: 'blob'
        }

        field.model = app.data.createBean('Tasks', {
            parent: parentRecord,
            parent_type: 'Contacts',
            parent_name: 'blob',
            parent_id: '111-222-33333'
        });

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }
    });
    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
    });

    it('should not set value when id is undefined', function() {
        var expected_module = 'Accounts';

        field.model.clear();
        field.setValue({id: undefined, value: undefined, module: expected_module});
        var actual_id = field.model.get('parent_id'),
            actual_name = field.model.get('parent_name'),
            actual_module = field.model.get('parent_type');
        expect(actual_id).toBeUndefined();
        expect(actual_name).toBeUndefined();
        expect(actual_module).toEqual(expected_module);
    });

    it("should set value correctly", function() {
        var expected_id = '0987',
            expected_name = 'blahblah',
            expected_module = 'Accounts';

        field.setValue({id: expected_id, value: expected_name, module: expected_module});
        var actual_id = field.model.get('parent_id'),
            actual_name = field.model.get('parent_name'),
            actual_module = field.model.get('parent_type');
        expect(actual_id).toEqual(expected_id);
        expect(actual_name).toEqual(expected_name);
        expect(actual_module).toEqual(expected_module);
    });
    it("should get related module for parent", function() {
        var actual_id = parentRecord.id;
        var actual_module = parentRecord.type;
        var _relatedModuleSpy = sinon.spy(field, 'getSearchModule');
        var _relateIdSpy = sinon.spy(field, '_getRelateId');

        field.format();
        expect(_relatedModuleSpy).toHaveBeenCalled();
        expect(_relateIdSpy).toHaveBeenCalled();
        expect(field.href).toEqual("#"+actual_module+"/"+actual_id);
    });
    it('should call _createListViewAvatar when present on list view', function() {
        field = SugarTest.createField('base','parent_name', 'parent', 'list', fieldDef);
        var _createListViewAvatar = sinon.stub(field, '_createListViewAvatar');
        sinon.stub(field.model, 'get').callsFake(function() {
            return 1;
        });
        field.render();
        expect(_createListViewAvatar).toHaveBeenCalled();
    });

    using('remove or not removing field\'s parent model',
        [
            {
                removeParent: true,
                labelText: 'Label Text',
                empty: false,
                expected: {field: 'parent_name', label: 'Label Text'}
            },
            {
                removeParent: false,
                labelText: 'Label Text',
                empty: true,
                expected: {field: 'parent_name', label: 'Label Text'}
            },
            {
                removeParent: true,
                labelText: 'Label Text',
                empty: true,
                expected: {field: 'parent_name', label: 'Label Text'}
            },
            {
                removeParent: false,
                labelText: 'Label Text',
                empty: false,
                expected: {field: 'parent_name', label: 'Contacts'}
            },
        ],
        function(values) {
            it('should set label appropriately with or without a parent module',
                function() {
                    field.tplName = 'detail';
                    if (values.removeParent) {
                        sinon.stub(field, 'getSearchModule').callsFake(function() {
                            return undefined;
                        });
                    }
                    sinon.stub(app.lang, 'get').callsFake(function() {
                        return values.labelText;
                    });
                    sinon.stub(field, 'isFieldEmpty').callsFake(function() {
                        return values.empty;
                    });
                    field.format();
                    expect(field.context.get('record_label')).toEqual(values.expected);
                });
        });

    describe('isAvailableParentType', function () {
        it('should return true if the specified module is an option on the field', function () {
            field.typeFieldTag = 'select';
            field.$el.html('<select><option value="Accounts">Account</option></select>');
            expect(field.isAvailableParentType('Accounts')).toBe(true);
        });
        it('should return false if the specified module is not an option on the field', function () {
            field.typeFieldTag = 'select';
            field.$el.html('<select><option value="Accounts">Account</option></select>');
            expect(field.isAvailableParentType('Contacts')).toBe(false);
        });
    });
});
