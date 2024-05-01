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
describe('Plugins.Dropdown', function() {
    var app,
        layout,
        sandbox;

    beforeEach(function() {
        var layoutName = 'module-list',
            viewName = 'module-menu',
            modules = {
                Accounts: 'Accounts',
                Contacts: 'Contacts',
                Leads: 'Leads'
            };
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        // update the metadata for each module to inclue header menu metadata
        _.each(modules, function(moduleName) {
            var moduleNameUpper = moduleName.toUpperCase();
            SugarTest.testMetadata.updateModuleMetadata(
                moduleName,
                {
                    menu: {
                        header: {
                            meta: [
                                {
                                    label: 'LNK_NEW_' + moduleNameUpper,
                                    acl_action: 'create',
                                    acl_module: moduleName,
                                    route: '#' + moduleName + '/create'
                                },
                                {
                                    route: '#' + moduleName,
                                    label: 'LNK_' + moduleNameUpper + '_LIST',
                                    acl_action: 'list',
                                    acl_module: moduleName
                                }
                            ]
                        }
                    }
                }
            );
        });
        // load the necessary templates
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base');
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base', 'list');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'favorites');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'recently-viewed');
        SugarTest.loadPlugin('Dropdown');
        SugarTest.testMetadata.set();
        // set up stubs
        sandbox = sinon.createSandbox({
            useFakeTimers: true
        });
        sandbox.stub(app.metadata, 'getModuleNames').returns(modules);
        // create the layout
        layout = SugarTest.createLayout('base', 'Accounts', layoutName);
        layout.template = app.template.getLayout(layoutName);
        SugarTest.loadComponent('base', 'view', viewName);
        // render the menu
        layout._resetMenu();
        // need to append the layout to the DOM so boostrap-dropdown can hear
        // the events
        $('body').append(layout.$el);
        // the default selector won't work without the bootstrap dropdown css
        // so override it in tests with something that produces the same results
        layout.dropdownItemSelector = '.open [role=menu] li:not(.divider) a';
    });

    afterEach(function() {
        sandbox.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['layout']['Dropdown'];
        delete app.plugins.plugins['view']['Dropdown'];
        $('body').empty();
    });

    describe('keyboard events', function() {
        var event;

        beforeEach(function() {
            event = $.Event('keydown');
        });

        afterEach(function() {
            event = undefined;
        });

        describe('when there are no open dropdowns', function() {
            var keys = [
                $.ui.keyCode.ESCAPE,
                $.ui.keyCode.UP,
                $.ui.keyCode.DOWN
            ];
            using('captured keys', keys, function(key) {
                var noop = function() {
                    var spyOnCloseDropdown =
                            sandbox.spy(layout, 'closeDropdown'),
                        spyOnFocusSubmenuItem =
                            sandbox.spy(layout, '_focusSubmenuItem'),
                        spyOnToggleSubmenu =
                            sandbox.spy(layout, '_toggleSubmenu');
                    event.keyCode = event.which = key;
                    layout.$el.trigger(event);
                    expect(spyOnCloseDropdown).not.toHaveBeenCalled();
                    expect(spyOnFocusSubmenuItem).not.toHaveBeenCalled();
                    expect(spyOnToggleSubmenu).not.toHaveBeenCalled();
                };
                it('should not do anything when any keys are pressed', noop);
            });
        });

        //FIXME: SC-5006 will refactor those tests.
        describe('when a dropdown is open', function() {
            let toggle = null;
            using('keys that should close dropdown', [
                { keyCode: $.ui.keyCode.ESCAPE },
                { keyCode: $.ui.keyCode.TAB }
            ], function(dataProvider) {
                it('should close the dropdown', function() {
                    var spyOnFocusSubmenuItem =
                            sandbox.spy(layout, '_focusSubmenuItem'),
                        spyOnToggleSubmenu =
                            sandbox.spy(layout, '_toggleSubmenu'),
                        toggle = layout.$('[data-bs-toggle="dropdown"]').first();
                    // toggle open the first dropdown
                    toggle.click();
                    expect(layout.isDropdownOpen()).toBe(true);
                    // now close it
                    event.keyCode = event.which = dataProvider.keyCode;
                    layout.$el.trigger(event);
                    expect(layout.isDropdownOpen()).toBe(false);
                    expect(spyOnFocusSubmenuItem).not.toHaveBeenCalled();
                    expect(spyOnToggleSubmenu).not.toHaveBeenCalled();
                });
            });

            it('should not change focus when UP is pressed', function() {
                toggle = layout.$('[data-bs-toggle="dropdown"]').first(),
                    dropdown = toggle.parent(),
                    createItem = dropdown.find(
                        '.dropdown-menu a[data-route="#Accounts/create"]'
                    );
                // toggle open the first dropdown
                toggle.click();
                // apply focus to the create item
                createItem.focus();
                // now move up
                event.keyCode = event.which = $.ui.keyCode.UP;
                layout.$el.trigger(event);
                // focus should still be on the create item
                expect(document.activeElement == createItem[0]).toBe(true);
            });

            xit('should move focus down the list when DOWN is pressed', function() {
                toggle = layout.$('[data-bs-toggle="dropdown"]').first(),
                    dropdown = toggle.parent(),
                    createItem = dropdown.find(
                        '.dropdown-menu a[data-route="#Accounts/create"]'
                    ),
                    listItem = dropdown.find(
                        '.dropdown-menu a[data-route="#Accounts"]'
                    );
                // toggle open the first dropdown
                toggle.click();
                // apply focus to the create item
                createItem.focus();
                // now move down
                event.keyCode = event.which = $.ui.keyCode.DOWN;
                layout.$el.trigger(event);
                // focus should be on the list item
                expect(document.activeElement).toBe(listItem[0]);
            });

            xit('should not change focus when DOWN is pressed', function() {
                toggle = layout.$('[data-bs-toggle="dropdown"]').first(),
                    dropdown = toggle.parent(),
                    listItem = dropdown.find(
                        '.dropdown-menu a[data-route="#Accounts"]'
                    );
                // toggle open the first dropdown
                toggle.click();
                // apply focus to the list item
                listItem.focus();
                // now move down
                event.keyCode = event.which = $.ui.keyCode.DOWN;
                layout.$el.trigger(event);
                // focus should still be on the list item
                expect(document.activeElement).toBe(listItem[0]);
            });
        });
    });
});
