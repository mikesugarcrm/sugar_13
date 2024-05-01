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
describe('Base.Layout.DriWorkflows', function() {
    let app;
    let layout;
    let context;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'layout', 'dri-workflows');
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: new Backbone.Collection(),
            _fetchCalled: false,
            parentModel: new Backbone.Model(),
            parentModule: 'dri-workflows',
        });
        context.prepare();
        layout = SugarTest.createLayout('base', 'Contacts', 'dri-workflows', {}, context);
        initOptions = {
            context: context,
        };
        sinon.stub(layout, '_super');
    });

    afterEach(function() {
        layout._components = null;
        sinon.restore();
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('should call the initialize function and initialze some properties', function() {
            sinon.stub(layout, 'listenTo');
            sinon.stub(layout, 'toggleMoreLess');
            sinon.stub(layout, 'addFiltersInCollection');
            layout.reloadJourneys = sinon.stub();
            layout.initialize(initOptions);

            expect(layout.listenTo).toHaveBeenCalled();
            expect(layout.toggleMoreLess).toHaveBeenCalled();
            expect(layout.addFiltersInCollection).toHaveBeenCalled();
            expect(layout._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('_render', function() {
        it('should call the hide function properly if it is enabled', function() {
            sinon.stub(layout, 'hide');
            layout.enabled = false;
            layout._render();
            expect(layout.hide).toHaveBeenCalled();
            expect(layout._super).toHaveBeenCalledWith('_render');
        });
    });

    describe('toggleMoreLess', function() {
        it('should call toggleMoreLess to Toggle between more and less views', function() {
            sinon.stub(layout, 'loadData');
            layout.context.set('moreLess', 'more');
            layout.loadDataClicked = false;
            layout.toggleMoreLess();
            expect(layout.loadData).toHaveBeenCalled();
        });
    });

    describe('syncNewJourneys', function() {
        beforeEach(function() {
            layout._components = [
                {
                    name: 'dri-workflow',
                    model: new Backbone.Model({'state': 'completed'}),
                    MORE_LESS_STATUS: {
                        MORE: 'more',
                        LESS: 'less',
                    },
                    addRemoveClasses: sinon.stub(),
                },
            ];
            sinon.stub(layout, 'loadData');
        });

        it('should call loadData method', function() {
            layout.syncNewJourneys();
            expect(layout.loadData).toHaveBeenCalled();
        });

        it('should call addRemoveClasses method', function() {
            layout.syncNewJourneys();
            expect(layout._components[0].addRemoveClasses).toHaveBeenCalledWith('less');
        });
    });

    describe('cleanJourneys', function() {
        it('should remove all journey views', function() {
            sinon.stub(layout.collection, 'get').returns(false);
            sinon.stub(layout, 'removeJourneyView');
            layout._components = {
                'component': {
                    name: 'dri-workflow',
                    model: {
                        id: '99',
                    },
                },
            };

            layout.cleanJourneys();
            expect(layout.removeJourneyView).toHaveBeenCalled();
        });
    });

    describe('reloadJourneys', function() {
        it('should reload all journey view data', function() {
            layout.loadDataClicked = true;
            sinon.stub(layout, 'addFiltersInCollection');
            sinon.stub(layout, 'removeJourneyViews');
            sinon.stub(layout, 'loadData');
            sinon.stub(layout.context, 'resetLoadFlag');
            sinon.stub(layout, 'getComponent').returns(layout);
            layout.loadRemoval = true;
            layout.reloadJourneys();
            expect(layout.addFiltersInCollection).toHaveBeenCalled();
            expect(layout.removeJourneyViews).toHaveBeenCalled();
            expect(layout.loadData).toHaveBeenCalled();
            expect(layout.getComponent).toHaveBeenCalled();
            expect(layout.context.resetLoadFlag).toHaveBeenCalled();
        });
    });

    describe('_populateJourneys', function() {
        beforeEach(function() {
            layout.context.parent = new Backbone.Model();
            sinon.stub(layout, 'addJourney');
            sinon.stub(app.user.lastState, 'buildKey');
        });

        it('should call collection each method and should not call getSortedJourneys method', function() {
            sinon.stub(app.user.lastState, 'get');
            sinon.stub(layout.collection, 'each');

            layout._populateJourneys();

            expect(layout.collection.each).toHaveBeenCalled();
            expect(layout.getSortedJourneys).not.toHaveBeenCalled();
        });

        it('should call addJourney and getSortedJourneys', function() {
            sinon.stub(app.user.lastState, 'get').returns(['Test Account']);
            sinon.stub(layout, 'getSortedJourneys').returns(['Test Journey']);

            layout._populateJourneys();

            expect(layout.addJourney).toHaveBeenCalled();
            expect(layout.getSortedJourneys).toHaveBeenCalled();
        });
    });

    describe('checkHide', function() {
        it('should call checkHide to toggles the display of all journey views according to the state', function() {
            let cJourney = {
                length: 7,
            };
            layout.collection.length = 7;
            layout.loadDataClicked = true;
            layout.context.set('showActiveJourneys', false);
            sinon.stub(layout.collection, 'filter').returns(cJourney);
            layout.checkHide();
            expect(layout.collection.filter).toHaveBeenCalled();
        });
    });

    describe('loadData', function() {
        it('should call loadData to load the data properly', function() {
            layout.context._fetchCalled = true;
            layout.collection.dataFetched = true;
            layout.loadDataClicked = true;
            sinon.stub(layout.context, 'loadData');
            sinon.stub(layout, 'render');
            layout.loadData();
            expect(layout.context.loadData).toHaveBeenCalled();
            expect(layout.render).toHaveBeenCalled();
            expect(layout.context._fetchCalled).toBe(false);
            expect(layout.collection.dataFetched).toBe(false);
        });
    });

    describe('addJourney', function() {
        it('should initialize a new journey panel, adds it to the layout and loads the data', function() {
            let journey = {
                id: 99,
                on: function() {
                    return true;
                }
            };
            let view = {
                loadData: sinon.stub(),
                render: sinon.stub(),
                toggleMoreLess: sinon.stub(),
                MORE_LESS_STATUS: {
                    MORE: 'more',
                },
            };
            layout._addedIds = [];
            sinon.stub(layout.context, 'getChildContext').returns(context);
            sinon.stub(layout, 'createComponentFromDef').returns(view);
            sinon.stub(layout, 'getActiveOrArchiveMode').returns('active');
            sinon.stub(layout, 'addComponentAfterHeader');
            layout.addJourney(journey);

            expect(layout.context.getChildContext).toHaveBeenCalled();
            expect(layout.createComponentFromDef).toHaveBeenCalled();
            expect(layout.addComponentAfterHeader).toHaveBeenCalled();
        });
    });

    describe('removeJourneyView', function() {
        it('should remove a journey view', function() {
            let view = {
                model: {
                    id: 7,
                },
                dispose: function() {
                    return true;
                }
            };
            layout._addedIds = [];
            sinon.stub(layout, 'removeComponent');
            sinon.stub(layout, 'setSmartGuideCount');
            layout.removeJourneyView(view);
            expect(layout.removeComponent).toHaveBeenCalled();
        });
    });

    describe('removeJourney', function() {
        it('should remove all journey view', function() {
            sinon.stub(layout, 'removeJourneyView');
            layout._components = {
                'component': {
                    name: 'dri-workflow',
                    model: new Backbone.Model({'id': 'testId'}),
                },
            };

            layout.removeJourney(new Backbone.Model({'id': 'testId'}));
            expect(layout.removeJourneyView).toHaveBeenCalled();
        });
    });

    describe('startJourneyClicked', function() {
        it('should start a new journey related to the parent', function() {
            let parentModel = {
                get: function() {
                    return 99;
                },
            };
            let model = {
                'dri_workflow_template_id': '99',
                set: function() {
                    return true;
                },
                get: function() {
                    return 'dri_workflow_template_id';
                },
                module: 'Accounts',
            };
            layout.startingJourney = false;
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
            sinon.stub(layout.context, 'get').returns(parentModel);
            sinon.stub(layout, 'disablingJourneyAndStartLoading');
            layout.startJourneyClicked(model);
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
            expect(layout.disablingJourneyAndStartLoading).toHaveBeenCalled();
            expect(layout.startingJourney).toBe(true);
        });
    });

    describe('createJourneySuccess', function() {
        it('should create Customer Journey success handler', function() {
            let parentData = {
                get: function() {
                    return 99;
                },
            };
            let parms = {
                'item': 'dri-workflow',
                'key': '99',
            };
            let diff = [{
                'key': '99',
            }];
            let parentModel = {
                getChangeDiff: sinon.stub().returns(diff),
                changedAttributes: sinon.stub().returns(parms),
                trigger: sinon.stub(),
                set: sinon.stub(),
                getSynced: function() {
                    return true;
                }
            };
            sinon.stub(app.utils, 'deepCopy').returns(parentData);
            sinon.stub(app.data, 'createBean');
            sinon.stub(layout.context, 'get').returns(parentModel);
            sinon.stub(layout, '_updateJourneysOrder');
            layout.createJourneySuccess({parentData: parentData});

            expect(app.utils.deepCopy).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(layout.context.get).toHaveBeenCalled();
        });
    });

    describe('disablingJourneyAndStartLoading', function() {
        it('should disable the Smart Guide and show loader', function() {
            sinon.stub(layout.$el, 'width').returns(7);
            sinon.stub(layout.$el, 'height').returns(8);
            sinon.stub(layout.$el, 'prepend');
            layout.disablingJourneyAndStartLoading();
            expect(layout.$el.width).toHaveBeenCalled();
            expect(layout.$el.height).toHaveBeenCalled();
            expect(layout.$el.prepend).toHaveBeenCalled();
        });
    });

    describe('updateJourneysOrder', function() {
        beforeEach(function() {
            layout.context.parent = new Backbone.Model();
            sinon.stub(app.user.lastState, 'buildKey');
            sinon.stub(app.user.lastState, 'set');
        });

        it('should call get, set and buildKey methods of user lastState', function() {
            sinon.stub(app.user.lastState, 'get').returns(['Test Account']);
            layout._updateJourneysOrder();

            expect(app.user.lastState.buildKey).toHaveBeenCalled();
            expect(app.user.lastState.get).toHaveBeenCalled();
            expect(app.user.lastState.set).toHaveBeenCalled();
        });

        it('should call get and buildKey methods and should not call set method of user lastState', function() {
            sinon.stub(app.user.lastState, 'get');
            layout._updateJourneysOrder();

            expect(app.user.lastState.buildKey).toHaveBeenCalled();
            expect(app.user.lastState.get).toHaveBeenCalled();
            expect(app.user.lastState.set).not.toHaveBeenCalled();
        });
    });

    describe('addFiltersInCollection', function() {
        it('should Add the filter in collection of archived on the base of selected value in widget', function() {
            sinon.stub(layout, 'getActiveOrArchiveMode').returns('active');
            layout.addFiltersInCollection();
            expect(layout.getActiveOrArchiveMode).toHaveBeenCalled();
            expect(layout.collection.filterDef.archived).toBe(0);
        });
        it('should Add the filter in collection of archived on the base of selected value in widget', function() {
            sinon.stub(layout, 'getActiveOrArchiveMode').returns('archived');
            layout.addFiltersInCollection();
            expect(layout.getActiveOrArchiveMode).toHaveBeenCalled();
            expect(layout.collection.filterDef.archived).toBe(1);
        });
    });

    describe('getActiveOrArchiveMode', function() {
        it('should return the active or archive mode saved by user in widget setting', function() {
            sinon.stub(app.CJBaseHelper, 'getValueFromCache').returns('dri-workflows');
            expect(layout.getActiveOrArchiveMode()).toBe('dri-workflows');
            expect(app.CJBaseHelper.getValueFromCache).toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        it('should call layout.stopListening  with layout._super_dispose', function() {
            sinon.stub(layout, 'stopListening');
            layout._dispose();
            expect(layout.stopListening).toHaveBeenCalled();
            expect(layout._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
