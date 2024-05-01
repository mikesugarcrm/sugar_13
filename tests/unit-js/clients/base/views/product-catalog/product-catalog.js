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
describe('Base.Views.ProductCatalog', function() {
    var app;
    var view;
    var viewMeta;
    var context;
    var layout;
    var addClassStub;
    var removeClassStub;
    var showStub;
    var hideStub;
    var offStub;
    var initObj;
    var closestComponent;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewMeta = {
            config: false
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'create', {});
        closestComponent = {
            on: $.noop,
            off: $.noop,
            triggerBefore: sinon.stub()
        };
        sinon.stub(layout, 'closestComponent').callsFake(function() {
            return closestComponent;
        });
        initObj = {
            context: context,
            layout: layout
        };
        view = SugarTest.createView('base', null, 'product-catalog', viewMeta, context, true, layout);

        removeClassStub = sinon.stub();
        addClassStub = sinon.stub();
        showStub = sinon.stub();
        hideStub = sinon.stub();
        offStub = sinon.stub();

        sinon.stub(view, '$').callsFake(function() {
            return {
                addClass: addClassStub,
                removeClass: removeClassStub,
                show: showStub,
                hide: hideStub,
                off: offStub
            };
        });
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;

        sinon.restore();
        addClassStub = null;
        removeClassStub = null;
        showStub = null;
        hideStub = null;
        offStub = null;
    });

    describe('initialize()', function() {
        it('should set events for keyup product-catalog-search-term', function() {
            expect(view.events).toEqual({
                'keyup .product-catalog-search-term': 'onSearchTermChange'
            });
        });

        it('should set activeFetchCt to 0', function() {
            expect(view.activeFetchCt).toBe(0);
        });

        describe('when calling functions', function() {
            beforeEach(function() {
                sinon.spy(view, 'getSearchTextPlaceholder');
                sinon.spy(view, 'initializeProviderModules');
                sinon.spy(view, 'getTreeStateConfigSettings');
                sinon.spy(view, 'getSpriteSheetManifestObject');
                view.initialize(initObj);
            });

            it('should call getSearchTextPlaceholder to set searchText', function() {
                expect(view.getSearchTextPlaceholder).toHaveBeenCalled();
            });

            it('should call initializeProviderModules to set searchText', function() {
                expect(view.initializeProviderModules).toHaveBeenCalled();
            });

            it('should call getTreeStateConfigSettings to set searchText', function() {
                expect(view.getTreeStateConfigSettings).toHaveBeenCalled();
            });

            it('should call getSpriteSheetManifestObject to set searchText', function() {
                expect(view.getSpriteSheetManifestObject).toHaveBeenCalled();
            });
        });

        it('should set searchText to LBL_SEARCH_CATALOG_PLACEHOLDER', function() {
            expect(view.searchText).toBe('LBL_SEARCH_CATALOG_PLACEHOLDER');
        });

        it('should set dataLoaded to False', function() {
            expect(view.dataLoaded).toBeFalsy();
        });

        it('should set treeModule', function() {
            expect(view.treeModule).toBe('ProductTemplates');
        });
    });

    describe('getSearchTextPlaceholder()', function() {
        it('should return LBL_SEARCH_CATALOG_PLACEHOLDER', function() {
            expect(view.getSearchTextPlaceholder()).toBe('LBL_SEARCH_CATALOG_PLACEHOLDER');
        });
    });

    describe('initializeProviderModules()', function() {
        it('should set treeModule', function() {
            view.initializeProviderModules();
            expect(view.treeModule).toBe('ProductTemplates');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(app.controller.context, 'on').callsFake(function() {});
            view.bindDataChange();
        });

        it('should set an event listener on the context for productCatalogDashlet:add:complete', function() {
            var viewDetails = view.closestComponent('record') ?
                view.closestComponent('record') :
                view.closestComponent('create');

            if (!_.isUndefined(viewDetails)) {
                expect(app.controller.context.on)
                    .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add:complete');
            }
        });
    });

    describe('onSearchTermChange()', function() {
        beforeEach(function() {
            sinon.stub(jQuery.fn, 'val').callsFake(function() {
                return 'testTerm';
            });
            sinon.stub(view, 'loadData').callsFake(function() {});
        });

        it('should set previous search term to the current search term', function() {
            view.onSearchTermChange({});

            expect(view.previousSearchTerm).toBe('testTerm');
        });

        it('should call loadData with the current search term', function() {
            view.onSearchTermChange({});

            expect(view.loadData).toHaveBeenCalledWith({
                searchTerm: 'testTerm'
            });
        });

        it('should not call loadData twice if the same search term is used', function() {
            view.onSearchTermChange({});
            view.onSearchTermChange({});

            expect(view.loadData.calledOnce).toBeTruthy();
        });
    });

    describe('loadData()', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function(term) {
                return term;
            });
            sinon.stub(app.api, 'call').callsFake(function() {});
            sinon.stub(view, 'toggleLoading').callsFake(function() {});
            view.isConfig = false;
        });

        it('should hide the product catalog no results message', function() {
            view.loadData();

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(addClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should call toggleLoading with true, true', function() {
            view.loadData();

            expect(view.toggleLoading).toHaveBeenCalledWith(true);
        });

        it('should increment activeFetchCt', function() {
            view.activeFetchCt = 0;
            view.loadData();

            expect(view.activeFetchCt).toBe(1);
        });

        it('should add the search term to the url if used', function() {
            view.loadData({
                searchTerm: 'test1'
            });

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                filter: 'test1'
            });
        });

        it('should add the search term to the url with encodeURI if used', function() {
            view.loadData({
                searchTerm: 'test%1'
            });

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                filter: 'test%1'
            });
        });
    });

    describe('toggleLoading()', function() {
        it('should call show if startLoading is true', function() {
            view.toggleLoading(true);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(showStub).toHaveBeenCalled();
        });

        it('should call show if startLoading is false', function() {
            view.toggleLoading(false);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('_onCatalogFetchSuccess()', function() {
        var responseData;
        var emptyResponseData;
        var checkBuildPhaserStub;
        var phaserObj;
        var dispatchStub;

        beforeEach(function() {
            checkBuildPhaserStub = sinon.stub();
            dispatchStub = sinon.stub();

            view.activeFetchCt = 1;
            responseData = {
                records: [{
                    id: 'record1'
                }],
                next_offset: -1
            };
            emptyResponseData = {
                records: [],
                next_offset: -1
            };
            phaserObj = {
                destroy: function() {},
                events: {
                    destroy: function() {},
                    onSetTreeData: {
                        dispatch: dispatchStub
                    }
                }
            };
            sinon.stub(view, 'checkBuildPhaser').callsFake(function() {});
        });

        afterEach(function() {
            responseData = null;
            emptyResponseData = null;
            checkBuildPhaserStub = null;
            dispatchStub = null;
            phaserObj = null;
            view.phaser = null;
        });

        it('should set jsTreeData', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.jsTreeData.records[0].id).toBe('record1');
        });

        it('should decrement activeFetchCt', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.activeFetchCt).toBe(0);
        });

        it('should show no results message if there are no results', function() {
            view._onCatalogFetchSuccess(emptyResponseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(removeClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should hide no results if there are results', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(addClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should show search terms if there are results', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-search-term');
            expect(removeClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should set dataLoaded to true', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.dataLoaded).toBeTruthy();
        });

        it('should call removeClass with no data', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(removeClassStub).toHaveBeenCalled();
        });

        it('should call checkBuildPhaser if phaser is undefined', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.checkBuildPhaser).toHaveBeenCalled();
        });

        it('should call onSetTreeData if phaser is ready', function() {
            view.phaser = phaserObj;
            view._onCatalogFetchSuccess(responseData);

            expect(dispatchStub).toHaveBeenCalled();
        });
    });

    describe('checkBuildPhaser()', function() {
        beforeEach(function() {
            sinon.stub(view, '_createPhaser').callsFake(function() {});
        });

        it('should not call _createPhaser if only dataLoaded is true', function() {
            view.dataLoaded = true;
            view.phaserReady = false;
            view.checkBuildPhaser();

            expect(view._createPhaser).not.toHaveBeenCalled();
        });

        it('should not call _createPhaser if only phaserReady is true', function() {
            view.dataLoaded = false;
            view.phaserReady = true;
            view.checkBuildPhaser();

            expect(view._createPhaser).not.toHaveBeenCalled();
        });

        it('should call _createPhaser if both dataLoaded and phaserReady are true', function() {
            view.dataLoaded = true;
            view.phaserReady = true;
            view.checkBuildPhaser();

            expect(view._createPhaser).toHaveBeenCalled();
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, 'checkBuildPhaser').callsFake(function() {});
        });

        it('should call checkBuildPhaser', function() {
            view.render();

            expect(view.checkBuildPhaser).toHaveBeenCalled();
        });
    });

    describe('_onMouseWheelChange()', function() {
        var mouseEvent;
        var phaserObj;
        var dispatchStub;
        var preventDefaultStub;

        beforeEach(function() {
            dispatchStub = sinon.stub();
            preventDefaultStub = sinon.stub();
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, 'checkBuildPhaser').callsFake(function() {});
            mouseEvent = {
                preventDefault: preventDefaultStub,
                originalEvent: {
                    deltaY: 4.71
                }
            };
            phaserObj = {
                destroy: function() {},
                device: {
                    firefox: false
                },
                events: {
                    destroy: function() {},
                    onScrollWheel: {
                        dispatch: dispatchStub
                    }
                }
            };
            view.phaser = phaserObj;
        });

        afterEach(function() {
            mouseEvent = null;
            preventDefaultStub = null;
            dispatchStub = null;
            phaserObj = null;
            view.phaser = null;
        });

        it('should call preventDefault on the incoming mouse event', function() {
            view._onMouseWheelChange.call(view, mouseEvent);

            expect(preventDefaultStub).toHaveBeenCalled();
        });

        it('should call preventDefault on the incoming mouse event', function() {
            view._onMouseWheelChange.call(view, mouseEvent);

            expect(dispatchStub).toHaveBeenCalledWith(4.71);
        });

        it('should send phaser the scroll delta for IE 11', function() {
            mouseEvent.type = 'mousewheel';
            mouseEvent.originalEvent.wheelDelta = 120;
            view._onMouseWheelChange(mouseEvent);

            expect(dispatchStub).toHaveBeenCalledWith(6);
        });
    });

    describe('onPhaserTreeReadyHandler()', function() {
        var phaserObj;
        var dispatchStub;
        var jsTreeData;

        beforeEach(function() {
            dispatchStub = sinon.stub();
            jsTreeData = {
                records: [{
                    id: 'record1'
                }],
                next_offset: -1
            };
            phaserObj = {
                destroy: function() {},
                events: {
                    destroy: function() {},
                    onSetTreeData: {
                        dispatch: dispatchStub
                    }
                }
            };
            view.phaser = phaserObj;
        });

        afterEach(function() {
            phaserObj = null;
            dispatchStub = null;
            jsTreeData = null;
            view.phaser = null;
        });

        it('should dispatch to the phaser onSetTreeData event the jsTree data', function() {
            view.jsTreeData = jsTreeData;
            view.onPhaserTreeReadyHandler();

            expect(dispatchStub).toHaveBeenCalledWith(jsTreeData);
        });
    });

    describe('getSpriteSheetManifestObject()', function() {
        beforeEach(function() {
            sinon.stub(view, '_getSpriteSheets').callsFake(function() {
                return 'test1';
            });
        });

        it('should get back an object with atlasJSONHash in it', function() {
            expect(view.getSpriteSheetManifestObject()).toEqual({
                atlasJSONHash: 'test1'
            });
        });
    });

    describe('_getSpriteSheets()', function() {
        it('should return an array with a specific object in it', function() {
            expect(view._getSpriteSheets()).toEqual([{
                id: 'prodCatTS',
                imagePath: 'clients/base/views/product-catalog/product-catalog-ss.png',
                dataPath: 'clients/base/views/product-catalog/product-catalog-ss.json'
            }]);
        });
    });

    describe('_getTreeNodeTextColor()', function() {
        beforeEach(function() {
            view.treeConfig = {
                categoryColor: 'categoryColorBlue',
                itemColor: 'itemColorRed'
            };
        });

        it('should return the category color for itemType category', function() {
            expect(view._getTreeNodeTextColor('category', {})).toBe('categoryColorBlue');
        });

        it('should return the category color for itemType product', function() {
            expect(view._getTreeNodeTextColor('product', {})).toBe('itemColorRed');
        });

        it('should return the category color for itemType showMore', function() {
            expect(view._getTreeNodeTextColor('showMore', {})).toBe('itemColorRed');
        });
    });

    describe('_getTreeNodeIconName()', function() {
        var result;
        beforeEach(function() {
            sinon.stub(view, '_getTreeIconClosedStateName').callsFake(function() {
                return 'closed';
            });
            sinon.stub(view, '_getTreeIconOpenStateName').callsFake(function() {
                return 'open';
            });
        });

        afterEach(function() {
            result = null;
        });

        it('should return category icon for closed state', function() {
            result = view._getTreeNodeIconName('category', {
                state: 'closed'
            });

            expect(result).toBe('closed');
        });

        it('should return category icon for open state', function() {
            result = view._getTreeNodeIconName('category', {
                state: 'open'
            });

            expect(result).toBe('open');
        });

        it('should return product icon', function() {
            result = view._getTreeNodeIconName('product', {});

            expect(result).toBe('list-alt');
        });

        it('should return showMore icon', function() {
            result = view._getTreeNodeIconName('showMore', {});

            expect(result).toBe('empty');
        });
    });

    describe('_getTreeIconOpenStateName()', function() {
        it('should return tree icon for open state', function() {
            expect(view._getTreeIconOpenStateName()).toBe('folder-open-o');
        });
    });

    describe('_getTreeIconClosedStateName()', function() {
        it('should return tree icon for closed state', function() {
            expect(view._getTreeIconClosedStateName()).toBe('folder');
        });
    });

    describe('_getTreeIconHeight()', function() {
        beforeEach(function() {
            view.treeConfig = {
                iconHeight: 16
            };
        });

        it('should return icon height 12 for list-alt icons', function() {
            expect(view._getTreeIconHeight('list-alt', {})).toBe(12);
        });

        it('should return icon height 16 for other icons', function() {
            expect(view._getTreeIconHeight('folder', {})).toBe(16);
        });
    });

    describe('_getTreeIconWidth()', function() {
        beforeEach(function() {
            view.treeConfig = {
                iconWidth: 16
            };
        });

        it('should return a width of 16', function() {
            expect(view._getTreeNodeSpriteSheetId('', '', {})).toBe('prodCatTS');
        });
    });

    describe('_getTreeNodeSpriteSheetId()', function() {
        it('should return tree icon for closed state', function() {
            expect(view._getTreeIconClosedStateName()).toBe('folder');
        });
    });

    describe('_onTreeNodeItemClicked()', function() {
        var target;
        beforeEach(function() {
            sinon.stub(view, '_onTreeNodeCategoryClicked').callsFake($.noop);
            sinon.stub(view, '_onTreeNodeIconClicked').callsFake($.noop);
            sinon.stub(view, '_onTreeNodeNameClicked').callsFake($.noop);
            view.game = {
                _view: view
            };
        });

        afterEach(function() {
            target = null;
        });

        it('should call _onTreeNodeCategoryClicked when item type is category', function() {
            target = {
                _itemType: 'category'
            };
            view._onTreeNodeItemClicked(target);

            expect(view._onTreeNodeCategoryClicked).toHaveBeenCalledWith(target, false);
        });

        it('should call _onTreeNodeCategoryClicked when item type is showMore', function() {
            target = {
                _itemType: 'showMore'
            };
            view._onTreeNodeItemClicked(target);

            expect(view._onTreeNodeCategoryClicked).toHaveBeenCalledWith(target, false);
        });

        it('should call _onTreeNodeNameClicked when item type is not category nor showMore', function() {
            target = {
                _itemType: 'product'
            };
            view._onTreeNodeItemClicked(target);

            expect(view._onTreeNodeNameClicked).toHaveBeenCalledWith(target);
        });
    });

    describe('_onTreeNodeIconClicked()', function() {
        var target;
        beforeEach(function() {
            sinon.stub(view, '_fetchRecord').callsFake($.noop);
            target = {
                _itemId: 'prod1'
            };
        });

        afterEach(function() {
            target = null;
        });

        it('should call _fetchRecord with itemId', function() {
            view._onTreeNodeIconClicked(target);

            expect(view._fetchRecord).toHaveBeenCalledWith('prod1');
        });
    });

    describe('_onTreeNodeNameClicked()', function() {
        var target;
        beforeEach(function() {
            sinon.stub(view, '_fetchRecord').callsFake($.noop);
            target = {
                _itemId: 'prod2'
            };
        });

        afterEach(function() {
            target = null;
        });

        it('should call _fetchRecord with itemId', function() {
            view._onTreeNodeNameClicked(target);

            expect(view._fetchRecord).toHaveBeenCalledWith('prod2');
        });
    });

    describe('_getPhaserCanvasId()', function() {
        it('should return the correct canvas ID based on the view\'s CID', function() {
            expect(view._getPhaserCanvasId()).toBe(`product-catalog-canvas-${view.cid}`);
        });
    });

    describe('getPhaserGameConfig()', function() {
        beforeEach(function() {
            sinon.stub(view, '_getPhaserCanvasId').callsFake(function() {
                return 'product-catalog-canvas-123';
            });
            sinon.stub(view, '_getPhaserGameConfig').callsFake(function(cfg) {
                return cfg;
            });
            view.$.restore();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    off: $.noop,
                    closest: function() {
                        return {
                            height: function() {
                                return 320;
                            },
                            find: function() {
                                return {
                                    width: function() {
                                        return 250;
                                    },
                                    innerHeight: function() {
                                        return 60;
                                    },
                                };
                            },
                        };
                    }
                };
            });
            window.Phaser = {
                CANVAS: 'canvas'
            };
        });
        afterEach(function() {
            delete window.Phaser;
        });

        it('should return a phaser config object', function() {
            expect(view.getPhaserGameConfig()).toEqual({
                height: 260,
                parent: 'product-catalog-canvas-123',
                renderer: 'canvas',
                transparent: true,
                width: 250
            });
        });
    });

    describe('_getPhaserGameConfig()', function() {
        var cfg;
        beforeEach(function() {
            cfg = {
                test1: 'hello'
            };
        });

        afterEach(function() {
            cfg = null;
        });

        it('should return the same object', function() {
            expect(view._getPhaserGameConfig(cfg)).toBe(cfg);
        });
    });

    describe('getStates()', function() {
        beforeEach(function() {
            sinon.stub(view, '_getBootState').callsFake(function() {
                return 'bootState';
            });
            sinon.stub(view, '_getLoadState').callsFake(function() {
                return 'loadState';
            });
            sinon.stub(view, '_getTreeState').callsFake(function() {
                return 'treeState';
            });
            sinon.stub(view, '_getAdditionalStates').callsFake(function(states) {
                return states;
            });
        });

        it('should call _getBootState', function() {
            view.getStates();

            expect(view._getBootState).toHaveBeenCalled();
        });

        it('should call _getLoadState', function() {
            view.getStates();

            expect(view._getLoadState).toHaveBeenCalled();
        });

        it('should call _getTreeState', function() {
            view.getStates();

            expect(view._getTreeState).toHaveBeenCalled();
        });

        it('should return the states object', function() {
            expect(view.getStates()).toEqual({
                boot: 'bootState',
                load: 'loadState',
                tree: 'treeState'
            });
        });
    });

    describe('_getAdditionalStates()', function() {
        var states;
        beforeEach(function() {
            states = {
                test1: 'hello'
            };
        });

        afterEach(function() {
            states = null;
        });

        it('should return the states object', function() {
            expect(view._getAdditionalStates(states)).toBe(states);
        });
    });

    describe('_fetchMoreRecords()', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function(term) {
                return term;
            });
            sinon.stub(app.api, 'call').callsFake(function() {});
            sinon.stub(view, 'toggleLoading').callsFake(function() {});
            view.isConfig = false;
        });

        it('should call toggleLoading with true, false', function() {
            view._fetchMoreRecords('record1', undefined, function() {});

            expect(view.toggleLoading).toHaveBeenCalledWith(true);
        });

        it('should increment activeFetchCt', function() {
            view.activeFetchCt = 0;
            view._fetchMoreRecords('record1', undefined, function() {});

            expect(view.activeFetchCt).toBe(1);
        });

        it('should add the offset to the url if used', function() {
            view._fetchMoreRecords('record1', 10, function() {});

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                root: 'record1',
                offset: 10
            });
        });
    });

    describe('_fetchRecord()', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function(id) {
                return 'ProductTemplates/' + id;
            });
            sinon.stub(app.api, 'call').callsFake(function() {});
            view._fetchRecord('test', {});
        });

        it('should call app.api.buildURL', function() {
            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('getFetchRecordModule()', function() {
        beforeEach(function() {
            view.treeModule = 'Products';
        });

        it('should return the treeModule', function() {
            expect(view.getFetchRecordModule()).toBe('Products');
        });
    });

    describe('_sendItemToRecord()', function() {
        var productTemplateData;
        var viewDetails;

        beforeEach(function() {
            productTemplateData = {
                id: 'prodTemplateId',
                name: 'prodTemplateName',
                date_entered: 'yesterday',
                date_modified: 'today',
                pricing_formula: 'ProfitMargin'
            };
            sinon.stub(app.controller.context, 'trigger').callsFake(function() {});

            viewDetails = view.closestComponent('record') ?
                view.closestComponent('record') :
                this.closestComponent('create');
        });

        afterEach(function() {
            productTemplateData = null;
        });

        it('should trigger context event when the closest component does not disallow it', function() {
            closestComponent.triggerBefore.returns(true);
            view._sendItemToRecord(productTemplateData);
            if (!_.isUndefined(viewDetails)) {
                expect(app.controller.context.trigger)
                    .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
            }
        });

        it('should not trigger context event when the closest component disallows it', function() {
            closestComponent.triggerBefore.returns(false);
            view._sendItemToRecord(productTemplateData);
            if (!_.isUndefined(viewDetails)) {
                expect(app.controller.context.trigger).not.toHaveBeenCalled();
            }
        });
    });

    describe('_massageDataBeforeSendingToRecord()', function() {
        var productTemplateData;
        var oldUserId;
        var newUserId = '1234';

        beforeEach(function() {
            productTemplateData = {
                id: 'prodTemplateId',
                name: 'prodTemplateName',
                date_entered: 'yesterday',
                date_modified: 'today',
                pricing_formula: 'ProfitMargin',
                status: 'Available',
                my_favorite: true,
                sync_key: 'my_sync_key',
                team_id: '1',
                team_set_id: '1',
                team_name: 'test',
                team_count: '1',
                team_count_link: 'test'
            };

            oldUserId = app.user.id;
            app.user.id = newUserId;

            view._massageDataBeforeSendingToRecord(productTemplateData);
        });

        afterEach(function() {
            productTemplateData = null;
        });

        it('should add product_template_id as the template id', function() {
            expect(productTemplateData.product_template_id).toBe('prodTemplateId');
        });

        it('should add product_template_name as the template name', function() {
            expect(productTemplateData.product_template_name).toBe('prodTemplateName');
        });

        it('should add assigned_user_id as the assigned_user_id', function() {
            expect(productTemplateData.assigned_user_id).toBe(newUserId);
        });

        using('fields that should be removed', [
            'id', 'date_entered', 'date_modified', 'pricing_formula', 'status', 'my_favorite', 'sync_key',
            'team_id', 'team_set_id', 'team_name', 'team_count', 'team_count_link'
        ], field => {
            it('should remove the field', () => {
                expect(productTemplateData[field]).toBeUndefined();
            });
        });
    });

    describe('_openItemInDrawer()', function() {
        var openStub;
        var ptModel;
        beforeEach(function() {
            openStub = sinon.stub();
            ptModel = app.data.createBean('ProductTemplates');
            sinon.stub(app.data, 'createBean').callsFake(function() {
                return ptModel;
            });
            app.drawer = {
                open: openStub
            };
            sinon.stub(view, '_getClosestComponent').callsFake(() => 'my_component');
            view._openItemInDrawer({});
        });

        afterEach(function() {
            openStub = null;
            delete app.drawer;
        });

        it('should call app.drawer.open', function() {
            expect(openStub).toHaveBeenCalledWith({
                layout: 'product-catalog-dashlet-drawer-record',
                context: {
                    module: 'ProductTemplates',
                    model: ptModel,
                    closestComponent: 'my_component'
                }
            });
        });
    });

    describe('_onProductDashletAddComplete()', function() {
        beforeEach(function() {
            view.isFetchActive = true;

            view._onProductDashletAddComplete();
        });

        it('should set isFetchActive to false', function() {
            expect(view.isFetchActive).toBeFalsy();
        });

        it('should call view.$ with #product-catalog-container-view.cid', function() {
            expect(view.$).toHaveBeenCalledWith(`#product-catalog-container-${view.cid}`);
        });

        it('should call removeClass with disabled', function() {
            expect(removeClassStub).toHaveBeenCalledWith('disabled');
        });
    });

    describe('_dispose()', function() {
        var phaserDestroyStub;
        var eventsDestroyStub;

        beforeEach(function() {
            phaserDestroyStub = sinon.stub();
            eventsDestroyStub = sinon.stub();

            view.phaser = {
                destroy: phaserDestroyStub,
                events: {
                    destroy: eventsDestroyStub
                }
            };
            sinon.stub(view, '_super').callsFake(function() {});
        });

        afterEach(function() {
            phaserDestroyStub = null;
            eventsDestroyStub = null;
        });

        it('should call off to stop listening for the wheel event', function() {
            view.wheelEventName = 'wheel';
            view._dispose();

            expect(view.$).toHaveBeenCalledWith(`.product-catalog-container-${view.cid}`);
            expect(offStub).toHaveBeenCalledWith('wheel');
        });

        it('should call destroy on phaser.events and phaser', function() {
            view._dispose();

            expect(eventsDestroyStub).toHaveBeenCalled();
            expect(phaserDestroyStub).toHaveBeenCalled();
        });
    });

    describe('treeState', function() {
        var treeState;

        beforeEach(function() {
            treeState = view._getTreeState();
            treeState.game = {
                _view: view
            };
        });

        describe('_createNode', function() {
            var group;
            var groupIndex;
            var node;
            var createdIcon;
            var createdText;
            var createdPreview;
            var createdHoverElem;

            beforeEach(function() {
                // Mock the arguments to _createNode
                group = {
                    width: 450,
                    add: sinon.stub(),
                    addAt: sinon.stub(),
                    onChildInputOver: {
                        add: sinon.stub()
                    },
                    onChildInputOut: {
                        add: sinon.stub()
                    }
                };
                groupIndex = 0;
                node = {
                    type: 'product',
                    id: '12345',
                    data: 'Node Text'
                };

                // Mock some dimension settings on the treeState object. This will help us make sure that sizes and
                // spacings are being calculated correctly
                treeState.iconStartX = 5;
                treeState.iconWidth = 32;
                treeState.iconTextPadding = 7;
                treeState.iconYOffset = 8;

                // Mock the objects created during _createNode
                createdIcon = {};
                createdText = {
                    width: 200
                };
                createdPreview = {};
                createdHoverElem = {};

                // Stub the utility functions to return certain values that we can check later
                sinon.stub(view, '_getTreeNodeTextColor').returns('black');
                sinon.stub(view, '_getTreeNodeIconName')
                    .withArgs('product').returns('list-alt')
                    .withArgs('preview').returns('preview');
                sinon.stub(view, '_getTreeNodeSpriteSheetId').returns('prodCatTS');

                // Stub the functions that create game elements to return our mock objects created above
                sinon.stub(treeState, '_createNodeIcon').returns(createdIcon);
                sinon.stub(treeState, '_createNodeText').returns(createdText);
                sinon.stub(treeState, '_createNodePreview').returns(createdPreview);
                sinon.stub(treeState, '_createNodeHoverElem').returns(createdHoverElem);
            });

            it('should create a card icon object', function() {
                treeState._createNode(group, node, groupIndex);
                expect(treeState._createNodeIcon).toHaveBeenCalledWith(node, jasmine.objectContaining({
                    startX: 13,
                    startY: treeState.iconYOffset,
                    iconSpriteSheetId: 'prodCatTS',
                    iconName: 'list-alt',
                    itemName: 'Node Text',
                    itemId: '12345',
                    itemType: 'product'
                }));
            });

            it('should create a text object', function() {
                treeState._createNode(group, node, groupIndex);
                expect(treeState._createNodeText).toHaveBeenCalledWith(node, jasmine.objectContaining({
                    startX: 44,
                    startY: 0,
                    display: {
                        font: treeState.itemFont,
                        fill: 'black'
                    },
                    itemName: 'Node Text',
                    itemId: '12345',
                    itemType: 'product'
                }));
            });

            it('should create a preview icon object if the node is a product', function() {
                treeState._createNode(group, node, groupIndex);
                expect(treeState._createNodePreview).toHaveBeenCalledWith(node, jasmine.objectContaining({
                    startX: 251,
                    startY: 8,
                    iconSpriteSheetId: 'prodCatTS',
                    iconName: 'preview',
                    itemName: 'Node Text',
                    itemId: '12345',
                    itemType: 'product'
                }));
            });

            it('should create an invisible hover element if the node is a product', function() {
                treeState._createNode(group, node, groupIndex);
                expect(treeState._createNodeHoverElem).toHaveBeenCalledWith(node, group, jasmine.objectContaining({
                    startX: 13,
                    startY: 0,
                    iconName: '',
                }));
            });

            it('should not create a preview icon object or hover element if the node is a category', function() {
                node.type = 'category';
                treeState._createNode(group, node, groupIndex);
                expect(treeState._createNodeIcon).toHaveBeenCalled();
                expect(treeState._createNodeText).toHaveBeenCalled();
                expect(treeState._createNodePreview).not.toHaveBeenCalled();
                expect(treeState._createNodeHoverElem).not.toHaveBeenCalled();
            });

            it('should add all created objects to the group', function() {
                treeState._createNode(group, node, groupIndex);
                expect(group.add).toHaveBeenCalledWith(createdIcon);
                expect(group.add).toHaveBeenCalledWith(createdText);
                expect(group.add).toHaveBeenCalledWith(createdPreview);
                expect(group.addAt).toHaveBeenCalledWith(createdHoverElem, 0);
            });
        });
    });
});
