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
describe('Base.Field.CJ_Forms.FieldTriggerFilter', function() {
    let field;
    let fieldType = 'field-trigger-filter';
    let app;
    let model;
    let fieldName = 'field_trigger_filter';
    let module = 'CJ_Forms';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = createField(model);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('_loadTemplate', function() {
        it('should load the Template', function() {
            sinon.stub(field, '_super');
            field._loadTemplate();
            expect(field.type).toBe('filter');
            expect(field._super).toHaveBeenCalledWith('_loadTemplate');
        });
    });

    describe('_doValidateField', function() {
        it('should validates that filter field is not empty', function() {
            let error = {
                Account: {
                    moduleName: 'Account'
                },
            };
            field.filterId = {
                moduleName: 'Account'
            };
            field.model.set('main_trigger_type', 'sugar_action_to_smart_guide');
            sinon.stub(field, 'format').returns(field.filterId);
            let callback =   sinon.stub();
            field._doValidateField(field, error, callback);
            expect(field.format).toHaveBeenCalled();
        });
    });

    describe('initialize', function() {
        it('should call the initializer', function() {
            sinon.stub(field, '_super');
            let initOptions = {};
            field.openBuildFilterView = true;
            field.model.removeValidationTask =  sinon.stub();
            field.initialize(initOptions);
            expect(field._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('_render', function() {
        it('should call the render', function() {
            field.openBuildFilterView = true;
            field.view.action = 'edit';
            field.action = 'detail';
            field.disposeComponents =   sinon.stub();
            field.renderComponents =   sinon.stub();
            sinon.stub(field, '_super');
            field._render();
            expect(field._super).toHaveBeenCalledWith('_render');
        });
    });

    describe('initProperties', function() {
        it('should initialize the properties', function() {
            sinon.stub(field, '_super');
            field.openBuildFilterView = true;
            field.def.openBuildFilterView = false;
            field.initProperties();
            expect(field._super).toHaveBeenCalledWith('initProperties');
            expect(field.openBuildFilterView).toBe(false);
        });
    });

    describe('onModuleChange', function() {
        it('should perform functionality on module change', function() {
            sinon.stub(field, '_super');
            field.filterCollection = {
                moduleName: module
            };
            field.onModuleChange(model, module);
            expect(field._super).toHaveBeenCalledWith('onModuleChange');
        });
    });

    describe('format', function() {
        it('should format the data', function() {
            sinon.stub(field, '_super').returns('99');
            field.openBuildFilterView = true;
            field.filterCollection = {
                moduleName: module
            };
            field.name = fieldName;
            field.model.set(fieldName, '1000');
            field.format();
            expect(field.format()).toBe(1000);
        });
    });

    describe('getFilterCollectionModelById', function() {
        it('should get filter collection model by id', function() {
            field.openBuildFilterView = true;
            field.filter = {
                model: model,
                moduleName: module
            };
            expect(field.getFilterCollectionModelById()).toBe(field.filter.model);
        });
    });

    describe('buildComponents', function() {
        it('should build the component', function() {
            sinon.stub(field, '_super');
            field.openBuildFilterView = true;
            field.filter = {
                model: model,
                moduleName: module
            };
            field.buildComponents();
            expect(field._super).toHaveBeenCalledWith('buildComponents');
        });
    });

    describe('getFilterMeta', function() {
        it('should get filter meta', function() {
            sinon.stub(field, '_super');
            field.initFilterModel =  sinon.stub();
            field.initFilterCollection =   sinon.stub();
            field.initFilterContext =  sinon.stub();
            field.getFilterModule =  sinon.stub();
            field.openBuildFilterView = true;
            field.filterDefaultModule = true;
            field.filterId = {
                model: model,
                moduleName: module
            };
            let result = field.getFilterMeta();
            expect(result.action).toEqual('detail');
            expect(result.name).toEqual('cj-filterpanel');
        });
    });

    describe('loadComponents', function() {
        it('should get filter meta', function() {
            field.canLoadComponents =  sinon.stub().returns(true);
            field.initFilterCollection =   sinon.stub();
            field.filterCollection = {
                load: sinon.stub()
            };
            field.loadComponents();
            expect(field.canLoadComponents).toHaveBeenCalled();
            expect(field.initFilterCollection).toHaveBeenCalled();
            expect(field.filterCollection.load).toHaveBeenCalled();
        });
    });
});

