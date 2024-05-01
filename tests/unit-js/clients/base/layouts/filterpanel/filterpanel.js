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

describe("Base.Layout.Filterpanel", function(){

    var app, layout, getModuleStub, activityStreamEnabled = true;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        getModuleStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Filter Panel", function() {
        var oLastState;
        beforeEach(function() {
            getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
                return {activityStreamEnabled: activityStreamEnabled};
            });
            oLastState = app.user.lastState;
            app.user.lastState = {
                key: function(){},
                get: function(){},
                set: function(){},
                register: function(){}
            };
            layout = SugarTest.createLayout("base", "Accounts", "filterpanel", {});
        });
        afterEach(function () {
            app.user.lastState = oLastState;
        });

        describe('initialize', function() {

            it('should trigger filterpanel:change:module', function() {
                var spy = sinon.spy();
                layout.off();
                layout.on('filterpanel:change:module', spy);

                layout.initialize(layout.options);
                expect(spy).toHaveBeenCalled();
            });

            it('should initialize the filterOptions object', function() {
                // Verify the filter options fallback chain.
                layout.options.meta = layout.options.meta || {};
                layout.options.meta.filter_options = {
                    stickiness: false,
                    show_actions: false
                };
                layout.context.set('filterOptions', {
                    show_actions: true
                });

                layout.initialize(layout.options);

                expect(layout.context.get('filterOptions').stickiness).toEqual(false);
                expect(layout.context.get('filterOptions').show_actions).toEqual(true);
            });
        });


        it("should trigger `filter:reinitialize` on render", function() {
            var spy = sinon.spy();
            layout.on('filter:reinitialize', spy);
            layout.render();
            expect(spy).toHaveBeenCalled();
        });

        describe('events', function(){
           it('should update current module and link on filter change', function(){
               layout.trigger('filterpanel:change:module','test','testLink');
               expect(layout.currentModule).toEqual('test');
               expect(layout.currentLink).toEqual('testLink');
           });
        });

        describe('applying last filter when a change happens on list view', function() {
            var collection, origFilterDef, triggerStub;
            beforeEach(function() {
                collection = new Backbone.Collection();
                triggerStub = sinon.stub(layout, 'trigger');
                //Fake quicksearch field
                var $input = $('<input>').addClass('search-name').val('query test');
                $('<div>').addClass('search').append($input[0]).appendTo(layout.$el);
            });
            afterEach(function() {
                triggerStub.restore();
            });
            it('should trigger filtering if no condition', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection);

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should trigger filtering because the filter contains $favorites (1)', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}, {$favorite: ''}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should trigger filtering because the filter contains $favorites (1)', function() {
                //Fake original filter def
                origFilterDef = {$favorite: ''};
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
            });
            it('should not trigger filtering because the filter does not contain $favorite', function() {
                //Fake original filter def
                origFilterDef = [{field1: { $equals: 'value1'}}, {field2: { $starts: 'value2'}}];
                collection.origFilterDef = origFilterDef;

                //Call the method
                layout.applyLastFilter(collection, 'favorite');

                expect(triggerStub).not.toHaveBeenCalled();
            });
        });
    });

    describe('disableActivityStreamToggle', function() {
        it('should set activity stream toggle to inactive when activity stream not enabled', function() {
            getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
                return {activityStreamEnabled: activityStreamEnabled};
            });
            var meta = {'availableToggles': [
                {'name': 'list', 'icon': 'sicon sicon-data-table', 'label': 'LBL_LISTVIEW'},
                {'name': 'activitystream', 'icon': 'sicon sicon-clock', 'label': 'LBL_ACTIVITY_STREAM'}
            ], 'components': [
                {'layout': 'filter', 'targetEl': '.filter', 'position': 'prepend'},
                {'view': 'filter-actions', 'targetEl': '.filter-options'},
                {'view': 'filter-rows', 'targetEl': '.filter-options'},
                {'layout': 'activitystream', 'context': {'module': 'Activities'}},
                {'layout': 'list'}
            ]};
            activityStreamEnabled = false;

            layout = SugarTest.createLayout('base', 'Accounts', 'filterpanel', meta);

            var activityStreamToggle = _.find(layout.meta.availableToggles, function(toggle) {
                return toggle.name === 'activitystream';
            });

            expect(activityStreamToggle.disabled).toEqual(true);
            expect(activityStreamToggle.label).toEqual('LBL_ACTIVITY_STREAM_DISABLED');

            activityStreamEnabled = true;
        });

    });

    describe('disablePipelineToggle', function() {
        it('should set pipeline toggle to inactive when pipeline not enabled for module', function() {
            var meta = {'availableToggles': [
                {'name': 'pipeline', 'label': 'LBL_PIPELINE_VIEW_BTN', 'route': 'pipeline'},
                {'name': 'list', 'icon': 'sicon sicon-data-table', 'label': 'LBL_LISTVIEW'},
                {'name': 'activitystream', 'icon': 'sicon sicon-clock', 'label': 'LBL_ACTIVITY_STREAM'}
            ], 'components': [
                {'layout': 'filter', 'targetEl': '.filter', 'position': 'prepend'},
                {'view': 'filter-actions', 'targetEl': '.filter-options'},
                {'view': 'filter-rows', 'targetEl': '.filter-options'},
                {'layout': 'activitystream', 'context': {'module': 'Activities'}},
                {'layout': 'list'}
            ]};

            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    is_setup: false
                };
            });

            layout = SugarTest.createLayout('base', 'Leads', 'filterpanel', meta);

            var pipelineToggle = _.find(layout.meta.availableToggles, function(toggle) {
                return toggle.name === 'pipeline';
            });

            expect(pipelineToggle.disabled).toEqual(true);
            expect(pipelineToggle.label).toEqual('LBL_VISUAL_PIPELINE_DISABLED');

            app.metadata.getModule.restore();
        });
    });
});
