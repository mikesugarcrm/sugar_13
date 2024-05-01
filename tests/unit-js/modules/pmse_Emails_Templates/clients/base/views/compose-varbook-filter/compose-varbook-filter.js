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
describe('pmse_Emails_Temapltes.Base.Views.compose-varbook-filter', function() {
    var app;
    var view;
    var model;
    var context;
    var filterFieldMock;
    var moduleFilterMock;

    beforeEach(function() {
        app = SugarTest.app;

        // TODO: Find out if a custom metadata object would be better to use
        // and if so how to do that.
        context = app.context.getContext();
        model = app.data.createBean('pmse_Emails_Templates');
        context.set('model', model);
        view = SugarTest.createView('base', 'pmse_Emails_Templates', 'compose-varbook-filter', null, context, true);

        view._currentSearch = null;

        // Make mock objects for selector functions.
        filterFieldMock = {};
        filterFieldMock.select2 = sinon.stub();
        filterFieldMock.on = sinon.stub();
        filterFieldMock.off = sinon.stub();
        moduleFilterMock = {};
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('_render', function() {

        beforeEach(function() {
            sinon.stub(app.view.View.prototype._render, 'call');
            sinon.stub(view, 'buildModuleFilterList');
            sinon.stub(view, 'buildFilter');
        });

        it('should build the module filter and the modulefilter list', function() {
            view._render();

            expect(view.buildModuleFilterList).toHaveBeenCalled();
            expect(view.buildFilter).toHaveBeenCalled();
            expect(app.view.View.prototype._render.call).toHaveBeenCalled();
        });
    });

    describe('buildModuleFilterList', function() {

        beforeEach(function() {
            view.collection = {};
            view.collection.baseModule = 'boggle';
            view._allModulesId = 'goat';
            sinon.stub(app.lang, 'get').returns('sheep');
            sinon.stub(app.api, 'call');
        });

        afterEach(function() {
            // Trying to dispose the view makes it take a big shit if it has collection still.
            delete view.collection;
        });

        it('should set the filter list and call the api with the right url', function() {

            var expectedUrl = app.api.buildURL('pmse_Emails_Templates',
                view.collection.baseModule +  '/find_modules',
                null,
                {module_list: view.collection.baseModule});

            view.buildModuleFilterList();

            expect(view._moduleFilterList).toEqual([{id: 'goat', text: 'sheep'}]);
            // Using jasmine.any object to allow for use of _.bind or other wrappers to the callback function.
            expect(app.api.call).toHaveBeenCalledWith('read', expectedUrl, null, jasmine.any(Object));
        });
    });

    describe('_onGetModuleFilterListSuccess', function() {
        var mockResult;

        beforeEach(function() {
            view._moduleFilterList = [];
            view.collection = {};
            view.collection.baseModule = 0;
            mockResult = {};
            mockResult.success = true;
            mockResult.result = [];
        });

        afterEach(function() {
            // Trying to dispose the view makes it take a big shit if it has collection still.
            delete view.collection;
        });

        it('should add modules to the filter list', function() {
            mockResult.result.push({value: 1, text: 'hello'});
            mockResult.result.push({value: 29, text: 'goat'});
            mockResult.result.push({value: 'llama', text: 'boggle'});

            view._onGetModuleFilterListSuccess(mockResult);

            expect(view._moduleFilterList.length).toEqual(3);
        });

        it('should not add the base module to the filter list', function() {
            mockResult.result.push({value: 1, text: 'hello'});
            mockResult.result.push({value: 0, text: 'no adderino pls'});
            mockResult.result.push({value: 'llama', text: 'boggle'});

            view._onGetModuleFilterListSuccess(mockResult);

            expect(view._moduleFilterList.length).toEqual(2);
        });
    });

    describe('buildFilter', function() {
        var $mock; // jQuery object mock.

        beforeEach(function() {
            $mock = {};
            sinon.stub(view, '$').returns($mock);
            $mock.val = sinon.stub();

            view._selectedModule = 24;
            view._allModulesId = 7;

            sinon.stub(_, 'bind');
            sinon.stub(view, 'getFilterField').returns(filterFieldMock);
        });

        it('should set up the filter input with select2', function() {
            filterFieldMock.length = 1;
            view.buildFilter();

            expect(filterFieldMock.select2.callCount).toEqual(2);
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 24);
            expect(filterFieldMock.on).toHaveBeenCalled();
            expect(filterFieldMock.off).toHaveBeenCalled();
        });

        it('should set up the filter input with select2 and set the selected module', function() {
            view._selectedModule = null;
            filterFieldMock.length = 1;
            view.buildFilter();

            expect(filterFieldMock.select2.callCount).toEqual(2);
            expect(view._selectedModule).toEqual(7);
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 7);
            expect(filterFieldMock.on).toHaveBeenCalled();
            expect(filterFieldMock.off).toHaveBeenCalled();
        });

        it('should do nothing when there is no input', function() {
            view._selectedModule = null;
            filterFieldMock.length = 0;
            view.buildFilter();

            expect(view._selectedModule).toEqual(null);
            expect(filterFieldMock.select2).not.toHaveBeenCalled();
            expect(filterFieldMock.on).not.toHaveBeenCalled();
            expect(filterFieldMock.off).not.toHaveBeenCalled();
        });
    });

    describe('getFilterField', function() {

        beforeEach(function() {
            sinon.stub(view, '$').returns({});
        });

        it('should get the filter field in a jQuery object', function() {
            result = view.getFilterField();

            expect(view.$).toHaveBeenCalled();
        });
    });

    describe('getModuleFilter', function() {

        beforeEach(function() {
            sinon.stub(view, '$').returns({});
        });

        it('should get the module filter in a jQuery object', function() {
            result = view.getModuleFilter();

            expect(view.$).toHaveBeenCalled();
        });
    });

    describe('unbind', function() {

        beforeEach(function() {
            sinon.stub(view, '_super');
            sinon.stub(view, 'getFilterField').returns(filterFieldMock);
        });

        it('should try to destroy the select2', function() {
            filterFieldMock.length = 1;
            view.unbind();

            expect(filterFieldMock.off).toHaveBeenCalled();
            expect(filterFieldMock.select2).toHaveBeenCalledWith('destroy');
            expect(view._super).toHaveBeenCalledWith('unbind');
        });

        it('should not try to destroy the select2', function() {
            filterFieldMock.length = 0;
            view.unbind();

            expect(filterFieldMock.off).not.toHaveBeenCalled();
            expect(filterFieldMock.select2).not.toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('unbind');
        });
    });

    describe('throttledSearch', function() {
        var eventTargetMock;
        var eventMock;

        beforeEach(function() {
            eventTargetMock = {};
            eventTargetMock.val = sinon.stub().returns('tomato');
            eventMock = {};
            eventMock.currentTarget = eventTargetMock;
            sinon.stub(view, 'applyFilter');
            sinon.stub(view, '$').returns(eventTargetMock);
        });

        it('should apply the new filter', function() {
            view._currentSearch = 'tomahto';
            view.throttledSearch(eventMock);

            expect(view.applyFilter).toHaveBeenCalled();
        });

        it('should not apply the existing filter again', function() {
            view._currentSearch = 'tomato';
            view.throttledSearch(eventMock);

            expect(view.applyFilter).not.toHaveBeenCalled();
        });

    });

    describe('initSelection', function() {
        var $mock;
        var callback;

        beforeEach(function() {
            $mock = {};
            $mock.val = sinon.stub();
            $mock.is = sinon.stub();
            sinon.stub(_, 'findWhere').returns({id: 1, text: 2});
            callback = sinon.stub();
            sinon.stub(view, 'getFilterField').returns(filterFieldMock);
        });

        it('should run the callback', function() {
            $mock.is.returns(true);
            view.initSelection($mock, callback);

            // Arbitrary values based on the stub above.
            expect(callback).toHaveBeenCalledWith({id: 1, text: 2});
        });

        it('should not run the callback', function() {
            $mock.is.returns(false);
            view.initSelection($mock, callback);

            expect(callback).not.toHaveBeenCalled();
        });
    });

    describe('formatModuleSelection', function() {

        beforeEach(function() {
            moduleFilterMock.text = sinon.stub();
            sinon.stub(app.lang, 'get');
            sinon.stub(view, 'getModuleFilter').returns(moduleFilterMock);
        });

        it('should update the text', function() {
            view.formatModuleSelection({text: 'goat'});

            // Arbitrary values based on the stub above.
            expect(moduleFilterMock.text).toHaveBeenCalledWith('goat');
            expect(app.lang.get).toHaveBeenCalledWith('LBL_MODULE');
        });
    });

    describe('formatModuleChoice', function() {
        it('should wrap the text', function() {
            result = view.formatModuleChoice({text: 'goat'});

            expect(result).toEqual('<div><span class="select2-match"></span>goat</div>');
        });
    });

    describe('handleModuleSelection', function() {
        var event;

        beforeEach(function() {
            sinon.stub(_, 'findWhere')
            .withArgs(0, {id: 1}).returns({stuff: 'goat'})
            .withArgs(0, {id: 2}).returns({})
            .withArgs(0, {id: 3}).returns({stuff: 'bear'})
            .withArgs(0, {id: 4}).returns({stuff: 'llama'});

            event = {val: 1};
            view._moduleFilterList = 0;
            view._selectedModule = 3;
            view._allModulesId = 4;
            moduleFilterMock.css = sinon.stub();

            sinon.stub(view, 'applyFilter');
            sinon.stub(_, 'isEmpty').returns(false)
                .withArgs({}).returns(true);
            sinon.stub(view, 'getFilterField').returns(filterFieldMock);
            sinon.stub(view, 'getModuleFilter').returns(moduleFilterMock);
        });

        it('should change the selected module', function() {
            view.handleModuleSelection(event, null);

            expect(view._selectedModule).toEqual(1);
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 1);
            expect(moduleFilterMock.css).toHaveBeenCalled();
            expect(view.applyFilter).toHaveBeenCalled();
        });

        it('should use the override and not change the selected module', function() {
            view.handleModuleSelection(event, 2);

            expect(view._selectedModule).toEqual(3);
            expect(filterFieldMock.select2).not.toHaveBeenCalled();
            expect(moduleFilterMock.css).not.toHaveBeenCalled();
            expect(view.applyFilter).not.toHaveBeenCalled();
        });

        it('should use the same module when args are null', function() {
            event.val = null;
            view.handleModuleSelection(event, null);

            expect(view._selectedModule).toEqual(3);
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 3);
            expect(moduleFilterMock.css).toHaveBeenCalled();
            expect(view.applyFilter).toHaveBeenCalled();
        });

        it('should use all modules ID when all else fails', function() {
            event.val = null;
            view._selectedModule = null;
            view.handleModuleSelection(event, null);

            expect(view._selectedModule).toEqual(4);
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 4);
            expect(moduleFilterMock.css).toHaveBeenCalled();
            expect(view.applyFilter).toHaveBeenCalled();
        });
    });

    describe('applyFilter', function() {

        beforeEach(function() {
            view._allModulesId = 4;
            view._selectedModule = 3;
            view._currentSearch = 0;

            sinon.stub(view, '_toggleClearQuickSearchIcon');
            sinon.stub(view.context, 'trigger');
            sinon.stub(_, 'isEmpty').returns(false)
                .withArgs(0).returns(true);

        });

        it('should clear the icon and search the given module', function() {
            view.applyFilter();

            expect(view._toggleClearQuickSearchIcon).toHaveBeenCalledWith(false);
            expect(view.context.trigger).toHaveBeenCalledWith('compose:addressbook:search', [3], 0);
        });

        it('should have the icon and search the given module', function() {
            view._currentSearch = 1;
            view.applyFilter();

            expect(view._toggleClearQuickSearchIcon).toHaveBeenCalledWith(true);
            expect(view.context.trigger).toHaveBeenCalledWith('compose:addressbook:search', [3], 1);
        });

        it('should clear the icon and search ALL modules', function() {
            view._selectedModule = 4;
            view.applyFilter();

            expect(view._toggleClearQuickSearchIcon).toHaveBeenCalledWith(false);
            expect(view.context.trigger).toHaveBeenCalledWith('compose:addressbook:search', [], 0);
        });

        it('should have the icon and search ALL modules', function() {
            view._selectedModule = 4;
            view._currentSearch = 'Bobs Axes';
            view.applyFilter();

            expect(view._toggleClearQuickSearchIcon).toHaveBeenCalledWith(true);
            expect(view.context.trigger).toHaveBeenCalledWith('compose:addressbook:search', [], 'Bobs Axes');
        });
    });

    describe('_toggleClearQuickSearchIcon', function() {
        var $mock; // jQuery object mock.

        beforeEach(function() {
            $mock = {};
            sinon.stub(view, '$').returns($mock);
            $mock.append = sinon.stub();
            $mock.remove = sinon.stub();

        });

        it('should add the icon', function() {
            $mock[0] = false;
            view._toggleClearQuickSearchIcon(true);

            expect($mock.append).toHaveBeenCalled();
            expect($mock.remove).not.toHaveBeenCalled();
        });

        it('should remove the icon', function() {
            $mock[0] = true;
            view._toggleClearQuickSearchIcon(false);

            expect($mock.append).not.toHaveBeenCalled();
            expect($mock.remove).toHaveBeenCalled();
        });

        it('should not replace an existing icon', function() {
            $mock[0] = true;
            view._toggleClearQuickSearchIcon(true);

            expect($mock.append).not.toHaveBeenCalled();
            expect($mock.remove).not.toHaveBeenCalled();
        });
    });

    describe('clearInput', function() {
        var $mock; // jQuery object mock.

        beforeEach(function() {
            $mock = {};
            sinon.stub(view, '$').returns($mock);
            $mock.val = sinon.stub();

            view._currentSearch = 'Patar Fwee Fwee';
            view._selectedModule = 'Goat Food';
            view._allModulesId = 7;

            sinon.stub(view, 'applyFilter');
            sinon.stub(view, 'getFilterField').returns(filterFieldMock);
        });

        it('should clear the search stuff and change the select element', function() {
            filterFieldMock.length = 1;
            view.clearInput();

            expect(view._currentSearch).toEqual('');
            expect(view._selectedModule).toEqual(7);
            expect($mock.val).toHaveBeenCalledWith('');
            expect(filterFieldMock.select2).toHaveBeenCalledWith('val', 7);
            expect(view.applyFilter).toHaveBeenCalled();
        });

        it('should clear the input and not touch elements', function() {
            filterFieldMock.length = 0;
            view.clearInput();

            expect(view._currentSearch).toEqual('');
            expect(view._selectedModule).toEqual(7);
            expect($mock.val).toHaveBeenCalledWith('');
            expect(filterFieldMock.select2).not.toHaveBeenCalled();
            expect(view.applyFilter).toHaveBeenCalled();
        });
    });

});
