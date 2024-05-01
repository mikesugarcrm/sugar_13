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
describe('View.Views.Base.ActivityTimelineBaseView', function() {
    var app;
    var context;
    var layout;
    var layoutName = 'base';
    var moduleName = 'Cases';
    var view;
    var viewName = 'activity-timeline-base';
    var viewModuleCard = 'activity-card-definition';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewModuleCard);
        SugarTest.loadComponent('base', 'layout', 'base');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                activity_modules: [
                    {
                        module: 'Calls',
                    },
                    {
                        module: 'Emails',
                    },
                    {
                        module: 'Messages',
                        link: 'message_invites',
                    },
                ],
            },
            moduleName
        );
        SugarTest.testMetadata.addViewDefinition(
            viewModuleCard,
            {
                module: 'Emails',
                record_date: 'date_sent',
                fields: [
                    'name',
                    'date_sent',
                ],
            },
            'Emails'
        );

        SugarTest.testMetadata.addViewDefinition(
            viewModuleCard,
            {
                module: 'Calls',
                fields: [
                    'name',
                    'status',
                ],
            },
            'Calls'
        );

        SugarTest.testMetadata.addViewDefinition(
            viewModuleCard,
            {
                module: 'Messages',
                fields: [
                    'name',
                    'direction',
                ],
                link: 'message_invites',
            },
            'Messages'
        );

        SugarTest.testMetadata.updateModuleMetadata('Calls', {});
        SugarTest.testMetadata.updateModuleMetadata('Emails', {});
        SugarTest.testMetadata.set();
        app.data.declareModels();

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();

        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {module: moduleName},
            context,
            false,
            layout
        );

        view.filter = {
            module: 'all_modules',
        };
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
    });

    describe('_getBaseModel', function() {
        it('should get model from parent context', function() {
            var caseModel = app.data.createBean('Cases');
            caseModel.set('_module', 'Cases');

            var parentContext = app.context.getContext();
            parentContext.set({
                module: 'Cases',
                rowModel: caseModel,
            });

            var mainContext = app.context.getContext();
            mainContext.set({module: 'Cases'});
            mainContext.parent = parentContext;

            var model = view._getBaseModel({
                context: mainContext,
            });

            expect(model).toEqual(caseModel);
        });
    });

    describe('_setActivityModulesAndFields', function() {
        it('should set activityModules and moduleFieldNames', function() {
            sinon.stub(view, 'getDefaultModules').returns(['Emails']);
            sinon.stub(view, 'getModulesCardMeta').withArgs('Emails').returns({
                fields: [
                    'name',
                    'date_sent',
                ],
                record_date: 'test_date',
            });
            view._setActivityModulesAndFields();

            expect(view.activityModules).toEqual(['Emails']);

            expect(view.moduleFieldNames).toEqual({
                Emails: ['name', 'date_sent'],
            });
            expect(view.recordDateFields).toEqual({
                Emails: 'test_date',
            });
        });
    });

    describe('_getModuleFieldMeta', function() {
        var getViewMetaStub;

        beforeEach(function() {
            getViewMetaStub = sinon.stub(app.metadata, 'getView');
            view.activityModules = ['Calls', 'Emails'];
            view.moduleFieldNames = {
                Calls: ['name', 'status'],
                Emails: ['name', 'date_sent']
            };
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should get field from preview meta', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                            {name: 'status'},
                        ]
                    }
                ]
            });

            getViewMetaStub.withArgs('Emails', 'preview').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                        ]
                    },
                    {
                        fields: [
                            {name: 'date_sent'},
                            {name: 'leave_me_alone'},
                        ],
                    }
                ]
            });

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(2);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(2);
        });

        it('should get field from record meta when preview meta not available', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Calls', 'record').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                        ]
                    },
                    {
                        fields: [
                            {name: 'status'},
                            {name: 'leave_me_alone'},
                        ],
                    }
                ]
            });

            getViewMetaStub.withArgs('Emails', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Emails', 'record').returns({
                panels: [
                    {
                        fields: [
                            {name: 'name'},
                        ]
                    },
                ]
            });

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(2);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(1);
        });

        it('should return empty fields when neither preview nor record meta is available', function() {
            getViewMetaStub.withArgs('Calls', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Calls', 'record').returns(undefined);

            getViewMetaStub.withArgs('Emails', 'preview').returns(undefined);
            getViewMetaStub.withArgs('Emails', 'record').returns(undefined);

            var fieldMeta = view._getModuleFieldMeta();
            expect(fieldMeta.Calls.panels[0].fields.length).toBe(0);
            expect(fieldMeta.Emails.panels[0].fields.length).toBe(0);
        });
    });

    describe('_initCollection', function() {
        var mixedBeanCollectionStub;

        beforeEach(function() {
            mixedBeanCollectionStub = sinon.stub(app.MixedBeanCollection, 'extend').returns(
                function MockCollectionConstructor() {
                    this.collection = 'fake_collection';
                }
            );
        });

        it('should not take action when any of base module, record or activity modules do not exists', function() {
            view.baseModule = 'Cases';
            view.baseRecord = undefined;
            view.activityModules = ['Calls'];

            view._initCollection();
            expect(mixedBeanCollectionStub).not.toHaveBeenCalled();
        });

        it('should create new mixed bean collection', function() {
            view.baseModule = 'Cases';
            view.baseRecord = app.data.createBean('Cases');
            view.activityModules = ['Calls', 'Emails'];

            view._initCollection();
            expect(view.relatedCollection.collection).toEqual('fake_collection');
        });
    });

    describe('loadData', function() {
        it('should fetch collection', function() {
            var fetchStub = sinon.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };

            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });
    });

    describe('fetchModels', function() {
        it('should fetch models', function() {
            var fetchStub = sinon.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };

            view.fetchModels();
            expect(fetchStub).toHaveBeenCalled();
        });

        it('should not fetch models when all models had been fetched', function() {
            var fetchStub = sinon.stub();
            view.relatedCollection = {
                fetch: fetchStub,
            };
            view.fetchCompleted = true;

            view.fetchModels();
            expect(fetchStub).not.toHaveBeenCalled();
        });
    });

    describe('_setIconClass', function() {
        it('should set icon class base on model type', function() {
            var moduleIcons = [
                {name: 'Calls', icon: 'sicon-phone-lg'},
                {name: 'Emails', icon: 'sicon-email-lg'},
                {name: 'Meetings', icon: 'sicon-meetings-lg'},
                {name: 'Notes', icon: 'sicon-note-lg'},
            ];

            sinon.stub(app.metadata, 'getModule').callsFake((module) => {
                const iconMeta = _.find(moduleIcons, (item) => item.name === module);
                return {name: module, icon: iconMeta.icon};
            });

            view.models = _.map(moduleIcons, function(module) {
                return app.data.createBean(module.name, {_module: module.name});
            });
            view._setIconClass();

            _.each(view.models, function(model, ind) {
                expect(model.get('icon_class')).toEqual(moduleIcons[ind].icon);
            });
        });
    });

    describe('_patchFieldsToModel', function() {
        it('should set fieldMeta to model based on its module type', function() {
            var fieldsMeta = [
                {name: 'Calls', meta: 'mock_call_meta'},
                {name: 'Emails', meta: 'mock_email_meta'},
                {name: 'Meetings', meta: 'mock_meeting_meta'},
                {name: 'Notes', meta: 'mock_note_meta'},
            ];

            var previewMeta = {};
            _.each(fieldsMeta, function(metaObj) {
                previewMeta[metaObj.name] = metaObj.meta;
            });

            view.meta = {preview: previewMeta};

            _.each(fieldsMeta, function(metaObj) {
                var model = app.data.createBean(metaObj.name, {_module: metaObj.name});
                view._patchFieldsToModel(model);
                expect(model.get('fieldsMeta')).toEqual(metaObj.meta);
            });
        });
    });

    describe('createRecord', function() {
        it('should open drawer with linked model', function() {
            var model = app.data.createBean(moduleName);
            view.createLinkModel = sinon.stub().returns(model);
            app.drawer = {
                open: sinon.stub()
            };
            view.createRecord(null, {module: 'Notes', link: 'notes'});
            expect(app.drawer.open.lastCall.args[0]).toEqual({
                layout: 'create',
                context: {
                    create: true,
                    module: 'Notes',
                    model: model
                }
            });
        });
    });

    describe('reloadData', function() {
        it('should reset collection and models', function() {
            var fetchStub = sinon.stub(view, 'fetchModels');
            var _showSkeletonStub = sinon.stub(view, '_showSkeleton');
            view.relatedCollection = {
                reset: sinon.stub(),
                resetPagination: sinon.stub()
            };
            view.fetchCompleted = true;
            view.models = [{id: 1}];
            view.reloadData();
            expect(view.relatedCollection.reset).toHaveBeenCalled();
            expect(view.relatedCollection.resetPagination).toHaveBeenCalled();
            expect(view.fetchCompleted).toBeFalsy();
            expect(view.models.length).toEqual(0);
            expect(_showSkeletonStub).toHaveBeenCalled();
            expect(fetchStub).toHaveBeenCalled();
        });
    });

    describe('createCard', function() {
        it('should init card components', function() {
            var initComponentsStub = sinon.stub();
            layout.initComponents = initComponentsStub;
            sinon.stub(app.view, 'createLayout').returns(layout);

            var model = app.data.createBean('');

            view.createCard(model);
            expect(initComponentsStub).toHaveBeenCalled();
        });
    });

    describe('getModuleLink', function() {
        using('valid and invalid module names', [
            {value: 'Calls', expected: 'calls'},
            {value: 'Emails', expected: 'emails'},
            {value: 'Messages', expected: 'message_invites'},
            {value: null, expected: undefined},
            {value: 'Randomize', expected: undefined},
        ], function(provider) {
            it('should return correct link name', function() {
                var link = view.getModuleLink(provider.value);
                expect(link).toBe(provider.expected);
            });
        });
    });

    describe('_renderCards', function() {
        it('should render cards and show show-more link', function() {
            var disposeStub = sinon.stub(view, 'disposeActivities');
            var appendStub = sinon.stub(view, 'appendCardsToView');
            var _hideSkeletonStub = sinon.stub(view, '_hideSkeleton');
            var jqueryStub = sinon.stub(view, '$');
            var showStub = sinon.stub();
            var hideStub = sinon.stub();
            jqueryStub.withArgs('.dashlet-footer').returns({
                hide: hideStub,
                show: showStub
            });
            var htmlStub = sinon.stub();
            jqueryStub.withArgs('.activity-timeline-cards').returns({
                html: htmlStub,
                hasClass: function() {return false;}
            });
            view.fetchCompleted = false;
            view.models = 'existing cards';
            view._renderCards();
            expect(appendStub).toHaveBeenCalledWith('existing cards');
            expect(htmlStub).toHaveBeenCalledWith('');
            expect(_hideSkeletonStub).toHaveBeenCalled();
            expect(disposeStub).toHaveBeenCalled();
            expect(showStub).toHaveBeenCalled();
        });
        it('should show empty template and hide show-more link', function() {
            var getStub = sinon.stub(app.template, 'get');
            var templateStub = sinon.stub().returns('empty');
            getStub.withArgs('activity-timeline-base.empty-list').returns(templateStub);
            var _hideSkeletonStub = sinon.stub(view, '_hideSkeleton');
            var jqueryStub = sinon.stub(view, '$');
            var showStub = sinon.stub();
            var hideStub = sinon.stub();
            jqueryStub.withArgs('.dashlet-footer').returns({
                hide: hideStub,
                show: showStub
            });
            var htmlStub = sinon.stub();
            jqueryStub.withArgs('.activity-timeline-cards').returns({
                html: htmlStub,
                hasClass: function() {return false;}
            });
            view.fetchCompleted = true;
            view.models = null;
            view._renderCards();
            expect(templateStub).toHaveBeenCalled();
            expect(_hideSkeletonStub).toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
            expect(htmlStub).toHaveBeenCalledWith('empty');
        });
    });
});
