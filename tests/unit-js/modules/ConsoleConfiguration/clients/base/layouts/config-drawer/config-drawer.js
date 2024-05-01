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
describe('ConsoleConfiguration.Layout.ConfigDrawer', function() {
    var app;
    var layout;
    var context;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: app.data.createBeanCollection('ConsoleConfiguration')
        });
        context.prepare();
        options = {
            context: context
        };

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                relField1: {
                    name: 'relField1',
                    type: 'enum',
                    vname: 'LBL_REL_1'
                },
                relField2: {
                    name: 'relField2',
                    type: 'enum',
                    vname: 'LBL_REL_2'
                },
                priority: {
                    audited: true,
                    name: 'priority',
                    type: 'enum',
                    vname: 'LBL_PRIORITY'
                },
                fields: {
                    name: {
                        type: 'name'
                    }
                },
                field1: {
                    name: 'field1',
                    property: '',
                },
                field2: {
                    name: 'field2',
                },
                fieldX: {
                    name: 'fieldX',
                },
                isBwcEnabled: false
            };
        });
        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        layout = SugarTest.createLayout('base', 'ConsoleConfiguration', 'config-drawer', {}, context, true);
        sinon.stub(layout, 'checkAccess').callsFake(function() {
            return true;
        });
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
        context = null;
        options = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(layout, 'setAllowedModules').callsFake(function() {});
            sinon.stub(layout, '_super');
            sinon.stub(layout, '_parseConsoleContext');
        });

        it('should call the _super method with initialize', function() {
            layout.initialize(options);
            expect(layout._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call setAllowedModules', function() {
            layout.initialize(options);
            expect(layout.setAllowedModules).toHaveBeenCalled();
        });

        it('should call _parseConsoleContext if no console ID exists on the context', function() {
            sinon.stub(layout.context, 'get').withArgs('consoleId').returns(undefined);
            layout.initialize(options);
            expect(layout._parseConsoleContext).toHaveBeenCalled();
        });

        it('should not call _parseConsoleContext if a console ID already exists on the context', function() {
            sinon.stub(layout.context, 'get').withArgs('consoleId').returns('12345');
            layout.initialize(options);
            expect(layout._parseConsoleContext).not.toHaveBeenCalled();
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(layout, 'addModelToCollection').callsFake(function() {});
            sinon.stub(layout, 'removeModelFromCollection').callsFake(function() {});
            sinon.stub(layout.context, 'on').callsFake(function() {});
            layout.bindDataChange();
        });

        it('should call layout.context.on with consoleconfiguration:config:model:add', function() {
            expect(layout.context.on).toHaveBeenCalledWith('consoleconfiguration:config:model:add');
        });

        it('should call layout.context.on with consoleconfiguration:config:model:remove', function() {
            expect(layout.context.on).toHaveBeenCalledWith('consoleconfiguration:config:model:remove');
        });
    });

    describe('loadData', function() {
        beforeEach(function() {
            sinon.stub(layout, 'addModelToCollection').callsFake(function() {});
            sinon.stub(layout, 'setActiveTabIndex').callsFake(function() {});
            sinon.stub(app.metadata, 'getModuleNames').callsFake(function() {
                return ['Accounts', 'Opportunities', 'Cases'];
            });
            sinon.spy(JSON, 'parse');

            // Stub the calls to get context and model information
            sinon.stub(layout.context, 'get').withArgs('consoleId').returns('12345');
            sinon.stub(layout.model, 'get')
                .withArgs('enabled_modules').returns({
                '12345': ['Accounts', 'Opportunities']
            })
                .withArgs('order_by_primary').returns({
                '12345': {
                    'Accounts': 'next_renewal_date:asc',
                    'Opportunities': 'date_closed'
                }
            })
                .withArgs('order_by_secondary').returns({
                '12345': {
                    'Accounts': '',
                    'Opportunities': ''
                }
            })
                .withArgs('filter_def').returns({
                '12345': {
                    'Accounts': [{'$owner': ''}],
                    'Opportunities': [{'$owner': ''}]
                }
            });

            sinon.stub(layout, '_getModelDefaults').returns({});
        });

        it('should call layout.model.get with various arguments', function() {
            layout.loadData();
            expect(layout.model.get).toHaveBeenCalledWith('order_by_primary');
            expect(layout.model.get).toHaveBeenCalledWith('order_by_secondary');
            expect(layout.model.get).toHaveBeenCalledWith('filter_def');
        });

        it('should call layout.addModelToCollection with moduleName and data', function() {
            layout.loadData();
            expect(layout.addModelToCollection).toHaveBeenCalledWith('Accounts', {
                defaults: {},
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_primary_direction: 'asc',
                order_by_secondary: '',
                order_by_secondary_direction: 'asc',
                filter_def: [{'$owner': ''}]
            });
        });

        it('should return the list of available and supported module names', function() {
            expect(layout.getAvailableModules()).toEqual(['Accounts', 'Opportunities']);
        });

        it('should call layout.setActiveTabIndex', function() {
            layout.loadData();
            expect(layout.setActiveTabIndex).toHaveBeenCalled();
        });
    });

    describe('_checkModuleAccess', function() {
        beforeEach(function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    'ConsoleConfiguration': {
                        admin: true
                    }
                };
            });
            sinon.spy(app.user, 'get');
            layout._checkModuleAccess();
        });

        it('should call app.user.getAcls method', function() {
            expect(app.user.getAcls).toHaveBeenCalled();
        });

        it('should call the app.user.get method with', function() {
            expect(app.user.get).toHaveBeenCalledWith('type');
        });
    });

    describe('setAllowedModules', function() {
        beforeEach(function() {
            layout.supportedModules = ['Accounts'];
            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {return {Accounts: 'Accounts'};});
            sinon.stub(layout.context, 'set').callsFake(function() {});
            layout.setAllowedModules();
        });

        it('should call the app.metadata.getModule method', function() {
            expect(app.metadata.getModule).toHaveBeenCalledWith('Accounts');
        });

        it('should call layout.context.on method with allowedModules and modules', function() {
            expect(layout.context.set).toHaveBeenCalledWith('allowedModules', {Accounts: 'Accounts'});
        });
    });

    describe('setActiveIndex', function() {
        beforeEach(function() {
            layout.context.set({
                consoleTabs: ['Accounts', 'Opportunities']
            });
            layout.context.parent = new app.Context();

            sinon.stub(layout.context, 'set').callsFake(function() {});
        });

        describe('when collection.length is < 1', function() {
            it('should not call layout.context.set', function() {
                layout.collection = {
                    length: 0,
                    off: function() {}
                };
                layout.context.parent.set({
                    tabs: [
                        {name: 'LBL_OVERVIEW'},
                        {name: 'LBL_ACCOUNTS'},
                        {name: 'LBL_OPPORTUNITIES'}
                    ],
                    activeTab: 2
                });
                layout.setActiveTabIndex();

                expect(layout.context.set).not.toHaveBeenCalled();
            });
        });

        describe('when collection.length is >= 1', function() {
            beforeEach(function() {
                layout.collection = {
                    length: 1,
                    off: function() {}
                };
                layout.context.parent.set({
                    tabs: [
                        {name: 'LBL_OVERVIEW'},
                        {name: 'LBL_ACCOUNTS'},
                        {name: 'LBL_OPPORTUNITIES'}
                    ],
                    activeTab: 2
                });

                layout.setActiveTabIndex();
            });
            it('should call layout.context.set with activeTabIndex 1', function() {

                expect(layout.context.set).toHaveBeenCalledWith('activeTabIndex', 1);
            });
        });

        describe('when active tab is Overview', function() {
            beforeEach(function() {
                layout.collection = {
                    length: 1,
                    off: function() {}
                };
                layout.context.parent.set({
                    tabs: [
                        {name: 'LBL_OVERVIEW'},
                        {name: 'LBL_ACCOUNTS'},
                        {name: 'LBL_OPPORTUNITIES'}
                    ],
                    activeTab: 0
                });

                layout.setActiveTabIndex();
            });
            it('should call layout.context.set with activeTabIndex 0', function() {

                expect(layout.context.set).toHaveBeenCalledWith('activeTabIndex', 0);
            });
        });
    });

    describe('removeModelFromCollection', function() {
        var module;
        var removeStub;
        beforeEach(function() {
            removeStub = sinon.stub();
            sinon.stub(layout, 'setActiveTabIndex').callsFake(function() {});
        });

        afterEach(function() {
            removeStub = null;
        });

        describe('when module is not an enabled_module', function() {
            it('should not call layout.collection.remove', function() {
                module = 'Potato';
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    remove: removeStub
                };
                layout.removeModelFromCollection(module);
                expect(layout.collection.remove).not.toHaveBeenCalled();
            });
        });

        describe('when module is enabled and model is not empty', function() {
            beforeEach(function() {
                module = 'Accounts';
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    remove: removeStub
                };
                layout.removeModelFromCollection(module);
            });

            it('should call layout.collection.remove', function() {
                expect(layout.collection.remove).toHaveBeenCalledWith(layout.collection.models[0]);
            });

            it('should call layout.setActiveTabIndex', function() {
                expect(layout.setActiveTabIndex).toHaveBeenCalled();
            });
        });
    });

    describe('addModelToCollection', function() {
        var module;
        var data;
        var addStub;
        beforeEach(function() {
            module = 'Accounts';
            data = {
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            };

            addStub = sinon.stub();
            sinon.stub(layout.context, 'get').callsFake(function() {
                return {Accounts: 'Accounts'};
            });
            sinon.stub(layout, 'setActiveTabIndex').callsFake(function() {});
            sinon.stub(layout, 'setTabContent').callsFake(function() {});
            sinon.stub(layout, 'addValidationTasks').callsFake(function() {});
        });

        it('should call setActiveTabIndex method', function() {
            layout.collection = {
                models: [
                    {
                        get: function() {
                            return 'Accounts';
                        }
                    }
                ],
                off: function() {},
                add: addStub
            };
            layout.addModelToCollection(module, data);

            expect(layout.setActiveTabIndex).toHaveBeenCalled();
        });

        describe('when existingBean is not empty', function() {
            it('should not call layout.addValidationTasks method', function() {
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    add: addStub
                };
                layout.addModelToCollection(module, data);

                expect(layout.addValidationTasks).not.toHaveBeenCalled();
            });
        });

        describe('when existingBean is empty', function() {
            beforeEach(function() {
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Opportunities';
                            }
                        }
                    ],
                    off: function() {},
                    add: addStub
                };
                sinon.stub(layout, 'setFilterableFields').callsFake(function() {});
                layout.addModelToCollection(module, data);
            });

            it('should call layout.collection.add with bean', function() {
                expect(layout.collection.add).toHaveBeenCalled();
            });

            it('should call layout.setTabContent with bean', function() {
                expect(layout.setTabContent).toHaveBeenCalled();
            });

            it('should not layout.addValidationTasks method', function() {
                expect(layout.addValidationTasks).toHaveBeenCalled();
            });
        });
    });

    describe('setTabContent', function() {
        var bean;
        beforeEach(function() {
            bean = app.data.createBean(layout.module, {
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            });

            sinon.stub(bean, 'set').callsFake(function() {});
            sinon.stub(bean, 'get').callsFake(function() {
                return 'Accounts';
            });

            sinon.stub(app.metadata, 'getView').callsFake(function() {
                return {
                    panels: [{
                        fields: [
                            {
                                name: 'field_group',
                                label: 'LBL_FIELD_GROUP',
                                subfields: [
                                    {
                                        audited: true,
                                        name: 'priority',
                                        label: 'LBL_PRIORITY',
                                        type: 'enum',
                                        default: true,
                                        enabled: true,
                                        related_fields: [
                                            'relField1',
                                            'relField2'
                                        ]
                                    }
                                ]
                            }
                        ]
                    }]
                };
            });
            sinon.stub(app.lang, 'get').withArgs('LBL_PRIORITY').returns('Priority')
                .withArgs('LBL_REL_1').returns('Related Field 1')
                .withArgs('LBL_REL_2').returns('Related Field 2');

            sinon.spy(layout, '_getMultiLineFields');
            sinon.spy(layout, 'getColumns');
        });

        it('should call bean.get method', function() {
            layout.setTabContent(bean);
            expect(bean.get).toHaveBeenCalledWith('enabled_module');
        });

        it('should get the multi-line-list fields for the correct module', function() {
            layout.setTabContent(bean);
            expect(layout._getMultiLineFields).toHaveBeenCalledWith('Accounts');
        });

        it('should get columns for the correct module', function() {
            layout.setTabContent(bean, true);
            expect(layout.getColumns).toHaveBeenCalledWith(bean);
        });

        it('should call bean.set with tabContent and content', function() {
            layout.setTabContent(bean);
            expect(bean.set).toHaveBeenCalledWith('tabContent', {
                fields: {
                    priority: {
                        audited: true,
                        name: 'priority',
                        label: 'LBL_PRIORITY',
                        type: 'enum',
                        default: true,
                        enabled: true,
                        related_fields: [
                            'relField1',
                            'relField2'
                        ]
                    },
                    relField1: {
                        name: 'relField1',
                        type: 'enum',
                        vname: 'LBL_REL_1'
                    },
                    relField2: {
                        name: 'relField2',
                        type: 'enum',
                        vname: 'LBL_REL_2'
                    }
                },
                sortFields: {
                    priority: 'Priority',
                    relField1: 'Related Field 1',
                    relField2: 'Related Field 2'
                }
            });
        });
    });

    describe('addValidationTasks', function() {
        var bean;

        describe('when bean is not undefined', function() {
            beforeEach(function() {
                bean = app.data.createBean(layout.module, {
                    enabled: true,
                    enabled_module: 'Accounts',
                    order_by_primary: 'next_renewal_date',
                    order_by_secondary: '',
                    filter_def: [{'$owner': ''}]
                });

                sinon.stub(bean, 'addValidationTask').callsFake(function() {});
                sinon.stub(layout, '_validatePrimaryOrderBy').callsFake(function() {});
            });

            it('should call bean.addValidationTask method', function() {
                layout.addValidationTasks(bean);
                expect(bean.addValidationTask).toHaveBeenCalledWith('check_order_by_primary');
            });
        });

        describe('when bean is undefined', function() {
            var model;
            beforeEach(function() {
                bean = undefined;
                layout.collection = {
                    models: [
                        {
                            addValidationTask: sinon.stub()
                        }
                    ],
                    off: function() {}
                };

                model = layout.collection.models[0];
                sinon.stub(layout, '_validatePrimaryOrderBy').callsFake(function() {});
            });

            it('should call bean.addValidationTask method', function() {
                layout.addValidationTasks(bean);
                expect(model.addValidationTask).toHaveBeenCalledWith('check_order_by_primary');
            });
        });
    });

    describe('_validatePrimaryOrderBy', function() {
        var fields;
        var errors;
        var callback;
        beforeEach(function() {
            fields = {
                name: 'primary_order_by',
                type: 'enum',
                vname: 'LBL_CONSOLE_SORT_ORDER_PRIMARY'
            };

            errors = {
                order_by_primary: {
                    required: false
                }
            };

            callback = sinon.stub();
        });

        describe('when order_by_primary is empty', function() {
            it('should set order_by_primary required as true', function() {
                layout.get = function() {return;};
                layout._validatePrimaryOrderBy(fields, errors, callback);
                expect(errors.order_by_primary.required).toBe(true);
            });
        });
    });

    describe('setSortValues', function() {
        var bean;
        beforeEach(function() {
            bean = app.data.createBean(layout.module);

            sinon.stub(bean, 'set');
        });

        it('should clear the primary sort', function() {
            sinon.stub(bean, 'get')
                .withArgs('order_by_primary').returns('primary_value')
                .withArgs('order_by_secondary').returns('');

            sinon.stub(layout, 'getColumns').returns({});

            layout.setSortValues(bean);
            expect(bean.set).toHaveBeenCalledWith('order_by_primary', '');
        });

        it('should set the value of the secondary sort to the primary field', function() {
            sinon.stub(bean, 'get')
                .withArgs('order_by_primary').returns('primary_value')
                .withArgs('order_by_secondary').returns('secondary_value');

            sinon.stub(layout, 'getColumns').returns({
                secondary_value: {},
            });

            layout.setSortValues(bean);
            expect(bean.set).toHaveBeenCalledWith('order_by_primary', 'secondary_value');
        });

        it('should clear the secondary sort', function() {
            sinon.stub(bean, 'get')
                .withArgs('order_by_primary').returns('primary_value')
                .withArgs('order_by_secondary').returns('secondary_value');

            sinon.stub(layout, 'getColumns').returns({});

            layout.setSortValues(bean);
            expect(bean.set).toHaveBeenCalledWith('order_by_secondary', '');
        });
    });

    describe('getColumns', function() {
        var bean;
        beforeEach(function() {
            bean = app.data.createBean(layout.module);

            sinon.stub(bean, 'get')
                .withArgs('columns').returns({
                field1: {
                    name: 'field1',
                    console: {
                        related_fields: ['fieldX']
                    }
                },
                field2: {
                    name: 'field2'
                }
            });
        });

        it('!!should add related_fields', function() {
            const result = layout.getColumns(bean);
            expect(result).toEqual({
                field1: {
                    name: 'field1',
                    console: {
                        related_fields: ['fieldX']
                    }
                },
                field2: {
                    name: 'field2'
                },
                fieldX: {
                    name: 'fieldX'
                }
            });
        });
    });
});
