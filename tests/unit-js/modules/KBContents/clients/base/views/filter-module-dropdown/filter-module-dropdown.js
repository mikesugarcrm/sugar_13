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
describe('KBContents.Base.Views.FilterModuleDropdown', function() {

    var app, view, sandbox, context, layout, moduleName = 'KBContents';
    var parentLayout;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.parent = new Backbone.Model();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent(
            'base',
            'view',
            'filter-module-dropdown'
        );
        SugarTest.loadComponent(
            'base',
            'view',
            'filter-module-dropdown',
            moduleName
        );
        SugarTest.testMetadata.set();
        parentLayout = app.view.createLayout({type: 'base'});
        layout = SugarTest.createLayout(
            'base',
            moduleName,
            'filter',
            {},
            null,
            null,
            {
                layout: parentLayout
            }
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'filter-module-dropdown',
            null,
            context,
            moduleName,
            layout
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        parentLayout.dispose();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        view = null;
        layout = null;
    });

    describe('getModuleListForSubpanels()', function() {
        var pullSubpanelRelationshipsStub, getSubpanelsAclsActionsStub,
            pruneHiddenModulesStub, getRelatedModuleStub, hasAccessStub,
            langStub;

        beforeEach(function() {
            pullSubpanelRelationshipsStub = sandbox.stub(
                view,
                'pullSubpanelRelationships').callsFake(function() {
                    return {
                        test1: 'test1',
                        test2: 'test2',
                        test3: 'test3',
                        test4: 'test4',
                        test5: 'test5',
                        test6: 'test6'
                    };
                }
            );
            getSubpanelsAclsActionsStub = sandbox.stub(
                view,
                '_getSubpanelsAclsActions').callsFake(function() {
                    return {
                        test1: 'list',
                        test2: 'edit',
                        test3: 'detail'
                    };
                }
            );
            pruneHiddenModulesStub = sandbox.stub(
                view,
                '_pruneHiddenModules').callsFake(function(subpanels) {
                    delete subpanels.test5;
                    return subpanels;
                }
            );
            getRelatedModuleStub = sandbox.stub(
                app.data,
                'getRelatedModule').callsFake(function(module, value) {
                    return value;
                }
            );
            hasAccessStub = sandbox.stub(
                app.acl,
                'hasAccess').callsFake(function(aclToCheck, module) {
                    switch (module) {
                        case 'test1':
                        case 'test3':
                        case 'test5':
                            return true;
                    }
                    return false;
                }
            );
            langStub = sandbox.stub(app.lang, 'get').callsFake(function(lang) {
                return lang;
            });
        });

        it('should returns Array when get module list for subpanels', function() {
            var moduleList = view.getModuleListForSubpanels();
            expect(moduleList).toEqual(jasmine.any(Array));
        });

        it('should contain first value as "all modules" get module list for subpanels',
            function() {
                var moduleList = view.getModuleListForSubpanels();

                expect(moduleList).toEqual(jasmine.any(Array));
                expect(moduleList[0].id).toEqual('all_modules');
            }
        );

        it('should prune hidden modules get module list for subpanels', function() {
            view.getModuleListForSubpanels();

            expect(pruneHiddenModulesStub).toHaveBeenCalled();
            expect(pruneHiddenModulesStub.getCall(0).returnValue.test5).toBeUndefined();
        });

        it('should check subpanel acl get module list for subpanels', function() {
            view.getModuleListForSubpanels();

            expect(hasAccessStub).toHaveBeenCalled();

            expect(hasAccessStub.getCall(0).args[0]).toEqual('list');
            expect(hasAccessStub.getCall(0).args[1]).toEqual('test1');
            expect(hasAccessStub.getCall(0).returnValue).toBeTruthy();
            expect(hasAccessStub.getCall(1).args[0]).toEqual('edit');
            expect(hasAccessStub.getCall(1).args[1]).toEqual('test2');
            expect(hasAccessStub.getCall(1).returnValue).toBeFalsy();
            expect(hasAccessStub.getCall(2).args[0]).toEqual('detail');
            expect(hasAccessStub.getCall(2).args[1]).toEqual('test3');
            expect(hasAccessStub.getCall(2).returnValue).toBeTruthy();
            expect(hasAccessStub.getCall(3).args[0]).toEqual('list');
            expect(hasAccessStub.getCall(3).args[1]).toEqual('test4');
            expect(hasAccessStub.getCall(3).returnValue).toBeFalsy();
            expect(hasAccessStub.getCall(4).args[0]).toEqual('list');
            expect(hasAccessStub.getCall(4).args[1]).toEqual('test6');
            expect(hasAccessStub.getCall(4).returnValue).toBeFalsy();
        });

        it('should returns valid value after prune and acl check get module list for subpanels',
            function() {
                var moduleList = view.getModuleListForSubpanels();

                expect(moduleList).toEqual(jasmine.any(Array));
                expect(moduleList[0].id).toEqual('all_modules');

                expect(moduleList).toContain({
                    id: 'test1',
                    text: 'test1'
                });
                expect(moduleList).toContain({
                    id: 'test3',
                    text: 'test3'
                });
                expect(moduleList).not.toContain({
                    id: 'test2',
                    text: 'test2'
                });
                expect(moduleList).not.toContain({
                    id: 'test4',
                    text: 'test4'
                });
                expect(moduleList).not.toContain({
                    id: 'test5',
                    text: 'test5'
                });
                expect(moduleList).not.toContain({
                    id: 'test6',
                    text: 'test6'
                });
            }
        );
    });

    describe('_getSubpanelsAclsActions()', function() {
        var getModuleStub;

        beforeEach(function() {
            getModuleStub = sandbox.stub(
                app.metadata,
                'getModule').callsFake(function(module) {
                    return {
                        layouts: {
                            subpanels: {
                                meta: {
                                    components: [
                                        {
                                            context: {
                                                module: module,
                                                link: 'abc'
                                            }
                                        },
                                        {
                                            context: {
                                                module: module,
                                                link: 'test2'
                                            },
                                            acl_action: 'edit'
                                        }
                                    ]
                                }
                            }
                        }
                    };
                }
            );
        });

        it('should returns object when get subpanels acl actions', function() {
            var actions = view._getSubpanelsAclsActions();
            expect(actions).toEqual(jasmine.any(Object));
        });

        it('should set default list acl action if acl_action not defined when get subpanels acl actions',
            function() {
                var actions = view._getSubpanelsAclsActions();
                expect(actions.abc).toBeDefined();
                expect(actions.abc).toEqual('list');
            }
        );

        it('should set acl action from component meta if acl_action defined when get subpanels acl actions',
            function() {
                var actions = view._getSubpanelsAclsActions();
                expect(actions.test2).toBeDefined();
                expect(actions.test2).toEqual('edit');
            }
        );
    });
});
