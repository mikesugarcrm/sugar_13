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
describe('Base.View.ActiveSubscriptions', function() {
    var app;
    var context;
    var currentModule = '';
    var layout;
    var view;
    var layoutName = 'dashlet';
    var module = 'Purchases';
    var moduleName = 'Accounts';
    var viewName = 'active-subscriptions';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                panels: [
                    {
                        fields: [
                            {
                                name: 'linked_subscriptions_account_field',
                                type: 'enum',
                                options: '',
                            },
                        ],
                    }

                ],
                fields: [
                    'name',
                    'start_date',
                    'end_date',
                    'pli_collection',
                ],
            }
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.parent = app.data.createBean('Home');
        context.parent.set('dashboard_module', moduleName);
        context.prepare();

        dashboard = app.view.createLayout({
            name: 'dashboard',
            type: 'dashboard',
            context: context.parent,
        });

        layout = app.view.createLayout({
            name: layoutName,
            context: context,
            meta: {index: 0},
            layout: dashboard,
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
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        dashboard.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
        dashboard = null;
    });

    // _getBaseModel is deprecated in 10.2.0
    describe('_getBaseModel', function() {
        it('should get model from parent context', function() {
            var accountModel = app.data.createBean('Accounts');
            accountModel.set('_module', 'Accounts');

            var parentContext = app.context.getContext();
            parentContext.set({
                module: 'Accounts',
                rowModel: accountModel,
            });

            var mainContext = app.context.getContext();
            mainContext.set({module: 'Accounts'});
            mainContext.parent = parentContext;
            view.context = mainContext;
            view.collection = {
                off: $.noop
            };
            view._getBaseModel();
            expect(view.baseModel).toEqual(accountModel);
        });
    });

    describe('_buildFieldsList', function() {
        describe('when we are on an Accounts module', function() {
            it('should set the configPanelOptions', function() {
                var configPanel = _.first(view.dashletConfig.panels);
                var options = _.first(configPanel.fields).options;

                expect(Object.keys(options).length).toBe(1);
                expect(options.id).toBe('ID');
            });
        });

        describe('when we are not on the Accounts module', function() {
            it('should gather the appropriate fields and set the configPanelOptions', function() {
                var getFieldStub = sinon.stub(app.metadata, 'getField').returns({
                    name: 'account_id',
                });
                view.currentModule = 'Contacts';
                view._buildFieldsList();
                var configPanel = _.first(view.dashletConfig.panels);
                var options = _.first(configPanel.fields).options;

                expect(Object.keys(options).length).toBe(1);
                expect(options.account_id).toBe('LBL_ACCOUNT_NAME');
            });
        });
    });

    describe('_initCollection', function() {
        it('should not take action when base module or model does not exist', function() {
            var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                'off': $.noop
            });
            view.baseModule = 'Accounts';

            view._initCollection();
            expect(beanCollectionStub).not.toHaveBeenCalled();
        });

        describe('when the dashlet is on the Accounts module', function() {
            it('should create new bean collection', function() {
                var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                    'off': $.noop
                });
                var today = app.date().formatServer(true);
                view.baseModule = 'Accounts';
                view.currentModule = 'Accounts';

                var accountModel = app.data.createBean('Accounts', {'id': 'my_id'});
                var parentContext = app.context.getContext();
                parentContext.set({
                    module: 'Accounts',
                    model: accountModel,
                    layout: layoutName,
                });
                view.context = parentContext;
                view.settings.set({linked_subscriptions_account_field: 'id'});

                view.meta = {
                    'fields': ['name']
                };
                view.purchasesModule = true;
                app.config.maxRecordFetchSize = 1000;
                view._initCollection();
                expect(beanCollectionStub.lastCall.args[0]).toEqual('Purchases');
                expect(beanCollectionStub.lastCall.args[2].fields)
                    .toEqual(['name', 'start_date', 'end_date', 'pli_collection',]);
                expect(beanCollectionStub.lastCall.args[2].filter[0].account_id.$equals).toEqual('my_id');
                expect(beanCollectionStub.lastCall.args[2].filter[1].service.$equals)
                    .toEqual(1);
                expect(beanCollectionStub.lastCall.args[2].filter[2].start_date.$lte).toEqual(today);
                expect(beanCollectionStub.lastCall.args[2].filter[3].end_date.$gte).toEqual(today);
                expect(beanCollectionStub.lastCall.args[2].limit).toEqual(app.config.maxRecordFetchSize);
            });
        });

        describe('when the dashlet is being added to a different module', function() {
            it('should create new bean collection', function() {
                var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                    'off': $.noop
                });
                var today = app.date().formatServer(true);
                view.baseModule = 'Accounts';
                view.currentModule = 'Contacts';

                var accountModel = app.data.createBean('Accounts', {'id': 'my_id'});
                var contactModel = app.data.createBean('Contacts', {'account_id': 'my_id'});
                var parentContext = app.context.getContext();
                parentContext.set({
                    module: 'Accounts',
                    model: contactModel,
                    layout: layoutName,
                });
                view.context = parentContext;
                view.settings.set({linked_subscriptions_account_field: 'account_id'});

                view.meta = {
                    'fields': ['name']
                };
                view.purchasesModule = true;
                app.config.maxRecordFetchSize = 1000;
                view._initCollection();
                expect(beanCollectionStub.lastCall.args[0]).toEqual('Purchases');
                expect(beanCollectionStub.lastCall.args[2].fields)
                    .toEqual(['name', 'start_date', 'end_date', 'pli_collection',]);
                expect(beanCollectionStub.lastCall.args[2].filter[0].account_id.$equals).toEqual('my_id');
                expect(beanCollectionStub.lastCall.args[2].filter[1].service.$equals)
                    .toEqual(1);
                expect(beanCollectionStub.lastCall.args[2].filter[2].start_date.$lte).toEqual(today);
                expect(beanCollectionStub.lastCall.args[2].filter[3].end_date.$gte).toEqual(today);
                expect(beanCollectionStub.lastCall.args[2].limit).toEqual(app.config.maxRecordFetchSize);
            });
        });


        it('calls _daysDifferenceCalculator', function() {
            sinon.stub(app.date.fn, 'formatUser').callsFake(function() {
                return {split: function() {
                        return (['2019-10-21']);
                    }};
            });
            var model = app.data.createBean('Accounts', {
                start_date: '2019-10-21',
                end_date: '2020-10-20',
                get: sinon.stub(),
                set: function() {},
            });

            view.baseModule = 'Accounts';
            view.baseModel = model;
            view.collection = {
                models: [model],
                'off': $.noop
            };
            view._daysDifferenceCalculator();
            expect(view.collection.models[0].get('endDate')).toEqual('2019-10-21');
            expect(view.collection.models[0].get('startDate')).toEqual('2019-10-21');
        });

        it('calls _caseComparator', function() {
            sinon.stub(app.date.fn, 'formatUser').callsFake(function() {
                return {split: function() {
                        return (['2019-10-21']);
                    }};
            });
            var model = app.data.createBean('Accounts', {
                start_date: '2019-10-21',
                end_date: '2020-10-20',
                get: sinon.stub(),
                set: function() {},
            });

            view.baseModule = 'Accounts';
            view.baseModel = model;
            view.collection = {
                models: [model],
                'off': $.noop
            };
            view._caseComparator();
            expect(view.overallSubscriptionEndDate).toEqual(18555);
            expect(view.overallSubscriptionStartDate).toEqual(18190);
        });
    });

    describe('loadData', function() {
        it('should fetch collection', function() {
            var fetchStub = sinon.stub();
            view.collection = {
                fetch: fetchStub,
                off: $.noop
            };
            view.purchasesModule = true;
            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });
    });
});
