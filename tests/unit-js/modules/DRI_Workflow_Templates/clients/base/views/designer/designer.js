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

describe('DRI_Workflow_Templates.Views.Designer', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'designer';
    let module = 'DRI_Workflow_Templates';
    let activity;

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        activity = new Backbone.Model();
        context = app.context.getContext({
            module: module,
            model: model,
            create: true
        });
        context.prepare(true);

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'dri-workflow');
        SugarTest.loadComponent('base', 'layout', 'base');
        SugarTest.loadPlugin('CJEvents', 'customer-journey');

        layout = SugarTest.createLayout(
            'base',
            null,
            'default',
            {},
            null,
            false
        );
        view = SugarTest.createView(
            'base',
            module,
            viewName,
            null,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();
        delete app.drawer;

        app = null;
        view = null;
        model = null;
        layout = null;
        context = null;
        activity = null;
    });

    describe('getTypeClass', function() {
        using('input', [
            {
                activity_type: 'Tasks',
                type: 'Internal',
            },
            {
                activity_type: 'Calls',
                type: '',
            },
        ],

        function(input) {
            it('getTypeClass should return empty string if activity_type is not Tasks', function() {
                activity.set('type', input.type);
                activity.set('activity_type', input.activity_type);

                expect(view.getTypeClass(activity)).toBe(input.type);
            });
        });
    });

    describe('addActivity', function() {
        it('should call drawer open, data createBean and view getStageContextById functions', function() {
            let stage = new Backbone.Model({'id': '102bacfe-f838-11e6-a213-5254009e5526'});
            let stageContext = new Backbone.Model({model: new Backbone.Model()});
            stageContext.getChildContext = function() {};
            app.drawer = {
                open: $.noop
            };
            view.stages = {
                '102bacfe-f838-11e6-a213-5254009e5526': {
                    activities: {
                        0: {
                            data: {
                                sort_order: 3,
                            },
                        },
                    },
                }
            };
            sinon.stub(app.drawer, 'open');
            sinon.stub(app.data, 'createBean').returns(new Backbone.Model());
            sinon.stub(view, 'getStageContextById').returns(stageContext);

            view.addActivity(stage, 'DRI_Workflow_Templates');

            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(view.getStageContextById).toHaveBeenCalled();
        });
    });

    describe('reloadView', function() {
        it('should call view reloadData function if there is model', function() {
            sinon.stub(view, 'reloadData');
            view.reloadView(context, model);

            expect(view.reloadData).toHaveBeenCalled();
        });
    });

    describe('addSubActivity', function() {
        it('should call drawer open, data createBean and view getStageContextById functions', function() {
            let activity = new Backbone.Model({
                'id': '102bacfe-f838-11e6-a213-5254009e5526',
                'sort_order': 7,
                'dri_subworkflow_id': '158a346a-073a-11ed-aae0-b2805c40fe0c',
                'dri_subworkflow_template_id': 'c108bb4a-775a-11e9-b570-f218983a1c3e',
            });
            let stageContext = new Backbone.Model();
            stageContext.getChildContext = function() {};
            app.drawer = {
                open: $.noop
            };
            view.activitySortOrder = 'sort_order';
            view.activityStageId = 'dri_subworkflow_id';
            view.stages = {
                '158a346a-073a-11ed-aae0-b2805c40fe0c': {
                    activities: {
                        '102bacfe-f838-11e6-a213-5254009e5526': {
                            children: {
                                0: {
                                    model: new Backbone.Model({'sort_order': '2.5'}),
                                }
                            },
                        },
                    },
                }
            };
            sinon.stub(app.drawer, 'open');
            sinon.stub(app.data, 'createBean').returns(new Backbone.Model());
            sinon.stub(view, 'getStageContextById').returns(stageContext);

            view.addSubActivity(activity, 'DRI_Workflow_Templates');

            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(view.getStageContextById).toHaveBeenCalled();
        });
    });

    describe('loadCompleted', function() {
        beforeEach(function() {
            sinon.stub(view, 'toggleMoreLess');
            sinon.stub(view, '_super');
            view.MORE_LESS_STATUS = {MORE: true};
        });

        it('should not call toggleMoreLess', function() {
            view.disposed = true;
            view.loadCompleted();

            expect(view.toggleMoreLess).not.toHaveBeenCalled();
        });

        it('should call toggleMoreLess', function() {
            view.disposed = false;
            view.loadCompleted();

            expect(view.toggleMoreLess).toHaveBeenCalled();
        });
    });

    describe('getIconByType', function() {
        using('input', [
            {
                type: 'customer_task',
                icon: 'sicon sicon-star-fill',
            },
            {
                type: 'milestone',
                icon: 'sicon sicon-trophy icon-trophy',
            },
            {
                type: 'internal_task',
                icon: 'sicon sicon-user',
            },
            {
                type: 'agency_task',
                icon: 'sicon sicon-account',
            },
            {
                type: 'automatic_task',
                icon: 'sicon sicon-refresh',
            },
        ],

        function(input) {
            it('getIconByType function should return icon as input icon according to type', function() {
                expect(view.getIconByType(input.type)).toBe(input.icon);
            });
        });
    });

    describe('getStatusLabel', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get').callsFake(function(label, module) {
                return label === 'LBL_WIDGET_POINT' ? 'Point' : 'Points';
            });
        });

        it('should return status label as 0 Points', function() {
            expect(view.getStatusLabel(activity)).toBe('0 Points');
            expect(app.lang.get).toHaveBeenCalled();
        });

        it('should return status label as 1 Point', function() {
            activity.set('points', 1);

            expect(view.getStatusLabel(activity)).toBe('1 Point');
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('getIconTooltip', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'getAppListStrings').callsFake(function(name) {
                if (name === 'dri_workflow_task_templates_type_list') {
                    return {
                        '': '',
                        'customer_task': 'Customer Task',
                        'milestone': 'Milestone',
                        'internal_task': 'Internal Task',
                        'agency_task': 'Agency Task',
                    };
                } else {
                    return {
                        'Tasks': 'Task',
                        'Calls': 'Call',
                        'Meetings': 'Meeting',
                    };
                }
            });
        });

        it('getIconTooltip should return icon tooltip as Agency Task', function() {
            activity.set('type', 'agency_task');
            activity.set('activity_type', 'Tasks');

            expect(view.getIconTooltip(activity)).toBe('Agency Task');
            expect(app.lang.getAppListStrings).toHaveBeenCalled();
        });

        it('getIconTooltip should return icon tooltip as Call', function() {
            activity.set('type', '');
            activity.set('activity_type', 'Calls');

            expect(view.getIconTooltip(activity)).toBe('Call');
            expect(app.lang.getAppListStrings).toHaveBeenCalled();
        });
    });

    describe('addStageClick', function() {
        it('should call drawer open, context getChildContext and model getRelatedCollection functions', function() {
            let stage = new Backbone.Model({'sort_order': 5});
            let collection = {
                last: function() {
                    return new Backbone.Model();
                }
            };
            app.drawer = {
                open: $.noop
            };
            sinon.stub(app.drawer, 'open');
            sinon.stub(view.context, 'getChildContext');
            sinon.stub(app.data, 'createBean').returns(stage);
            sinon.stub(view.model, 'getRelatedCollection').returns(collection);

            view.addStageClick();

            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(view.context.getChildContext).toHaveBeenCalled();
            expect(view.model.getRelatedCollection).toHaveBeenCalled();
        });
    });
});
