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
describe('View.Views.Base.DriWorkflowsHeader', function() {
    let app;
    let view;
    let context;
    let layout;
    let initOptions;
    let moduleName = 'Accounts';
    let viewName = 'dri-workflows-header';
    let layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadPlugin('ToggleMoreLess');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: []
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();
        layout = SugarTest.createLayout('base', moduleName, 'dashboard');
        view = SugarTest.createView('base', moduleName, viewName, {}, context, null, layout);
        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('should call the initialize function and initialze some properties', function() {
            view.access = true;
            sinon.stub(app.user.lastState, 'get');
            sinon.stub(app.user.lastState, 'key');
            sinon.stub(view, 'listenTo');
            sinon.stub(view, 'render');
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
            view.initialize(initOptions);
            expect(app.user.lastState.get).toHaveBeenCalled();
            expect(app.user.lastState.key).toHaveBeenCalled();
            expect(view.listenTo).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
            expect(view.access).toBe(false);
            expect(view._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('checkUserLimitSuccess', function() {
        it('should set grace period message on successful check-user-limit api call', function() {
            let response = {
                remaining_days: '2',
            };
            sinon.stub(app.lang, 'get').returns('User limit has reached, upgrade the license.');
            view.gracePeriodMessage = 'success';
            sinon.stub(view, '_render');
            view.checkUserLimitSuccess(response);
            expect(app.lang.get).toHaveBeenCalled();
            expect(view._render).toHaveBeenCalled();
            expect(view.gracePeriodMessage).toBe('User limit has reached, upgrade the license.');
        });
    });

    describe('_toggleMoreLess', function() {
        it('should toggle the more and less view', function() {
            let moreLess = 'Accounts';
            view._toggleMoreLess(moreLess);
            expect(view.context.attributes.moreLess).toBe('Accounts');
        });
    });

    describe('_initSortablePlugin', function() {
        it('should call parent function of view element', function() {
            sinon.stub(view.$el, 'parent').returns({
                sortable: sinon.stub(),
            });
            view._initSortablePlugin();

            expect(view.$el.parent).toHaveBeenCalled();
        });
    });

    describe('handleSort', function() {
        it('should call show method of alert and set method of user lastState', function() {
            sinon.stub(app.user.lastState, 'buildKey');
            sinon.stub(app.user.lastState, 'set');
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get');
            sinon.stub(view.$el, 'parent').returns({
                sortable: function() {
                    return ['Test Account', 'Test Contact'];
                },
            });
            view.context.parent = new Backbone.Model();
            view.handleSort();

            expect(app.alert.show).toHaveBeenCalled();
            expect(app.user.lastState.set).toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        it('should call view.stopListening  with view._super_dispose', function() {
            sinon.stub(view, 'stopListening');
            view._dispose();
            expect(view.stopListening).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
