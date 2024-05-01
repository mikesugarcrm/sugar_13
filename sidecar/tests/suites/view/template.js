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

const Template = require('../../../src/view/template');

describe('View/Template', function() {
    beforeEach(function() {
        this.app = SugarTest.app;
        this.sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        //Reset the cache after every test
        this.app.cache.cutAll();
        Handlebars.templates = {};
        this.sandbox.restore();
    });

    it('should compile templates', function() {
        var src = "Hello {{name}}!";
        var key = 'testKey';
        var temp = Template.compile(key, src);
        expect(temp({name: 'Jim'})).toEqual('Hello Jim!');
    });

    it('should compile template into empty function if template is invalid', function() {
        var src = 'Hello {{}}!';
        var key = 'invalidTemplate';
        this.sandbox.stub(this.app.logger, 'error');
        var temp = Template.compile(key, src);
        expect(temp({name: 'Jim'})).toEqual('');
    });

    it('should retrieve compiled templates', function() {
        var src = "Hello {{name}}!";
        var key = 'testKey';
        //Compile the template
        Template.compile(key, src);

        // We don't cache templates by default
        expect(this.app.cache.get("templates")).toBeUndefined();

        //The compiled template should be attached to Handlebars
        expect(Template.get(key)).toEqual(Handlebars.templates[key]);

        //Get should return a compiled template
        expect(Template.get(key)({name: 'Jim'})).toEqual('Hello Jim!');
    });

    it('should retrieve compiled templates from cache', function() {
        var src = "Hello {{name}}!";
        var key = 'testKey';
        //Compile the template
        Template.compile(key, src);
        //Initialize will reset the internal variables referencing the templates in memory
        Template.init();

        //Get should return a compiled template
        expect(Template.get(key)({name: "Jim"})).toEqual("Hello Jim!");
    });

    it('should load multiple templates in a single call', function() {
        var data = {
            views: {
                hello: {
                    templates: {
                        hello: "Hello {{name}}!",
                    },
                },
                foo: {
                    templates: {
                        foo: 'Bar',
                    },
                },
            },
        };
        Template.set(data);

        //Get should return both the templates
        expect(Template.get('hello')({name: 'Jim'})).toEqual('Hello Jim!');
        expect(Template.get('foo')()).toEqual('Bar');
    });

    it('should set and get layout templates', function() {
        var source = "<div>Layout Template</div>",
            data = {
                layouts: {
                    test: {
                        templates: {
                            test: source,
                        },
                    },
                },
            };

        Template.set(data);

        expect(Template.getLayout('test')()).toEqual(source);
    });

    it('should lazy compile templates when setters called for field, view, or layout', function() {
        var addStub = sinon.stub(Template, '_add');
        //these assertions are just here to ensure that our setters delegate to template._add
        // (as opposed to precompiling directly)
        Template.setField(
            'enum',
            'detail',
            'Cases',
            fixtures.metadata.modules.Cases.fieldTemplates.enum.templates.detail,
            true
        );
        expect(addStub).toHaveBeenCalled();
        addStub.resetHistory();
        Template.setView('myview', 'Cases', {}, false);
        expect(addStub).toHaveBeenCalled();
        addStub.resetHistory();
        Template.setLayout('mylayout', 'Cases', {}, false);
        expect(addStub).toHaveBeenCalled();
        addStub.resetHistory();
        addStub.restore();
    });

    it('should retrieve the module based template', function() {
        Template.setField(
            'enum',
            'detail',
            'Cases',
            fixtures.metadata.modules.Cases.fieldTemplates.enum.templates.detail,
            true
        );
        expect(Template.getField('enum', 'detail', 'Cases')({
            value: 'Hello',
        })).toEqual('Cases Enum Detail: Hello');
    });

    it('should retrieve the field view for type if no module set', function() {
        Template.setField(
            'enum',
            'detail',
            null,
            fixtures.metadata.modules.Cases.fieldTemplates.enum.templates.detail,
            true
        );
        expect(Template.getField('enum', 'detail', 'Cases')({
            value: 'Hello',
        })).toEqual('Cases Enum Detail: Hello');
    });

    it('should fall back to the fallback arg before the base template including module', function() {
        Template.setField(
            'enum',
            'detail',
            'Cases',
            fixtures.metadata.modules.Cases.fieldTemplates.enum.templates.detail,
            true
        );
        expect(Template.getField('enum', 'list', 'Cases', 'detail')({
            value: 'Hello',
        })).toEqual('Cases Enum Detail: Hello');
    });

    it('should fall back to the fallback arg before the base template with for no module', function() {
        Template.setField(
            'enum',
            'detail',
            null,
            fixtures.metadata.modules.Cases.fieldTemplates.enum.templates.detail,
            true
        );
        expect(Template.getField('enum', 'list', 'Cases', 'detail')({
            value: 'Hello',
        })).toEqual('Cases Enum Detail: Hello');
    });

    it('should retrieve base template if none are available for the current field', function() {
        Template.compile('f.base.detail', fixtures.metadata.fields.base.templates.detail);
        expect(Template.getField('non-existent-field', 'detail')({
            value: 'Hello',
        })).toEqual('<h3></h3><span name="">Hello</span>');
    });

    it('should retrieve the fallback template if no other templates are available', function() {
        Template.compile('f.base.detail', fixtures.metadata.fields.base.templates.detail);
        expect(Template.getField('non-existent-field', 'non-existent-view', null, 'detail')({
            value: 'Hello',
        })).toEqual('<h3></h3><span name="">Hello</span>');
    });

    it('should set multiple templates from a single view', function() {
        var data = {
            views: {
                test123: {
                    templates: {
                        test123: 'foo',
                        test_2: 'bar',
                    }
                }
            }
        };

        Template.set(data);

        expect(Template.getView('test123')()).toEqual('foo');
        expect(Template.getView('test123.test_2')()).toEqual('bar');
    });
});
