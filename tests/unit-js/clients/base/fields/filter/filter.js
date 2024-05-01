
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
describe('Base.FilterField', function() {
    var app;
    var field;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('filter', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base', 'filter-module-dropdown');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base', 'result-partial');
        SugarTest.loadHandlebarsTemplate('filter-module-dropdown', 'view', 'base', 'selection-partial');
        SugarTest.loadHandlebarsTemplate('filter-filter-dropdown', 'view', 'base', 'filter-filter-dropdown');
        SugarTest.loadHandlebarsTemplate('filter-filter-dropdown', 'view', 'base', 'result-partial');
        SugarTest.loadHandlebarsTemplate('filter-filter-dropdown', 'view', 'base', 'selection-partial');
        SugarTest.loadHandlebarsTemplate('filter-rows', 'view', 'base', 'filter-rows');
        SugarTest.loadHandlebarsTemplate('filter-rows', 'view', 'base', 'filter-row-partial');
        SugarTest.loadHandlebarsTemplate('filter-actions', 'view', 'base', 'filter-actions');

        SugarTest.loadComponent('base', 'view', 'filter-module-dropdown');
        SugarTest.loadComponent('base', 'view', 'filter-filter-dropdown');
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.loadComponent('base', 'view', 'filter-actions');

        SugarTest.declareData('base', 'Filters', true, true);

        SugarTest.testMetadata.set();

        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        field = SugarTest.createField(
            'base',
            'calendar_filter',
            'filter',
            'edit',
            {},
            module,
            model,
            context,
            false
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        it('should set the filter id', function() {
            var filterId = 'filterId1';

            field._super = function() {
                this.model = new Backbone.Model({});
                this.model.addValidationTask = function() {
                    return;
                };
            };

            sinon.stub(field, 'getModelFilterId')
                .withArgs()
                .returns(filterId);
            field.initialize({});
            expect(field.filterId).toBe(filterId);
        });
    });

    describe('initListeners()', function() {
        var listenToStub;
        beforeEach(function() {
            listenToStub = sinon.stub(field, 'listenTo');
            field.initListeners();
        });
        afterEach(function() {
            listenToStub.restore();
        });

        it('should add listener for change:calendar_filter on field.model', function() {
            expect(listenToStub).toHaveBeenCalled();
            expect(listenToStub.getCall(0).args[1]).toEqual('change:' + field.name);
        });
    });

    describe('initProperties()', function() {
        it('should initialize properties', function() {
            field.initProperties();
            expect(field.filterDefaultId).toBe('assigned_to_me');
            expect(field.filterId).toBe('all_records');
            expect(field.filterDefaultModule).toBe(field.module);
        });
    });

    describe('_render()', function() {
        it('should call disposeComponents once', function() {
            sinon.stub(field, 'disposeComponents');
            sinon.stub(field, 'renderComponents');

            field._render();

            expect(field.disposeComponents).toHaveBeenCalledOnce();

            field.disposeComponents.restore();
            field.renderComponents.restore();
        });

        it('should call renderComponents once', function() {
            sinon.stub(field, 'renderComponents');

            field._render();

            expect(field.renderComponents).toHaveBeenCalledOnce();

            field.renderComponents.restore();
        });

        it('should render the template for the field in DOM', function() {
            sinon.stub(field, 'renderComponents');

            field._render();

            var contentWrapper = field.$el.find('div[data-content=wrapper]');
            expect(contentWrapper.length).toEqual(1);

            field.renderComponents.restore();
        });

        it('should render the filterpanel component', function() {
            sinon.stub(field, 'renderComponents');

            field.initFilterCollection();
            var filterModel = app.data.createBean('Filters', {
                editable: false,
                id: 'all_records',
                name: 'LBL_LISTVIEW_FILTER_ALL',
                filter_definition: []
            });

            field.filterCollection._initFiltersModuleCache();
            field.filterCollection.collection = field.filterCollection._createCachedCollection();
            field.filterCollection.collection.add(filterModel);
            field.buildComponents();
            field.renderComponents();

            field.filterpanel.render();

            expect(field.filterpanel.$('.row-fluid').length).toEqual(1);
            expect(field.filterpanel.$('.filter-definition-container').length).toEqual(1);
            expect(field.filterpanel.$('button[data-action=filter-reset]').length).toEqual(1);
            expect(field.filterpanel.$('button[data-action=filter-close]').length).toEqual(1);
            expect(field.filterpanel.$('button[data-action=filter-delete]').length).toEqual(1);
            expect(field.filterpanel.$('button[data-action=filter-save]').length).toEqual(1);

            field.renderComponents.restore();
        });
    });

    describe('buildFilterName()', function() {
        it('should build filter name', function() {
            field.model.set('name', 'Calls');
            var expected = 'Calendar: Calls';

            var result = field.buildFilterName(module);

            expect(result).toBe(expected);
        });
    });
});
