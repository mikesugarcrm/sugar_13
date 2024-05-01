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
describe('DashboardFilters plugin', function() {
    let plugin;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('DashboardFilters');
        plugin = app.plugins.plugins.view.DashboardFilters;
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app = null;
    });

    describe('initFiltersProperties', function() {
        it('should initialize filter properties correctly', function() {
            plugin.initFiltersProperties();

            // Assert the initial state of filter properties
            expect(plugin._activeFilterGroupId).toBe(false);
            expect(plugin._hasGroupFilters).toBe(false);
            expect(plugin._filterGroups).toEqual({});
            expect(plugin._filterGroupsView).toEqual({});
        });
    });

    describe('invalidGroupSave', function() {
        it('should mark invalid groups correctly', function() {
            var invalidGroups = ['group1', 'group2',];

            var group1 = createMockGroupView(true);
            var group2 = createMockGroupView(true);
            plugin._filterGroupsView = {
                group1: group1,
                group2: group2,
            };

            // Call the function under test
            plugin.invalidGroupSave(invalidGroups);

            // Assert that the corresponding group views are marked as invalid
            expect(plugin._filterGroupsView.group1.isGroupInvalid()).toBe(true);
            expect(plugin._filterGroupsView.group2.isGroupInvalid()).toBe(true);
        });
    });

    describe('isValid', function() {
        it('should return true if all groups are valid', function() {
            // Create mock group views
            var group1 = createMockGroupView(true);
            var group2 = createMockGroupView(true);

            // Set up the plugin with the mock group views
            plugin._filterGroupsView = {
                group1: group1,
                group2: group2,
            };

            // Call the function under test
            var isValid = plugin.isValid();

            // Assert that the function returns true
            expect(isValid).toBe(true);
        });

        it('should return false if any group is invalid', function() {
            // Create mock group views
            var group1 = createMockGroupView(true);
            var group2 = createMockGroupView(false);

            // Set up the plugin with the mock group views
            plugin._filterGroupsView = {
                group1: group1,
                group2: group2,
            };

            // Call the function under test
            var isValid = plugin.isValid();

            // Assert that the function returns false
            expect(isValid).toBe(false);
        });
    });

    // Helper function to create a mock group view
    function createMockGroupView(isValid) {
        return {
            isValid: function() {
                return isValid;
            },
            toggleGroupInvalid: function() {

            },
            dispose: function() {

            },
            isGroupInvalid: function() {
                return true;
            },
        };
    }
});
