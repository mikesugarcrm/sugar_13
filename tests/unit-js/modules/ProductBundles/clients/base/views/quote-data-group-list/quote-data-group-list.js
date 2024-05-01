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
describe('ProductBundles.Base.Views.QuoteDataGroupList', function() {
    var app;
    var view;
    var viewMeta;
    var viewContext;
    var viewParentContext;
    var viewContextOnSpy;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var pbnMetadata;
    var prodMetadata;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = app.data.createBean('ProductBundles', {
            product_bundle_items: app.data.createMixedBeanCollection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ])
        });
        viewLayoutModel.fields = {
            product_bundle_items: {
                link: ['products', 'product_bundle_notes']
            }
        };
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'ProductBundles', 'default', layoutDefs, viewParentContext);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            selection: {
                type: 'multi',
                actions: [
                    {
                        name: 'edit_row_button',
                        type: 'button'
                    },
                    {
                        name: 'delete_row_button',
                        type: 'button'
                    }
                ]
            }
        };

        pbnMetadata = {
            panels: [{
                fields: [{
                    name: 'description',
                    rows: 3
                }]
            }]
        };

        prodMetadata = {
            panels: [{
                fields: [
                    'field1', 'field2', 'field3', 'field4'
                ]
            }]
        };

        viewParentContext = app.context.getContext();
        viewParentContext.set({
            module: 'Quotes',
            create: false,
            currency_id: 'quote_currency_id',
            base_rate: '2.0',
            billing_accounts: {
                id: 'test'
            }
        });
        viewParentContext.prepare();

        viewContext = app.context.getContext();
        viewContext.set({
            module: 'ProductBundles'
        });
        viewContext.parent = viewParentContext;
        viewContext.prepare();

        viewContextOnSpy = sinon.stub(viewContext, 'on').callsFake(function() {});

        sinon.stub(app.metadata, 'getView')
            .withArgs('ProductBundleNotes').returns(pbnMetadata)
            .withArgs('Products').returns(prodMetadata);

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-list',
            viewMeta, viewContext, true, layout);

        view.context.set('parentModel', viewParentContext);
        sinon.stub(view, 'setElement');
    });

    afterEach(function() {
        viewParentContext = null;
        viewContext = null;
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        var initOptions;
        var initModel;

        afterEach(function() {
            initOptions = null;
            initModel = null;
        });

        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should set listColSpan to be the layout listColSpan', function() {
            expect(view.listColSpan).toBe(layout.listColSpan);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        it('should set collection based on product_bundle_items from the model', function() {
            expect(view.collection.length).toBe(3);
        });

        describe('setting mass_collection on the context', function() {
            var layoutLayout;
            var layoutLayoutMassCollection;

            beforeEach(function() {
                layoutLayoutMassCollection = new Backbone.Collection();
                layoutLayout = {
                    getComponent: function() {
                        return {
                            massCollection: layoutLayoutMassCollection
                        };
                    }
                };
                initModel = new Backbone.Model();
                initModel.fields = {
                    product_bundle_items: {
                        link: []
                    }
                };
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2,
                        layout: layoutLayout
                    },
                    model: initModel
                };
                sinon.stub(view, 'addMultiSelectionAction').callsFake(function() {});
            });

            afterEach(function() {
                layoutLayoutMassCollection = null;
                layoutLayout = null;
            });

            it('should set mass_collection to the layout.layout mass collection', function() {
                view.initialize(initOptions);

                expect(view.context.get('mass_collection')).toEqual(layoutLayoutMassCollection);
            });
        });

        describe('setting isCreateView and isOppsConvert', function() {
            var initModel;
            var collection;
            var parentModel;

            beforeEach(function() {
                sinon.stub(view, 'addMultiSelectionAction').callsFake(function() {});
                parentModel = new Backbone.Model();
                initModel = new Backbone.Model();
                initModel.fields = {
                    product_bundle_items: {
                        link: []
                    }
                };
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2
                    },
                    model: initModel
                };
                collection = new Backbone.Collection();
            });

            it('should set isCreateView true if create is on parent context', function() {
                viewParentContext.set('create', true);
                view.initialize(initOptions);
                expect(view.isCreateView).toBeTruthy();
            });

            it('should set isOppsConvert true if isCreateView parent.convert is on the context ' +
                'and parentModelModule is RevenueLineItems', function() {
                parentModel.set('_module', 'RevenueLineItems');
                viewParentContext.set({
                    create: true,
                    convert: true,
                    parentModel: parentModel
                });
                view.initialize(initOptions);
                expect(view.isOppsConvert).toBeTruthy();
            });

            it('should set isOppsConvert true if isCreateView parent.convert is on the context ' +
                'and parentModelModule is Opportunities', function() {
                parentModel.set('_module', 'Opportunities');
                viewParentContext.set({
                    create: true,
                    convert: true,
                    parentModel: parentModel,
                    fromLink: 'foo'
                });
                view.initialize(initOptions);
                expect(view.isOppsConvert).toBeTruthy();
            });

            it('should set isOppsConvert false if isCreateView parent.convert is on the context ' +
                'and parentModelModule is Opportunities but fromLink is quotes', function() {
                parentModel.set('_module', 'Opportunities');
                viewParentContext.set({
                    create: true,
                    convert: true,
                    parentModel: parentModel,
                    fromLink: 'quotes'
                });
                view.initialize(initOptions);
                expect(view.isOppsConvert).toBeFalsy();
            });

            it('should set isOppsConvert false if the parentModel is not defined', function() {
                viewParentContext.set({
                    create: true,
                    convert: true,
                    fromLink: 'foo'
                });
                view.initialize(initOptions);
                expect(view.isOppsConvert).toBeFalsy();
            });

            it('should set isCreateView false if create is not on parent context', function() {
                viewParentContext.unset('create');
                view.initialize(initOptions);
                expect(view.isCreateView).toBeFalsy();
            });

            it('should set isOppsConvert false if create is not true', function() {
                viewParentContext.unset('create');
                viewParentContext.set('convert', true);
                view.initialize(initOptions);
                expect(view.isCreateView).toBeFalsy();
            });
        });

        describe('setting isEmptyGroup', function() {
            var collection;

            beforeEach(function() {
                sinon.stub(view, 'addMultiSelectionAction').callsFake(function() {});
                initModel = new Backbone.Model();
                initModel.fields = {
                    product_bundle_items: {
                        link: []
                    }
                };
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2
                    },
                    model: initModel
                };
                collection = new Backbone.Collection();
            });

            it('should set isEmptyGroup true if product_bundle_items collection has no records', function() {
                initModel.set('product_bundle_items', collection);
                initOptions.model = initModel;
                view.initialize(initOptions);
                expect(view.isEmptyGroup).toBeTruthy();
            });

            it('should set isEmptyGroup false if product_bundle_items collection has records', function() {
                collection.add(new Backbone.Model({
                    id: 'test1'
                }));
                initModel.set('product_bundle_items', collection);
                initOptions.model = initModel;
                view.initialize(initOptions);
                expect(view.isEmptyGroup).toBeFalsy();
            });
        });

        describe('setting fields', function() {
            var viewModel;
            var collection;

            beforeEach(function() {
                viewContextOnSpy.resetHistory();
                collection = new Backbone.Collection();
                viewModel = new Backbone.Model({
                    id: 'viewId1',
                    product_bundle_items: collection
                });
                viewModel.fields = {
                    product_bundle_items: {
                        link: []
                    }
                };
                sinon.stub(collection, 'on').callsFake(function() {});
                sinon.stub(view.layout, 'on').callsFake(function() {});
                sinon.stub(view.context.parent, 'on').callsFake(function() {});
                view.name = 'viewName';
                view.initialize({
                    context: viewContext,
                    meta: viewMeta,
                    model: viewModel,
                    layout: {
                        listColSpan: 2
                    }
                });
            });

            afterEach(function() {
                viewModel = null;
            });

            it('should add listener on layout for quotes:group:create:qli', function() {
                expect(view.layout.on.args[0][0]).toBe('quotes:group:create:qli');
            });

            it('should add listener on layout for quotes:group:create:note', function() {
                expect(view.layout.on.args[1][0]).toBe('quotes:group:create:note');
            });

            it('should call view.layout.on should be called with "quotes:sortable:over"', function() {
                expect(view.layout.on.args[2][0]).toBe('quotes:sortable:over');
            });

            it('should call view.layout.on should be called with "quotes:sortable:out"', function() {
                expect(view.layout.on.args[3][0]).toBe('quotes:sortable:out');
            });

            it('should add listener on layout for editablelist:<viewName>:cancel', function() {
                expect(view.layout.on.args[4][0]).toBe('editablelist:' + view.name + ':cancel');
            });

            it('should add listener on layout for editablelist:<viewName>:save', function() {
                expect(view.layout.on.args[5][0]).toBe('editablelist:' + view.name + ':save');
            });

            it('should add listener on layout for editablelist:<viewName>:saving', function() {
                expect(view.layout.on.args[6][0]).toBe('editablelist:' + view.name + ':saving');
            });

            it('should add listener on context.parent for quotes:collections:all:checked', function() {
                expect(view.context.parent.on.args[0][0]).toBe('quotes:collections:all:checked');
            });

            it('should add listener on context.parent for quotes:collections:not:all:checked', function() {
                expect(view.context.parent.on.args[1][0]).toBe('quotes:collections:not:all:checked');
            });

            it('should call view.collection.on should be called with "add remove"', function() {
                expect(view.collection.on.args[0][0]).toBe('add remove');
            });

            it('should call setElement', function() {
                expect(view.setElement).toHaveBeenCalled();
            });

            it('should set this.pbnListMetadata to be the ProductBundleNotes metadata', function() {
                expect(view.pbnListMetadata).toBe(pbnMetadata);
            });

            it('should set this.qliListMetadata to be the Products metadata', function() {
                expect(view.qliListMetadata).toBe(prodMetadata);
            });

            it('should initialize pbnDescriptionMetadata', function() {
                expect(view.pbnDescriptionMetadata).toEqual({
                    name: 'description',
                    rows: 3
                });
            });

            it('should set this._fields to be the Products metadata fields', function() {
                expect(view._fields).toEqual(view.qliListMetadata.panels[0].fields);
            });

            describe('setting leftColumns', function() {
                it('should be type fieldset', function() {
                    expect(view.leftColumns[0].type).toBe('fieldset');
                });

                it('should have one item in fields', function() {
                    expect(view.leftColumns[0].fields.length).toBe(1);
                });

                it('should have two buttons in fields', function() {
                    expect(view.leftColumns[0].fields[0].buttons.length).toBe(2);
                });

                it('should have buttons of type quote-data-actionmenu', function() {
                    expect(view.leftColumns[0].fields[0].type).toBe('quote-data-actionmenu');
                });

                it('should have first button in fields is edit', function() {
                    expect(view.leftColumns[0].fields[0].buttons[0].name).toBe('edit_row_button');
                    expect(view.leftColumns[0].fields[0].buttons[0].type).toBe('button');
                });

                it('should have second button in fields is edit', function() {
                    expect(view.leftColumns[0].fields[0].buttons[1].name).toBe('delete_row_button');
                    expect(view.leftColumns[0].fields[0].buttons[1].type).toBe('button');
                });
            });

            describe('setting leftSaveCancelColumn', function() {
                it('should be type fieldset', function() {
                    expect(view.leftSaveCancelColumn[0].type).toBe('fieldset');
                });

                it('should have two items in fields', function() {
                    expect(view.leftSaveCancelColumn[0].fields.length).toBe(2);
                });

                it('should have first button in fields is cancel', function() {
                    expect(view.leftSaveCancelColumn[0].fields[0].name).toBe('inline-cancel');
                    expect(view.leftSaveCancelColumn[0].fields[0].type).toBe('quote-data-editablelistbutton');
                    expect(view.leftSaveCancelColumn[0].fields[0].icon).toBe('sicon-close');
                });

                it('should have second button in fields is save', function() {
                    expect(view.leftSaveCancelColumn[0].fields[1].name).toBe('inline-save');
                    expect(view.leftSaveCancelColumn[0].fields[1].type).toBe('quote-data-editablelistbutton');
                    expect(view.leftSaveCancelColumn[0].fields[1].icon).toBe('sicon-check-circle');
                });
            });
        });
    });

    describe('_getSugarLogicDependenciesForModel()', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
            model.module = 'Products';
        });

        afterEach(function() {
            model = null;
        });

        it('will find the module in metadata and return the dependencies', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    dependencies: [{dependency: true}]
                };
            });
            var dep = view._getSugarLogicDependenciesForModel(model);
            expect(app.metadata.getModule).toHaveBeenCalledWith('Products');

            expect(dep.length).toEqual(1);
        });

        it('will load any dependencies from the record view', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    dependencies: [{dependency: true}],
                    views: {
                        record: {
                            meta: {
                                dependencies: [{dependency1: true}]
                            }
                        }
                    }
                };
            });

            var dep = view._getSugarLogicDependenciesForModel(model);
            expect(app.metadata.getModule).toHaveBeenCalledWith('Products');

            expect(dep.length).toEqual(2);
        });

        it('will load dependencies from cache', function() {
            sinon.spy(app.metadata, 'getModule');
            view.moduleDependencies.Products = [{dependency: true}];
            expect(app.metadata.getModule).not.toHaveBeenCalled();
        });
    });

    describe('onCancelRowEdit()', function() {
        var rowModel1;
        var rowModel2;

        beforeEach(function() {
            rowModel1 = new Backbone.Model({
                id: 'rowModel1'
            });

            rowModel2 = new Backbone.Model({});
            view.collection.add(rowModel1);
            view.collection.add(rowModel2);
        });

        afterEach(function() {
            rowModel1 = null;
            rowModel2 = null;
        });

        it('should only remove the rowModel from the collection when isNew()', function() {
            view.onCancelRowEdit(rowModel1);
            expect(view.collection.length).toBe(5);
        });

        it('should remove the rowModel from the collection when is New', function() {
            view.onCancelRowEdit(rowModel2);
            expect(view.collection.length).toBe(4);
        });
    });

    describe('onSaveRowEdit()', function() {
        var rowModel;
        var rowModelId;
        var oldModelId;
        var attrStub;

        beforeEach(function() {
            attrStub = sinon.stub();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    length: 1,
                    attr: attrStub
                };
            });
            sinon.stub(view, '_setRowFields').callsFake(function() {});
            sinon.stub(view, 'toggleRow').callsFake(function() {});
            sinon.stub(view, 'onNewItemChanged').callsFake(function() {});
            sinon.stub(view, 'toggleCancelButton').callsFake(function() {});

            oldModelId = 'oldRowModel1';
            rowModel = new Backbone.Model({});
            rowModelId = rowModel.cid;
            rowModel.module = 'Products';
        });

        afterEach(function() {
            rowModel = null;
            oldModelId = null;
        });

        it('should call toggleCancelButton', function() {
            view.onSaveRowEdit(rowModel, rowModelId);
            expect(view.toggleCancelButton).toHaveBeenCalled();
        });


        describe('model id == oldModelId', function() {
            it('should do nothing if model id is the same as oldModelId', function() {
                view.onSaveRowEdit(rowModel, rowModelId);
                expect(view.$).not.toHaveBeenCalled();
            });
        });
    });

    describe('onSavingRow()', function() {
        beforeEach(function() {
            sinon.stub(view, 'toggleCancelButton').callsFake(function() {});
        });

        it('should call toggleCancelButton', function() {
            view.onSavingRow(true, 'cid1');

            expect(view.toggleCancelButton).toHaveBeenCalledWith(true, 'cid1');
        });
    });

    describe('toggleCancelButton()', function() {
        var setDisabledSpy;
        beforeEach(function() {
            setDisabledSpy = sinon.spy();
            view.fields = [{
                name: 'inline-cancel',
                setDisabled: setDisabledSpy,
                model: view.model
            }];
        });

        afterEach(function() {
            setDisabledSpy = null;
            view.fields = null;
        });

        it('should call toggleCancelButton', function() {
            view.toggleCancelButton(true, view.model.cid);

            expect(setDisabledSpy).toHaveBeenCalled();
        });
    });

    describe('onAddNewItemToGroup()', function() {
        var linkName;
        var relatedModel;
        var relatedModelId;
        var groupModel;

        beforeEach(function() {
            linkName = 'products';
            groupModel = new Backbone.Model();
            relatedModel = new Backbone.Model();
            relatedModelId = relatedModel.cid;
            relatedModel.fields = {};

            sinon.stub(app.data, 'createRelatedBean').callsFake(function() {
                return relatedModel;
            });

            view.model.set({
                currency_id: 'currency_id_1',
                base_rate: '50.37'
            });
        });

        afterEach(function() {
            linkName = null;
            relatedModel = null;
            relatedModelId = null;
        });

        describe('when adding new item to group', function() {
            beforeEach(function() {
                view.onAddNewItemToGroup(linkName);
            });

            it('should set the new relatedModel position to be the max of the collection models positions', function() {
                expect(relatedModel.get('position')).toBe(3);
            });

            it('should set the new relatedModel modelView to be edit', function() {
                expect(relatedModel.modelView).toBe('edit');
            });

            it('should add the new relatedModel to toggledModels', function() {
                expect(view.toggledModels[relatedModelId]).toEqual(relatedModel);
            });

            it('should add the new relatedModel to collection', function() {
                expect(view.collection.contains(relatedModel)).toBeTruthy();
            });

            it('should add the new relatedModel with proper currency payload', function() {
                expect(relatedModel.get('currency_id')).toBe('quote_currency_id');
                expect(relatedModel.get('base_rate')).toBe('2.0');
            });

            it('should set ignoreUserPrefCurrency on relatedModel so that the values are not overridden', function() {
                expect(relatedModel.ignoreUserPrefCurrency).toBeTruthy();
            });
        });

        describe('adding sortable or non-sortable classes', function() {
            var addClassStub;

            beforeEach(function() {
                addClassStub = sinon.stub();
                sinon.stub(view, '$').callsFake(function() {
                    return {
                        length: 1,
                        addClass: addClassStub
                    };
                });
            });

            afterEach(function() {
                addClassStub = null;
            });

            it('should call addClass on related row when isCreateView is true', function() {
                view.isCreateView = true;
                view.onAddNewItemToGroup(linkName);
                expect(addClassStub).toHaveBeenCalledWith(view.sortableCSSClass);
            });

            it('should call removeClass on related row when isCreateView is false', function() {
                view.isCreateView = false;
                view.onAddNewItemToGroup(linkName);
                expect(addClassStub).toHaveBeenCalledWith(view.nonSortableCSSClass);
            });
        });

        describe('adding new item with preopulated data', function() {
            var prepopulateModel;
            var oldUserId;
            var newUserId = '1234';

            beforeEach(function() {
                oldUserId = app.user.id;
                app.user.id = newUserId;
                prepopulateModel = new Backbone.Model({
                    _module: 'RevenueLineItems',
                    account_id: 'newAcctId1',
                    account_name: 'newAcctName1',
                    opportunity_id: 'newOppId1',
                    opportunity_name: 'newOppName1',
                    currency_id: 'prepopulated-data-currency-id',
                    base_rate: '15'
                });
                view.onAddNewItemToGroup(linkName, prepopulateModel.toJSON());
            });

            afterEach(function() {
                app.user.id = oldUserId;
                prepopulateModel = null;
            });

            it('should reset the _module', function() {
                expect(relatedModel.get('_module')).toBe('Products');
            });

            it('should populate with passed in data', function() {
                expect(relatedModel.get('account_id')).toBe('newAcctId1');
                expect(relatedModel.get('account_name')).toBe('newAcctName1');
                expect(relatedModel.get('opportunity_id')).toBe('newOppId1');
                expect(relatedModel.get('opportunity_name')).toBe('newOppName1');
            });

            it('should populate currency_id, base_rate and assigned_user_id with passed in data', function() {
                expect(relatedModel.get('currency_id')).toBe('prepopulated-data-currency-id');
                expect(relatedModel.get('base_rate')).toBe('15');
                expect(relatedModel.get('assigned_user_id')).toBe(newUserId);
            });
        });

        describe('adding custom service duration field to relatedModels fields', function() {
            describe('when relatedModels has both service duration value and unit fields',
                function() {
                var durationField;
                beforeEach(function() {
                    relatedModel.fields.service_duration_value = {name: 'service_duration_value'};
                    relatedModel.fields.service_duration_unit = {name: 'service_duration_unit'};

                    durationField = {
                        'name': 'service_duration',
                        'type': 'fieldset',
                        'css_class': 'service-duration-field',
                        'label': 'LBL_SERVICE_DURATION',
                        'inline': true,
                        'show_child_labels': false,
                        'fields': [
                            relatedModel.fields.service_duration_value,
                            relatedModel.fields.service_duration_unit,
                        ],
                        'related_fields': [
                            'service_start_date',
                            'service_end_date',
                            'renewable',
                            'service',
                        ],
                    };

                    view.onAddNewItemToGroup(linkName);
                });
                afterEach(function() {
                    durationField = null;
                });
                it('should add service duration field in relatedModel fields', function() {
                    expect(relatedModel.fields.service_duration).toBeDefined();
                    expect(relatedModel.fields.service_duration).toEqual(durationField);
                });
            });

            describe('when relatedModels has service duration value or unit field undefined',
                function() {
                it('should not add service duration field in relatedModel fields', function() {
                    relatedModel.fields.service_duration_value = undefined;
                    relatedModel.fields.service_duration_unit = {name: 'service_duration_unit'};

                    view.onAddNewItemToGroup(linkName);

                    expect(relatedModel.fields.service_duration).not.toBeDefined();
                });

                it('should not add service duration field in relatedModel fields', function() {
                    relatedModel.fields.service_duration_value = {name: 'service_duration_value'};
                    relatedModel.fields.service_duration_unit = undefined;

                    view.onAddNewItemToGroup(linkName);

                    expect(relatedModel.fields.service_duration).not.toBeDefined();
                });
            });
        });
    });

    describe('onNewItemChanged()', function() {
        var layoutCollection;
        beforeEach(function() {
            layoutCollection = new Backbone.Collection();
            view.collection = layoutCollection;
            sinon.stub(view, 'toggleEmptyRow').callsFake(function() {});
        });

        afterEach(function() {
            layoutCollection = null;
        });

        describe('when layout collection has records', function() {
            beforeEach(function() {
                layoutCollection.add(new Backbone.Model({
                    id: 'test1'
                }));
                view.onNewItemChanged();
            });

            it('should set isEmptyGroup false', function() {
                expect(view.isEmptyGroup).toBeFalsy();
            });

            it('should call toggleEmptyRow with false', function() {
                expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
            });
        });

        describe('when layout collection has no records', function() {
            beforeEach(function() {
                view.onNewItemChanged();
            });

            it('should set isEmptyGroup true', function() {
                expect(view.isEmptyGroup).toBeTruthy();
            });

            it('should call toggleEmptyRow with true', function() {
                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
            });
        });
    });

    describe('_onSortableGroupOver()', function() {
        it('should always call toggleEmptyRow with false', function() {
            sinon.stub(view, 'toggleEmptyRow');
            view._onSortableGroupOver();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
        });
    });

    describe('_onSortableGroupOut()', function() {
        var evtParam;
        var uiParam;
        beforeEach(function() {
            sinon.stub(view, 'toggleEmptyRow');
            uiParam = {};
            evtParam = {};
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
        });

        describe('when isEmptyGroup is true', function() {
            beforeEach(function() {
                view.isEmptyGroup = true;
                uiParam = {
                    sender: null
                };
            });

            it('should always call toggleEmptyRow with true because the collection is empty', function() {
                view._onSortableGroupOut(evtParam, uiParam);

                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
            });
        });

        describe('when isEmptyGroup is false', function() {
            beforeEach(function() {
                view.isEmptyGroup = false;
            });

            describe('when ui.sender is null', function() {
                beforeEach(function() {
                    uiParam = {
                        sender: null
                    };
                });

                describe('when view.collection.length = 1', function() {
                    beforeEach(function() {
                        view.collection.reset(new Backbone.Model({
                            id: 1
                        }));
                    });

                    describe('when the current item 0 is hidden', function() {
                        beforeEach(function() {
                            uiParam.item = {
                                get: function() {
                                    return '<div style="display: none"></div>';
                                }
                            };
                        });

                        it('should call toggleEmptyRow with true', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
                        });
                    });

                    describe('when the current item 0 is not hidden', function() {
                        beforeEach(function() {
                            uiParam.item = {
                                get: function() {
                                    return '<div style="display: block"></div>';
                                }
                            };
                        });

                        it('should call toggleEmptyRow with true', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                        });
                    });
                });

                describe('when view.collection.length != 1', function() {
                    beforeEach(function() {
                        view.collection.reset();
                    });

                    it('should call toggleEmptyRow with false', function() {
                        view._onSortableGroupOut(evtParam, uiParam);

                        expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                    });
                });
            });

            describe('when ui.sender is not null', function() {
                describe('when ui.sender el is the same as the view.el', function() {
                    beforeEach(function() {
                        view.el = '<div id="viewEl" style="display: block"></div>';
                        uiParam.sender = {
                            length: 1,
                            get: function() {
                                return view.el;
                            }
                        };
                    });

                    describe('when view.collection.length = 1', function() {
                        beforeEach(function() {
                            view.collection.reset(new Backbone.Model({
                                id: 1
                            }));
                        });

                        describe('when the current item 0 is hidden', function() {
                            beforeEach(function() {
                                uiParam.item = {
                                    get: function() {
                                        return '<div style="display: none"></div>';
                                    }
                                };
                            });

                            it('should call toggleEmptyRow with true', function() {
                                view._onSortableGroupOut(evtParam, uiParam);

                                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
                            });
                        });

                        describe('when the current item 0 is not hidden', function() {
                            beforeEach(function() {
                                uiParam.item = {
                                    get: function() {
                                        return '<div style="display: block"></div>';
                                    }
                                };
                            });

                            it('should call toggleEmptyRow with true', function() {
                                view._onSortableGroupOut(evtParam, uiParam);

                                expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                            });
                        });
                    });

                    describe('when view.collection.length != 1', function() {
                        beforeEach(function() {
                            view.collection.reset();
                        });

                        it('should call toggleEmptyRow with false', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                        });
                    });
                });

                describe('when ui.sender el is different from the view.el', function() {
                    beforeEach(function() {
                        view.el = '<div id="viewEl" style="display: block"></div>';
                        uiParam.sender = {
                            length: 1,
                            get: function() {
                                return '<div id="diffEl" style="display: block"></div>';
                            }
                        };
                    });

                    it('should call toggleEmptyRow with false because sender is not in the same group', function() {
                        view._onSortableGroupOut(evtParam, uiParam);

                        expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                    });
                });
            });
        });
    });

    describe('toggleEmptyRow()', function() {
        var addClassSpy;
        var removeClassSpy;
        beforeEach(function() {
            addClassSpy = sinon.stub();
            removeClassSpy = sinon.stub();
            sinon.stub(view, '$').callsFake(function() {
                return {
                    addClass: addClassSpy,
                    removeClass: removeClassSpy
                };
            });
        });

        it('should call remove class hidden when showEmptyRow is true', function() {
            view.toggleEmptyRow(true);

            expect(removeClassSpy).toHaveBeenCalled();
            expect(addClassSpy).not.toHaveBeenCalled();
        });

        it('should call add class hidden when showEmptyRow is false', function() {
            view.toggleEmptyRow(false);

            expect(removeClassSpy).not.toHaveBeenCalled();
            expect(addClassSpy).toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            sinon.stub(view, 'toggleEmptyRow');
            sinon.stub(view, '_super');
        });

        it('should call toggleEmptyRow with true when isEmptyGroup = true', function() {
            view.isEmptyGroup = false;
            view.collection.reset();
            view.render();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
        });

        it('should call toggleEmptyRow with false when isEmptyGroup = false', function() {
            view.isEmptyGroup = true;
            view.collection.add(new Backbone.Model({
                id: 'test1'
            }));
            view.render();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
        });
    });

    describe('_renderHtml', function() {
        var $el;
        var $trs;

        describe('with group header', function() {
            var afterStub;
            var removeStub;

            beforeEach(function() {
                sinon.stub(view, 'template').callsFake(function() {});
                afterStub = sinon.stub();
                removeStub = sinon.stub();
                $el = {
                    length: 1,
                    after: afterStub
                };
            });

            afterEach(function() {
                afterStub = null;
                removeStub = null;
            });

            it('should remove existing quote-data-group-list rows if there is a header and rows', function() {
                $trs = {
                    length: 1,
                    remove: removeStub
                };
                sinon.stub(view, '$').callsFake(function(selector) {
                    if (selector === 'tr.quote-data-group-header') {
                        return $el;
                    } else if (selector === 'tr.quote-data-group-list') {
                        return $trs;
                    }
                });
                view._renderHtml();

                expect(removeStub).toHaveBeenCalled();
            });

            it('should not call remove if no quote-data-group-list rows', function() {
                $trs = {
                    length: 0,
                    remove: removeStub
                };
                sinon.stub(view, '$').callsFake(function(selector) {
                    if (selector === 'tr.quote-data-group-header') {
                        return $el;
                    } else if (selector === 'tr.quote-data-group-list') {
                        return $trs;
                    }
                });
                view._renderHtml();

                expect(removeStub).not.toHaveBeenCalled();
            });

            it('should call after() after the header rows', function() {
                $trs = {
                    length: 0
                };
                sinon.stub(view, '$').callsFake(function(selector) {
                    if (selector === 'tr.quote-data-group-header') {
                        return $el;
                    } else if (selector === 'tr.quote-data-group-list') {
                        return $trs;
                    }
                });
                view._renderHtml();

                expect(afterStub).toHaveBeenCalled();
            });
        });

        describe('with no group header', function() {
            beforeEach(function() {
                sinon.stub(view.$el, 'html').callsFake(function() {});
                sinon.stub(view, '$').callsFake(function() {
                    return {
                        length: 0
                    };
                });
            });

            it('should call view.$el.html to render the html', function() {
                view._renderHtml();

                expect(view.$el.html).toHaveBeenCalled();
            });
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view, '_setRowFields').callsFake(function() {});
            sinon.stub(view, 'toggleRow').callsFake(function() {});
            sinon.stub(view, 'onAddNewItemToGroup').callsFake(function() {});
            sinon.stub(view.context.parent, 'trigger').callsFake(function() {});
        });

        it('should call _setRowFields', function() {
            view._render();
            expect(view._setRowFields).toHaveBeenCalled();
        });

        describe('adding Opps convert RLIs to the Quote', function() {
            var relatedModels;
            var relatedModel;
            beforeEach(function() {
                relatedModel = new Backbone.Model({
                    id: 'relatedModelId1',
                    name: 'relatedModelName1'
                });
                relatedModels = [relatedModel];
                view.context.parent.set('relatedRecords', relatedModels);

                view.isCreateView = false;
                view.isOppsConvert = false;
                view.addedConvertModels = false;
            });

            afterEach(function() {
                relatedModel = null;
                relatedModels = null;
            });

            it('should not call onAddNewItemToGroup if isCreateView is false', function() {
                view.isOppsConvert = true;
                view._render();

                expect(view.onAddNewItemToGroup).not.toHaveBeenCalled();
            });

            it('should not call onAddNewItemToGroup if isOppsConvert is false', function() {
                view.isCreateView = true;
                view._render();

                expect(view.onAddNewItemToGroup).not.toHaveBeenCalled();
            });

            it('should not call onAddNewItemToGroup if addedConvertModels is true', function() {
                view.isCreateView = true;
                view.isOppsConvert = true;
                view.addedConvertModels = true;
                view._render();

                expect(view.onAddNewItemToGroup).not.toHaveBeenCalled();
            });

            it('should call onAddNewItemToGroup with related models', function() {
                view.isCreateView = true;
                view.isOppsConvert = true;
                view._render();

                expect(view.onAddNewItemToGroup).toHaveBeenCalledWith('products', relatedModel.toJSON());
            });

            it('should call onAddNewItemToGroup and set addedConvertModels to true', function() {
                view.isCreateView = true;
                view.isOppsConvert = true;
                view._render();

                expect(view.addedConvertModels).toBeTruthy();
                expect(view.context.parent.get('addedConvertModels')).toBeTruthy();
            });
        });

        it('should call toggleRow if toggledModels has data', function() {
            view.toggledModels = {
                id1: new Backbone.Model({module: 'Products'})
            };
            view._render();
            expect(view.toggleRow).toHaveBeenCalled();
        });

        it('should not call toggleRow if toggledModels is empty', function() {
            view.toggledModels = {};
            view._render();
            expect(view.toggleRow).not.toHaveBeenCalled();
        });
    });

    describe('_onEditRowBtnClicked()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.stub($.fn, 'closest').callsFake(function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });

            sinon.stub(view, 'toggleRow').callsFake(function() {});
        });

        it('should call toggleRow', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow).toHaveBeenCalled();
        });

        it('should call toggleRow with first param row module', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[0]).toBe('rowModule');
        });

        it('should call toggleRow with second param row model id', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[1]).toBe('rowModelId');
        });

        it('should call toggleRow with third param true to toggle row', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[2]).toBeTruthy();
        });
    });

    describe('_onDeleteRowBtnClicked()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.stub($.fn, 'closest').callsFake(function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });

            sinon.stub(app.alert, 'show');
        });

        it('should call alert show', function() {
            view._onDeleteRowBtnClicked(evt);
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('_onDeleteRowModelFromList()', function() {
        var deleteModel;

        beforeEach(function() {
            deleteModel = app.data.createBean('Products', {
                id: 'modelId1'
            });
            sinon.stub(deleteModel, 'destroy').callsFake(function() {});
            sinon.stub(view.layout, 'trigger').callsFake(function() {});

            view._onDeleteRowModelFromList(deleteModel, false);
        });

        afterEach(function() {
            deleteModel.dispose();
            deleteModel = null;
        });

        it('should call destroy() on the delete model', function() {
            expect(deleteModel.destroy).toHaveBeenCalled();
        });

        it('should call layout.trigger with quotes:line_nums:reset', function() {
            expect(view.layout.trigger).toHaveBeenCalledWith('quotes:line_nums:reset');
        });
    });

    describe('isolateRowParams()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.stub($.fn, 'closest').callsFake(function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });
        });

        it('should return the correct params', function() {
            var result = view.isolateRowParams(evt);

            expect(result.module).toEqual('rowModule');
            expect(result.id).toEqual('rowModelId');
        });
    });

    describe('toggleRow()', function() {
        var toggleClassStub;
        var rowModel;
        var rowModule;
        var rowModelId;
        var sortableStub;
        var addClassStub;
        var removeClassStub;
        var jqueryStub;
        var attrStub;

        beforeEach(function() {
            view.toggledModels = {};
            rowModule = 'Products';
            rowModelId = 'testId1';
            rowModel = new Backbone.Model({
                id: rowModelId,
                module: rowModule
            });
            view.collection.add(rowModel);

            view.rowFields[rowModelId] = rowModel;

            toggleClassStub = sinon.stub();
            sortableStub = sinon.stub();
            addClassStub = sinon.stub();
            removeClassStub = sinon.stub();
            attrStub = sinon.stub();
            jqueryStub = {
                toggleClass: toggleClassStub,
                hasClass: function() {
                    return true;
                },
                parent: function() {},
                addClass: addClassStub,
                removeClass: removeClassStub,
                attr: attrStub
            };

            sinon.stub(view, '$').callsFake(function() {
                return jqueryStub;
            });

            sinon.stub(jqueryStub, 'parent').callsFake(function() {
                return {
                    sortable: sortableStub
                };
            });

            sinon.stub(view, 'toggleFields').callsFake(function() {});
            sinon.stub(view.context.parent, 'trigger').callsFake(function() {});
            sinon.stub(view.context, 'trigger').callsFake(function() {});

            view.toggleRow(rowModule, rowModelId, true);
        });

        afterEach(function() {
            toggleClassStub = null;
            sortableStub = null;
            addClassStub = null;
            removeClassStub = null;
            jqueryStub = null;
            rowModel = null;
            rowModule = null;
            rowModelId = null;
        });

        describe('when isEdit is true', function() {
            it('should add the toggled model to toggledModels', function() {
                expect(view.toggledModels[rowModelId]).toEqual(rowModel);
            });

            it('should set modelView to edit on the toggledModel', function() {
                expect(view.toggledModels[rowModelId].modelView).toEqual('edit');
            });

            it('should trigger quotes:item:toggle with true and the rowid', function() {
                expect(view.context.parent.trigger).toHaveBeenCalledWith('quotes:item:toggle', true, rowModelId);
            });

            it('should have called addClass with not-sortable', function() {
                expect(addClassStub).toHaveBeenCalledWith('not-sortable');
            });

            it('should call sortable with a cancel structure', function() {
                expect(sortableStub).toHaveBeenCalledWith({
                    cancel: '.not-sortable, .dropdown-toggle, .dropdown-menu'
                });
            });

            it('should have called removeClass with not-sortable', function() {
                expect(removeClassStub).toHaveBeenCalledWith('ui-sortable');
            });

            it('should have called view.context.trigger with list:editrow:fire, toggleModel', function() {
                expect(view.context.trigger).toHaveBeenCalledWith('list:editrow:fire', rowModel);
            });
        });

        describe('when isEdit is false', function() {
            it('should delete the toggled model from toggledModels', function() {
                // set the model first then remove it
                expect(view.toggledModels[rowModelId]).toEqual(rowModel);

                // then remove it
                view.toggleRow(rowModule, rowModelId, false);
                expect(view.toggledModels[rowModelId]).toBeUndefined();

            });

            it('should set modelView on the toggled model when removing', function() {
                expect(rowModel.modelView).toBe('edit');

                view.toggleRow(rowModule, rowModelId, false);
                expect(rowModel.modelView).toBe('list');
            });

            it('should trigger quotes:item:toggle with false and the rowid', function() {
                view.toggleRow(rowModule, rowModelId, false);
                expect(view.context.parent.trigger).toHaveBeenCalledWith('quotes:item:toggle', false, rowModelId);
            });

            it('should have called attr on the row to add the id', function() {
                view.toggleRow(rowModule, rowModelId, false);
                expect(attrStub).toHaveBeenCalledWith('record-id', rowModelId);
            });

            describe('with jquery not stubbed', function() {
                var testRow;
                beforeEach(function() {
                    view.$.restore();
                    testRow = $('<tr name="Products_productId1" class="not-sortable"></tr>');
                    sinon.stub(view, '$').callsFake(function() {
                        return testRow;
                    });
                });

                it('should call removeClass not-sortable if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('not-sortable')).toBeFalsy();
                });

                it('should add class "sortable" if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('sortable')).toBeTruthy();
                });

                it('should add class "ui-sortable" if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('ui-sortable')).toBeTruthy();
                });
            });
        });

        it('should call this.$ with module and id', function() {
            expect(view.$).toHaveBeenCalledWith('tr[name=' + rowModule + '_' + rowModelId + ']');
        });

        it('should call toggleClass with first param being correct class', function() {
            expect(toggleClassStub.lastCall.args[0]).toBe('tr-inline-edit');
        });

        it('should call toggleClass with second param being isEdit = true', function() {
            expect(toggleClassStub.lastCall.args[1]).toBeTruthy();
        });

        it('should call toggleClass with second param being isEdit = false', function() {
            view.toggleRow(rowModule, rowModelId, false);
            expect(toggleClassStub.lastCall.args[1]).toBeFalsy();
        });

        it('should call toggleFields with first param being correct class', function() {
            expect(view.toggleFields.lastCall.args[0]).toEqual(rowModel);
        });

        it('should call toggleFields with second param being isEdit = true', function() {
            expect(view.toggleFields.lastCall.args[1]).toBeTruthy();
        });

        it('should call toggleFields with second param being isEdit = false', function() {
            view.toggleRow(rowModule, rowModelId, false);
            expect(view.toggleFields.lastCall.args[1]).toBeFalsy();
        });
    });

    describe('_setRowFields()', function() {
        var field1;
        var field2;
        var field3;
        var field4;

        beforeEach(function() {
            field1 = {
                model: new Backbone.Model()
            };

            field2 = {
                model: new Backbone.Model()
            };

            field4 = {
                model: new Backbone.Model(),
                parent: true
            };

            view.fields = [
                field1,
                field2,
                field4
            ];

            view._setRowFields();
        });

        afterEach(function() {
            view.rowFields = null;
            view.fields = null;
        });

        it('should set rowFields from fields', function() {
            expect(view.rowFields[field1.model.cid][0]).toEqual(field1);
            expect(view.rowFields[field2.model.cid][0]).toEqual(field2);
        });

        it('should set not set rowFields for fields with a parent', function() {
            expect(view.rowFields[field4.model.cid]).toBeUndefined();
        });
    });

    describe('getFieldNames()', function() {
        var prodMeta;
        var prodFieldsMeta;
        var pbnMeta;
        var pbnFieldsMeta;

        beforeEach(function() {
            prodMeta = {
                panels: [{
                    fields: [{
                        name: 'field1'
                    }, {
                        name: 'field2'
                    }]
                }]
            };

            prodFieldsMeta = {
                field1: {
                    name: 'field1'
                },
                field2: {
                    name: 'field2'
                }
            };

            pbnMeta = {
                panels: [{
                    fields: [{
                        name: 'field3'
                    }, {
                        name: 'field4'
                    }]
                }]
            };

            pbnFieldsMeta = {
                field3: {
                    name: 'field3'
                },
                field4: {
                    name: 'field4'
                }
            };

            sinon.stub(app.metadata, 'getModule')
                .withArgs('Products').returns(prodFieldsMeta)
                .withArgs('ProductBundleNotes').returns(pbnFieldsMeta);

            view.pbnListMetadata = pbnMeta;
            view.qliListMetadata = prodMeta;
        });

        afterEach(function() {
            prodMeta = null;
            prodFieldsMeta = null;
            pbnMeta = null;
            pbnFieldsMeta = null;
        });

        it('should return the Products metadata fieldnames', function() {
            expect(view.getFieldNames('Products')).toEqual(['field1', 'field2']);
        });

        it('should return the ProductBundleNotes metadata fieldnames', function() {
            expect(view.getFieldNames('ProductBundleNotes')).toEqual(['field3', 'field4']);
        });
    });

    describe('AllChecked Functions', function() {
        var $item = {
            prop: function() {
                return undefined;
            },
            trigger: sinon.stub()
        };
        var oldJquery;

        beforeEach(function() {
            oldJquery = $;
            $ = function(item) {
                return $item;
            };
            sinon.stub(view, '$').callsFake(function() {
                return [1];
            });
        });

        afterEach(function() {
            $ = oldJquery;
        });

        describe('onAllChecked', function() {
            it('should trigger a click', function() {
                view.onAllChecked();
                expect($item.trigger).toHaveBeenCalledWith('click');
            });
        });

        describe('onNotAllChecked', function() {
            it('should trigger a click', function() {
                $item.attr = function() {
                    return 1;
                };
                view.onNotAllChecked();
                expect($item.trigger).toHaveBeenCalledWith('click');
            });
        });

    });
});
