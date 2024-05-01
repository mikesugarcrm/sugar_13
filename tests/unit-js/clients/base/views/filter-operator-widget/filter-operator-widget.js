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
describe('Filter Operator Widget', function() {
    let widget;
    let app;
    let manager;

    beforeEach(function() {
        app = SugarTest.app;
        app.data.declareModels();
        SugarTest.loadPlugin('FilterOperatorManager');
        SugarTest.loadComponent('base', 'view', 'filter-operator-widget');
        context = app.context.getContext();
        context.prepare();

        app.filterOperators = app.filterOperators || {};

        app.filterOperators = _.extend(app.filterOperators, {
            BetweenOperator: class BetweenOperator {
                getUpdatedInput() {}
            }
        });

        manager = app.view.createView({
            type: 'dashboard-filter-group-widget',
        });

        widget =  app.view.createView({
            type: 'filter-operator-widget',
            manager: manager,
            operators: {},
            users: {},
            filterData: {
                qualifier_name: 'between',
                input_name0: {}
            },
            seedFieldDef: {},
            seedModule: '',
            fieldType: '',
            filterId: '',
            tooltipTitle: '',
        });
    });

    afterEach(function() {
        widget.dispose();
        widget = null;
        manager.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinon.restore();
    });

    it('should initialize properties correctly', function() {
        widget._initProperties();

        expect(widget._manager).toBeDefined();
        expect(widget._operators).toBeDefined();
        expect(widget._users).toBeDefined();
        expect(widget._filterData).toBeDefined();
        expect(widget._seedFieldDef).toBeDefined();
        expect(widget._seedModule).toBeDefined();
        expect(widget._fieldType).toBeDefined();
        expect(widget._filterId).toBeDefined();
        expect(widget._tooltipTitle).toBeDefined();
        expect(widget._searchTerm).toBeDefined();
        expect(widget._optionsList).toBeDefined();
        expect(widget._loading).toBe(true);
        expect(widget._lastItemClickedIdx).toBe(false);
        expect(widget._inputData).toBe(false);
        expect(widget._inputType).toBe(false);
        expect(widget._inputValue).toBe(false);
        expect(widget._inputValue1).toBe(false);
        expect(widget._inputValue2).toBe(false);
        expect(widget._inputValue3).toBe(false);
    });

    it('should handle qualifier changed', function() {
        var event = {currentTarget: {value: 'new_qualifier'}};
        spyOn(widget, '_initProperties');
        spyOn(widget, 'render');

        widget.qualifierChanged(event);

        expect(widget._filterData.qualifier_name).toBe('new_qualifier');
        expect(widget._filterData.input_name0).toBe('');
        expect(widget._filterData.input_name1).toBe('');
        expect(widget._filterData.input_name2).toBe('');
        expect(widget._filterData.input_name3).toBe('');
        expect(widget._initProperties).toHaveBeenCalled();
        expect(widget.render).toHaveBeenCalled();
    });

    describe('_render', function() {
        it('should render the widget', function() {
            // You can mock and spy on the required functions and elements as needed
            spyOn(widget, '_setupSearchOptions');
            spyOn(widget, 'render');
            spyOn(widget.$('.runtime-filter-select'), 'select2');
            spyOn(widget.$('[data-type="time"]'), 'timepicker');
            spyOn(widget, '_createSearchEngine');
            spyOn(widget, '_toggleLoadingScreen');
            spyOn(widget, 'createTempRelateController');
            spyOn(widget, '_markSelectedItems');
            spyOn(widget._manager.$('.runtime-filter-summary-text'), 'html');
            spyOn(widget.$('[data-fieldname="search-multiple"]'), 'focus');
            spyOn(widget.$('[data-fieldname="search-multiple"]'), 'val');

            widget._render();
        });
    });
});
