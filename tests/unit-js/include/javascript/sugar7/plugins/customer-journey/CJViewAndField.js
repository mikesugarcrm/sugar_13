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
describe('Plugins.CJViewAndField', function() {
    let moduleName = 'Accounts';
    let view;
    let pluginsBefore;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        view = SugarTest.createView('base', moduleName, 'record');
        view.fields = [
            {
                name: 'activity_not_applicable_button',
                def: {
                    name: 'activity_not_applicable_button',
                    type: 'rowaction'
                },
                model: {
                    module: 'Tasks',
                    get: sinon.stub().returns('incomplete'),
                },
                hide: sinon.stub(),
            },
            {
                name: 'journey_unarchive_button',
                def: {
                    name: 'journey_unarchive_button',
                    type: 'rowaction'
                },
                model: {
                    module: 'Tasks',
                    get: sinon.stub().returns(false),
                },
                hide: sinon.stub(),
            },
            {
                name: 'journey_archive_button',
                def: {
                    name: 'journey_archive_button',
                    type: 'rowaction'
                },
                model: {
                    module: 'Tasks',
                    get: sinon.stub().returns('incomplete'),
                },
                hide: sinon.stub(),
            },
            {
                name: 'activity_start_button',
                def: {
                    name: 'activity_start_button',
                    type: 'rowaction'
                },
                model: {
                    module: 'Meetings',
                },
            },
        ];
        pluginsBefore = view.plugins;
        view.plugins = ['CJViewAndField'];
        SugarTest.loadPlugin('CJViewAndField', 'customer-journey');
        SugarTest.app.plugins.attach(view, 'view');
        sinon.stub(view, 'listenTo');
        view.trigger('init');
    });

    afterEach(function() {
        view.plugins = pluginsBefore;
        view.fields = null;
        view.dispose();
        app.view.reset();
        sinon.restore();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
    });

    describe('toggleButtons', function() {
        it('if the provided fields are of rowaction type it should hide them', function() {
            sinon.stub(view, 'isClosableForNotApplicable');
            sinon.stub(view, 'hideField');
            view.toggleButtons();
            expect(view.isClosableForNotApplicable).toHaveBeenCalled();
            expect(view.hideField).toHaveBeenCalled();
        });
    });

    describe('isClosableForNotApplicable', function() {
        it('The isClosableForNotApplicable function will return true and hence the field will hide', function() {
            sinon.stub(view, 'isBlocked').returns(false);
            sinon.stub(view, 'isBlockedByStages').returns(false);
            sinon.stub(view, 'hideField');
            view.toggleButtons();
            expect(view.hideField).toHaveBeenCalled();
        });
    });

    describe('isClosableForNotApplicable', function() {
        it('The isClosableForNotApplicable function will return true and hence the field will hide', function() {
            sinon.stub(view, 'isBlocked').returns(false);
            sinon.stub(view, 'isBlockedByStages').returns(false);
            sinon.stub(view, 'hideField');
            view.toggleButtons();
            expect(view.hideField).toHaveBeenCalled();
        });
    });

    describe('isClosableForNotApplicable', function() {
        it('The isClosableForNotApplicable function will return false and hence the field will hide', function() {
            sinon.stub(view, 'isBlocked').returns(false);
            sinon.stub(view, 'isBlockedByStages').returns(false);
            sinon.stub(view, 'hideField');
            view.toggleButtons();
            expect(view.hideField).toHaveBeenCalled();
        });
        it('The isStartable function will return false and hence the field will hide', function() {
            sinon.stub(view, 'hideField');
            view.toggleButtons();
            expect(view.hideField).toHaveBeenCalled();
        });
    });

    describe('createStageData', function() {
        beforeEach(function() {
            view.stageModel = new Backbone.Model();
        });

        afterEach(function() {
            view.stageModel = null;
        });

        it('The function should return a usable object made from stage\'s data', function() {
            view.stageModel.set('name', 'Stage Record');
            view.stageModel.set('id', '123-456');
            expect(view.createStageData(view.stageModel)).toEqual({
                data: {
                    id: '123-456',
                    name: 'Stage Record'
                },
                activities: {},
                model: view.stageModel,
                stateClass: '',
            });
        });
    });

    describe('isBlocked', function() {
        beforeEach(function() {
            view.tasksModel = new Backbone.Model();
        });

        afterEach(function() {
            view.tasksModel = null;
        });

        it('Should return blocked_by string length', function() {
            view.tasksModel.set('blocked_by', '123-456');
            expect(view.isBlocked(view.tasksModel)).toEqual(7);
        });

        it('Should return undefined because blocked_by is not set', function() {
            expect(view.isBlocked(view.tasksModel)).toBe(undefined);
        });
    });

    describe('isBlocked', function() {
        beforeEach(function() {
            view.tasksModel = new Backbone.Model();
        });

        afterEach(function() {
            view.tasksModel = null;
        });

        it('Should return id length because activity is blocked', function() {
            view.tasksModel.set('blocked_by', '123-456');
            expect(view.isBlocked(view.tasksModel)).toEqual(7);
        });

        it('Should return undefined because activity is not blocked', function() {
            expect(view.isBlocked(view.tasksModel)).toBe(undefined);
        });
    });

    describe('isBlockedByStages', function() {
        beforeEach(function() {
            view.tasksModel = new Backbone.Model();
        });

        afterEach(function() {
            view.tasksModel = null;
        });

        it('Should return blocked_by_stages string length', function() {
            view.tasksModel.set('blocked_by_stages', '123-456');
            expect(view.isBlockedByStages(view.tasksModel)).toEqual(7);
        });

        it('Should return undefined because blocked_by_stages is not set', function() {
            expect(view.isBlockedByStages(view.tasksModel)).toBe(undefined);
        });
    });

    describe('getDueDateInfo', function() {
        beforeEach(function() {
            view.tasksModel = new Backbone.Model();
        });

        afterEach(function() {
            view.tasksModel = null;
        });

        it('Should return false because the activitys due date is not present', function() {
            view.tasksModel.set('blocked_by_stages', '123-456');
            view.tasksModel.model = 'Tasks';
            sinon.stub(view, 'isClosed');
            expect(view.getDueDateInfo(view.tasksModel)).toBeFalsy();
        });
    });

    describe('getStartDateInfo', function() {
        beforeEach(function() {
            view.tasksModel = new Backbone.Model();
        });

        afterEach(function() {
            view.tasksModel = null;
        });

        it('Should return false because the activity start date is not present', function() {
            view.tasksModel.set('blocked_by_stages', '123-456');
            view.tasksModel.model = 'Tasks';
            sinon.stub(view, 'isClosed');
            expect(view.getStartDateInfo(view.tasksModel)).toBeFalsy();
        });
    });
});
