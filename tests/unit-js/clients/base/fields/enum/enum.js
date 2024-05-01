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
describe('enum field', function() {
    var app;
    var field;
    var fieldName = 'test_enum';
    var model;
    var module = 'Contacts';
    var options;
    var stubAppListStrings;

    beforeEach(function() {
        Handlebars.templates = {};
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'list');
        SugarTest.testMetadata.set();
        SugarTest.testMetadata._addDefinition(fieldName, 'fields', {
        }, module);

        SugarTest.app.data.declareModels();
        model = app.data.createBean(module);

        options = {'': '', 'Defect': 'DefectValue', 'Feature': 'FeatureValue'};
        stubAppListStrings = sinon.stub(app.lang, 'getAppListStrings').returns(options);

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
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        stubAppListStrings.restore();
        SugarTest.testMetadata.dispose();
    });

    it("should format a labeled select and have option selected on edit template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$('input').select2('data');
        expect(actual.id).toEqual(original);
        expect(actual.text).toEqual(expected);
    });

    it("should format a labeled string for detail template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "detail", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
        expect($.trim(actual)).toEqual(expected);
    });

    it("should call loadEnumOptions and set items during render", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual(app.lang.getAppListStrings());
        loadEnumSpy.restore();
    });

    it("should default the value of the field to the first option if undefined", function() {
        var field = SugarTest.createField('base', fieldName, 'enum', 'edit', {options: "bugs_type_dom"}, module, model);
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toEqual('first');
    });

    it("should not default the value of the field to the first option if multi select", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {isMultiSelect: true, options: "bugs_type_dom"});
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toBeUndefined();
    });

    describe('enum API', function() {
        it('should load options from enum API if options is undefined or null', function() {
            var callStub = sinon.stub(app.api, 'enumOptions').callsFake(function(module, field, callbacks) {
                expect(field).toEqual('test_enum');
                //Call success callback
                callbacks.success(app.lang.getAppListStrings());
                callbacks.complete();
            });
            field = SugarTest.createField('base', fieldName, 'enum', 'detail', {/* no options */});
            var renderSpy = sinon.spy(field, '_render');
            field.render();

            expect(callStub).toHaveBeenCalled();
            expect(renderSpy.calledTwice).toBe(true);
            expect(field.items).toEqual(app.lang.getAppListStrings());

            var field2 = SugarTest.createField('base', fieldName, 'enum', 'detail', {options: null}),
                renderSpy2 = sinon.spy(field2, '_render');
            field2.render();

            expect(callStub.calledTwice).toBe(true);
            expect(renderSpy2.calledTwice).toBe(true);
            expect(field2.items).toEqual(app.lang.getAppListStrings());

            callStub.restore();
            renderSpy.restore();
            renderSpy2.restore();
            field2.dispose();
        });
        it('should avoid duplicate enum api call', function() {
            var apiSpy = sinon.spy(app.api, 'enumOptions');
            var field = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, model);
            var field2 = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, model, field.context);
            var expected = {
                    aaa: 'bbb',
                    fake1: 'fvalue1',
                    fake2: 'fvalue2'
                };
            sinon.stub(field.model, 'setDefault');
            //setup fake REST end-point for enum
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', /.*rest\/v10\/Contacts\/enum\/test_enum.*/,
                [200, { 'Content-Type': 'application/json'}, JSON.stringify(expected)]);
            field.render();
            SugarTest.server.respond();
            field2.render();
            field.render();

            expect(apiSpy.calledOnce).toBe(true);
            //second field should be ignored, once first ajax called is being called
            expect(apiSpy.calledTwice).toBe(false);
            _.each(expected, function(value, key) {
                expect(field.items[key]).toBe(value);
                expect(field2.items[key]).toBe(value);
            });
            apiSpy.restore();
            field.dispose();
            field2.dispose();
        });

        it('should call the error callback', function() {
            var sandbox = sinon.createSandbox();
            var callback = sandbox.spy();
            var onError = sandbox.spy();
            var field = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, model);
            var error = {
                status: 403,
                code: 'not_authorized',
                message: 'You are not authorized to perform this action.'
            };

            sandbox.stub(app.api, 'enumOptions').callsFake(function(module, field, callbacks) {
                callbacks.error(error);
                callbacks.complete();
            });
            sandbox.stub(app.api, 'defaultErrorHandler');
            sandbox.stub(app.lang, 'get').withArgs('LBL_NO_DATA', module).returns('No Data');

            field.loadEnumOptions(true, callback, onError);

            expect(callback).toHaveBeenCalledOnce();
            expect(onError).toHaveBeenCalledWith(error);
            expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);
            expect(_.size(field.items)).toBe(1);
            expect(field.items['']).toBe('No Data');

            sandbox.restore();
            field.dispose();
        });
    });

    describe("getSelect2Options", function() {
        it("should allow separator to be configured via metadata", function(){
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, separator: '|', options: "bugs_type_dom"});
            var select2opts = field.getSelect2Options([]);
            expect(select2opts.separator).toEqual('|');
            expect(select2opts.multiple).toBe(true);
        });

        it('should allow multiselect to be configured via metadata', function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
                viewName: 'detail',
                fieldDef: {
                    container_class: 'my-container-class',
                    dropdown_class: 'my-dropdown-class',
                    dropdown_width: 50,
                    enum_width: 30,
                    isMultiSelect: true,
                    options: 'bugs_type_dom',
                    searchBarThreshold: 12,
                },
            });

            var select2opts = field.getSelect2Options([]);

            expect(select2opts.containerCssClass).toBe('my-container-class');
            expect(select2opts.dropdownCss).toEqual({width: 50});
            expect(select2opts.dropdownCssClass).toBe('my-dropdown-class');
            expect(select2opts.minimumResultsForSearch).toBe(12);
            expect(select2opts.multiple).toBe(true);
            expect(select2opts.width).toBe(30);
        });
    });

    describe("multi select enum", function() {

        it("should display a labeled comma list for detail template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should display a labeled comma list for list template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should format server's default value into a string array", function(){
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = "^Weird^";
            var expected = ["Weird"];
            var original2 = "^Very^,^Weird^";
            var expected2 = ["Very", "Weird"];
            expect(field.format(original)).toEqual(expected);
            expect(field.format(original2)).toEqual(expected2);
        });

        it("should format the model's value into a string array when model is updated", function() {
            var value = "^1^,^2^",
                actual,
                expected = ["1", "2"];
            field = SugarTest.createField("base", fieldName, "enum", "edit", {isMultiSelect: true});
            field.items = {
                '1': 'Foo',
                '2': 'Bar',
                '3': 'Baz'
            };
            field.render();
            field.model.set(field.name, value);
            actual = field.$('input').select2('val');
            expect(actual).toEqual(expected);
        });

        describe("blank value on multi select", function() {
            it('should transform the empty key on render', function() {
                field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
                field.render();
                expect(field.items['']).toBeUndefined();
            });
            it('should prevent focus otherwise the dropdown is opened and it\'s impossible to remove an item', function() {
                field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
                var jQueryStub = sinon.stub(field, '$');
                field.focus();
                expect(jQueryStub).not.toHaveBeenCalled();
                jQueryStub.restore();
            });
        });
    });

    describe('_sortResults', function() {
        var getAppListKeysStub, _sortBySpy, results, _order, getEditableDropdownFilterStub;

        var _expectOrder = function(results, order) {
            _.each(order, function(key, i) {
                expect(results[i].id).toEqual(key + '');
            });
        };

        beforeEach(function() {
            field = SugarTest.createField('base', fieldName, 'enum', 'edit');
            field.items = {};
            field.items['90'] = 90;
            field.items['100'] = 100;
            field.items['1'] = 'One';
            field.items[''] = '';
            field.items['Defect'] = 'DefectValue';
            field.items['Feature'] = 'FeatureValue';
            results = _.map(field.items, function(label, key) {
                return {id: key, text: label};
            });
            getAppListKeysStub = sinon.stub(app.lang, 'getAppListKeys').callsFake(function() {
                return _order;
            });
            _sortBySpy = sinon.spy(_, 'sortBy');
        });

        using('undefined `app_list_keys` or already filtered results',
            [
                [{}],
                [[90, 100, '1', '', 'Defect', 'Feature']]
            ],
            function(values) {

                it('should not sort the results', function() {
                    _order = false;

                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();

                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();
                });

                it('should not sort already filtered results', function() {
                    _order = ['', 'Defect', 100, 'Feature', 90];
                    field.items = values;
                    field.filteredOptions = true;
                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();
                });
            }
        );

        using('different order',
            [
                [['', 'Feature', 90, '1', 'Defect', 100]],
                [['', 'Defect', 100, '1', 'Feature', 90]]
            ],
            function(values) {

                it('should sort the results', function() {
                    _order = values;

                    results = field._sortResults(results);
                    _expectOrder(results, _order);
                    expect(_sortBySpy).toHaveBeenCalled();

                    results = field._sortResults(results);
                    _expectOrder(results, _order);
                    expect(_sortBySpy).toHaveBeenCalled();

                    expect(field._keysOrder).not.toEqual({});
                });
            });

        using('role-specific ordering or no-role',
            [
                {
                    order: [{'': '', 'Defect': 'DefectValue', '90': 90}],
                    expected: ['', 90, 'Defect'],
                    _keysOrder: {
                        '': 0,
                        90: 1,
                        'Defect': 2
                    }
                }
            ],
            function(values) {
                it('should sort the results', function() {
                    var expected = values.expected;
                    var ddFilterResult = [['', true], ['Feature', false], [90, true], ['1', false], ['Defect', true], [100, false]];
                    getEditableDropdownFilterStub = sinon.stub(app.metadata, 'getEditableDropdownFilter').returns(ddFilterResult);
                    field._keysOrder = values._keysOrder;
                    field.isFiltered = true;
                    _order = values.order;
                    results = _.map(_order[0], function(label, key) {
                        return {id: key, text: label};
                    });
                    results = field._sortResults(results);
                    _expectOrder(results, expected);
                    expect(_sortBySpy).toHaveBeenCalled();
                    expect(field._keysOrder).not.toEqual({});
                    getEditableDropdownFilterStub.restore();
                });
            });

        using('visibility grid',
            [
                {
                    order: [{'1': 'One', '90': '90', '': ''}],
                    expected: ['90', '', '1'],
                    targetField: 'testField',
                    targetValue: 'testValue',
                    visibility_grid: {
                        values: {}
                    },
                    modelValues: {
                        'testField': 'testValue'
                    }
                },
                {
                    order: [{'1': 'One', '90': '90', '': ''}],
                    expected: ['1', '90', ''],
                    targetField: '',
                    targetValue: 'testValue',
                    visibility_grid: {
                        values: {}
                    },
                    modelValues: {
                    }
                }
            ],
            function(provider) {
                it('should correctly order the values', function() {
                    var expected = provider.expected;
                    field.def.visibility_grid = provider.visibility_grid;
                    field.def.visibility_grid.trigger = provider.targetField;
                    field.def.visibility_grid.values[provider.targetValue] = provider.expected;

                    _.each(provider.modelValues, function(value, key) {
                        field.model.set(key, value);
                    });

                    _order = provider.order;
                    results = _.map(_order[0], function(label, key) {
                        return {id: key, text: label};
                    });
                    results = field._sortResults(results);
                    _expectOrder(results, expected);
                });
            });
    });

    describe('massupdate', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'massupdate');
            SugarTest.testMetadata.set();
        });

        describe('render', function() {
            it('should render with the appendValues checkbox only if it is multiselect', function() {
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: false, options: 'bugs_type_dom'});
                field.render();

                expect(field.$(field.appendValueTag)).not.toExist();

                field.dispose();
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: true, options: 'bugs_type_dom'});
                field.render();

                expect(field.$(field.appendValueTag)).toExist();
            });
        });

        describe('bindDomChange', function() {
            it('should update the model on append_value checkbox change when enum is multiselect', function() {
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: true, options: 'bugs_type_dom'});
                field.render();

                expect(field.appendValue).toBeUndefined();
                expect(field.model.get(fieldName + '_replace')).toBeUndefined();

                field.$(field.appendValueTag).prop('checked', false).trigger('change');

                expect(field.appendValue).toBeFalsy();
                expect(field.model.get(fieldName + '_replace')).toBe('0');

                field.$(field.appendValueTag).prop('checked', true).trigger('change');

                expect(field.appendValue).toBeTruthy();
                expect(field.model.get(fieldName + '_replace')).toBe('1');
            });
        });
    });

    describe('direction', function() {
        var oldDirection;

        beforeEach(function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
            });
            oldDirection = app.lang.direction;
        });

        afterEach(function() {
            app.lang.direction = oldDirection;
        });

        it('should be undefined if there are no items', function() {
            field.items = {};
            expect(field.direction()).toBeUndefined();
        });

        it('should be undefined if the app direction is ltr', function() {
            field.items =  {'I am': 'an item'};
            app.lang.direction = 'ltr';

            expect(field.direction()).toBeUndefined();
        });

        it('should be rtl if the first item is RTL and the app direction is already rtl', function() {
            field.items = {'RTL': 'I want to be displayed in RTL'};
            app.lang.direction = 'rtl';
            sinon.stub(app.utils, 'isDirectionRTL')
                .withArgs('I want to be displayed in RTL')
                .returns(true);

            expect(field.direction()).toEqual('rtl');
        });

        it('should be ltr otherwise', function() {
            // note, because this uses _.values on an Object, its results may be inconsistent if some items are meant
            // to be displayed in RTL and some are meant to be displayed in LTR. Hopefully this is not a situation
            // which comes up often
            field.items = {'LTR': 'I want to be displayed in LTR'};
            app.lang.direction = 'rtl';
            sinon.stub(app.utils, 'isDirectionRTL')
                .withArgs('I want to be displayed in LTR')
                .returns(false);

            expect(field.direction()).toEqual('ltr');
        });
    });

    describe('bindKeyDown', function() {
        var callback;
        var onStub;

        beforeEach(function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
            });
            callback = sinon.spy();
            onStub = sinon.stub();
        });

        it('should bind the given callback to keydown.record', function() {
            sinon.stub(field, '$')
                .withArgs(field.fieldTag)
                .returns({data: $.noop, on: onStub});

            field.bindKeyDown(callback);

            expect(onStub).toHaveBeenCalledWith('keydown.record', {field: field}, callback);
        });

        it('should bind events to the focusser and search plugins if they are available', function() {
            var focuserOnStub = sinon.stub();
            var searchOnStub = sinon.stub();
            var dataStub = sinon.stub().withArgs('select2').returns({
                focusser: {on: focuserOnStub},
                search: {on: searchOnStub}
            });
            sinon.stub(field, '$')
                .withArgs(field.fieldTag)
                .returns({
                    data: dataStub,
                    on: onStub,
                });

            field.bindKeyDown(callback);

            expect(focuserOnStub).toHaveBeenCalledWith('keydown.record', {field: field}, callback);
            expect(searchOnStub).toHaveBeenCalledWith('keydown.record', {field: field}, callback);
        });
    });

    describe('focus', function() {
        var select2Stub;

        beforeEach(function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
            });
            select2Stub = sinon.stub();
            sinon.stub(field, '$')
                .withArgs(field.fieldTag)
                .returns({select2: select2Stub});
        });

        using('different field properties',
        [
            {
                action: 'disabled',
                isMultiSelect: false,
            },
            {
                action: 'list',
                isMultiSelect: true,
            }
        ],
        function(data) {
            it('should not open the select2 on disabled or multiselect enums', function() {
                field.action = data.action;
                field.def.isMultiSelect = data.isMultiSelect;

                field.focus();

                expect(select2Stub).not.toHaveBeenCalled();
            });
        });

        it('should open the select2 on non-disabled, non-multiselect enums', function() {
            field.action = 'list';
            field.def.isMultiSelect = false;

            field.focus();

            expect(select2Stub).toHaveBeenCalledWith('open');
        });
    });

    describe('getLoadEnumOptionsModule', function() {
        it('should default to the enum_module', function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
                viewName: 'edit',
                fieldDef: {enum_module: 'Bugs', options: 'bugs_type_dom'},
                module: 'Cases',
            });

            expect(field.getLoadEnumOptionsModule()).toEqual('Bugs');
        });

        it('should return the field\'s module if there is no enum_module', function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
                viewName: 'edit',
                fieldDef: {options: 'bugs_type_dom'},
                module: 'Bugs',
            });

            expect(field.getLoadEnumOptionsModule()).toEqual('Bugs');
        });
    });

    describe('unformat', function() {
        describe('multiselect', function() {
            describe('array values', function() {
                it(
                    'should strip out invalid keys and preserve input order order when ordered is true in fieldDefs',
                    function() {
                        // it's a tad confusing, but "ordered" here really means "not sortable"
                        field = SugarTest.createField({
                            client: 'base',
                            name: fieldName,
                            type: 'enum',
                            viewName: 'list',
                            fieldDef: {isMultiSelect: true, options: 'bugs_type_dom', ordered: true}
                        });
                        field.items = options;

                        expect(field.unformat(['Feature', 'Not on the list', 'Defect'])).toEqual(['Feature', 'Defect']);
                    }
                );

                it(
                    'should strip out invalid keys and enforce key order when ordered is false in fieldDefs',
                    function() {
                        field = SugarTest.createField({
                            client: 'base',
                            name: fieldName,
                            type: 'enum',
                            viewName: 'list',
                            fieldDef: {isMultiSelect: true, options: 'bugs_type_dom', ordered: false},
                        });
                        field.items = options;

                        expect(field.unformat(['Feature', 'Not on the list', 'Defect'])).toEqual(['Defect', 'Feature']);
                    }
                );
            });

            it('should unformat nulls into server equivalent format of empty array', function() {
                // Backbone.js won't sync null values so server doesn't pick up on change and clear multi-select field
                field = SugarTest.createField({
                    client: 'base',
                    name: fieldName,
                    type: 'enum',
                    viewName: 'list',
                    fieldDef: {isMultiSelect: true, options: 'bugs_type_dom'}
                });
                var original = null;
                var expected = [];
                expect(field.unformat(original)).toEqual(expected);
            });
        });

        it('should just return the given value for non-multiselect enums', function() {
            field = SugarTest.createField({
                client: 'base',
                name: fieldName,
                type: 'enum',
            });

            expect(field.unformat('a value')).toEqual('a value');
        });
    });
});
