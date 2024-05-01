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
describe('Base.View.TabbedDashlet', function() {
    var moduleName = 'Accounts';
    var viewName = 'tabbed-dashlet';
    var layoutName = 'tabbed-layout';
    var app;
    var view;
    var layout;
    var tabsMeta;

    beforeEach(function() {
        app = SugarTest.app;

        tabsMeta = [
            {
                module: 'Meetings',
                invitation_actions: {
                    name: 'accept_status_users',
                    type: 'invitation-actions'
                }
            },
            {
                module: 'Emails'
            }
        ];

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', layoutName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                tabs: tabsMeta,
                panels: [
                    {
                        'name': 'panel_body',
                        'columns': 1,
                        'placeholders': true,
                        'fields': [
                            {name: 'visibility', type: 'base', label: 'visibility'}
                        ]
                    }
                ]
            },
            moduleName
        );

        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        layout = SugarTest.createLayout('base', moduleName, layoutName);
        view = SugarTest.createView('base', moduleName, viewName, null, null, null, layout);
        view.settings = new Backbone.Model();
        view._defaultSettings = {
            filter: 7,
            limit: 10,
            visibility: 'user'
        };
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
        layout = null;
        app = null;
    });

    it('should retrieve its default settings', function() {
        view._initSettings();
        expect(view.settings.get('filter')).toEqual(view._defaultSettings.filter);
        expect(view.settings.get('limit')).toEqual(view._defaultSettings.limit);
        expect(view.settings.get('visibility')).toEqual(view._defaultSettings.visibility);
    });

    it('should override its default settings', function() {
        view.settings.set('filter', 12);

        view._initSettings();
        expect(view.settings.get('filter')).toEqual(12);
        expect(view.settings.get('limit')).toEqual(view._defaultSettings.limit);
        expect(view.settings.get('visibility')).toEqual(view._defaultSettings.visibility);
    });

    describe('Visibility toggle.', function() {
        it('Should set state when visibility is toggled.', function() {
            view._initVisibility();
            var event = $.Event('click');
            var element = event.currentTarget = $('<input/>', {value: 'test'});
            element.appendTo(view.$el);
            var setStateStub = sinon.stub(app.user.lastState, 'set');
            //Prevent actual calls to load data (makes an XHR request)
            sinon.stub(layout, 'loadData');

            view.visibilitySwitcher(event);
            expect(setStateStub.calledOnce).toBe(true);
            // Shouldn't be toggled twice with same value.
            view.visibilitySwitcher(event);
            expect(setStateStub.calledTwice).toBe(false);
        });
    });

    describe('_getBaseModel', function() {
        using('different modules and names',
            [
                {module: 'Accounts'},
                {module: 'Contacts'},
                {module: 'Opportunities'},
                {module: 'Leads'},
                {module: 'Cases'},
                {module: 'RevenueLineItems'},
                {module: 'Bugs'},
            ],
            function(input) {
                it('should get model from parent context', function() {
                    var layout = SugarTest.createLayout(
                        'base',
                        'Notes',
                        'dashboard'
                    );
                    var view = SugarTest.createView(
                        'base',
                        'Notes',
                        'attachments',
                        {},
                        null,
                        null,
                        layout
                    );

                    var testModel = app.data.createBean(input.module);
                    testModel.set('_module', input.module);

                    var parentContext = app.context.getContext();
                    parentContext.set({
                        module: input.module,
                        rowModel: testModel,
                    });

                    var mainContext = app.context.getContext();
                    mainContext.set({parentModule: input.module});
                    mainContext.parent = parentContext;

                    var model = view._getBaseModel({
                        context: mainContext,
                    });

                    expect(model).toEqual(testModel);
                });
            }
        );
    });

    describe('_getTabFieldDefs', function() {
        var mockEmailsTabMeta;
        var mockEmailsFields;

        beforeEach(function() {
            mockEmailsTabMeta = {
                fields: ['name', 'assigned_user_name']
            };

            mockEmailsFields = {
                name: {
                    type: 'name'
                },
                assigned_user_name: {
                    type: 'relate'
                }
            };

            sinon.stub(app.metadata, 'getModule').returns(mockEmailsFields);
        });

        it('should populate tab field definitions from metadata', function() {
            var result = view._getTabFieldDefs(mockEmailsTabMeta);
            expect(result.name).toEqual(mockEmailsFields.name);
            expect(result.assigned_user_name).toEqual(mockEmailsFields.assigned_user_name);
        });

        it('should add a "link: true" property to name fields', function() {
            var result = view._getTabFieldDefs(mockEmailsTabMeta);
            expect(result.name.link).toBeTruthy();
        });
    });

    describe('_getTabRowActions', function() {
        var tabData;

        beforeEach(function() {
            tabData = {
                row_actions: [
                    {
                        type: 'link-action'
                    },
                    {
                        type: 'unlink-action'
                    },
                    {
                        type: 'create-Ellen-Line-Item-action'
                    }
                ]
            };
        });

        it('should filter out invalid row actions for a module tab', function() {
            tabData.module = 'Emails';
            var result = view._getTabRowActions(tabData);
            expect(result.length).toEqual(1);
            expect(result[0]).toEqual({
                type: 'create-Ellen-Line-Item-action'
            });
        });

        it('should not filter out valid row actions for a module tab', function() {
            tabData.module = 'Messages';
            var result = view._getTabRowActions(tabData);
            expect(result.length).toEqual(3);
        });
    });
});
