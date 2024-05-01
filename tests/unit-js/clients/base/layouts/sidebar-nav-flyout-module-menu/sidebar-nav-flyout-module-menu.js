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

describe('Base.Layout.SidebarNavFlyoutModuleMenu', function() {
    let layout;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        layout = SugarTest.createLayout('base', 'Accounts', 'sidebar-nav-flyout-module-menu', {
            collectionSettings: {
                recently_viewed: {
                    filter: {
                        $tracker: '-7 DAY'
                    },
                    icon: 'sicon-clock',
                    limit: 3,
                    toggle: {
                        limit: 10,
                        label_toggle: 'toggle',
                        label_untoggle: 'untoggle'
                    }
                },
                favorites: {
                    filter: {
                        $favorite: ''
                    },
                    icon: 'sicon-star-fill',
                    limit: 3
                }
            }
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
        layout.dispose();
        layout = null;
    });

    describe('_initConfig', function() {
        beforeEach(function() {
            sinon.stub(app.metadata, 'getModuleNames').returns(['Accounts', 'Contacts', 'Forecasts']);
        });

        it('should exclude modules from a collection if specified', function() {
            layout.meta.collectionSettings.recently_viewed.modules = 'all';
            layout.meta.collectionSettings.recently_viewed.excludedModules = ['Forecasts'];
            layout._initConfig();
            expect(layout._collectionSettings.recently_viewed.modules).toEqual(['Accounts', 'Contacts']);
        });
    });

    describe('_getMenuComponents', function() {
        let mockActions;

        beforeEach(function() {
            mockActions = ['action1', 'action2'];

            sinon.stub(app.lang, 'get').returns('Accounts');
            sinon.stub(layout, '_getMenuActions').returns(mockActions);
        });

        it('should set the second component to contain the base menu actions', function() {
            let components = layout._getMenuComponents();
            expect(components[1].view.actions).toEqual(mockActions);
        });

        it('should create a list component for each collection', function() {
            let components = layout._getMenuComponents();
            expect(components[2].view.type).toEqual('sidebar-nav-flyout-actions');
            expect(components[2].view.name).toEqual('recently_viewed');
            expect(components[3].view.type).toEqual('sidebar-nav-flyout-actions');
            expect(components[3].view.name).toEqual('favorites');
        });
    });

    describe('_updateCollections', function() {
        let mockCollection;
        let mockComponent;

        beforeEach(function() {
            mockCollection = {
                fetch: function() {}
            };
            sinon.stub(mockCollection, 'fetch').callsFake(function(options) {
                options.success();
            });

            mockComponent = {
                updateActions: sinon.stub()
            };
            sinon.stub(layout, 'getComponent').returns(mockComponent);

            sinon.stub(layout, 'getCollection').returns(mockCollection);
            sinon.stub(layout, '_createCollectionActions');
        });

        it('should fetch each collection', function() {
            layout._updateCollections();
            expect(layout.getCollection).toHaveBeenCalledWith('recently_viewed');
            expect(layout.getCollection).toHaveBeenCalledWith('favorites');
            expect(mockCollection.fetch.callCount).toEqual(2);
        });

        it('should update the action list for each collection', function() {
            layout._updateCollections();
            expect(mockComponent.updateActions.callCount).toEqual(2);
            expect(layout.getComponent).toHaveBeenCalledWith('recently_viewed');
            expect(layout._createCollectionActions).toHaveBeenCalledWith('recently_viewed');
            expect(layout.getComponent).toHaveBeenCalledWith('favorites');
            expect(layout._createCollectionActions).toHaveBeenCalledWith('favorites');
        });
    });

    describe('createCollectionToggleAction', function() {
        it('should have the toggle label if the collection is untoggled', function() {
            layout._collectionSettings.recently_viewed.toggle.toggled = false;
            expect(layout._createCollectionToggleAction('recently_viewed').label).toEqual('toggle');
        });

        it('should have the untoggle label if the collection is toggled', function() {
            layout._collectionSettings.recently_viewed.toggle.toggled = true;
            expect(layout._createCollectionToggleAction('recently_viewed').label).toEqual('untoggle');
        });
    });
});
