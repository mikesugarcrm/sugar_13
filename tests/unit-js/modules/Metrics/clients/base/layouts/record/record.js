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
describe('Metrics.Layout.Record', function() {
    var app;
    var bean;
    var layout;
    var context;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        bean = app.data.createBean('Metrics');
        bean.addValidationTasks = sinon.stub();
        context.set({
            model: bean,
            collection: app.data.createBeanCollection('Metrics')
        });
        context.prepare();
        options = {
            context: context
        };

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

        sinon.stub(app.data, 'getBeanClass').callsFake(function() {
            return {
                prototype: {
                    getFilterableFields: function() {
                        return {};
                    }
                }
            };
        });

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
        layout = SugarTest.createLayout('base', 'Metrics', 'record', {}, context, true);
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
            sinon.stub(layout, 'setModel').callsFake(function() {});
            sinon.stub(layout, 'loadData').callsFake(function() {});
            sinon.stub(layout, '_super');
        });

        it('should call loadData with createmode false when action is not edit', function() {
            sinon.stub(layout.context, 'get').returns('detail');
            sinon.stub(layout.model, 'get').returns('test');
            layout.initialize(options);

            expect(layout.loadData).toHaveBeenCalled();
            expect(layout.createMode).toBeFalsy();
            expect(layout.action).toEqual('detail');
        });

        it('should call loadData with createmode false when action is edit and model not loaded', function() {
            sinon.stub(layout.context, 'get').returns('edit');
            layout.initialize(options);

            expect(layout.loadData).toHaveBeenCalled();
            expect(layout.createMode).toBeFalsy();
            expect(layout.action).toEqual('edit');
        });

        it('should call setModel with createmode true when action is create', function() {
            sinon.stub(layout.context, 'get').returns('create');
            sinon.stub(layout.model, 'get').returns(undefined);
            layout.initialize(options);

            expect(layout.loadData).not.toHaveBeenCalled();
            expect(layout.setModel).toHaveBeenCalledWith(layout.model);
            expect(layout.createMode).toBeTruthy();
            expect(layout.action).toEqual('edit');
        });

        it('should call the _super method with initialize', function() {
            layout.initialize(options);
            expect(layout._super).toHaveBeenCalledWith('initialize', [options]);
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

    describe('setTabContent', function() {
        var bean;
        beforeEach(function() {
            bean = app.data.createBean(layout.module, {
                enabled: true,
                metric_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            });

            sinon.stub(bean, 'set').callsFake(function() {});
            sinon.stub(bean, 'get').callsFake(function() {
                return 'Accounts';
            });

            sinon.stub(app.lang, 'get').withArgs('LBL_PRIORITY').returns('Priority')
                .withArgs('LBL_REL_1').returns('Related Field 1')
                .withArgs('LBL_REL_2').returns('Related Field 2');

            sinon.spy(layout, '_getMultiLineFields');
            sinon.spy(layout, 'getColumns');
        });

        it('should call bean.get method', function() {
            layout.setTabContent(bean);
            expect(bean.get).toHaveBeenCalledWith('metric_module');
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
        beforeEach(function() {
            bean = app.data.createBean(layout.module, {
                enabled: true,
                metric_module: 'Accounts',
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
