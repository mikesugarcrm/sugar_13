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

describe('Base.Layout.SidebarNavItemGroupModules', function() {
    let layout;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', null, 'sidebar-nav-item-group-modules');
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
    });

    describe('resetModuleList', function() {
        let modules;

        beforeEach(function() {
            modules = {
                Accounts: {
                    css_class: 'default pinned'
                },
                Cases: {
                    css_class: 'default'
                },
                Administration: {
                    css_class: ''
                }
            };

            sinon.stub(layout, '_disposeComponents');
            sinon.stub(layout, '_getDefaultModules').returns(modules);
            sinon.stub(layout, 'addModuleItem');
            sinon.stub(layout, '_getAppContextModule').returns('Accounts');
            sinon.stub(layout, '_checkModuleComponentExists');
        });

        it('should dispose all existing components before re-creating module items', function() {
            layout.resetModuleList();
            expect(layout._disposeComponents).toHaveBeenCalled();
            expect(layout._disposeComponents).toHaveBeenCalledBefore(layout._createModuleItem);
        });

        it('should create module items for each of the modules in the module list', function() {
            layout.resetModuleList();
            expect(layout.addModuleItem).toHaveBeenCalledWith('Accounts', {css_class: 'default pinned'});
            expect(layout.addModuleItem).toHaveBeenCalledWith('Cases', {css_class: 'default'});
            expect(layout.addModuleItem).toHaveBeenCalledWith('Administration', {css_class: ''});
        });

        it('should set the active module after creating the module items', function() {
            layout.resetModuleList();
            expect(layout._checkModuleComponentExists).toHaveBeenCalledWith('Accounts');
            expect(layout._checkModuleComponentExists).toHaveBeenCalledAfter(layout.addModuleItem);
        });
    });

    describe('_getNumberPinned', function() {
        using('different max pinned settings', [
            {
                value: 6,
                expected: 6
            },
            {
                value: 0,
                expected: 1
            },
            {
                value: 101,
                expected: 100
            },
        ], function(testSettings) {
            it('should use the user preference if there is one', function() {
                sinon.stub(app.user, 'getPreference').returns(testSettings.value);
                app.config.maxPinnedModules = 4;
                expect(layout._getNumberPinned()).toEqual(testSettings.expected);
            });

            it('should fall back to use the admin config if there is no user preference', function() {
                sinon.stub(app.user, 'getPreference').returns(undefined);
                app.config.maxPinnedModules = testSettings.value;
                expect(layout._getNumberPinned()).toEqual(testSettings.expected);
            });
        });
    });

    describe('addModuleItem', function() {
        let createdItem;

        beforeEach(function() {
            createdItem = {
                render: sinon.stub()
            };

            sinon.stub(app.metadata, 'getFullModuleList').returns({
                Accounts: 'Accounts'
            });
            sinon.stub(layout, '_createModuleItem').returns(createdItem);
            sinon.stub(layout, 'addComponent');
        });

        it('should not try to add the item if it is not a valid module', function() {
            layout.addModuleItem('Potato');
            expect(layout._createModuleItem).not.toHaveBeenCalled();
        });

        it('should render the module item after it is added to the layout', function() {
            layout.addModuleItem('Accounts');
            expect(layout.addComponent).toHaveBeenCalledWith(createdItem);
            expect(createdItem.render).toHaveBeenCalledAfter(layout.addComponent);
        });
    });

    describe('_checkModuleComponentExists', function() {
        let mockModuleComponents;

        beforeEach(function() {
            mockModuleComponents = {
                Accounts: {
                    setActive: sinon.stub()
                },
                Contacts: {
                    setActive: sinon.stub()
                }
            };

            layout._moduleComponents = mockModuleComponents;

            sinon.stub(layout, 'addModuleItem');
        });

        it('should add a new module component if a new module is loaded', function() {
            layout._checkModuleComponentExists('NewModule');
            expect(layout.addModuleItem).toHaveBeenCalledWith('NewModule');
        });

        it('should not try to add a Home module item', function() {
            layout._checkModuleComponentExists('Home');
            expect(layout.addModuleItem).not.toHaveBeenCalled();
        });
    });

    describe('adjustMenuItems', function() {
        using('various sizes', [{
            // 8 pins expected, limited by maxPinned setting
            // Active item is included
            groupHeight: 500,
            minGroupHeight: 40,
            maxPinned: 8,
            expectedPins: 8,
            activePin: 7
        },{
            // 8 pins expected, limited by maxPinned setting
            // Active item is not included
            groupHeight: 500,
            minGroupHeight: 40,
            maxPinned: 8,
            expectedPins: 8,
            activePin: 10
        },{
            // 4 pins expected, limited by space
            // Active item is included
            groupHeight: 180,
            minGroupHeight: 40,
            maxPinned: 8,
            expectedPins: 4,
            activePin: 4
        },{
            // 4 pins expected, limited by space
            // Active item is not included
            groupHeight: 180,
            minGroupHeight: 40,
            maxPinned: 8,
            expectedPins: 4,
            activePin: 7
        },{
            // 4 pins expected, limited by space
            // There is no active item
            groupHeight: 180,
            minGroupHeight: 40,
            maxPinned: 8,
            expectedPins: 4,
            activePin: null
        }], function(testValues) {
            it('should adjust the items shown', function() {
                layout.maxPinned = testValues.maxPinned;

                // Set up the jQuery object mocks
                let navItems = '';
                for (let i = 0; i < 10; i++) {
                    navItems += '<div class="sidebar-nav-item"></div>';
                }
                navItems = $(navItems);
                if (_.isNumber(testValues.activePin)) {
                    $(navItems[testValues.activePin - 1]).addClass('active');
                }
                sinon.stub(layout.$el, 'height').returns(testValues.groupHeight);
                sinon.stub(layout.$el, 'css').withArgs('min-height').returns(testValues.minGroupHeight);
                sinon.stub(layout.$el, 'find').returns(navItems);

                // Verify the range of pinned items is correct. If the active
                // item is outside the range, expect room to be made for it
                layout.adjustMenuItems();
                let spacesForPins = Math.floor(testValues.groupHeight / testValues.minGroupHeight);
                let activeItemIsOutsidePins = testValues.activePin && testValues.activePin > spacesForPins;
                let limitedBySpace = testValues.maxPinned > spacesForPins;
                if (limitedBySpace && activeItemIsOutsidePins) {
                    spacesForPins--;
                }
                expect(navItems.slice(0, spacesForPins).hasClass('pinned')).toBe(true);
                expect(navItems.slice(spacesForPins).hasClass('pinned')).toBe(false);
            });
        });
    });
});
