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
describe('Base.View.CJFieldTriggerFilterRowsView', function() {

    let app;
    let view;
    let model;
    let context;
    let initOptions;

    beforeEach(function() {

        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean('Accounts');
        parentLayout = app.view.createLayout({type: 'base'});
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        view = SugarTest.createView('base', 'Accounts', 'cj-field-trigger-filter-rows', {}, false, false, parentLayout);
        SugarTest.app.data.declareModels();
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        context.parent.parent = app.context.getContext();
        view.collection = new Backbone.Collection();
        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        parentLayout.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        parentLayout = null;
    });

    describe('initialize', function() {
        it('should call the initialize', function() {
            sinon.stub(app.template, 'get').returns('dri_workflow_template');
            view.initialize(initOptions);
            expect(view._super).toHaveBeenCalledWith('initialize');
            expect(app.template.get).toHaveBeenCalled();
            expect(view.formRowTemplate).toBe('dri_workflow_template');

        });
    });

    describe('render', function() {
        it('should call the render', function() {
            sinon.stub(view, 'getViewCurrentAction').returns('detail');
            view.viewCurrentAction = 'detail';
            sinon.stub(app.template, 'get').returns('dri_workflow_template');
            view.render();
            expect(view._super).toHaveBeenCalledWith('render');
            expect(app.template.get).toHaveBeenCalled();
            expect(view.formRowTemplate).toBe('dri_workflow_template');
            expect(view.getViewCurrentAction).toHaveBeenCalled();
        });
    });

    describe('createField', function() {
        it('should call the createField', function() {
            let object = {
                action: 'edit'
            };
            sinon.stub(view, 'getViewCurrentAction').returns('detail');
            sinon.stub(app.view, 'createField').returns(object);
            let result = view.createField(model, []);
            expect(result.action).toEqual('detail');
            expect(app.view.createField).toHaveBeenCalled();
            expect(view.getViewCurrentAction).toHaveBeenCalled();
        });
    });

    describe('getViewCurrentAction', function() {
        it('should  return the main view action', function() {
            view.layout  = parentLayout;
            view.layout.options.action = 'detail';
            view.viewCurrentAction = 'edit';
            view.getViewCurrentAction();
            expect(view.viewCurrentAction).toEqual('detail');
        });
    });

    describe('initValueField', function() {
        it('should  initalize value field', function() {
            view._operatorsWithNoValues = '$or';
            view.moduleName = 'Accounts';
            view.fieldTypeMap = {
                enum: 'enum'
            };
            view.fieldList = {
                dropdown: {
                    isMultiSelect: true,
                    searchBarThreshold: 0,
                    type: 'enum'
                }
            };
            let field = {
                dropdown: {
                    isMultiSelect: true,
                    searchBarThreshold: 0
                }
            };
            let data = {
                operatorField: {
                    model: {
                        get: sinon.stub().returns('$in')
                    }
                },
                operator: '$or',
                name: 'dropdown',
                empty: sinon.stub()
            };
            let fielValue = {
                removeClass: sinon.stub().returns(data),
                attr: sinon.stub(),
                append: sinon.stub()
            };
            let row = {
                data: sinon.stub().returns(data),
                find: sinon.stub().returns(fielValue),
            };
            sinon.stub(view, 'getViewCurrentAction').returns('detail');
            sinon.stub(app.metadata, 'getModule').returns(model);
            sinon.stub(view, '_renderField');
            sinon.stub(app.metadata, '_patchFields').returns(field);
            view.initValueField(row);
            expect(view.getViewCurrentAction).toHaveBeenCalled();
            expect(app.metadata.getModule).toHaveBeenCalled();
            expect(view._renderField).toHaveBeenCalled();
            expect(app.metadata._patchFields).toHaveBeenCalled();
        });
    });
});
