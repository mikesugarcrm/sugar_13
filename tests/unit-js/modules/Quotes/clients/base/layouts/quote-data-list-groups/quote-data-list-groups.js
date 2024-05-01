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
describe('Quotes.Base.Layouts.QuoteDataListGroups', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        sinon.stub(app.metadata, 'getView').callsFake(function() {
            return {
                panels: [
                    {
                        fields: [
                            'field1', 'field2', 'field3', 'field4'
                        ]
                    }]
            };
        });

        sinon.stub(app.metadata, 'getLayout').callsFake(function() {
            return {
                name: 'ProductBundlesQuoteDataGroupMetadata'
            };
        });
        layout = SugarTest.createLayout('base', 'Quotes', 'quote-data-list-groups', null, null, true);
        sinon.stub(layout, 'before').callsFake(function() {
        });
        sinon.stub(layout, '_super').callsFake(function() {
        });
        sinon.stub(layout, 'closestComponent').callsFake(function() {
            return {
                cid: 'c37'
            };
        });
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should have className', function() {
            expect(layout.className).toBe('table dataTable quote-data-list-table');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('table');
        });

        it('should have sortableTag', function() {
            expect(layout.sortableTag).toBe('tbody');
        });

        it('should initialize groupIds to an empty array', function() {
            expect(layout.groupIds).toEqual([]);
        });

        it('should initialize currentBulkSaveRequests to an empty array', function() {
            expect(layout.currentBulkSaveRequests).toEqual([]);
        });

        it('should initialize quoteDataGroupMeta to ProductBundlesQuoteDataGroupLayout metadata', function() {
            expect(layout.quoteDataGroupMeta).toEqual({
                name: 'ProductBundlesQuoteDataGroupMetadata'
            });
        });

        it('should call .before render', function() {
            layout.initialize({});
            expect(layout.before).toHaveBeenCalledWith('render');
        });
    });

    describe('bindDataChange()', function() {
        var bundles;

        beforeEach(function() {
            sinon.spy(layout.context, 'on');
            sinon.spy(layout.model, 'on');
            sinon.stub(layout, '_onProductBundleChange');
            sinon.stub(layout, '_setCopyQuoteData');
            sinon.stub(layout, '_checkProductsQuoteLink');
            sinon.stub(app.controller.context, 'on');

        });

        afterEach(function() {
            bundles = null;
        });

        describe('app.controller.context', function() {
            it('should not set listener if user has no access to Quotes edit', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Quotes: {
                            edit: 'no'
                        },
                        Products: {}
                    };
                });
                layout.bindDataChange();

                expect(app.controller.context.on).not.toHaveBeenCalledWith('c37:productCatalogDashlet:add');
            });

            it('should not set listener if user has no access to Products', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Quotes: {},
                        Products: {
                            access: 'no'
                        }
                    };
                });
                layout.bindDataChange();

                expect(app.controller.context.on).not.toHaveBeenCalledWith('c37:productCatalogDashlet:add');
            });

            it('should not set listener if user has no access to Products edit', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Quotes: {},
                        Products: {
                            edit: 'no'
                        }
                    };
                });
                layout.bindDataChange();

                expect(app.controller.context.on).not.toHaveBeenCalledWith('c37:productCatalogDashlet:add');
            });

            it('should not set listener if user has no access to Quotes edit', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Quotes: {},
                        Products: {}
                    };
                });
                layout.bindDataChange();
                expect(app.controller.context.on).toHaveBeenCalledWith('c37:productCatalogDashlet:add');
            });
        });

        describe('when setting event listeners', function() {
            beforeEach(function() {
                layout.bindDataChange();
            });

            it('should listen on layout.model for change:show_line_nums', function() {
                expect(layout.model.on).toHaveBeenCalledWith('change:show_line_nums');
            });

            it('should listen on layout.model for change:bundles', function() {
                expect(layout.model.on).toHaveBeenCalledWith('change:bundles');
            });

            it('should listen on layout.context for quotes:group:create', function() {
                expect(layout.context.on).toHaveBeenCalledWith('quotes:group:create');
            });

            it('should listen on layout.context for quotes:group:delete', function() {
                expect(layout.context.on).toHaveBeenCalledWith('quotes:group:delete');
            });

            it('should listen on layout.context for quotes:selected:delete', function() {
                expect(layout.context.on).toHaveBeenCalledWith('quotes:selected:delete');
            });

            it('should listen on layout.context for quotes:defaultGroup:create', function() {
                expect(layout.context.on).toHaveBeenCalledWith('quotes:defaultGroup:create');
            });

            it('should listen on layout.context for quotes:defaultGroup:save', function() {
                expect(layout.context.on).toHaveBeenCalledWith('quotes:defaultGroup:save');
            });

            it('should listen on layout.context for productCatalogDashlet:add', function() {
                expect(app.controller.context.on).toHaveBeenCalledWith('c37:productCatalogDashlet:add');
            });
        });

        describe('when in create view', function() {
            beforeEach(function() {
                layout.isCreateView = true;
                bundles = $.noop;
                layout.model.set({
                    bundles: bundles
                }, {
                    silent: true
                });
            });

            it('should call _onProductBundleChange with bundles', function() {
                layout.bindDataChange();

                expect(layout._onProductBundleChange).toHaveBeenCalledWith(bundles);
            });
        });

        describe('when in create view from Copying a Quote', function() {
            beforeEach(function() {
                layout.isCreateView = true;
                layout.isCopy = true;
                bundles = $.noop;
                layout.model.set({
                    bundles: bundles
                }, {
                    silent: true
                });
                sinon.stub(layout, 'toggleCopyAlert').callsFake(function() {
                });
            });

            it('should not call toggleCopyAlert if there are no items to copy', function() {
                layout.context.set('copyItemCount', 0);
                layout.bindDataChange();

                expect(layout.toggleCopyAlert).not.toHaveBeenCalled();
            });

            it('should call toggleCopyAlert if there are items to copy', function() {
                layout.context.set('copyItemCount', 1);
                layout.bindDataChange();

                expect(layout.toggleCopyAlert).toHaveBeenCalledWith(true);
            });

            it('should call _onProductBundleChange with bundles', function() {
                layout.bindDataChange();

                expect(layout._setCopyQuoteData).toHaveBeenCalled();
            });

            it('should set copyItemCount by the context copyItemCount', function() {
                layout.context.set('copyItemCount', 3);
                layout.bindDataChange();

                expect(layout.copyItemCount).toBe(3);
            });
        });

        describe('when not in create view - on model sync', function() {
            beforeEach(function() {
                layout.isCreateView = false;
                bundles = new Backbone.Collection();
            });

            it('should call _checkProductsQuoteLink', function() {
                layout.model.set({
                    bundles: bundles
                }, {
                    silent: true
                });
                layout.bindDataChange();
                layout.model.trigger('sync', layout.model);

                expect(layout._checkProductsQuoteLink).toHaveBeenCalled();
            });

            it('should not call _onProductBundleChange when bundles is more than 0', function() {
                var bundleModel = app.data.createBean('ProductBundles', {
                    id: 'bundle1'
                });
                bundles.add(bundleModel);
                layout.model.set({
                    bundles: bundles
                }, {
                    silent: true
                });
                layout.bindDataChange();
                layout.model.trigger('sync', layout.model);

                expect(layout._onProductBundleChange).not.toHaveBeenCalled();
            });

            it('should call _onProductBundleChange', function() {
                layout.model.set({
                    bundles: bundles
                }, {
                    silent: true
                });
                layout.bindDataChange();
                layout.model.trigger('sync', layout.model);

                expect(layout._checkProductsQuoteLink).toHaveBeenCalled();
            });
        });
    });

    describe('toggleCopyAlert()', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(app.alert, 'dismiss').callsFake(function() {
            });
        });

        it('should call alert.show when called with showAlert = true', function() {
            layout.toggleCopyAlert(true);

            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call alert.dismiss when called with showAlert = false', function() {
            layout.toggleCopyAlert(false);

            expect(app.alert.dismiss).toHaveBeenCalled();
        });
    });

    describe('completedCopyItem()', function() {
        beforeEach(function() {
            sinon.stub(layout, 'toggleCopyAlert').callsFake(function() {
            });
            sinon.stub(layout, 'render').callsFake(function() {
            });
        });

        describe('when bundleComplete is false', function() {
            it('should decrement copyItemCount', function() {
                layout.copyItemCount = 2;
                layout.completedCopyItem();

                expect(layout.copyItemCount).toBe(1);
            });

            it('should call toggleCopyAlert if copyItemCount is 0', function() {
                layout.copyItemCount = 1;
                layout.completedCopyItem();

                expect(layout.toggleCopyAlert).toHaveBeenCalledWith(false);
            });
        });

        describe('when bundleComplete is true', function() {
            it('should decrement copyBundleCount', function() {
                layout.copyBundleCount = 2;
                layout.completedCopyItem(true);

                expect(layout.copyBundleCount).toBe(1);
            });

            it('should call render if copyBundleCount is 0', function() {
                layout.copyBundleCount = 1;
                layout.completedCopyItem(true);

                expect(layout.render).toHaveBeenCalled();
            });
        });
    });

    describe('_setCopyQuoteData()', function() {
        var relatedRecords;
        var defaultGroup;

        beforeEach(function() {

            sinon.stub(layout, '_updateDefaultGroupWithNewData').callsFake(function() {
            });
            sinon.stub(layout.context, 'once').callsFake(function() {
            });
            sinon.stub(layout, '_onCreateQuoteGroup').callsFake(function() {
            });
            sinon.stub(layout, 'completedCopyItem').callsFake(function() {
            });
        });

        afterEach(function() {
            relatedRecords = null;
            defaultGroup = null;
        });

        describe('when relatedRecords is a default group', function() {
            var pbItem1;

            beforeEach(function() {
                pbItem1 = {
                    modelView: 'test',
                    cid: 'test1'
                };
                relatedRecords = [
                    {
                        default_group: true,
                        product_bundle_items: [pbItem1]
                    }];
                defaultGroup = {
                    quoteDataGroupList: {
                        collection: new Backbone.Collection(),
                        toggledModels: {}
                    }
                };
                layout.context.set('relatedRecords', relatedRecords);
                sinon.spy(defaultGroup.quoteDataGroupList.collection, 'add');
                sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                    return defaultGroup;
                });

                layout._setCopyQuoteData();
            });

            afterEach(function() {
                pbItem1 = null;
            });

            it('should set copyBundleCount by the relatedRecords length', function() {
                expect(layout.copyBundleCount).toBe(1);
            });

            it('should set any product_bundle_items modelView to edit', function() {
                expect(pbItem1.modelView).toBe('edit');
            });

            it('should set pbItem1 to the quoteDataGroupList.toggledModels object', function() {
                expect(defaultGroup.quoteDataGroupList.toggledModels[pbItem1.cid]).toBe(pbItem1);
            });

            it('should add product_bundle_items to the group collection', function() {
                expect(defaultGroup.quoteDataGroupList.collection.add)
                    .toHaveBeenCalledWith(relatedRecords[0].product_bundle_items);
            });

            it('should call completedCopyItem for the bundle item', function() {
                expect(layout.completedCopyItem).toHaveBeenCalledWith();
            });

            it('should call completedCopyItem for the bundle with true', function() {
                expect(layout.completedCopyItem).toHaveBeenCalledWith(true);
            });
        });

        describe('when relatedRecords is not the default group', function() {
            beforeEach(function() {
                relatedRecords = [
                    {
                        default_group: false
                    }];
                layout.context.set('relatedRecords', relatedRecords);
                sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                    return defaultGroup;
                });

                layout._setCopyQuoteData();
            });

            it('should call context.once with quotes:group:create:success', function() {
                expect(layout.context.once).toHaveBeenCalledWith('quotes:group:create:success');
            });

            it('should call _onCreateQuoteGroup', function() {
                expect(layout._onCreateQuoteGroup).toHaveBeenCalled();
            });
        });
    });

    describe('_onCopyQuoteDataNewGroupedCreateSuccess()', function() {
        var group;
        var pbItem1;
        var triggerStub;
        var groupModel;
        var record;

        beforeEach(function() {
            triggerStub = sinon.stub();
            sinon.stub(layout, 'completedCopyItem').callsFake(function() {
            });

            pbItem1 = {
                modelView: 'test',
                cid: 'test1'
            };
            groupModel = new Backbone.Model();
            group = {
                model: groupModel,
                trigger: triggerStub,
                quoteDataGroupList: {
                    collection: new Backbone.Collection(),
                    toggledModels: {}
                }
            };
            sinon.spy(group.quoteDataGroupList.collection, 'add');
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return group;
            });
            record = {
                name: 'testRecord',
                product_bundle_items: [pbItem1]
            };

            layout._onCopyQuoteDataNewGroupedCreateSuccess(record, {});
        });

        afterEach(function() {
            group = null;
            pbItem1 = null;
            triggerStub = null;
            groupModel = null;
            record = null;
        });

        it('should set the record name on the group model', function() {
            expect(groupModel.get('name')).toBe('testRecord');
        });

        it('should set the product_bundle_items modelView to edit', function() {
            expect(pbItem1.modelView).toBe('edit');
        });

        it('should set pbItem1 to the quoteDataGroupList.toggledModels object', function() {
            expect(group.quoteDataGroupList.toggledModels[pbItem1.cid]).toBe(pbItem1);
        });

        it('should add product_bundle_items to the group collection', function() {
            expect(group.quoteDataGroupList.collection.add)
                .toHaveBeenCalledWith(record.product_bundle_items);
        });

        it('should call trigger on the default group with quotes:line_nums:reset', function() {
            expect(triggerStub).toHaveBeenCalledWith('quotes:line_nums:reset');
        });

        it('should call completedCopyItem for the bundle item', function() {
            expect(layout.completedCopyItem).toHaveBeenCalled();
        });

        it('should call completedCopyItem for the bundle with true', function() {
            expect(layout.completedCopyItem).toHaveBeenCalledWith(true);
        });
    });

    describe('_onProductCatalogDashletAddItem()', function() {
        var defaultGroupId;
        var defaultGroupMock;
        var defaultGroupTriggerStub;

        beforeEach(function() {
            defaultGroupId = 'default1';
            defaultGroupTriggerStub = sinon.stub();
            defaultGroupMock = {
                name: 'quote-data-group',
                groupId: defaultGroupId,
                trigger: defaultGroupTriggerStub,
                dispose: $.noop
            };
            layout._components.push(defaultGroupMock);
            layout.defaultGroupId = defaultGroupId;
            sinon.stub(app.controller.context, 'trigger');
            sinon.stub(app.user, 'get').withArgs('id').returns('seed_test_id')
                .withArgs('full_name').returns('Test Name');
        });

        afterEach(function() {
            defaultGroupId = null;
            defaultGroupMock = null;
            defaultGroupTriggerStub = null;
        });

        it('should update the assigned user info', function() {
            var productData = {};
            layout._onProductCatalogDashletAddItem(productData);

            expect(app.user.get).toHaveBeenCalledWith('id');
            expect(app.user.get).toHaveBeenCalledWith('full_name');

            expect(productData.assigned_user_id).toEqual('seed_test_id');
            expect(productData.assigned_user_name).toEqual('Test Name');

            productData = null;
        });

        it('should call trigger on default group', function() {
            layout._onProductCatalogDashletAddItem({});

            expect(defaultGroupTriggerStub).toHaveBeenCalledWith('quotes:group:create:qli');
        });

        it('should call trigger on app.controller.context', function() {
            layout._onProductCatalogDashletAddItem({});

            expect(app.controller.context.trigger).toHaveBeenCalledWith('c37:productCatalogDashlet:add:complete');
        });
    });

    describe('_checkProductsQuoteLink()', function() {
        var bundles;
        var bundleModel;
        var productModel;
        var callArgs;
        var pbItems;

        beforeEach(function() {
            productModel = app.data.createBean('Products', {
                id: 'product1'
            });
            productModel.module = 'Products';
            pbItems = new Backbone.Collection(productModel);
            bundleModel = app.data.createBean('ProductBundles', {
                product_bundle_items: pbItems
            });
            layout.model.set({
                id: 'quote1',
                billing_accounts: {
                    id: 'account1',
                    name: 'test name'
                },
                bundles: {
                    models: [bundleModel]
                }
            }, {
                silent: true
            });

            sinon.stub(app.api, 'call').callsFake(function() {
            });

            layout._checkProductsQuoteLink();
            callArgs = app.api.call.args;
        });

        afterEach(function() {
            bundles = null;
            bundleModel = null;
            productModel = null;
            callArgs = null;
            pbItems = null;
        });

        describe('when app.api.call is used with correct params', function() {
            it('should use create call type', function() {
                expect(callArgs[0][0]).toBe('create');
            });

            it('should use bulk URL', function() {
                expect(callArgs[0][1]).toBe(app.api.buildURL(null, 'bulk'));
            });

            it('should use bulk requests', function() {
                var request = callArgs[0][2].requests[0];
                var url = app.api.buildURL('Products/product1/link/quotes/quote1');

                expect(request.url).toBe(url.substr(4));
                expect(request.method).toBe('POST');
                expect(request.data.id).toBe('product1');
                expect(request.data.link).toBe('quotes');
                expect(request.data.related.quote_id).toBe('quote1');
                expect(request.data.relatedId).toBe('quote1');
            });
        });
    });

    describe('_onShowLineNumsChanged()', function() {
        beforeEach(function() {
            sinon.stub(layout.context, 'trigger').callsFake(function() {
            });
        });

        it('should trigger the quotes:show_line_nums:changed event with true', function() {
            layout._onShowLineNumsChanged({}, true);

            expect(layout.context.trigger).toHaveBeenCalledWith('quotes:show_line_nums:changed', true);
        });

        it('should trigger the quotes:show_line_nums:changed event with false', function() {
            layout._onShowLineNumsChanged({}, false);

            expect(layout.context.trigger).toHaveBeenCalledWith('quotes:show_line_nums:changed', false);
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub($.fn, 'sortable').callsFake(function() {
            });
        });

        describe('when there are no sortable items', function() {
            beforeEach(function() {
                sinon.stub(layout, '$').callsFake(function() {
                    return [];
                });
            });

            it('should not call sortable', function() {
                layout._render();
                expect($.fn.sortable).not.toHaveBeenCalled();
            });
        });

        describe('when there are sortable items', function() {
            beforeEach(function() {
                sinon.stub(layout, '$').callsFake(function() {
                    return ['<div></div>'];
                });

                layout._render();
            });

            it('should call sortable', function() {
                expect($.fn.sortable).toHaveBeenCalled();
            });

            describe('with the correct sortable params', function() {
                var callObj;
                beforeEach(function() {
                    callObj = $.fn.sortable.args[0][0];
                });

                afterEach(function() {
                    callObj = null;
                });

                it('should call with axis = "y"', function() {
                    expect(callObj.axis).toBe('y');
                });

                it('should call with connectWith = "tbody"', function() {
                    expect(callObj.connectWith).toBe('tbody');
                });

                it('should call with cursor = "move"', function() {
                    expect(callObj.cursor).toBe('move');
                });

                it('should call with items = "tr.sortable"', function() {
                    expect(callObj.items).toBe('tr.sortable');
                });

                it('should call with out handler defined', function() {
                    expect(callObj.out).toBeDefined();
                });

                it('should call with over handler defined', function() {
                    expect(callObj.over).toBeDefined();
                });

                it('should call with placeholder = "ui-state-highlight"', function() {
                    expect(callObj.placeholder).toBe('ui-state-highlight');
                });

                it('should call with revert = true', function() {
                    expect(callObj.revert).toBeTruthy();
                });

                it('should call with start handler defined', function() {
                    expect(callObj.start).toBeDefined();
                });

                it('should call with stop handler defined', function() {
                    expect(callObj.stop).toBeDefined();
                });
            });
        });
    });

    describe('_onDragStart()', function() {
        beforeEach(function() {
            sinon.stub(app.tooltip, 'clear').callsFake(function() {
            });
            sinon.stub(app.tooltip, '_disable').callsFake(function() {
            });

            layout._onDragStart();
        });

        it('should call tooltip clear', function() {
            expect(app.tooltip.clear).toHaveBeenCalled();
        });

        it('should call tooltip _disable', function() {
            expect(app.tooltip._disable).toHaveBeenCalled();
        });
    });

    describe('_onDragStop()', function() {
        var evtParam;
        var uiParam;
        var oldGroup;
        var oldGroupId;
        var oldGroupModel;
        var oldGroupTriggerSpy;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var newGroupTriggerSpy;
        var rowModelId;
        var rowModelModule;
        var rowModel;

        beforeEach(function() {
            evtParam = {};

            oldGroupTriggerSpy = sinon.spy();
            newGroupTriggerSpy = sinon.spy();

            rowModelModule = 'Products';
            rowModel = app.data.createBean('Products', {
                id: rowModelId,
                module: rowModelModule,
                position: 2
            });
            rowModelId = rowModel.cid;

            oldGroupModel = app.data.createBean('ProductBundles', {
                id: oldGroupId,
                name: 'oldGroupModelName_original'
            });

            oldGroupId = oldGroupModel.cid;
            oldGroup = {
                groupId: oldGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                trigger: oldGroupTriggerSpy,
                model: oldGroupModel,
                addRowModel: function(model, isEdit) {
                    this.collection.add(model);
                },
                removeRowModel: function(model, isEdit) {
                    this.collection.remove(model);
                }
            };

            newGroupModel = app.data.createBean('ProductBundles', {
                id: 'new_id',
                name: 'newGroupModelName_original'
            });
            newGroupId = newGroupModel.cid;
            newGroup = {
                groupId: newGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                trigger: newGroupTriggerSpy,
                model: newGroupModel,
                addRowModel: function(model, isEdit) {
                    this.collection.add(model);
                },
                removeRowModel: function(model, isEdit) {
                    this.collection.remove(model);
                },
                $: function() {
                    return [
                        '<tr name="Products_' + oldGroupId + '" ></tr>',
                        '<tr name="Products_' + rowModelId + '" ></tr>'
                    ];
                }
            };

            newGroup.collection.comparator = function(model) {
                return model.get('position');
            };

            sinon.stub(app.tooltip, '_enable').callsFake(function() {
            });
            sinon.stub(layout, '_saveDefaultGroupThenCallBulk').callsFake(function() {
            });
            sinon.stub(layout, '_callBulkRequests').callsFake(function() {
            });

            sinon.stub(layout, '_getComponentByGroupId').callsFake(function(id) {
                if (id === oldGroupId) {
                    return oldGroup;
                } else {
                    return newGroup;
                }
            });
            sinon.stub(layout, '_updateRowPositions').callsFake(function(group) {
                return {
                    data: {
                        position: 0
                    },
                    method: 'PUT',
                    url: '/v10/ProductBundles/' + group.groupId + '/link/products/rowModelId1'
                };
            });
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
        });

        describe('when old group is different than new group', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + oldGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.stub($.fn, 'parent').callsFake(function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });
                sinon.stub(layout, '_moveItemToNewGroup').callsFake(function() {
                });
                oldGroup.collection.add(rowModel);

                layout.currentBulkSaveRequests = [
                    {
                        id: 'hey'
                    }];
                layout._onDragStop(evtParam, uiParam);
            });

            it('should call _getComponentByGroupId with oldGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(oldGroupId);
            });

            it('should call _getComponentByGroupId with newGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(newGroupId);
            });

            describe('triggering group events', function() {
                describe('on oldGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(oldGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(oldGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });

                describe('on newGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(newGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(newGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });
            });

            describe('on save default group', function() {
                it('should call _callBulkRequests with an regular saved group', function() {
                    expect(layout._saveDefaultGroupThenCallBulk).not.toHaveBeenCalled();
                    expect(layout._callBulkRequests).toHaveBeenCalled();
                });
                it('should call _saveDefaultGroupThenCallBulk with an unsaved group', function() {
                    sinon.stub(newGroupModel, 'isNew').callsFake(function() {
                        return true;
                    });
                    // reset everything back
                    layout._saveDefaultGroupThenCallBulk.restore();
                    layout._callBulkRequests.restore();
                    sinon.stub(layout, '_saveDefaultGroupThenCallBulk').callsFake(function() {
                    });
                    sinon.stub(layout, '_callBulkRequests').callsFake(function() {
                    });
                    newGroup.collection.remove(rowModel);
                    oldGroup.collection.add(rowModel);

                    layout._onDragStop(evtParam, uiParam);

                    expect(newGroupModel.isNew).toHaveBeenCalled();
                    expect(layout._saveDefaultGroupThenCallBulk).toHaveBeenCalled();
                    expect(layout._callBulkRequests).not.toHaveBeenCalled();
                });
            });
        });

        describe('when old group is the same as new group', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + newGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.stub($.fn, 'parent').callsFake(function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });

                newGroup.collection.add(rowModel);

                layout._onDragStop(evtParam, uiParam);
            });

            it('should have the rowModel still in newGroup', function() {
                expect(newGroup.collection.length).toBe(1);
            });

            it('should not call _getComponentByGroupId with oldGroup ID', function() {
                expect(layout._getComponentByGroupId).not.toHaveBeenCalledWith(oldGroupId);
            });

            it('should call _getComponentByGroupId with newGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(newGroupId);
            });

            describe('triggering group events', function() {
                describe('on oldGroup', function() {
                    it('should not trigger any events on the oldGroup', function() {
                        expect(oldGroupTriggerSpy).not.toHaveBeenCalled();
                    });
                });

                describe('on newGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(newGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(newGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });
            });

            it('should call _callBulkRequests with an regular saved group', function() {
                expect(layout._saveDefaultGroupThenCallBulk).not.toHaveBeenCalled();
                expect(layout._callBulkRequests).toHaveBeenCalled();
            });
        });

        describe('always re-enable tooltips', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + newGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.stub($.fn, 'parent').callsFake(function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });

                layout._onDragStop(evtParam, uiParam);
            });

            it('should call tooltip _enable', function() {
                expect(app.tooltip._enable).toHaveBeenCalled();
            });
        });
    });

    describe('moveMassCollectionItemsToNewGroup()', function() {
        var rowModel;
        var massCollection;
        var newGroupData;

        beforeEach(function() {
            massCollection = new Backbone.Collection();

            rowModel = app.data.createBean('Products', {
                id: 'rowModelId'
            });
            rowModel.cid = 'rowModelId';

            rowModel.link = {
                bean: {
                    id: 'oldGroupId'
                }
            };

            newGroupData = {
                related_record: {
                    id: 'newGroupId'
                }
            };

            massCollection.add(rowModel);
            layout.context.set('mass_collection', massCollection);

            sinon.stub(layout, '_moveItemToNewGroup').callsFake(function() {
            });
            sinon.stub(layout, '_callBulkRequests').callsFake(function() {
            });
        });

        afterEach(function() {
            rowModel = null;
            massCollection = null;
            newGroupData = null;
        });

        it('should not call _moveItemToNewGroup() if massCollection is empty', function() {
            massCollection.reset();
            layout.moveMassCollectionItemsToNewGroup(newGroupData);

            expect(layout._moveItemToNewGroup).not.toHaveBeenCalled();
        });

        it('should call _moveItemToNewGroup() with isRowEdit false', function() {
            layout.moveMassCollectionItemsToNewGroup(newGroupData);

            expect(layout._moveItemToNewGroup).toHaveBeenCalledWith(
                'oldGroupId',
                'newGroupId',
                'rowModelId',
                false,
                undefined,
                true
            );
        });

        it('should call _moveItemToNewGroup() with isRowEdit true', function() {
            rowModel.modelView = 'edit';
            layout.moveMassCollectionItemsToNewGroup(newGroupData);

            expect(layout._moveItemToNewGroup).toHaveBeenCalledWith(
                'oldGroupId',
                'newGroupId',
                'rowModelId',
                true,
                undefined,
                true
            );
        });

        it('should call _callBulkRequests()', function() {
            layout.moveMassCollectionItemsToNewGroup(newGroupData);

            expect(layout._callBulkRequests).toHaveBeenCalled();
        });

        it('should reset mass collection', function() {
            layout.moveMassCollectionItemsToNewGroup(newGroupData);

            expect(massCollection.length).toBe(0);
        });
    });

    describe('_onSaveUpdatedMassCollectionItemsSuccess()', function() {
        var bulkResponses;
        var newGroup;
        var groupModel;
        var groupCollection;
        var rowModel;

        beforeEach(function() {
            bulkResponses = [
                {
                    contents: {
                        record: {
                            id: 'bundleId1',
                            total: '100'
                        },
                        related_record: {
                            id: 'modelId1',
                            total: '100',
                            date_modified: '100',
                            position: 0
                        }
                    }
                }];

            groupModel = app.data.createBean('ProductBundles', {
                id: 'bundleId1',
                total: '0'
            });
            groupCollection = new Backbone.Collection();

            rowModel = app.data.createBean('Products', {
                id: 'modelId1',
                position: 1,
                date_modified: '50'
            });
            groupCollection.add(rowModel);

            newGroup = {
                model: groupModel,
                collection: groupCollection
            };

            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return newGroup;
            });

            layout._onSaveUpdatedMassCollectionItemsSuccess(bulkResponses);
        });

        afterEach(function() {
            bulkResponses = null;
            rowModel = null;
            groupModel = null;
            groupCollection = null;
            newGroup = null;
        });

        it('should update the group model with new data', function() {
            expect(groupModel.get('total')).toBe('100');
        });

        it('should update the row model with new data', function() {
            expect(rowModel.get('position')).toBe(0);
        });
    });

    describe('_updateModelWithRecord()', function() {
        var model;
        var record;

        beforeEach(function() {
            model = app.data.createBean('Products', {
                id: 'oldId'
            });
            record = {
                id: 'newId'
            };

            sinon.spy(model, 'setSyncedAttributes');
            sinon.spy(model, 'set');
        });

        afterEach(function() {
            model = null;
            record = null;
        });

        it('should do nothing if model does not exist', function() {
            layout._updateModelWithRecord(undefined, record);

            expect(model.set).not.toHaveBeenCalled();
        });

        it('should call setSyncedAttributes if model exists', function() {
            layout._updateModelWithRecord(model, record);

            expect(model.setSyncedAttributes).toHaveBeenCalledWith(record);
            expect(model.getSynced('id')).toBe('newId');
        });

        it('should call set if model exists', function() {
            layout._updateModelWithRecord(model, record);

            expect(model.set).toHaveBeenCalledWith(record);
            expect(model.get('id')).toBe('newId');
        });
    });

    describe('_moveItemToNewGroup()', function() {
        var oldGroup;
        var oldGroupId;
        var oldGroupModel;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var rowModelId;
        var rowModelModule;
        var rowModel;
        var oldGroupTriggerSpy;
        var newGroupTriggerSpy;

        beforeEach(function() {
            oldGroupTriggerSpy = sinon.spy();
            newGroupTriggerSpy = sinon.spy();

            rowModelId = 'rowModelId1';
            rowModelModule = 'Products';
            rowModel = new Backbone.Model({
                id: rowModelId,
                module: rowModelModule,
                position: 2
            });
            rowModel.module = rowModelModule;

            oldGroupId = 'oldGroupId1';
            oldGroupModel = app.data.createBean('ProductBundles', {
                id: oldGroupId,
                name: 'oldGroupModelName_original'
            });
            oldGroup = {
                groupId: oldGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                model: oldGroupModel,
                trigger: oldGroupTriggerSpy,
                removeRowModel: function(model, isEdit) {
                    this.collection.remove(model);
                }
            };

            rowModel.link = {
                bean: oldGroupModel
            };

            newGroupId = 'newGroupId1';
            newGroupModel = app.data.createBean('ProductBundles', {
                id: newGroupId,
                name: 'newGroupModelName_original'
            });
            newGroup = {
                groupId: newGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                model: newGroupModel,
                trigger: newGroupTriggerSpy,
                addRowModel: function(model, isEdit) {
                    this.collection.add(model);
                }
            };

            sinon.stub(layout, '_getComponentByGroupId').callsFake(function(id) {
                if (id === oldGroupId) {
                    return oldGroup;
                } else {
                    return newGroup;
                }
            });

            sinon.stub(layout, '_updateRowPositions').callsFake(function(group) {
                var retCalls = [];

                _.each(group.collection.models, _.bind(function(retCalls, model, index) {
                    if (model && index != model.previous('position')) {
                        retCalls.push({
                            data: {
                                position: index
                            },
                            method: 'PUT',
                            url: '/v10/ProductBundles/' + group.groupId + '/link/products/' + model.id
                        });
                    }
                }, this, retCalls), this);
                return retCalls;
            });
        });

        afterEach(function() {
            oldGroup = null;
            oldGroupId = null;
            oldGroupModel = null;
            newGroup = null;
            newGroupId = null;
            newGroupModel = null;
            rowModelId = null;
            rowModelModule = null;
            rowModel = null;
            oldGroupTriggerSpy = null;
            newGroupTriggerSpy = null;
        });

        describe('adding/removing from collections and setting positions', function() {
            beforeEach(function() {
                oldGroup.collection.add(rowModel);
                layout._moveItemToNewGroup(oldGroupId, newGroupId, rowModelId, false);
            });

            it('should call _getComponentByGroupId with oldGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(oldGroupId);
            });

            it('should call _getComponentByGroupId with newGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(newGroupId);
            });

            it('should set new position on rowModel when newPosition is not passed in', function() {
                expect(rowModel.get('position')).toBe(0);
            });

            it('should remove the rowModel from oldGroup', function() {
                expect(oldGroup.collection.length).toBe(0);
            });

            it('should add the rowModel to newGroup', function() {
                expect(newGroup.collection.length).toBe(1);
            });

            it('should call quotes:line_nums:reset on oldGroup', function() {
                expect(oldGroupTriggerSpy).toHaveBeenCalledWith('quotes:line_nums:reset');
            });

            it('should call quotes:line_nums:reset on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalledWith('quotes:line_nums:reset');
            });

            it('should call GET for the old group', function() {
                var oldGroupUrl = app.api.buildURL('ProductBundles/' + oldGroupId);
                var lastRequest = _.last(layout.currentBulkSaveRequests);
                oldGroupUrl = oldGroupUrl.substr(4);

                expect(lastRequest.url).toBe(oldGroupUrl);
            });
        });

        describe('when newPosition is passed in', function() {
            beforeEach(function() {
                oldGroup.collection.add(rowModel);
                layout._moveItemToNewGroup(oldGroupId, newGroupId, rowModelId, false, 2);
            });

            it('should set the position to be the passed in position', function() {
                expect(rowModel.get('position')).toBe(2);
            });
        });

        describe('when updating model.link.bean', function() {
            beforeEach(function() {
                oldGroup.collection.add(rowModel);
            });

            it('should not update model.link.bean if updateLinkBean is false', function() {
                layout._moveItemToNewGroup(oldGroupId, newGroupId, rowModelId, false, undefined, false);

                expect(rowModel.link.bean).toBe(oldGroupModel);
            });

            it('should update model.link.bean to new group model if updateLinkBean is true', function() {
                layout._moveItemToNewGroup(oldGroupId, newGroupId, rowModelId, false, undefined, true);

                expect(rowModel.link.bean).toBe(newGroupModel);
            });
        });

        describe('setting currentBulkSaveRequests', function() {
            var rowModel2Id;
            var rowModel2Module;
            var rowModel2;
            var result;

            beforeEach(function() {
                rowModel2Id = 'rowModelId2';
                rowModel2Module = 'Products';
                rowModel2 = new Backbone.Model({
                    id: rowModel2Id,
                    module: rowModel2Module,
                    position: 1
                });
                rowModel2.module = rowModel2Module;

                // set rowModel's position to 1
                rowModel.set('position', 2);

                oldGroup.collection.add(rowModel2);
                oldGroup.collection.add(rowModel);
                layout.currentBulkSaveRequests = [];

                layout._moveItemToNewGroup(oldGroupId, newGroupId, rowModelId, false, undefined, false, true);
            });

            afterEach(function() {
                rowModel2Id = null;
                rowModel2Module = null;
                rowModel2 = null;
            });

            it('should set currentBulkSaveRequests first call URL', function() {
                result = layout.currentBulkSaveRequests[0].url;

                expect(result.indexOf('ProductBundles/newGroupId1/link/products/rowModelId1')).not.toBe(-1);
            });

            it('should set currentBulkSaveRequests first call METHOD', function() {
                result = layout.currentBulkSaveRequests[0].method;

                expect(result).toBe('POST');
            });

            it('should set currentBulkSaveRequests first call DATA', function() {
                result = layout.currentBulkSaveRequests[0].data;

                expect(result).toEqual({
                    id: newGroupId,
                    link: 'products',
                    position: 0,
                    relatedId: rowModelId
                });
            });

            it('should set currentBulkSaveRequests second call URL', function() {
                result = layout.currentBulkSaveRequests[1].url;

                expect(result.indexOf('ProductBundles/oldGroupId1/link/products/rowModelId2')).not.toBe(-1);
            });

            it('should set currentBulkSaveRequests second call METHOD', function() {
                result = layout.currentBulkSaveRequests[1].method;

                expect(result).toBe('PUT');
            });

            it('should set currentBulkSaveRequests second call DATA', function() {
                result = layout.currentBulkSaveRequests[1].data;

                expect(result).toEqual({
                    position: 0
                });
            });

            it('should set currentBulkSaveRequests third call URL', function() {
                result = layout.currentBulkSaveRequests[2].url;

                expect(result.indexOf('ProductBundles/newGroupId1/link/products/rowModelId1')).not.toBe(-1);
            });

            it('should set currentBulkSaveRequests third call METHOD', function() {
                result = layout.currentBulkSaveRequests[2].method;

                expect(result).toBe('PUT');
            });

            it('should set currentBulkSaveRequests third call DATA', function() {
                result = layout.currentBulkSaveRequests[2].data;

                expect(result).toEqual({
                    position: 0
                });
            });
        });
    });

    describe('_updateDefaultGroupWithNewData()', function() {
        var group;
        var recordData;
        var elAttrSpy;
        var elDataSpy;
        var trAttrSpy;
        var trDataSpy;

        beforeEach(function() {
            elAttrSpy = sinon.spy();
            elDataSpy = sinon.spy();
            trAttrSpy = sinon.spy();
            trDataSpy = sinon.spy();
            group = {
                model: app.data.createBean('ProductBundle'),
                $el: {
                    attr: elAttrSpy,
                    data: elDataSpy
                },
                $: function() {
                    return {
                        attr: trAttrSpy,
                        data: trDataSpy
                    };
                }
            };
            recordData = {
                id: 'newId1',
                test: 'abc'
            };
            layout.groupIds = ['oldGroupId1'];
            layout.defaultGroupId = 'oldGroupId1';
            layout._updateDefaultGroupWithNewData(group, recordData);
        });

        afterEach(function() {
            group = null;
            recordData = null;
            elAttrSpy = null;
            trAttrSpy = null;
        });

        it('should update groupIds with the new group ID', function() {
            expect(layout.groupIds).toEqual([group.model.cid]);
        });

        it('should set the defaultGroupId to be the new group ID', function() {
            expect(layout.defaultGroupId).toBe(group.model.cid);
        });

        it('should set recordData on the model', function() {
            expect(group.model.get('id')).toBe('newId1');
            expect(group.model.get('test')).toBe('abc');
        });

        it('should set the group groupId to be the new group ID', function() {
            expect(group.groupId).toBe(group.model.cid);
        });

        it('should call attr function on $el to update the group ID', function() {
            expect(elAttrSpy).toHaveBeenCalled();
            expect(elDataSpy).toHaveBeenCalled();
        });

        it('should call attr function on any tr elements to update the group ID', function() {
            expect(trAttrSpy).toHaveBeenCalled();
            expect(trDataSpy).toHaveBeenCalled();
        });
    });

    describe('_saveDefaultGroupThenCallBulk', function() {
        var oldGroup;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var bulkSaveRequests;
        var layoutModelId;
        var appApiCallArgs;

        beforeEach(function() {
            oldGroup = {};
            newGroupModel = app.data.createBean('ProductBundles', {
                name: 'New Test Group'
            });
            newGroup = {
                model: newGroupModel
            };
            bulkSaveRequests = [];
            layoutModelId = 'layoutModelId1';

            sinon.stub(app.api, 'relationships').callsFake(function() {
            });

            layout.model.set('id', layoutModelId);
            layout._saveDefaultGroupThenCallBulk(oldGroup, newGroup, bulkSaveRequests);
            appApiCallArgs = app.api.relationships.args[0];
        });

        afterEach(function() {
            oldGroup = null;
            newGroup = null;
            newGroupModel = null;
            bulkSaveRequests = null;
            layoutModelId = null;
            appApiCallArgs = null;
        });

        it('should call app.api.relationships with method "create"', function() {
            expect(appApiCallArgs[0]).toBe('create');
        });

        it('should call app.api.relationships with module "Quotes"', function() {
            expect(appApiCallArgs[1]).toBe('Quotes');
        });

        describe('with proper payload', function() {
            it('should use layout model id', function() {
                expect(appApiCallArgs[2].id).toBe(layoutModelId);
            });

            it('should use product_bundles as link name', function() {
                expect(appApiCallArgs[2].link).toBe('product_bundles');
            });

            it('should have related object', function() {
                expect(appApiCallArgs[2].related.position).toBe(0);
                expect(appApiCallArgs[2].related.name).toBe('New Test Group');
            });
        });
    });

    describe('_onDefaultGroupSaveSuccess', function() {
        var oldGroup;
        var newGroup;
        var newGroupModel;
        var bulkSaveRequests;
        var newGroupOldId;
        var serverData;
        var newGroupSavedId;

        beforeEach(function() {
            oldGroup = {};
            newGroupModel = app.data.createBean('ProductBundles', {
                id: ''
            });
            newGroup = {
                model: newGroupModel,
                groupId: newGroupOldId
            };
            newGroupOldId = 'oldGroupId1';
            newGroupSavedId = 'savedGroupId2';
            serverData = {
                related_record: {
                    id: newGroupSavedId,
                    test: true
                }
            };
            bulkSaveRequests = [
                {
                    url: 'v10/ProductBundles/' + newGroupOldId + '/stuff'
                }];
            sinon.stub(layout, '_callBulkRequests').callsFake(function() {
            });
            sinon.stub(layout, '_updateDefaultGroupWithNewData').callsFake(function() {
            });

            layout._onDefaultGroupSaveSuccess(oldGroup, newGroup, bulkSaveRequests, newGroupOldId, serverData);
        });

        afterEach(function() {
            oldGroup = null;
            newGroup = null;
            bulkSaveRequests = null;
            newGroupOldId = null;
            newGroupSavedId = null;
            serverData = null;
        });

        it('should update old group id in requests to new group id', function() {
            expect(bulkSaveRequests[0].url).toBe('v10/ProductBundles/' + newGroupSavedId + '/stuff');
        });

        it('should call _updateDefaultGroupWithNewData to update the group', function() {
            expect(layout._updateDefaultGroupWithNewData).toHaveBeenCalled();
        });

        it('should call _callBulkRequests', function() {
            expect(layout._callBulkRequests).toHaveBeenCalled();
        });
    });

    describe('_callBulkRequests', function() {
        beforeEach(function() {
            layout.currentBulkSaveRequests = [
                {
                    url: 'testUrl'
                }];
            sinon.stub(app.api, 'call').callsFake(function() {
                layout.currentBulkSaveRequests = null;
            });

            layout._callBulkRequests();
        });

        it('should call with method create', function() {
            expect(app.api.call.args[0][0]).toBe('create');
        });

        it('should call with bulk url', function() {
            var url = app.api.call.args[0][1].split('/');
            expect(_.last(url)).toBe('bulk');
        });

        it('should call with requests array', function() {
            expect(app.api.call.args[0][2].requests).toBeDefined();
        });

        it('should have correct requests length', function() {
            expect(app.api.call.args[0][2].requests.length).toBe(1);
        });

        it('should empty currentBulkSaveRequests', function() {
            expect(layout.currentBulkSaveRequests.length).toBe(0);
        });

        it('should add the call to this.saveQueue', function() {
            expect(layout.saveQueue.length).toBe(1);
        });
    });

    describe('handleSaveQueueSuccess()', function() {
        var customSuccessParam;
        var request1;
        var request2;
        var response1;
        var response2;

        beforeEach(function() {
            sinon.stub(layout, '_processSaveQueue').callsFake(function() {
            });
            customSuccessParam = sinon.stub();
            sinon.spy(layout.saveQueue, 'shift');
            request1 = {
                callReturned: false,
                customSuccess: $.noop,
                request: 'request1',
                responseData: {
                    data: 'request1'
                }
            };
            request2 = {
                callReturned: false,
                customSuccess: $.noop,
                request: 'request2',
                responseData: {
                    data: 'request2'
                }
            };
            response1 = {
                data: 'responseData1'
            };
            response2 = {
                data: 'responseData2'
            };
        });

        afterEach(function() {
            customSuccessParam = null;
            request1 = null;
            request2 = null;
            response1 = null;
            response2 = null;
        });

        describe('when response comes back in proper order', function() {
            beforeEach(function() {
                request1.customSuccess = customSuccessParam;
                layout.saveQueue.push(request1);

                layout.handleSaveQueueSuccess(customSuccessParam, response1, 'request1');
            });

            it('should shift the saveQueue item off', function() {
                expect(layout.saveQueue.shift).toHaveBeenCalled();
            });

            it('should call the customSuccess function with the response data', function() {
                expect(customSuccessParam).toHaveBeenCalledWith(response1);
            });

            it('should call _processSaveQueue to handle any other items', function() {
                expect(layout._processSaveQueue).toHaveBeenCalled();
            });
        });

        describe('when response comes back but other calls need to be processed', function() {
            beforeEach(function() {
                request1.customSuccess = customSuccessParam;
                layout.saveQueue.push(request1);
                layout.saveQueue.push(request2);

                layout.handleSaveQueueSuccess(customSuccessParam, response2, 'request2');
            });

            it('should set callReturned to true for second request', function() {
                expect(request2.callReturned).toBeTruthy();
            });

            it('should set customSuccess to the passed in function for second request', function() {
                expect(request2.customSuccess).toBe(customSuccessParam);
            });

            it('should set callReturned to true for second request', function() {
                expect(request2.responseData).toBe(response2);
            });
        });
    });

    describe('_processSaveQueue()', function() {
        var request1;
        var request2;
        var request3;
        var requestSuccess1;
        var requestSuccess2;
        var requestSuccess3;

        beforeEach(function() {
            sinon.spy(layout.saveQueue, 'shift');
            sinon.spy(layout, '_processSaveQueue');
            requestSuccess1 = sinon.stub();
            requestSuccess2 = sinon.stub();
            requestSuccess3 = sinon.stub();
            request1 = {
                callReturned: false,
                customSuccess: requestSuccess1,
                request: 'request1',
                responseData: {
                    data: 'request1'
                }
            };
            request2 = {
                callReturned: false,
                customSuccess: requestSuccess2,
                request: 'request2',
                responseData: {
                    data: 'request2'
                }
            };
            request3 = {
                callReturned: false,
                customSuccess: requestSuccess3,
                request: 'request3',
                responseData: {
                    data: 'request3'
                }
            };
        });

        afterEach(function() {
            request1 = null;
            request2 = null;
            request3 = null;
            requestSuccess1 = null;
            requestSuccess2 = null;
            requestSuccess3 = null;
        });

        it('should do nothing if saveQueue is empty', function() {
            layout._processSaveQueue();

            expect(layout.saveQueue.shift).not.toHaveBeenCalled();
        });

        it('should call _processSaveQueue again after processing every item returned in order', function() {
            request1.callReturned = true;
            request2.callReturned = true;
            request3.callReturned = true;
            layout.saveQueue.push(request1);
            layout.saveQueue.push(request2);
            layout.saveQueue.push(request3);
            layout._processSaveQueue();

            expect(layout._processSaveQueue.callCount).toBe(4);
        });

        it('should not process any queue items if the first item in queue has not returned', function() {
            request1.callReturned = false;
            request2.callReturned = true;
            request3.callReturned = true;
            layout.saveQueue.push(request1);
            layout.saveQueue.push(request2);
            layout.saveQueue.push(request3);
            layout._processSaveQueue();

            expect(layout.saveQueue.shift).not.toHaveBeenCalled();
        });

        it('should call the queue item success function with the response data', function() {
            request1.callReturned = true;
            layout.saveQueue.push(request1);
            layout._processSaveQueue();

            expect(requestSuccess1).toHaveBeenCalledWith(request1.responseData);
        });
    });

    describe('_onSaveUpdatedGroupSuccess()', function() {
        var oldGroup;
        var oldGroupId;
        var oldGroupModel;
        var oldGroupTriggerSpy;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var newGroupTriggerSpy;
        var bulkResponses;
        var bulkOldModelUpdate;
        var bulkNewModelUpdate;
        var bundleItem1;
        var bundleItem2;
        var bundles;

        beforeEach(function() {
            oldGroupTriggerSpy = sinon.spy();
            newGroupTriggerSpy = sinon.spy();

            bundles = {
                remove: $.noop
            };

            bundleItem1 = app.data.createBean('Products', {
                id: 'product1',
                name: 'product1_original'
            });
            bundleItem1.link = {
                bean: {}
            };
            bundleItem2 = app.data.createBean('Products', {
                id: 'product2',
                name: 'product2_original'
            });
            bundleItem2.link = {
                bean: {}
            };

            oldGroupId = 'oldGroupId1';
            oldGroupModel = app.data.createBean('Products', {
                id: 'oldGroupModelId1',
                name: 'oldGroupModelName_original',
                position: 0,
                product_bundle_items: new Backbone.Collection(bundleItem1)
            });
            sinon.spy(oldGroupModel, 'setSyncedAttributes');

            oldGroup = {
                groupId: oldGroupId,
                collection: app.data.createMixedBeanCollection(),
                trigger: oldGroupTriggerSpy,
                dispose: $.noop,
                model: oldGroupModel
            };

            newGroupId = 'newGroupModelId1';
            newGroupModel = app.data.createBean('ProductBundles', {
                id: newGroupId,
                name: 'newGroupModelName_original',
                position: 2,
                product_bundle_items: new Backbone.Collection(bundleItem2)
            });
            sinon.spy(newGroupModel, 'setSyncedAttributes');
            newGroup = {
                groupId: newGroupId,
                collection: app.data.createMixedBeanCollection(),
                trigger: newGroupTriggerSpy,
                model: newGroupModel
            };

            oldGroup.collection.add(oldGroupModel);
            newGroup.collection.add(bundleItem2);

            sinon.stub(layout, '_getComponentByGroupId').callsFake(function(id) {
                if (id === oldGroupId) {
                    return oldGroup;
                } else {
                    return newGroup;
                }
            });

            bulkOldModelUpdate = {
                contents: {
                    record: {
                        id: 'oldGroupModelId1',
                        name: 'oldGroupModelName_new',
                        position: 1
                    },
                    related_record: {
                        id: 'product1',
                        name: 'product1_new',
                        position: 1
                    }
                }
            };
            bulkNewModelUpdate = {
                contents: {
                    record: {
                        id: 'newGroupModelId1',
                        name: 'newGroupModelName_new',
                        position: 1
                    },
                    related_record: {
                        id: 'product2',
                        name: 'product2_new'
                    }
                }
            };
            layout.currentBulkSaveRequests = [
                bulkOldModelUpdate,
                bulkNewModelUpdate
            ];
            bulkResponses = [
                bulkOldModelUpdate,
                bulkNewModelUpdate
            ];
            layout.model.set('bundles', bundles, {silent: true});
        });

        afterEach(function() {
            oldGroupModel.dispose();
            oldGroupModel = null;
            newGroupModel.dispose();
            newGroupModel = null;
            oldGroupId = null;
            newGroupId = null;
            oldGroup = null;
            newGroup = null;
            bulkOldModelUpdate = null;
            bulkNewModelUpdate = null;
            bulkResponses = null;
            bundles = null;
        });

        describe('when oldGroup is not sent', function() {
            beforeEach(function() {
                layout._onSaveUpdatedGroupSuccess(undefined, newGroup, bulkResponses);
            });

            it('should not trigger quotes:group:save:stop if oldGroup is not passed in', function() {
                expect(oldGroupTriggerSpy).not.toHaveBeenCalled();
            });

            it('should always trigger quotes:group:save:stop on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should not update old group', function() {
                expect(oldGroupModel.get('name')).toBe('oldGroupModelName_original');
                expect(oldGroupModel.setSyncedAttributes).not.toHaveBeenCalled();
            });

            it('should update the new group record position', function() {
                expect(newGroupModel.get('name')).toBe('newGroupModelName_new');
                expect(newGroupModel.get('position')).toBe(1);
                expect(newGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });

            it('should update the new group bundle item with response data', function() {
                expect(bundleItem2.get('name')).toBe('product2_new');
            });
        });

        describe('when oldGroup is sent', function() {
            beforeEach(function() {
                layout._onSaveUpdatedGroupSuccess(oldGroup, newGroup, bulkResponses);
            });

            it('should trigger quotes:group:save:stop on oldGroup', function() {
                expect(oldGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should always trigger quotes:group:save:stop on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should update the old group position', function() {
                expect(oldGroupModel.get('name')).toBe('oldGroupModelName_new');
                expect(oldGroupModel.get('position')).toBe(1);
                expect(oldGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });

            it('should update the new group record position', function() {
                expect(newGroupModel.get('name')).toBe('newGroupModelName_new');
                expect(newGroupModel.get('position')).toBe(1);
                expect(newGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });

            it('should update the new group bundle item with response data', function() {
                expect(bundleItem2.get('name')).toBe('product2_new');
            });
        });

        describe('when deleted item exists', function() {
            beforeEach(function() {
                newGroup.model = newGroupModel;
                bulkResponses.push({
                    contents: {
                        id: oldGroupId
                    }
                });
                layout.groupIds = [oldGroupId, newGroupId];
                sinon.stub(oldGroup, 'dispose').callsFake(function() {
                    this.model.unset('product_bundle_items');
                });
                layout._onSaveUpdatedGroupSuccess(oldGroup, newGroup, bulkResponses);
            });

            it('should remove the deleted group id from groupIds', function() {
                expect(layout.groupIds).toEqual([newGroupId]);
            });

            it('should move the deleted group models to new group', function() {
                expect(newGroup.model.get('product_bundle_items').length).toBe(2);
            });

            it('should remove the deleted group models', function() {
                expect(oldGroup.model.get('product_bundle_items')).toBeUndefined();
            });

            it('should dispose the deleted group', function() {
                expect(oldGroup.dispose).toHaveBeenCalled();
            });

            it('should trigger quotes:line_nums:reset on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalledWith('quotes:line_nums:reset');
            });
        });
    });

    describe('_updateRowPositions()', function() {
        var dataGroup;
        var dataGroupModel;
        var rowModel1;
        var rowModel2;
        var rowModel3;
        var rowModel4;
        var results;
        var callUrl;

        beforeEach(function() {
            dataGroupModel = new Backbone.Model({
                id: 'dataGroupId1'
            });
            dataGroupModel.id = 'dataGroupId1';
            dataGroup = {
                model: dataGroupModel,
                groupId: 'dataGroupId1',
                collection: new Backbone.Collection(),
                $: function() {
                    return [
                        '<tr name="Products_qliId1"></tr>',
                        '<tr name="ProductBundleNotes_pbnId2"></tr>',
                        '<tr name="Products_qliId3"></tr>',
                        '<tr name="ProductBundleNotes_pbnId4"></tr>'
                    ];
                }
            };

            dataGroup.collection.comparator = function(model) {
                return model.get('position');
            };

            sinon.spy(dataGroup.collection, 'sort');

            rowModel1 = new Backbone.Model({
                id: 'qliId1',
                module: 'Products',
                position: -1
            });
            rowModel2 = new Backbone.Model({
                id: 'pbnId2',
                module: 'ProductBundleNotes',
                position: -1
            });
            rowModel3 = new Backbone.Model({
                id: 'qliId3',
                module: 'Products',
                position: -1
            });
            rowModel4 = new Backbone.Model({
                id: 'pbnId4',
                module: 'ProductBundleNotes',
                position: -1
            });
        });

        afterEach(function() {
            dataGroup = null;
            rowModel1 = null;
            rowModel2 = null;
            rowModel3 = null;
            rowModel4 = null;
            results = null;
            callUrl = null;
        });

        describe('full row position updates', function() {
            beforeEach(function() {
                dataGroup.collection.add(rowModel1);
                dataGroup.collection.add(rowModel2);
                dataGroup.collection.add(rowModel3);
                dataGroup.collection.add(rowModel4);

                results = layout._updateRowPositions(dataGroup);
            });

            it('should call collection.sort on the group', function() {
                expect(dataGroup.collection.sort).toHaveBeenCalled();
            });

            describe('first call', function() {
                it('should be called with method PUT', function() {
                    expect(results[0].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[0].data.position).toBe(0);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[0].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId1');
                });
            });

            describe('second call', function() {
                it('should be called with method PUT', function() {
                    expect(results[1].method).toBe('PUT');
                });

                it('should be called with data position 1', function() {
                    expect(results[1].data.position).toBe(1);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[1].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId2');
                });
            });

            describe('third call', function() {
                it('should be called with method PUT', function() {
                    expect(results[2].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[2].data.position).toBe(2);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[2].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId3');
                });
            });

            describe('fourth call', function() {
                it('should be called with method PUT', function() {
                    expect(results[3].method).toBe('PUT');
                });

                it('should be called with data position 3', function() {
                    expect(results[3].data.position).toBe(3);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[3].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId4');
                });
            });
        });

        describe('swap two row positions', function() {
            beforeEach(function() {
                rowModel1.set('position', 0);
                // switched rowModel2 and rowModel3's position
                rowModel2.set('position', 2);
                rowModel3.set('position', 1);
                rowModel4.set('position', 3);

                dataGroup.collection.add(rowModel1);
                dataGroup.collection.add(rowModel2);
                dataGroup.collection.add(rowModel3);
                dataGroup.collection.add(rowModel4);

                results = layout._updateRowPositions(dataGroup);
            });

            describe('first call', function() {
                it('should be called with method PUT', function() {
                    expect(results[0].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[0].data.position).toBe(1);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[0].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId2');
                });
            });

            describe('second call', function() {
                it('should be called with method PUT', function() {
                    expect(results[1].method).toBe('PUT');
                });

                it('should be called with data position 2', function() {
                    expect(results[1].data.position).toBe(2);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[1].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId3');
                });
            });
        });
    });

    describe('_getComponentByGroupId()', function() {
        var group;
        var result;
        beforeEach(function() {
            group = {
                name: 'quote-data-group',
                groupId: 'groupId1'
            };
            layout._components = [group];
        });

        afterEach(function() {
            group = null;
            result = null;
            layout._components = [];
        });

        it('should return the group if given a correct id', function() {
            result = layout._getComponentByGroupId('groupId1');
            expect(result).toBe(group);
        });

        it('should return undefined if group is not found', function() {
            result = layout._getComponentByGroupId('groupId2');
            expect(result).toBe(undefined);
        });
    });

    describe('_onGroupDragTriggerOver()', function() {
        var evtParam;
        var uiParam;
        var group;
        var groupTriggerSpy;
        beforeEach(function() {
            evtParam = {
                target: '<div data-group-id="groupId1"></div>'
            };
            uiParam = {};
            groupTriggerSpy = sinon.spy();
            group = {
                trigger: groupTriggerSpy
            };
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
            groupTriggerSpy = null;
            group = null;
        });

        it('should trigger quotes:sortable:over on the group', function() {
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return group;
            });
            layout._onGroupDragTriggerOver(evtParam, uiParam);

            expect(groupTriggerSpy).toHaveBeenCalledWith('quotes:sortable:over');
        });

        it('should not trigger quotes:sortable:over if no group is found', function() {
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return undefined;
            });
            layout._onGroupDragTriggerOver(evtParam, uiParam);

            expect(groupTriggerSpy).not.toHaveBeenCalled();
        });
    });

    describe('_onGroupDragTriggerOut()', function() {
        var evtParam;
        var uiParam;
        var group;
        var groupTriggerSpy;
        beforeEach(function() {
            evtParam = {
                target: '<div data-group-id="groupId1"></div>'
            };
            uiParam = {};
            groupTriggerSpy = sinon.spy();
            group = {
                trigger: groupTriggerSpy
            };
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
            groupTriggerSpy = null;
            group = null;
        });

        it('should trigger quotes:sortable:over on the group', function() {
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return group;
            });
            layout._onGroupDragTriggerOut(evtParam, uiParam);

            expect(groupTriggerSpy).toHaveBeenCalledWith('quotes:sortable:out');
        });

        it('should not trigger quotes:sortable:over if no group is found', function() {
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return undefined;
            });
            layout._onGroupDragTriggerOut(evtParam, uiParam);

            expect(groupTriggerSpy).not.toHaveBeenCalled();
        });
    });

    describe('beforeRender()', function() {
        beforeEach(function() {
            sinon.stub(layout, '$').callsFake(function() {
                return [
                    '<div class="ui-sortable"></div>'
                ];
            });
            sinon.stub($.fn, 'sortable').callsFake(function() {
            });
            layout.beforeRender();
        });

        it('should call sortable("destoy") on groups with sortable', function() {
            expect($.fn.sortable).toHaveBeenCalledWith('destroy');
        });
    });

    describe('_onProductBundleChange()', function() {
        var quoteData;
        var defaultBundle;
        var bundle1;
        var bundle2;
        var bundle3;

        beforeEach(function() {
            defaultBundle = app.data.createBean('ProductBundles', {
                id: 'defaultId1',
                name: 'defaultName1',
                default_group: true
            });
            bundle1 = app.data.createBean('ProductBundles', {
                id: 'testId1',
                name: 'testName1'
            });
            bundle2 = app.data.createBean('ProductBundles', {
                id: 'testId2',
                name: 'testName2'
            });
            bundle3 = app.data.createBean('ProductBundles', {
                id: 'testId3',
                name: 'testName3'
            });
            quoteData = app.data.createBeanCollection('ProductBundles', [
                defaultBundle,
                bundle1,
                bundle2
            ]);

            sinon.spy(layout, '_addQuoteGroupToLayout');
            sinon.spy(layout, 'render');
        });

        describe('when not copying', function() {
            beforeEach(function() {
                layout.model.set('bundles', quoteData);
            });

            it('should set this.groupIds with quoteData record IDs', function() {
                expect(layout.groupIds).toEqual([defaultBundle.cid, bundle1.cid, bundle2.cid]);
            });

            it('should call _addQuoteGroupToLayout with new bundle 1 data', function() {
                expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle1);
            });

            it('should call _addQuoteGroupToLayout with new bundle 2 data', function() {
                expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle2);
            });

            it('should call _addQuoteGroupToLayout with only bundle 3 data', function() {
                // unset the spy to reset the called with list
                layout._addQuoteGroupToLayout.restore();
                sinon.spy(layout, '_addQuoteGroupToLayout');

                quoteData = app.data.createBeanCollection('ProductBundles', [
                    defaultBundle,
                    bundle1,
                    bundle2,
                    bundle3
                ]);

                layout.model.set('bundles', quoteData);
                expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(defaultBundle);
                expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(bundle1);
                expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(bundle2);
                expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle3);
            });

            it('should call render on quote_data change', function() {
                expect(layout.render).toHaveBeenCalled();
            });
        });

        describe('when copying', function() {
            beforeEach(function() {
                layout.isCopy = true;
                layout.model.set('bundles', quoteData);
            });

            it('should not call render on quote_data change if this is copy', function() {
                expect(layout.render).not.toHaveBeenCalled();
            });
        });
    });

    describe('_switchGroupModel()', function() {
        var group;
        var model;

        beforeEach(function() {
            group = {
                groupId: 'c54321',
                switchModel: function(model) {
                    this.groupId = model.cid;
                }
            };
            sinon.spy(group, 'switchModel');
            layout.groupIds = [group.groupId];

            model = app.data.createBean('ProductBundles');
            model.cid = 'c12345';
        });

        it('should switch the group model', function() {
            layout._switchGroupModel(group, model);
            expect(group.switchModel).toHaveBeenCalled();
        });

        it('should replace the old groupId in the groupIds list', function() {
            expect(layout.groupIds).toEqual(['c54321']);
            layout._switchGroupModel(group, model);
            expect(layout.groupIds).toEqual(['c12345']);
        });
    });

    describe('_addQuoteGroupToLayout()', function() {
        var initComponentsSpy;
        beforeEach(function() {
            initComponentsSpy = sinon.spy();
            sinon.spy(layout.context, 'getChildContext');
            sinon.stub(app.view, 'createLayout').callsFake(function() {
                return {
                    initComponents: initComponentsSpy
                };
            });
            sinon.stub(layout, 'addComponent').callsFake(function() {
            });

            layout._addQuoteGroupToLayout({});
        });

        it('should call layout.context.getChildContext', function() {
            expect(layout.context.getChildContext).toHaveBeenCalled({module: 'ProductBundles'});
        });

        it('should call app.view.createLayout', function() {
            expect(app.view.createLayout).toHaveBeenCalled();
        });

        it('should call group layout initComponents', function() {
            expect(initComponentsSpy).toHaveBeenCalled();
        });

        it('should call layout.addComponent', function() {
            expect(layout.addComponent).toHaveBeenCalled();
        });
    });

    describe('_onCreateQuoteGroup()', function() {
        var callArgs;
        var bundles;

        beforeEach(function() {
            bundles = new Backbone.Collection([
                {
                    id: 'testId1',
                    position: 0
                }, {
                    id: 'testId2',
                    position: 1
                }]);
            layout.model.set({
                id: 'testQuoteLayoutId',
                bundles: bundles,
                currency_id: 'currency_id_1',
                base_rate: '50.37'
            });

            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(app.api, 'relationships').callsFake(function() {
            });
        });

        afterEach(function() {
            callArgs = null;
            bundles = null;
        });

        describe('when in create view', function() {
            var newBundle;

            beforeEach(function() {
                newBundle = new Backbone.Model();
                sinon.stub(layout.context, 'trigger').callsFake(function() {
                });
                sinon.stub(layout, '_createNewProductBundleBean').callsFake(function() {
                    return newBundle;
                });
                sinon.stub(bundles, 'add');
                layout.isCreateView = true;

                layout._onCreateQuoteGroup();
                callArgs = app.api.relationships.firstCall;
            });

            afterEach(function() {
                newBundle = null;
            });

            it('should set _justSaved to true', function() {
                expect(newBundle.get('_justSaved')).toBeTruthy();
            });

            it('should set ignoreUserPrefCurrency on the new bundle to true', function() {
                expect(newBundle.ignoreUserPrefCurrency).toBeTruthy();
            });

            it('should add the new bundle to bundles', function() {
                expect(bundles.add).toHaveBeenCalledWith(newBundle);
            });

            it('should call context.trigger with quotes:group:create:success, newBundle', function() {
                expect(layout.context.trigger).toHaveBeenCalledWith('quotes:group:create:success', newBundle);
            });
        });

        describe('when not in create view', function() {
            beforeEach(function() {
                layout._onCreateQuoteGroup();
                callArgs = app.api.relationships.firstCall;
            });

            it('should call app.alert.show', function() {
                expect(app.alert.show).toHaveBeenCalled();
            });

            it('should call app.api.relationships', function() {
                expect(app.api.relationships).toHaveBeenCalled();
            });

            it('should call app.api.relationships with method create', function() {
                expect(callArgs.args[0]).toBe('create');
            });

            it('should call app.api.relationships with module Quotes', function() {
                expect(callArgs.args[1]).toBe('Quotes');
            });

            it('should call app.api.relationships with proper link payload Quote ID', function() {
                expect(callArgs.args[2].id).toBe('testQuoteLayoutId');
            });

            it('should call app.api.relationships with proper link payload link name', function() {
                expect(callArgs.args[2].link).toBe('product_bundles');
            });

            it('should call app.api.relationships with proper link payload position', function() {
                expect(callArgs.args[2].related.position).toBe(2);
            });

            it('should call app.api.relationships with proper link payload currency', function() {
                expect(callArgs.args[2].related.currency_id).toBe('currency_id_1');
                expect(callArgs.args[2].related.base_rate).toBe('50.37');
            });

            it('should call app.api.relationships with payload position of 1 when no bundles exist', function() {
                // because default group is 0, any new create could get a position of 1
                layout.model.set('bundles', new Backbone.Collection());
                layout.bundlesBeingSavedCt = 0;
                layout._onCreateQuoteGroup();

                callArgs = app.api.relationships.lastCall;

                expect(callArgs.args[2].related.position).toBe(1);
            });
        });
    });

    describe('_onCreateQuoteGroupSuccess()', function() {
        var newBundleData;
        var bundles;
        beforeEach(function() {
            bundles = new Backbone.Collection([
                {
                    id: 'testId1',
                    position: 0
                }, {
                    id: 'testId2',
                    position: 1
                }]);
            layout.model.set('bundles', bundles);
            newBundleData = {
                related_record: {
                    id: 'testId3',
                    position: 2,
                    product_bundle_items: []
                }
            };
            sinon.stub(app.alert, 'dismiss').callsFake(function() {
            });
            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(layout, '_addQuoteGroupToLayout').callsFake(function() {
            });
            sinon.stub(layout, 'render').callsFake(function() {
            });
            sinon.stub(layout.context, 'trigger').callsFake(function() {
            });
            sinon.spy(bundles, 'add');
        });

        afterEach(function() {
            newBundleData = null;
        });

        describe('regardless of show_line_nums', function() {
            beforeEach(function() {
                layout._onCreateQuoteGroupSuccess(newBundleData);
            });

            it('should call app.alert.dismiss to get rid of old alert', function() {
                expect(app.alert.dismiss).toHaveBeenCalledWith('adding_bundle_alert');
            });

            it('should call app.alert.show to display new alert', function() {
                expect(app.alert.show).toHaveBeenCalledWith('added_bundle_alert');
            });

            it('should add the newBundleData to records', function() {
                expect(layout.model.get('bundles').at(3).get('id')).toBe('testId3');
            });

            it('should call add on bundles collection', function() {
                expect(bundles.add).toHaveBeenCalledWith(newBundleData.related_record);
            });

            it('should trigger quotes:group:create:success and send newBundleData', function() {
                expect(layout.context.trigger).toHaveBeenCalledWith('quotes:group:create:success', newBundleData);
            });
        });

        describe('with show_line_nums', function() {
            it('should call context.trigger if show_line_nums is true', function() {
                layout.model.set('show_line_nums', true);
                layout._onCreateQuoteGroupSuccess(newBundleData);

                expect(layout.context.trigger).toHaveBeenCalledWith('quotes:show_line_nums:changed', true);
            });

            it('should not call context.trigger if show_line_nums is false', function() {
                layout.model.set('show_line_nums', false);
                layout._onCreateQuoteGroupSuccess(newBundleData);

                expect(layout.context.trigger).not.toHaveBeenCalledWith('quotes:show_line_nums:changed', true);
            });
        });
    });

    describe('_onCreateQuoteGroupSuccess()', function() {
        var newBundleData;
        var layoutModel;
        var bundlesCollection;

        beforeEach(function() {
            bundlesCollection = new Backbone.Collection({
                id: 'recordId1'
            });
            layoutModel = app.data.createBean('Quotes', {
                bundles: bundlesCollection
            });
            layout.model = layoutModel;
            newBundleData = {
                record: {
                    id: 'recordId1'
                },
                related_record: {
                    id: 'relatedId1'
                }
            };
            sinon.stub(app.alert, 'dismiss').callsFake(function() {
            });
            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(layout.context, 'trigger').callsFake(function() {
            });
        });

        afterEach(function() {
            newBundleData = null;
            bundlesCollection = null;
            layoutModel.dispose();
            layoutModel = null;
        });

        describe('general behavior', function() {
            beforeEach(function() {
                layout.model.set('show_line_nums', false);

                layout._onCreateQuoteGroupSuccess(newBundleData);
            });

            it('should call app.alert.dismiss on the adding_bundle_alert', function() {
                expect(app.alert.dismiss).toHaveBeenCalledWith('adding_bundle_alert');
            });

            it('should call app.alert.show on the added_bundle_alert', function() {
                expect(app.alert.show).toHaveBeenCalledWith('added_bundle_alert');
            });

            it('should set product_bundle_items to empty array when no product_bundle_items exist', function() {
                expect(newBundleData.related_record.product_bundle_items).toEqual([]);
            });

            it('should set related_record._justSaved = true', function() {
                expect(newBundleData.related_record._justSaved).toBeTruthy();
            });

            it('should not trigger quotes:show_line_nums:changed on layout.context', function() {
                expect(layout.context.trigger).not.toHaveBeenCalledWith('quotes:show_line_nums:changed', true);
            });

            it('should call app.alert.show on the added_bundle_alert', function() {
                expect(layout.context.trigger).toHaveBeenCalledWith('quotes:group:create:success');
            });
        });

        describe('when show_line_nums is true', function() {
            beforeEach(function() {
                layout.model.set('show_line_nums', true);

                layout._onCreateQuoteGroupSuccess(newBundleData);
            });

            it('should trigger quotes:show_line_nums:changed on layout.context', function() {
                expect(layout.context.trigger).toHaveBeenCalledWith('quotes:show_line_nums:changed', true);
            });
        });
    });

    describe('_onDeleteSelectedItems()', function() {
        var massCollection;
        var groupLayout;
        var groupLayoutId;
        var groupList;
        var rowModel1;
        var rowModel2;
        var request;

        beforeEach(function() {
            massCollection = new Backbone.Collection();

            rowModel1 = app.data.createBean('Products', {
                id: 'productId1'
            });
            rowModel1.module = 'Products';
            rowModel1.link = {
                bean: {
                    id: 'layoutId1'
                }
            };

            rowModel2 = app.data.createBean('Products', {
                id: 'productId2'
            });
            rowModel2.module = 'Products';
            rowModel2.link = {
                bean: {
                    id: 'layoutId1'
                }
            };

            massCollection.add(rowModel1);
            massCollection.add(rowModel2);

            groupList = {
                toggledModels: {
                    productId1: rowModel1
                }
            };
            groupLayoutId = 'layoutId1';
            groupLayout = {
                quoteDataGroupList: {
                    collection: new Backbone.Collection(),
                    toggledModels: {}
                },
                groupId: groupLayoutId,
                collection: app.data.createMixedBeanCollection(),
                getGroupListComponent: function() {
                    return groupList;
                },
                dispose: $.noop
            };

            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return groupLayout;
            });
            sinon.stub(app.api, 'buildURL').callsFake(function(path) {
                return '.../' + path;
            });
            sinon.stub(layout, '_callBulkRequests').callsFake(function() {
            });

            layout._onDeleteSelectedItems(massCollection);
        });

        afterEach(function() {
            massCollection = null;
            groupLayout = null;
            groupLayoutId = null;
            groupList = null;
            rowModel1 = null;
            rowModel2 = null;
            request = null;
        });

        it('should have 3 bulk requests ready', function() {
            expect(layout.currentBulkSaveRequests.length).toBe(3);
        });

        describe('first request', function() {
            beforeEach(function() {
                request = layout.currentBulkSaveRequests[0];
            });

            it('should have url Products/productId1', function() {
                expect(request.url).toBe('Products/productId1');
            });

            it('should have method = DELETE', function() {
                expect(request.method).toBe('DELETE');
            });
        });

        describe('second request', function() {
            beforeEach(function() {
                request = layout.currentBulkSaveRequests[1];
            });

            it('should have url Products/productId1', function() {
                expect(request.url).toBe('Products/productId2');
            });

            it('should have method = DELETE', function() {
                expect(request.method).toBe('DELETE');
            });
        });

        describe('third request', function() {
            beforeEach(function() {
                request = layout.currentBulkSaveRequests[2];
            });

            it('should have url Products/productId1', function() {
                expect(request.url).toBe('ProductBundles/layoutId1');
            });

            it('should have method = DELETE', function() {
                expect(request.method).toBe('GET');
            });
        });
    });

    describe('_onDeleteSelectedItemsSuccess()', function() {
        var massCollection;
        var bulkRequests;
        var rowModel;
        var groupLayoutId;
        var groupLayout;
        var groupLayoutCollection;
        var groupLayoutModel;

        beforeEach(function() {
            massCollection = new Backbone.Collection();
            groupLayoutCollection = new Backbone.Collection();
            rowModel = app.data.createBean('Products', {
                id: 'productId1'
            });
            rowModel.module = 'Products';
            rowModel.link = {
                bean: {
                    id: 'layoutId1'
                }
            };
            massCollection.add(rowModel);
            groupLayoutCollection.add(rowModel);

            groupLayoutModel = app.data.createBean('ProductBundles', {
                id: 'layoutId1',
                total: '100'
            });
            bulkRequests = [];

            groupLayoutId = 'layoutId1';
            groupLayout = {
                groupId: groupLayoutId,
                collection: groupLayoutCollection,
                model: groupLayoutModel,
                getGroupListComponent: function() {
                    return groupList;
                },
                trigger: sinon.spy(),
                dispose: $.noop
            };

            sinon.stub(app.alert, 'dismiss').callsFake(function() {
            });
            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return groupLayout;
            });
        });

        afterEach(function() {
            massCollection = null;
            bulkRequests = null;
            rowModel = null;
            groupLayoutId = null;
            groupLayout = null;
            groupLayoutCollection = null;
            groupLayoutModel = null;
        });

        describe('general behavior', function() {
            beforeEach(function() {
                layout._onDeleteSelectedItemsSuccess(massCollection, bulkRequests);
            });

            it('should call app.alert.dismiss deleting_line_item', function() {
                expect(app.alert.dismiss).toHaveBeenCalledWith('deleting_line_item');
            });

            it('should call app.alert.show deleted_line_item', function() {
                expect(app.alert.show).toHaveBeenCalledWith('deleted_line_item');
            });
        });

        describe('when request is a record delete', function() {
            beforeEach(function() {
                bulkRequests = [
                    {
                        contents: {
                            id: 'productId1'
                        }
                    }];

                layout._onDeleteSelectedItemsSuccess(massCollection, bulkRequests);
            });

            it('should remove rowModel from the group layout collection', function() {
                expect(groupLayout.collection.length).toBe(0);
            });

            it('should remove rowModel from massCollection', function() {
                expect(massCollection.length).toBe(0);
            });
        });

        describe('when request is a group update', function() {
            beforeEach(function() {
                bulkRequests = [
                    {
                        contents: {
                            id: 'layoutId1',
                            total: '0.00'
                        }
                    }];
                sinon.stub(layout, '_updateModelWithRecord');

                layout._onDeleteSelectedItemsSuccess(massCollection, bulkRequests);
            });

            it('should call _updateModelWithRecord and update the layout model', function() {
                expect(layout._updateModelWithRecord).toHaveBeenCalled();
            });

            it('should call trigger quotes:line_nums:reset on the layout', function() {
                expect(groupLayout.trigger).toHaveBeenCalledWith('quotes:line_nums:reset');
            });
        });
    });

    describe('_onDeleteQuoteGroupConfirm()', function() {
        var groupId;
        var groupName;
        var groupModel;
        var groupToDelete;
        var defaultGroupId;
        var defaultGroup;
        var defaultGroupModel;
        var bundleItem1;

        beforeEach(function() {
            bundleItem1 = app.data.createBean('Products', {
                id: 'bundleItemId1',
                position: 0
            });
            defaultGroupId = 'defaultGroupId1';
            defaultGroupModel = app.data.createBean('ProductBundles', {
                id: defaultGroupId,
                product_bundle_items: new Backbone.Collection()
            });
            defaultGroup = {
                id: defaultGroupId,
                model: defaultGroupModel,
                switchModel: function() {}
            };
            groupId = 'testId1';
            groupName = 'testName1';
            groupModel = new Backbone.Model({
                id: groupId,
                name: groupName,
                product_bundle_items: new Backbone.Collection(bundleItem1)
            });
            groupToDelete = {
                id: groupId,
                name: groupName,
                model: groupModel
            };
            sinon.stub(layout, '_getComponentByGroupId').callsFake(function() {
                return defaultGroup;
            });
            layout.defaultGroupId = defaultGroupId;
            layout.model.set('id', 'testQuoteLayoutId');
            layout.model.set('bundles', new Backbone.Collection(groupModel));
            sinon.stub(app.alert, 'show').callsFake(function() {
            });
            sinon.stub(layout, '_saveDefaultGroupThenCallBulk').callsFake(function() {
            });
            sinon.stub(layout, '_callBulkRequests').callsFake(function() {
            });
        });

        afterEach(function() {
            groupId = null;
            groupName = null;
            groupModel = null;
            groupToDelete = null;
            defaultGroupId = null;
            defaultGroup = null;
            defaultGroupModel = null;
            bundleItem1 = null;
        });

        it('should call app.alert.show', function() {
            layout._onDeleteQuoteGroupConfirm(groupId, groupName, groupToDelete);

            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call _saveDefaultGroupThenCallBulk if default group is not saved', function() {
            sinon.stub(defaultGroupModel, 'isNew').callsFake(function() {
                return true;
            });
            layout._onDeleteQuoteGroupConfirm(groupId, groupName, groupToDelete);

            expect(layout._saveDefaultGroupThenCallBulk).toHaveBeenCalled();
        });

        it('should call _callBulkRequests if default group is already saved', function() {
            layout._onDeleteQuoteGroupConfirm(groupId, groupName, groupToDelete);

            expect(layout._callBulkRequests).toHaveBeenCalled();
        });
    });

    describe('_dispose()', function() {
        beforeEach(function() {
            sinon.stub(layout, 'beforeRender').callsFake(function() {
            });
        });

        it('should call beforeRender', function() {
            layout._dispose();

            expect(layout.beforeRender).toHaveBeenCalled();
        });
    });
});
