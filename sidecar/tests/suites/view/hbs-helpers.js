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

const Bean = require('../../../src/data/bean');
const HbsHelpers = require('../../../src/view/hbs-helpers');
const User = require('../../../src/core/user');
const Context = require('../../../src/core/context');
const Language = require('../../../src/core/language');
const Template = require('../../../src/view/template');
const View = require('../../../src/view/view');

describe('View/HbsHelpers', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata(true);
    });

    afterEach(function() {
        sinon.restore();
    });

    // TODO: Create test for each helper

    describe('fieldValue', function() {
        it('should return value for an existing field', function() {
            var bean = new Bean({foo: 'bar'});
            expect(HbsHelpers.fieldValue(bean, 'foo')).toEqual('bar');
        });

        it('should return empty string for a non-existing field', function() {
            var bean = new Bean();
            expect(HbsHelpers.fieldValue(bean, 'foo', {hash: {}})).toEqual('');
        });

        it('should return default string for a non-existing field', function() {
            var bean = new Bean();
            expect(HbsHelpers.fieldValue(bean, 'foo', {hash: {defaultValue: 'bar'}})).toEqual('bar');
        });

    });

    describe('field', function() {
        it('should return a sugarfield span element', function() {
            var model = new Bean();
            var context = new Context({
                    module: 'Cases'
                });
            var view = new View({name: 'detail', context: context});
            var def = {name: 'TestName', label: 'TestLabel', type: 'text'};

            var fieldId = app.view.getFieldId();
            var result = HbsHelpers.field.call(def, view, {
                hash : {
                    model: model
                }
            });
            expect(result.toString()).toMatch(/<span sfuuid=.*(\d+).*/);
            expect(app.view.getFieldId()).toEqual(fieldId + 1);
            expect(view.fields[fieldId + 1]).toBeDefined();
        });

        it('should customize the view type', function() {
            var model = new Bean();
            var context = new Context({
                    module: 'Cases'
                });
            var view = new View({ name: 'detail', context: context});
            var def = {name: 'TestName', label: 'TestLabel', type: 'text'};
            var viewType = 'custom_view_name';

            var fieldId = app.view.getFieldId();
            var result = HbsHelpers.field.call(def, view, {
                hash: {
                    model: model,
                    template: viewType
                }
            });
            expect(app.view.getFieldId()).toEqual(fieldId + 1);
            expect(view.fields[fieldId + 1].options.viewName).toEqual(viewType);
        });

        it('should add the child field to the parent\'s field list', function() {
            var model = new Backbone.Model({id:23456});
            var context = new Context();

            var def = {name: 'ParentName', label: 'TestParent', type: 'base'};

            var view = SugarTest.createComponent('View', {
                type: 'detail',
                context: context
            });

            var field = SugarTest.createComponent('Field', {
                def: def,
                view: view,
                context: context,
                model: model
            });

            field = _.extend(field, {
                fields: []
            });

            var result = HbsHelpers.field.call(def, view, {
                hash: {
                    model: model,
                    template: 'custom_view_name',
                    parent: field
                }
            });

            expect(field.fields.length).toBe(1);
        });
    });

    describe('buildRoute', function() {
        var routerMock, model, context, module;

        beforeEach(function() {
            app.router = app.router || {};
            app.router.buildRoute = app.router.buildRoute || function() {};
            routerMock = sinon.mock(app.router);

            model = new Bean();
            model.set('id', '123');
            module = 'Cases';
            context = new Context({
                module: module
            });
        });

        afterEach(function() {
            routerMock.restore();
        });

        it('should call app.router.buildRoute with the appropriate inputs for create route', function() {
            var action = 'create';
            var expectedId = model.id;

            routerMock.expects('buildRoute').once().withArgs(module, expectedId, action);
            HbsHelpers.buildRoute({hash: {context: context, model: model, action: action}});
            expect(routerMock.verify()).toBeTruthy();
        });

        it('should call app.router.buildRoute with the appropriate inputs for non-create route', function() {
            var action = '';
            var expectedId = model.id;

            routerMock.expects('buildRoute').once().withArgs(module, expectedId, action);
            HbsHelpers.buildRoute({hash: {context: context, model: model, action: action}});
            expect(routerMock.verify()).toBeTruthy();
        });
    });

    describe('has', function() {
        it('should return the true value if the first value is found in the second value (array)', function() {
            var val1 = 'hello';
            var val2 = ['world', 'fizz', 'hello', 'buzz'];
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.has(val1, val2, options)).toEqual(returnTrue);
        });

        it('should return the true value if the first value is not found in the second value (array)', function() {
            var val1 = 'good bye';
            var val2 = ['world', 'fizz', 'hello', 'buzz'];
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.notHas(val1, val2, options)).toEqual(returnTrue);
        });

        it('should return the false value if the first value is found in the second value (array)', function() {
            var val1 = 'hello';
            var val2 = ['world', 'fizz', 'sidecar', 'buzz'];
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.has(val1, val2, options)).toEqual(returnFalse);
        });

        it('should return the true value if the first value is found in the second value (scalar)', function() {
            var val1 = 'hello';
            var val2 = 'hello';
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.has(val1, val2, options)).toEqual(returnTrue);
        });
    });

    describe('eachOptions', function() {
        it('should pull options hash from app list strings and return an iterated block string', function() {
            var optionName = 'custom_fields_importable_dom';
            var blockHtml = "<li>{{this.key}} {{this.value}}</li>";
            var template = Handlebars.compile(blockHtml);

            app.metadata.set(fixtures.metadata);
            expect(HbsHelpers.eachOptions(optionName, {fn: template})).toEqual("<li>true Yes</li><li>false No</li><li>required Required</li>");
        });

        it('should pull options array from app list strings and return an iterated block string', function() {
            var optionName = 'custom_fields_merge_dup_dom';
            var blockHtml = "<li>{{value}}</li>";
            var template = Handlebars.compile(blockHtml);

            expect(HbsHelpers.eachOptions(optionName, {fn: template})).toEqual("<li>Disabled</li><li>Enabled</li><li>In Filter</li><li>Default Selected Filter</li><li>Filter Only</li>");
        });

        it('should return an iterated block string for an object', function() {
            var options = {'Disabled': 0, 'Enabled': 1};
            var blockHtml = "<li>{{this.key}} {{this.value}}</li>";
            var template = Handlebars.compile(blockHtml);

            expect(HbsHelpers.eachOptions(options, {fn: template})).toEqual("<li>Disabled 0</li><li>Enabled 1</li>");
        });

        it('should return an iterated block string for an array', function() {
            var options = ['Disabled', 'Enabled'];
            var blockHtml = "<li>{{value}}</li>";
            var template = Handlebars.compile(blockHtml);

            expect(HbsHelpers.eachOptions(options, {fn: template})).toEqual("<li>Disabled</li><li>Enabled</li>");
        });

    });

    describe('eq', function() {
        it('should return the true value if conditional evaluates true', function() {
            var val1 = 1;
            var val2 = 1;
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.eq(val1, val2, options)).toEqual(returnTrue);
        });

        it('should return the false value if conditional evaluates false', function() {
            var val1 = 1;
            var val2 = 2;
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.eq(val1, val2, options)).toEqual(returnFalse);
        });
    });

    describe('notEq', function() {
        it('should return the false value if conditional evaluates true', function() {
            var val1 = 1;
            var val2 = 1;
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.notEq(val1, val2, options)).toEqual(returnFalse);
        });

        it('should return the true value if conditional evaluates false', function() {
            var val1 = 1;
            var val2 = 2;
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.notEq(val1, val2, options)).toEqual(returnTrue);
        });
    });

    describe('notMatch', function() {
        it('should return inverse of regex evaluation', function() {
            var val1 = 'foo-is-not-greedy';
            var nonGreedy = "^foo$";
            var greedy = 'foo';
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.notMatch(val1, nonGreedy, options)).toEqual(returnTrue);
            expect(HbsHelpers.notMatch(val1, greedy, options)).toEqual(returnFalse);
        });
    });

    describe('match', function() {
        it('should return result of regex evaluation', function() {
            var val1 = 'foo-is-not-greedy';
            var nonGreedy = "^foo$";
            var greedy = 'foo';
            var returnTrue = 'Success!';
            var returnFalse = 'Failure!';
            var options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(HbsHelpers.match(val1, nonGreedy, options)).toEqual(returnFalse);
            expect(HbsHelpers.match(val1, greedy, options)).toEqual(returnTrue);
        });
    });

    describe('isSortable', function() {
        it('should return block if isSortable is true in field viewdef', function() {
            var returnVal = 'Yup';
            var block = function() { return returnVal; };
            var module = 'Cases';
            var fieldViewdef = {
                name: 'text',
                sortable: true,
            };
            var getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {
                        text: {
                            sortable: false,
                        },
                    },
                };
            });
            expect(HbsHelpers.isSortable(module, fieldViewdef, { fn: block })).toEqual(returnVal);
            getModuleStub.restore();
        });

        it('should not return block if isSortable is false in field viewdef but true in vardef', function() {
            var returnVal = 'Yup';
            var block = function() { return returnVal; };
            var module = 'Cases';
            var fieldViewdef = {
                name: 'text',
                sortable: false,
            };
            var getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {
                        text: {
                            sortable: true,
                        },
                    },
                };
            });
            expect(HbsHelpers.isSortable(module, fieldViewdef, { fn: block })).not.toEqual(returnVal);
            getModuleStub.restore();
        });

        it('should return block if isSortable not defined in either field viewdef or vardef', function() {
            var returnVal = 'Yup';
            var block = function() { return returnVal; };
            var module = 'Cases';
            var fieldViewdef = {
                name: 'text',
            };
            var getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {
                        text: {},
                    },
                };
            });
            expect(HbsHelpers.isSortable(module, fieldViewdef, { fn: block })).toEqual(returnVal);
            getModuleStub.restore();
        });
    });

    describe('str', function() {
        it('should get a string from language bundle', function() {
            var lang = Language;
            app.metadata.set(fixtures.metadata);
            expect(HbsHelpers.str('LBL_ASSIGNED_TO_NAME', 'Contacts')).toEqual('Assigned to');
        });
    });

    describe('relativeTime', function() {
        it('should return an HTML time element with a datetime attribute and specified title attribute', function() {
            var time = '2017-03-14T00:14:35.934Z';
            var htmlString = HbsHelpers.relativeTime(time, {
                hash: {
                    title: 'My title',
                },
            }).string;

            if (_.isFunction($.parseHTML)) {
                var element = $.parseHTML(htmlString)[0];
                expect(element.tagName.toUpperCase()).toEqual('TIME');
                expect(element.title).toEqual('My title');
                // not checking the actual date value to avoid timezone issues
                expect(element.attributes.getNamedItem('datetime').value).toBeTruthy();
            } else {
                // Zepto doesn't have the $.parseHTML function
                expect(htmlString).toMatch(/<time datetime=".*<\/time>/);
            }
        });
    });

    describe('arrayJoin', function() {
        it('should join array elements with the specified separator', function() {
            expect(HbsHelpers.arrayJoin(['Bob', 'Kaz', 'Yvan'], ', ')).toEqual('Bob, Kaz, Yvan');
        });
    });

    describe('nl2br', function() {
        it('should convert newlines to breaks', function() {
            expect(HbsHelpers.nl2br("foo\nbar\r\nbaz\nbang")).toEqual(new Handlebars.SafeString("foo<br>bar<br>baz<br>bang"));
            expect(HbsHelpers.nl2br("\nbar\n\rbaz\r")).toEqual(new Handlebars.SafeString("<br>bar<br>baz<br>"));
        });

        it('should escape html entities', function() {
            expect(HbsHelpers.nl2br("Paste &copy;")).toEqual(new Handlebars.SafeString("Paste &amp;copy;"));
        });

        it('should convert newlines to breaks', function() {
            expect(HbsHelpers.nl2br("\nbar\r\nbaz\n")).toEqual(new Handlebars.SafeString("<br>bar<br>baz<br>"));
        });

        it('should accept input without newlines', function() {
            expect(HbsHelpers.nl2br('foo')).toEqual(new Handlebars.SafeString('foo'));
            expect(HbsHelpers.nl2br('')).toEqual(new Handlebars.SafeString(''));
            expect(HbsHelpers.nl2br("\\n")).toEqual(new Handlebars.SafeString("\\n"));
            expect(HbsHelpers.nl2br("\\r\\n")).toEqual(new Handlebars.SafeString("\\r\\n"));
        });

        it('should gracefully handle non-string values', function(){
            expect(HbsHelpers.nl2br(undefined)).toEqual(new Handlebars.SafeString(''));
            expect(HbsHelpers.nl2br({not: 'a string'})).toEqual(new Handlebars.SafeString('[object Object]'));
            expect(HbsHelpers.nl2br(3)).toEqual(new Handlebars.SafeString('3'));
        });

        it('should not allow HTML to be injected', function(){
            expect(HbsHelpers.nl2br("<b>Boldly</b>")).toEqual(new Handlebars.SafeString("&lt;b&gt;Boldly&lt;/b&gt;"));
            expect(HbsHelpers.nl2br("<script type='text/javascript'></script>")).toEqual(new Handlebars.SafeString("&lt;script type&#x3D;&#x27;text/javascript&#x27;&gt;&lt;/script&gt;"));
        })
    });

    describe('formatCurrency', function() {
        it('should format the value to a currency format', function() {
            User.set('decimal_precision', 2);
            User.set('decimal_separator', '.');
            User.set('number_grouping_separator', ',');
            var amount = 1999.99;
            var currencyId = '-99';
            expect(HbsHelpers.formatCurrency(amount, currencyId)).toEqual('$1,999.99');
        });
    });

    describe('formatDate', function() {
        it('should format the value to users date and time format', function() {
            var date = '2012-03-27 01:48:32';

            User.setPreference('datepref', 'Y-m-d');
            User.setPreference('timepref', 'h:i a');

            expect(HbsHelpers.formatDate(date, {hash: {dateOnly: false}})).toEqual('2012-03-27 01:48 am');
            expect(HbsHelpers.formatDate(date, {hash: {dateOnly: true}})).toEqual('2012-03-27');
        });
    });

    describe('firstChars', function() {
        it('should return the first n chars of a string', function() {
            var str = 'longstring';
            var length = 3;

            expect(HbsHelpers.firstChars(str, length)).toEqual('lon');
        });
    });

    describe('getModuleName', function() {
        it('should call `Language.get` with the module and options', function() {
            var getModuleNameStub = sinon.stub(Language, 'getModuleName'),
                hbsOptions = {
                    hash: {
                        defaultValue: 'test',
                        plural: true,
                    }
                },
                langOptions = {
                    defaultValue: hbsOptions.hash.defaultValue,
                    plural: hbsOptions.hash.plural,
                };

            HbsHelpers.getModuleName('Cases', hbsOptions);
            expect(getModuleNameStub).toHaveBeenCalledWith('Cases', langOptions);
        });
    });

    describe('partial', function() {
        var options;

        beforeEach(function() {
            SugarTest.seedMetadata(true);
            options = {hash: {}};
        });

        afterEach(function() {
            sinon.restore();
        });

        describe('a layout template', function() {
            var layout;

            beforeEach(function() {
                layout = SugarTest.createComponent('Layout', {
                    type : 'detail',
                    module: 'Contacts',
                });
            });

            afterEach(function() {
                layout.dispose();
            });

            it('should return a partial template', function() {
                var getLayoutStub = sinon.stub(Template, 'getLayout').callsFake(function() {
                    return function(){ return 'Layout'; };
                });
                var renderedTemplate = HbsHelpers.partial.call(layout, 'test', layout, {}, options);
                expect(renderedTemplate.toString()).toEqual('Layout');
                expect(getLayoutStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });

            it('should return a partial template with a different supplied module', function() {
                var getLayoutStub = sinon.stub(Template, 'getLayout').callsFake(function() {
                    return function(){return 'Layout'};
                });
                options.hash.module = 'Accounts';

                var renderedTemplate = HbsHelpers.partial.call(layout, 'test', layout, {}, options);
                expect(renderedTemplate.toString()).toEqual('Layout');
                expect(getLayoutStub).toHaveBeenCalledWith('detail.test', 'Accounts');
            });

            it('should return a partial template with different supplied data' ,function() {
                var getLayoutStub = sinon.stub(Template, 'getLayout').callsFake(function() {
                    return function(data){return 'Layout' + data.value};
                });
                var renderedTemplate = HbsHelpers.partial.call(layout, 'test', layout, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('LayoutData');
                expect(getLayoutStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });
        });

        describe('a view template', function() {
            var view;

            beforeEach(function(){
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts'
                });
            });

            afterEach(function() {
                view.dispose();
                view = null;
            });

            it('should return a partial template' ,function() {
                var getViewStub = sinon.stub(Template, 'getView').callsFake(function() {
                    return function(){ return 'View'; };
                });
                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getViewStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });

            it('should return a partial template with a different supplied module', function() {
                var getViewStub = sinon.stub(Template, 'getView').callsFake(function() {
                    return function(){ return 'View'; };
                });
                options.hash.module = 'Accounts';

                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getViewStub).toHaveBeenCalledWith('detail.test', 'Accounts');
            });

            it('should return a partial template with different supplied data', function() {
                var getViewStub = sinon.stub(Template, 'getView').callsFake(function() {
                    return function(data){return 'View' + data.value};
                });
                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('ViewData');
                expect(getViewStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });

            it('should load the partial from where it loaded the original template in the case of no override', function() {
                view.tplName = 'notDetail';
                sinon.stub(Template, 'getView')
                    .withArgs('notDetail.test').returns(function() {
                        return 'tplName';
                    });

                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('tplName');
            });

            it('should use an overridden template corresponding to its own name if it exists', function() {
                view.tplName = 'notDetail';
                sinon.stub(Template, 'getView')
                    .withArgs('notDetail.test').returns(function() {
                        return 'tplName';
                    })
                    .withArgs('detail.test').returns(function() {
                        return 'overridden';
                    });

                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('overridden');
            });
        });

        describe('a field template', function() {
            var view, field, context;

            beforeEach(function(){
                context = new Context();
                context.set('module', 'Contacts');
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts',
                });
                field = SugarTest.createComponent('Field', {
                    def: {
                        type: 'base',
                        name: 'testfield',
                        label: 'testfield'
                    },
                    context: context,
                    view: view,
                });
            });

            afterEach(function() {
                field.dispose();
            });

            it('should return a partial template', function() {
                var getFieldStub = sinon.stub(Template, 'getField').callsFake(function() {
                    return function(){ return 'Field'; };
                });
                var renderedTemplate = HbsHelpers.partial.call(field, 'test', field, {}, options);
                expect(renderedTemplate.toString()).toEqual('Field');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Contacts');
            });

            it('should return a partial template with a different supplied module', function() {
                var getFieldStub = sinon.stub(Template, 'getField').callsFake(function() {
                    return function(){ return 'Field'; };
                });

                options.hash.module = 'Accounts';

                var renderedTemplate = HbsHelpers.partial.call(field, 'test', field, {}, options);
                expect(renderedTemplate.toString()).toEqual('Field');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Accounts');
            });

            it('should return a partial template with different supplied data', function() {
                var getFieldStub = sinon.stub(Template, 'getField').callsFake(function() {
                    return function(data){return 'Field' + data.value};
                });
                var renderedTemplate = HbsHelpers.partial.call(field, 'test', field, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('FieldData');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Contacts');
            });
        });

        describe('generic partial support', function() {
            var view;
            beforeEach(function(){
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts',
                });
            });

            afterEach(function() {
                view.dispose();
            });

            it('should return a compiled partial template defined in the component options', function() {
                var getLayoutStub = sinon.spy(Template, 'getLayout');
                var getViewStub = sinon.spy(Template, 'getView');
                var getFieldStub = sinon.spy(Template, 'getField');
                view.setTemplateOption('partials', {'test': function() {return 'View'}});
                var renderedTemplate = HbsHelpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getLayoutStub).not.toHaveBeenCalled();
                expect(getViewStub).not.toHaveBeenCalled();
                expect(getFieldStub).not.toHaveBeenCalled();
            });

            it('should work if the parameter `context` is missing', function() {
                sinon.stub(Template, 'getView').returns(function(data) {
                    expect(data.templateComponent).toEqual(view);
                });

                HbsHelpers.partial.call(view, 'test', view, null, options);
            });


            it('should keep the original component as `templateComponent` in the `context`', function() {
                sinon.stub(Template, 'getView').returns(function(data) {
                    expect(data.templateComponent).toEqual(view);
                });

                HbsHelpers.partial.call(view, 'test', view, {}, options);
            });


            it('should merge extra parameters in `options.hash` into the `context`', function() {
                sinon.stub(Template, 'getView').returns(function(data) {
                    expect(data.catName).toEqual('Meow');
                });

                options.hash.catName = 'Meow';

                HbsHelpers.partial.call(view, 'test', view, {}, options);
            });
        });
    });
});
