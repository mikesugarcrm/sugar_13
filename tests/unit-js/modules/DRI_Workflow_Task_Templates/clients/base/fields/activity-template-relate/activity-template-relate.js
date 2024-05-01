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
describe('Base.Field.DRI_Workflow_Task_Templates.ActivityTemplateRelate', function() {
    let model;
    let app;
    let fieldName = 'activity_template_relate';
    let module = 'DRI_Workflow_Task_Templates';

    function createField(model) {
        return SugarTest.createField('base', fieldName, 'activity-template-relate', 'detail', {
        }, module, model, null, true);
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        SugarTest.loadComponent('base', 'field', 'relate');
        field = createField(model);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;
    });

    describe('initialize', function() {
        it('should initialize the relate', function() {
            expect(field.type).toBe('relate');
        });
    });

    describe(' _createSearchCollection', function() {
        using('input', [
            {
                search_module: 'DRI_Workflow_Task_Templates',
            },
            {
                search_module: '',
            },
        ],

        function(input) {
            it('should call the  _createSearchCollection function', function() {
                sinon.stub(field, 'getSearchModule').returns(input.search_module);
                sinon.stub(app.metadata, 'getModule').returns(input.search_module);
                sinon.stub(field, 'customEndpointCollection');
                field._createSearchCollection();
                if (app.metadata.getModule() == 'DRI_Workflow_Task_Templates') {
                    field.seachCollection = input.search_module;
                    expect(field.customEndpointCollection).toHaveBeenCalled();
                } else {
                    field.seachCollection = input.search_module;
                }
                expect(field.getSearchModule).toHaveBeenCalled();
                expect(field.seachCollection).toBe(input.search_module);
                expect(app.metadata.getModule).toHaveBeenCalled();
            });
        });
    });

    describe('openSelectDrawer', function() {
        beforeEach(function() {
            let layout = SugarTest.loadComponent('base', 'layout', 'selection-list');
            app.drawer = app.drawer || {};
            app.drawer.open = app.drawer.open || $.noop;
        });
        it('should call the  openSelectDrawer', function() {
            sinon.stub(field, 'getSearchModule').returns('DRI_Workflow_Task_Templates');
            sinon.stub(field, 'getSearchFields').returns(fieldName);
            sinon.stub(field, 'getFilterOptions').returns('filteroptions');
            sinon.stub(field, 'getCustomContext').returns(model);
            field.openSelectDrawer();
            expect(field.getSearchModule).toHaveBeenCalled();
            expect(field.getSearchFields).toHaveBeenCalled();
            expect(field.getFilterOptions).toHaveBeenCalled();
            expect(field.getCustomContext).toHaveBeenCalled();
        });
    });

    describe('getCustomContext', function() {
        it('should call the  getCustomContext', function() {
            let cxt = {};
            sinon.stub(field, 'getSearchModule').returns('DRI_Workflow_Task_Templates');
            sinon.stub(app.data, 'createBean').returns(model);
            sinon.stub(field, 'customEndpointCollection').returns('Endpoints');
            let result = field.getCustomContext(cxt);
            expect(result.model).toBe(model);
            expect(result.collection).toBe('Endpoints');
            expect(field.customEndpointCollection).toHaveBeenCalled();
            expect(field.getSearchModule).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
        });
    });

    describe(' customEndpointCollection', function() {
        it('should call the   customEndpointCollection', function() {
            sinon.stub(app.data, 'createBeanCollection').returns(model);
            sinon.stub(field, 'registerEndpoint').returns('Endpoints');
            let result = field.customEndpointCollection(module);
            expect(result).toBe(model);
            expect(field.registerEndpoint).toHaveBeenCalled();
            expect(app.data.createBeanCollection).toHaveBeenCalled();
        });
    });

    describe('registerEndpoint', function() {
        it('should call the   registerEndpoint', function() {
            sinon.stub(field, 'getAPIFilters').returns(model);
            sinon.stub(field, 'getAPIData').returns('Endpoints');
            let options = [];
            let result = field.registerEndpoint(options);
            expect(_.isFunction(result));
        });
    });

    describe('getAPIData', function() {
        using('input', [
            {
                method: 'update',
            },
            {
                method: 'create',
            },
        ],

        function(input) {
            it('should call the   getAPIData', function() {
                let callbacks = sinon.stub();
                let options = [];
                sinon.stub(app.data, 'getEditableFields').returns(model);
                sinon.stub(app.api, 'records').returns(model);
                let result = field.getAPIData(input.method,model,options,callbacks);
                expect(result).toBe(model);
                expect(app.data.getEditableFields).toHaveBeenCalled();
            });
        });
    });

    describe('getAPIFilters', function() {
        it('should call the   getAPIFilters', function() {
            let filter = [];
            sinon.stub(field, '_getCustomFilter').returns(module);
            let result = field.getAPIFilters(filter);
            expect(result[0]).toBe(module);
            expect(field._getCustomFilter).toHaveBeenCalled();
        });
    });

    describe('getAPIData', function() {
        it('should call the   getAPIData', function() {
            let callbacks = sinon.stub();
            let options = [];
            let method = 'update';
            sinon.stub(app.data, 'getEditableFields').returns(model);
            sinon.stub(app.api, 'records').returns(model);
            let result = field. getAPIData(method,model,options,callbacks);
            expect(result).toBe(model);
            expect(app.data.getEditableFields).toHaveBeenCalled();
        });
    });

    describe('_getCustomFilter', function() {
        using('input', [
            {
                journey_id: '123',
                id: '456',
            },
            {
                journey_id: '789',
                id: '',
            },
        ],

        function(input) {
            it('should call the _getCustomFilter function', function() {
                field.model.set({
                    dri_workflow_template_id: input.journey_id,
                    id: input.id,
                });
                field.result = field._getCustomFilter();
                let dictionary = _.first(field.result);
                let output = _.first(dictionary.$and);
                if (field.model.id == '') {
                    expect(output.dri_workflow_template_id.$equals).toBe(input.journey_id);
                } else {
                    expect(output.dri_workflow_template_id.$equals).toBe(input.journey_id);
                    expect(output.id.$not_equals).toBe(input.id);
                }
            });
        });
    });
});
