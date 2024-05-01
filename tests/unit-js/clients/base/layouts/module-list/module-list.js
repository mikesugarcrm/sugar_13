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
describe('Base.Layout.ModuleList', function() {

    var moduleName = 'Cases',
        layoutName = 'module-list',
        app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base');
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base', 'list');
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', moduleName, layoutName);
        layout.template = app.template.getLayout(layoutName);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    describe('Render', function() {

        var drawer;

        beforeEach(function() {

            drawer = app.drawer;
            app.drawer = {
                getActive: $.noop
            };

            sinon.stub(app.metadata, 'getModuleNames').callsFake(function() {
                return {
                    Home: 'Home',
                    Accounts: 'Accounts',
                    Bugs: 'Bugs',
                    Calendar: 'Calendar',
                    Calls: 'Calls',
                    Campaigns: 'Campaigns',
                    Cases: 'Cases',
                    Contacts: 'Contacts',
                    Forecasts: 'Forecasts',
                    Opportunities: 'Opportunities',
                    Prospects: 'Prospects',
                    Reports: 'Reports',
                    Tasks: 'Tasks'
                };
            });
            sinon.stub(app.metadata, 'getStrings').callsFake(function() {
                return {
                    Accounts: {}
                };
            });

            sinon.stub(app.controller.context, 'get').callsFake(function() {
                return moduleName;
            });

            layout._resetMenu();
        });

        afterEach(function() {

            app.drawer = drawer;

            sinon.restore();
        });

        it('should display all the modules in the module list metadata by order', function() {
            var modules = _.map(layout.$('[data-action="more-modules"]').prevUntil().get().reverse(), function(el) {
                return $(el).data('module');
            });
            expect(modules).toEqual(_.keys(app.metadata.getModuleNames()));
        });

        it('should select Cases module to be currently active module', function() {
            layout.layout = {
                trigger: $.noop,
                off: $.noop
            };
            var triggerStub = sinon.stub(layout.layout, 'trigger');

            layout.handleViewChange();

            expect(layout.$('[data-container=module-list]').children('.active').data('module')).toBe(moduleName);
            expect(triggerStub).toHaveBeenCalledWith('header:update:route');

            expect(layout.isActiveModule(moduleName)).toBeTruthy();
        });

        it('should select the drawer module if there is one open from the router', function() {
            layout.layout = {
                trigger: $.noop,
                off: $.noop
            };
            var module = 'Accounts';
            var ctx = new app.Context({
                module: module,
                fromRouter: true
            });

            var drawerLayout = SugarTest.createLayout('base', module, layoutName, null, ctx);

            sinon.stub(app.drawer, 'getActive').callsFake(function() {
                return drawerLayout;
            });

            layout.handleViewChange();

            expect(layout.isActiveModule(module)).toBeTruthy();
            drawerLayout.dispose();
        });

        using('mappedModule values', [
            {
                module: 'MyCustomCases',
                tabExists: true,
                toggleModuleCalled: true
            },
            {
                module: '',
                tabExists: false,
                toggleModuleCalled: false
            },
            {
                module: undefined,
                tabExists: false,
                toggleModuleCalled: true
            }
        ], function(value) {
            it('should show the correct mapped version of the module', function() {
                var _tabMap = {},
                    toggleModuleStub = sinon.stub(layout, 'toggleModule');
                layout.layout = {
                    trigger: $.noop,
                    off: $.noop
                };

                _tabMap[moduleName] = value.module;
                sinon.stub(app.metadata, 'getModuleTabMap').returns(_tabMap);
                sinon.stub(app.metadata, 'getFullModuleList').callsFake(function() {
                    return {
                        MyCustomCases: 'MyCustomCases',
                        Cases: 'Cases'
                    };
                });

                layout.handleViewChange();

                expect((layout.$('[data-module="' + value.module + '"]')).length > 0).toBe(value.tabExists);
                expect(toggleModuleStub.called).toBe(value.toggleModuleCalled);
            });
        });

        it('should show the correct mapped version of the module only if the module exists', function() {
            var _tabMap = {},
                toggleModuleStub = sinon.stub(layout, 'toggleModule');

            layout.layout = {
                trigger: $.noop,
                off: $.noop
            };

            _tabMap[moduleName] = 'InvalidModule';
            sinon.stub(app.metadata, 'getModuleTabMap').returns(_tabMap);
            sinon.stub(app.metadata, 'getFullModuleList').callsFake(function() {
                return {
                    Cases: 'Cases'
                };
            });

            layout.handleViewChange();

            expect(toggleModuleStub.called).toBeFalsy;
        });

        it('should hide cached versions of the modules', function() {
            layout.layout = {
                trigger: $.noop,
                off: $.noop
            };

            sinon.stub(app.metadata, 'getFullModuleList').callsFake(function() {
                return {
                    CachedModule: 'CachedModule',
                    Cases: 'Cases'
                };
            });
            layout._setActiveModule('CachedModule');
            layout.handleViewChange();

            expect(layout.$('[data-container=module-list]').children('.active').data('module')).toBe(moduleName);
            expect(layout.isActiveModule(moduleName)).toBeTruthy();
            expect(layout.$('[data-module=CachedModule]')).toHaveClass('hidden');
            expect(layout.$('[data-module=CachedModule]')).not.toHaveClass('active');
        });
    });
});
