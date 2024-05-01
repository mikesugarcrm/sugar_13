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
describe('Plugins.ConfigDrivenList', () => {
    let app;
    let layout;
    let view;
    let plugin;
    let moduleName = 'Accounts';
    let viewName = 'dashablelist';
    let layoutName = 'record';
    let dashletLayout;
    let context;

    beforeEach(() => {
        app = SugarTest.app;
        SugarTest.loadPlugin('ConfigDrivenList');
        plugin = app.plugins.plugins.layout.ConfigDrivenList;
        layout = SugarTest.createLayout('base', 'Accounts', 'Records');
    });

    afterEach(() => {
        sinon.restore();
        app.cache.cutAll();
        app = null;
    });

    describe('plugin', () => {
        it('should attach init event handler', () => {
            let stub = sinon.stub(layout, 'before');
            plugin.onAttach.apply(layout);
            expect(stub).toHaveBeenCalledWith('render');
        });

        it('should call _toggleFrozenHeaders before render', () => {
            let stub = sinon.stub(plugin, '_toggleFrozenHeaders');
            plugin.onAttach.apply(layout);
            layout.before('render', () => stub());
            layout.render();
            expect(stub).toHaveBeenCalled();
        });

        it('should call _toggleFrozenFirstColumn before render', () => {
            let stub = sinon.stub(plugin, '_toggleFrozenFirstColumn');
            sinon.stub(plugin, '_getFrozenColumnConfig').returns(true);
            plugin.onAttach.apply(layout);
            layout.before('render', () => stub());
            layout.render();
            expect(stub).toHaveBeenCalled();
        });
    });

    describe('_getFrozenColumnConfig', () => {
        it('should return based on app config and view config', () => {
            sinon.stub(layout, 'before');

            using('different combination of view name and settings return', [
                {name: '', settings: {}, config: false, expected: false},
                {name: '', settings: {}, config: true, expected: false},
                {name: 'dashablelist', settings: {get: () => {return true;},
                        freeze_first_column: true}, config: true, expected: true},
                {name: 'dashablelist', settings: {get: () => {return true;},
                        freeze_first_column: true}, config: false, expected: false},
                {name: 'dashablelist', settings: {get: () => {return false;},
                        freeze_first_column: false}, config: true, expected: false},
            ], (values) => {
                SUGAR.App.config.allowFreezeFirstColumn = values.config;
                plugin.name = values.name;
                plugin.settings = values.settings;
                let actual = plugin._getFrozenColumnConfig();
                expect(actual).toBe(values.expected);
            });
        });
    });

    describe('filterConfigFieldsForDashlet', () => {

        beforeEach(() => {
            SugarTest.testMetadata.init();
            SugarTest.loadComponent('base', 'view', viewName);
            SugarTest.loadComponent('base', 'field', 'base');

            SugarTest.testMetadata.set();
            app.data.declareModels();
            SugarTest.loadPlugin('Dashlet');
            app.user.set('module_list', [moduleName]);

            context = app.context.getContext();
            context.set({
                module: moduleName,
                layout: layoutName
            });
            context.parent = new Backbone.Model();
            context.parent.set('module', moduleName);
            context.prepare();

            dashletLayout = app.view.createLayout({
                name: layoutName,
                context: context
            });
        });

        afterEach(() => {
            dashletLayout.dispose();
            view.dispose();
            SugarTest.testMetadata.dispose();
        });

        it('should filter the dashlet metadata fields based on admin configs', () => {
            sinon.stub(layout, 'before');

            using('different combination of view name and settings return', [
                {
                    viewDef: {
                        'panels': [
                            {
                                fields: [
                                    {name: 'test1', showOnConfig: 'allowFreezeFirstColumn'},
                                    {name: 'test2'}
                                ]
                            }
                        ]
                    },
                    returnedViewDef: {
                        'panels': [
                            {
                                fields: [
                                    {name: 'test1', showOnConfig: 'allowFreezeFirstColumn'},
                                    {name: 'test2'}
                                ]
                            }
                        ]
                    },
                    allowFreeze: true
                },
                {
                    viewDef: {
                        'panels': [
                            {
                                fields: [
                                    {name: 'test1', showOnConfig: 'allowFreezeFirstColumn'},
                                    {name: 'test2'}
                                ]
                            }
                        ]
                    },
                    returnedViewDef: {
                        'panels': [
                            {
                                fields: [
                                    {name: 'test2'}
                                ]
                            }
                        ]
                    },
                    allowFreeze: false
                }
            ], (values) => {
                SUGAR.App.config.allowFreezeFirstColumn = values.allowFreeze;
                SugarTest.testMetadata.addViewDefinition(
                    viewName,
                    values.viewDef,
                    moduleName
                );
                view = SugarTest.createView('base', moduleName, viewName, null, context, null, dashletLayout);

                view.filterConfigFieldsForDashlet();
                expect(view.meta.panels[0].fields.length).toBe(values.returnedViewDef.panels[0].fields.length);
            });
        });
    });
});
