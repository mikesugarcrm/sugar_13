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

const Field = require('../../../src/view/field');
const User = require('../../../src/core/user');
const Context = require('../../../src/core/context');
const Template = require('../../../src/view/template');
const BeanCollection = require('../../../src/data/bean-collection');
const View = require('../../../src/view/view');

describe('View/View', function() {
    beforeEach(function() {
        SugarTest.seedMetadata(true);
        this.app = SugarTest.app;

        let bean = this.app.data.createBean('Contacts', {
            first_name: 'Foo',
            last_name: 'Bar',
        });
        bean.fields = fixtures.metadata.modules.Contacts.fields;
        this.context = new Context({
            url: 'someurl',
            module: 'Contacts',
            model: bean,
            collection: new BeanCollection([bean]),
        });
    });

    afterEach(function() {
        sinon.restore();
    });

    it('should respect the dataView property from child views', function () {
        let CustomView = View.extend({
            dataView: 'sidecar-testing',
            getFieldNames() {
                return ['field1', 'field2'];
            },
        });

        expect(this.context.get('dataView')).toBeUndefined();
        expect(this.context.get('fields')).toBeUndefined();

        new CustomView({
            context: this.context,
        });

        expect(this.context.get('dataView')).toBe('sidecar-testing');
        expect(this.context.get('fields')).toBeUndefined();
    });

    it('should add fields to context from child views', function () {
        let CustomView = View.extend({
            getFieldNames() {
                return ['field1', 'field2'];
            },
        });

        expect(this.context.get('dataView')).toBeUndefined();
        expect(this.context.get('fields')).toBeUndefined();

        new CustomView({
            context: this.context,
        });

        expect(this.context.get('dataView')).toBeUndefined();
        expect(this.context.get('fields')).toEqual(['field1', 'field2']);
    });

    it('should render edit views', function() {
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'edit',
        });

        view.render();
        let html = view.$el.html();
        expect(html).toContain('edit');

        expect(view.$el.find('input[value="Foo"]').length).toEqual(1);
    });

    it('should re-render views when fire app:locale:change fires', function() {
        SugarTest.createComponent('View', {
            context: this.context,
            type: 'custom',
        });
        let spy = sinon.spy(View.prototype, '_setLabels');
        this.app.events.trigger('app:locale:change');

        expect(spy).toHaveBeenCalled();

        spy.restore();
    });

    it('should render detail views', function() {
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'detail',
        });
        view.render();
        expect(view.moduleSingular).toEqual('Kontact');
        expect(view.modulePlural).toEqual("Kontacts");
        let html = view.$el.html();
        expect(html).toContain('detail');
    });

    it('should render with custom context for its template', function() {
        this.app.view.views.CustomView = View.extend({
            _renderHtml: function() {
                View.prototype._renderHtml.call(this, {prop: 'kommunizma'});
            },
        });
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'custom',
        });

        view.template = Handlebars.compile("K pobede {{prop}}!");
        view.render();
        let html = view.$el.html();
        expect(html).toContain('K pobede kommunizma!');
    });

    it('should return its fields, related fields and dispose them when re-rendering', function(){
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'detail',
        });
        let fields = ['first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name', 'parent_name',
            'date_modified'];
        let mock = sinon.mock(Field.prototype);

        mock.expects('dispose').exactly(11);

        //getFieldName should return its related fields
        expect(view.getFieldNames()).toEqual(['first_name', 'last_name', 'phone_work', 'phone_home', 'email1',
            'account_name', 'parent_name', 'date_modified', 'modified_by_name', 'account_id', 'parent_id',
            'parent_type']);

        expect(_.isEmpty(view.getFields())).toBeTruthy();
        expect(_.isEmpty(view.fields)).toBeTruthy();

        view.render();

        expect(_.keys(view.fields).length).toEqual(11);
        expect(_.pluck(view.getFields(), 'name')).toEqual(fields);

        // Make sure the number of fields is still the same
        view.render();

        expect(_.keys(view.fields).length).toEqual(11);
        expect(_.pluck(view.getFields(), 'name')).toEqual(fields);
        mock.verify();
    });

    it('should only load data when the user has read access for the View\'s module', function(){
        // Function that makes API call to load Fields defined in View
        let loadStub = sinon.stub(this.context, 'loadData').callsFake(function() {
            return;
        });
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'details',
            module: 'Bugs',
        });
        view.loadData();

        expect(loadStub).toHaveBeenCalled();
        loadStub.resetHistory();

        let acls = User.get('acl');
        // Remove access to Bugs module.
        User.set('acl', {Bugs: {read: 'no'}});

        view.loadData();

        expect(loadStub).not.toHaveBeenCalled();
        loadStub.restore();
        // Restore acls to what they were.
        User.set('acl', acls);
    });

    it('should occur an error dialog only if the primary view has not rendered due to the acl failure', function() {
        let hasAccessStub = sinon.stub(this.app.acl, 'hasAccessToModel').callsFake(function(action, model) {
            return false;
        });
        let view = SugarTest.createComponent('View', {
            context: this.context,
            type: 'details',
            module: 'Bugs',
            primary: true,
        });
        let errorSpy = sinon.spy(this.app.error, 'handleRenderError');

        view.render();
        expect(errorSpy).toHaveBeenCalled();
        errorSpy.restore();

        errorSpy = sinon.spy(this.app.error, 'handleRenderError');
        view.primary = false;
        expect(errorSpy).not.toHaveBeenCalled();

        hasAccessStub.restore();
        errorSpy.restore();
    });

    describe('loading the template', function() {
        using('different cases where templates are defined or not', [
            // We get the template defined in the view's module first.
            {
                module: 'Accounts',
                moduleTpl: true,
                expectedTpl: 'moduleTpl',
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: false,
                expectedTpl: 'moduleTpl',
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: true,
                expectedTpl: 'moduleTpl',
            },
            // If the template is not defined in the
            // view's module and `loadModule` is not passed, we fallback to the
            // template in base.
            {
                module: 'Accounts',
                moduleTpl: false,
                expectedTpl: 'baseTpl',
            },
            // If the template is not defined in the view's
            // module, we fallback to the one defined in `loadModule` module.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: true,
                expectedTpl: 'loadModuleTpl',
            },
            // If the template in `loadModule` module
            // is undefined, we do NOT fallback to the one defined in base.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: false,
                expectedTpl: 'emptyTpl',
            },
        ], function(data) {
            it('should load the template from the correct module', function() {
                let viewName = 'testView';
                let templates = {
                    moduleTpl: '<div>' + data.module + '</div>',
                    loadModuleTpl: '<div>' + data.loadModule + '</div>',
                    baseTpl: '<div>base</div>',
                    emptyTpl: Template.empty(),
                };

                let getViewStub = sinon.stub(Template, 'getView');
                if (data.loadModuleTpl) {
                    getViewStub.withArgs(viewName, data.loadModule).returns(
                        function() {
                            return templates.loadModuleTpl;
                        }
                    );
                }

                if (data.moduleTpl) {
                    getViewStub.withArgs(viewName, data.module).returns(
                        function() {
                            return templates.moduleTpl;
                        }
                    );
                }

                getViewStub.withArgs(viewName, void 0).returns(
                    function() {
                        return templates.baseTpl;
                    }
                );

                let view = this.app.view.createView({type: viewName, module: data.module, loadModule: data.loadModule});

                expect(view.template()).toEqual(templates[data.expectedTpl]);
            });
        });
    });
});
