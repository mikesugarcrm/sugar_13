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
describe('ForecastWorksheets.View.RecordList', function() {
    var app,
        view,
        layout,
        moduleName = 'ForecastWorksheets',
        ctePlugin,
        context,
        result;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadFile('../include/javascript/sugar7', 'utils', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.loadFile('../include/javascript/sugar7/plugins', 'DirtyCollection', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadPlugin('DirtyCollection');

        app.user.set({'id': 'test_userid'});

        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'noaccess');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.testMetadata.addViewDefinition('list', {
            favorite: false,
            selection: {
                type: 'multi',
                actions: []
            },
            rowactions: {
                actions: []
            },
            panels: [{
                name: 'panel_header',
                header: true,
                fields: ['name', 'likely_case', 'best_case', 'worst_case']
            }]
        }, 'ForecastWorksheets');

        SugarTest.testMetadata.set();
        app.data.reset();

        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        let parentContext = new app.Context({
            selectedUser: app.user.toJSON(),
            selectedTimePeriod: 'test_timeperiod',
            selectedRanges: [],
            selectedTimePeriodStartEnd: {
                'start': '2014-01-01',
                'end': '2014-03-31'
            },
            module: 'Forecasts'
        });
        let context = new app.Context({
            module: moduleName,
        });
        context.parent = parentContext;
        context.prepare();

        app.routing.start();

        layout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        view = SugarTest.createView('base', moduleName, 'recordlist', null, context, true, layout, true);
    });

    afterEach(function() {
        result = null;
        app.router.stop();
        sinon.restore();
        view.dispose();
        layout.dispose();
        view = null;
        layout = null;
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['ClickToEdit'];
        delete app.plugins.plugins['view']['DirtyCollection'];
        app.user.unset('id');
    });

    it('should have default recordlist plugins defined', function() {
        expect(_.indexOf(view.plugins, 'ListColumnEllipsis')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'ErrorDecoration')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'Editable')).not.toEqual(-1);
    });

    it('should have additional plugins defined', function() {
        expect(_.indexOf(view.plugins, 'ClickToEdit')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'DirtyCollection')).not.toEqual(-1);
    });

    it('should not have ReorderableColumns plugin', function() {
        expect(_.indexOf(view.plugins, 'ReorderableColumns')).toEqual(-1);
    });

    it('should not have MassCollection plugin', function() {
        expect(_.indexOf(view.plugins, 'MassCollection')).toEqual(-1);
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.stub(view, '_isUserManager');
        });

        it('should calculate totals if the forecast user is not a manager type', function() {
            view._isUserManager.returns(false);
            view._render();
            expect(view.calculateTotals).not.toHaveBeenCalled();
        });

        it('should not calculate totals if the forecast user is a manager type', function() {
            view._isUserManager.returns(true);
            view._render();
            expect(view.calculateTotals).not.toHaveBeenCalled();
        });
    });

    describe('renderCallback', function() {
        beforeEach(function() {
            sinon.stub(view.layout, 'show').callsFake(function() {});
            sinon.stub(view.layout, 'hide').callsFake(function() {});
            sinon.stub(app.template, 'getView').callsFake(function() { return function() {}; });
        });

        it('should always be hidden', function() {
            sinon.stub(view.layout, 'isVisible').callsFake(function() { return true; });

            view.selectedUser.is_manager = true;
            view.selectedUser.showOpps = false;
            view.renderCallback();

            expect(view.layout.show).not.toHaveBeenCalled();
            expect(view.layout.hide).toHaveBeenCalled();
        });
    });

    describe('filteredCollection', function() {
        beforeEach(function() {
            // add some models
            var m1 = new Backbone.Model({'name': 'test1', 'commit_stage': 'include', 'date_closed': '2014-01-05'}),
                m2 = new Backbone.Model({'name': 'test2', 'commit_stage': 'include', 'date_closed': '2014-01-05'}),
                m3 = new Backbone.Model({'name': 'test3', 'commit_stage': 'exclude', 'date_closed': '2014-01-05'});

            view.collection.add([m1, m2, m3]);
        });
        afterEach(function() {
            view.filters = [];
            view.collection.reset();
            view.filteredCollection.reset();
        });

        it('with no filters, filteredCollection should contain 3 records', function() {
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
        it('with include filter, filteredCollection should contain 2 records', function() {
            view.filters = ['include'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(2);
        });
        it('with exclude filter, filteredCollection should contain 1 records', function() {
            view.filters = ['exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(1);
        });
        it('with 2 filters, filteredCollection should contain 3 records', function() {
            view.filters = ['include', 'exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
    });

    describe('checkForDraftRows', function() {
        beforeEach(function() {
            // Set that we can edit
            view.canEdit = true;

            context = app.context.getContext();
            context.set({
                module: 'Forecasts'
            });
            context.prepare();
            view.context.parent = context;

            sinon.stub(view, '_isUserManager');
            sinon.stub(context, 'trigger').callsFake(function() {});
        });

        afterEach(function() {
            view.collection.reset();
            view.context.parent = undefined;
        });

        describe('when collection is empty', function() {
            it('should not trigger event when date is undefined', function() {
                view.checkForDraftRows(undefined);
                expect(view.context.parent.trigger).not.toHaveBeenCalled();
            });
        });

        describe('when collection has models', function() {
            beforeEach(function() {
                view.collection.add(new Backbone.Model({
                    name: 'test1',
                    date_modified: '2013-05-14 16:20:15',
                    date_closed: '2014-01-05'
                }));
            });

            it('should not trigger event', function() {
                view.checkForDraftRows('2013-05-14 16:21:15');
                expect(view.context.parent.trigger).not.toHaveBeenCalled();
            });

            it('should trigger event', function() {
                view.checkForDraftRows('2013-05-14 16:19:15');
                expect(view.context.parent.trigger).toHaveBeenCalled();
            });

            it('should trigger when date is undefined and has rows', function() {
                view.checkForDraftRows(undefined);
                expect(view.context.parent.trigger).toHaveBeenCalled();
            });

            it('should call layout.once when layout not visible but can edit', function() {
                view._isUserManager.returns(true);
                sinon.stub(view.layout, 'once');

                view.checkForDraftRows(undefined);

                expect(view.layout.once).toHaveBeenCalled();
            });

            it('should not call layout.once when layout not visible and can not edit', function() {
                view._isUserManager.returns(true);
                view.canEdit = false;
                sinon.stub(view.layout, 'once');
                view.checkForDraftRows(undefined);

                expect(view.layout.once).not.toHaveBeenCalled();
            });
        });
    });

    describe('updateSelectedUser', function() {
        beforeEach(function() {
            sinon.stub(view.collection, 'fetch').callsFake(function() {});
        });
        afterEach(function() {
            view.canEdit = false;
        });

        it('should change canEdit to be true', function() {
            view.updateSelectedUser({id: 'test_userid'});
            expect(view.canEdit).toBeTruthy();
        });

        it('should change canEdit to be false', function() {
            view.updateSelectedUser({id: 'test_user2'});
            expect(view.canEdit).toBeFalsy();
        });

        it('should call collection.fetch() is_manager is False', function() {
            view.updateSelectedUser({id: 'test_user2', is_manager: false});
            expect(view.collection.fetch).toHaveBeenCalled();
        });

        it('should call collection.fetch() with is_manager is True and showOpps is True', function() {
            view.updateSelectedUser({id: 'test_userid', is_manager: true, showOpps: true});
            expect(view.collection.fetch).toHaveBeenCalled();
        });
    });

    describe('updateTimeperiod', function() {
        beforeEach(function() {
            sinon.stub(view.collection, 'fetch').callsFake(function() {});
        });

        it('should update selectedTimePeriod and call collection.fetch when user is not a manager', function() {
            sinon.stub(view, '_isUserManager').returns(false);
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(view.collection.fetch).toHaveBeenCalled();
        });

        it('should update selectedTimePeriod and not call collection.fetch when user is a manager', function() {
            sinon.stub(view, '_isUserManager').returns(true);
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(view.collection.fetch).not.toHaveBeenCalled();
        });
    });

    describe('saveWorksheet', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model({'hello': 'world'});
            sinon.stub(model, 'save').callsFake(function() {});
            view.collection.add(model);
        });

        afterEach(function() {
            view.collection.reset();
            model = undefined;
        });

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            model.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(model.save).toHaveBeenCalled();
        });

        describe('Forecasts worksheet save dirty models with correct timeperiod after timeperiod changes', function() {
            var model;
            beforeEach(function() {
                model = new Backbone.Model({'hello': 'world'});
                sinon.stub(model, 'save').callsFake(function() {});
                sinon.stub(view.collection, 'fetch').callsFake(function() {});
                view.collection.add(model);
            });

            afterEach(function() {
                view.collection.reset();
                model = undefined;
            });

            it('model should contain the old timeperiod id', function() {
                model.set({'hello': 'jon1'});
                view.updateSelectedTimeperiod('my_new_timeperiod');

                expect(view.saveWorksheet()).toEqual(1);
                expect(model.save).toHaveBeenCalled();
                expect(view.collection.fetch).toHaveBeenCalled();
                expect(model.get('timeperiod_id')).toEqual('test_timeperiod');
                expect(view.selectedTimeperiod).toEqual('my_new_timeperiod');
                expect(view.dirtyTimeperiod).toEqual(undefined);
            });
        });
    });

    describe('sync', function() {
        var options;
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function() {});
            sinon.stub(app.api, 'call').callsFake(function() {});
            sinon.stub(app.data, 'getSyncCallbacks').callsFake(function() {});
            options = {};
        });

        describe('timeperiod_id', function() {
            it('should be set to view.selectedTimeperiod', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.timeperiod_id)).toBeFalsy();
                expect(options.params.timeperiod_id).toEqual(view.selectedTimeperiod);
            });
        });

        describe('user_id', function() {
            it('should be set to view.selectedUser.id', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.user_id)).toBeFalsy();
                expect(options.params.user_id).toEqual(view.selectedUser.id);
            });
        });

        describe('orderBy should', function() {
            afterEach(function() {
                delete view.collection.orderBy;
            });

            it('not be set', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeTruthy();
            });

            it('be set', function() {
                view.collection.orderBy = {
                    'field' : 'best_case',
                    'direction' : '_desc',
                    'column_name' : 'best_case'
                };
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeFalsy();
                expect(options.params.order_by).toEqual('best_case:_desc');
            });

            it('should convert parent_name to name', function() {
                view.collection.orderBy = {
                    'field' : 'parent_name',
                    'direction' : '_desc',
                    'column_name' : 'parent_name'
                };
                view.sync('read', view.collection, options);
                expect(options.params.order_by).toEqual('name:_desc');
                expect(view.collection.orderBy.field).toEqual('parent_name');
            });
        });
    });

    describe('setRowActionButtonStates', function() {
        var fieldDef = {},
            field,
            viewFields = [];
        beforeEach(function() {
            fieldDef = {
                'event': 'list:preview:fire',
                'type': 'button',
                'name': 'test_btn'
            };
            viewFields = view.fields;

            field = SugarTest.createField('base', 'rowaction', 'rowaction', 'list', fieldDef);
            sinon.stub(field, 'setDisabled').callsFake(function() {});
            view.fields = [field];
        });

        afterEach(function() {
            view.fields = viewFields;
            delete field;
        });

        it('should call field.setDisabled(false)', function() {
            field.model.set('parent_deleted', 0);
            view.setRowActionButtonStates();
            expect(field.setDisabled).toHaveBeenCalledWith(false);
        });

        it('should call field.setDisabled(true) ', function() {
            field.model.set('parent_deleted', 1);
            view.setRowActionButtonStates();
            expect(field.setDisabled).toHaveBeenCalledWith(true);
        });
    });

    describe('when Tab is pressed', function() {
        var event;
        beforeEach(function() {
            event = {
                which: 9,
                shiftKey: false,
                preventDefault: function() {},
                target: $('<input value="test" />')
            };
            view._viewCurrentIndex = 0;
            view._viewCurrentCTEList = [
                $('<div>'),
                $('<div>'),
                $('<div>'),
                $('<div>')
            ];
        });

        it('should increment the _viewCurrentIndex when shift is not pressed', function() {
            view._viewHandleKeyDown(event);
            expect(view._viewCurrentIndex).toBe(1);
        });

        it('should should reset the _viewCurrentIndex to the end when shift is pressed', function() {
            event.shiftKey = true;
            view._viewHandleKeyDown(event);

            expect(view._viewCurrentIndex).toBe(3);
        });
    });

    describe('when resetCTEFields is called', function() {
        beforeEach(function() {
            view._viewCurrentIndex = 1;
            view._viewCurrentCTEList = [
                $('<div>'),
                $('<div>'),
                $('<div>'),
                $('<div>')
            ];
            sinon.stub(view.$el, 'find').callsFake(function() {
                return [
                    $('<div>'),
                    $('<div>'),
                    $('<div>')
                ];
            });
            view._viewResetCTEList();
        });

        it('should set _viewCurrentIndex to 0', function() {
            expect(view._viewCurrentIndex).toEqual(0);
        });
    });

    describe('getCommitTotals', function() {
        var m1,
            m2,
            m3,
            totals;
        beforeEach(function() {
            // add some models
            m1 = new Backbone.Model({
                'name': 'test1',
                'commit_stage': 'include',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });
            m2 = new Backbone.Model({
                'name': 'test2',
                'commit_stage': 'include',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });
            m3 = new Backbone.Model({
                'name': 'test3',
                'commit_stage': 'exclude',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });

            view.collection.add([m1, m2, m3]);
        });

        afterEach(function() {
            m1 = null;
            m2 = null;
            m3 = null;
            view.collection.reset();
        });

        it('will return object with correct values', function() {
            totals = view.getCommitTotals();

            expect(totals.likely_case).toEqual('1000');
            expect(totals.overall_amount).toEqual('1500');
        });

        it('will not include model that is outside timeperiod', function() {
            m1.set('date_closed', '2013-01-05');
            totals = view.getCommitTotals();

            expect(totals.likely_case).toEqual('500');
            expect(totals.overall_amount).toEqual('1000');
        });

        describe('filteredTotals', function() {
            var getStub;
            beforeEach(function() {
                getStub = sinon.stub(view.context, 'get')
                    .withArgs('selectedTimePeriodStartEnd').returns({end: '2014-03-31', start: '2014-01-01'});
            });

            it('will only include items when filter is include', function() {
                getStub.withArgs('selectedRanges').returns(['include']);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual('1000');
                expect(totals.overall_amount).toEqual('1500');
            });

            it('will include all items when filter is include and exclude', function() {
                getStub.withArgs('selectedRanges').returns(['include', 'exclude']);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual('1500');
                expect(totals.overall_amount).toEqual('1500');
            });

            it('will include all items when filter is empty', function() {
                getStub.withArgs('selectedRanges').returns([]);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual('1500');
                expect(totals.overall_amount).toEqual('1500');
            });
        });
    });
});
