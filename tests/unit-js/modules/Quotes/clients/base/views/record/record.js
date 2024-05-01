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
describe('Quotes.Base.Views.Record', function() {
    var app;
    var view;
    var viewMeta;
    var model;
    var context;
    var quoteFields;
    var bundleFields;
    var productFields;

    beforeEach(function() {
        app = SugarTest.app;

        quoteFields = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        bundleFields = SugarTest.loadFixture('product-bundle-fields', '../tests/modules/ProductBundles/fixtures');
        productFields = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');

        SugarTest.loadFile('../modules/Quotes/clients/base/plugins', 'QuotesViewSaveHelper', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.testMetadata.init();
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.testMetadata.updateModuleMetadata('ProductBundles', {
            fields: bundleFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Products', {
            fields: productFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Quotes', {
            fields: quoteFields
        });

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        viewMeta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'name'
                        }, {
                            name: 'total',
                            calculated: true
                        }, {
                            name: 'bundles',
                            fields: [
                                'id',
                                'name',
                                {
                                    name: 'product_bundle_items',
                                    fields: [
                                        'name',
                                        'id'
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        };

        sinon.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Quotes', 'product_bundles').returns('ProductBundles');
        app.data.getRelatedModule.withArgs('ProductBundles', 'products').returns('Products');
        app.data.getRelatedModule.withArgs('ProductBundles', 'product_bundle_notes').returns('ProductBundleNotes');

        context = app.context.getContext();
        model = app.data.createBean('Quotes');
        context.set('model', model);
        view = SugarTest.createView('base', 'Quotes', 'record', viewMeta, context, true);
        view.hasUnsavedQuoteChanges = $.noop;
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('initialize', function() {
        it('should find all the calculated fields', function() {
            expect(view.calculatedFields).toContain('total');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view.context, 'on').callsFake(function() {});
        });

        it('should set listener on context for editable:handleEdit', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('editable:handleEdit');
        });

        it('should set listener on context for quotes:item:toggle', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('quotes:item:toggle');
        });

        it('should set listener on context for quotes:editableFields:add', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('editable:handleEdit');
        });

        it('should add a field to editableFields when the event is called', function() {
            view.context.on.restore();
            view.editableFields = [];
            view.bindDataChange();
            view.context.trigger('quotes:editableFields:add', {
                id: 'myEditableField1'
            });

            expect(view.editableFields[0].id).toBe('myEditableField1');
        });

        it('should add a field to additionalEditableFields when the event is called', function() {
            view.context.on.restore();
            view.editableFields = [];
            view.bindDataChange();
            view.context.trigger('quotes:editableFields:add', {
                id: 'myEditableField1'
            });

            expect(view.additionalEditableFields[0].id).toBe('myEditableField1');
        });
    });

    describe('setEditableFields', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
        });

        it('should add no additional editable fields', function() {
            view.editableFields = [];
            view.additionalEditableFields = [];
            view.setEditableFields();
            expect(view.editableFields.length).toBe(0);
        });

        it('should add additional editable fields', function() {
            view.editableFields = [];
            view.additionalEditableFields = [1];
            view.setEditableFields();
            expect(view.editableFields.length).toBe(1);
        });
    });

    describe('duplicateClicked()', function() {
        var bundles;
        var pbModel;
        var pbItem1;
        var pbItem2;
        var contextCollection;
        var removeClassStub;

        beforeEach(function() {
            pbItem1 = app.data.createBean('Products', {
                id: 'testId1',
                name: 'qliName1',
                quote_id: 'testQuoteId1',
                _module: 'Products'
            });
            pbItem2 = app.data.createBean('Products', {
                id: 'testId2',
                name: 'qliName2',
                product_template_name: 'prodTemplateName2',
                quote_id: 'testQuoteId2',
                _module: 'Products'
            });

            pbModel = app.data.createBean('ProductBundles', {
                id: 'bundleId1',
                product_bundle_items: [],
                '_products-rel_exp_values': 'test'
            });
            pbModel.get('product_bundle_items').add([pbItem1, pbItem2]);

            bundles = app.data.createMixedBeanCollection();
            bundles.add(pbModel);
            view.model.set({
                bundles: bundles,
                name: 'Test Quote'
            });

            contextCollection = new Backbone.Collection();
            view.context.set('collection', contextCollection);
            sinon.stub(app.controller, 'loadView').callsFake(function() {});
            app.router = {
                navigate: function() {}
            };
            sinon.stub(app.router, 'navigate').callsFake(function() {});

            removeClassStub = sinon.stub();
            sinon.stub(view, 'getField').callsFake(function() {
                return {
                    $el: {
                        removeClass: removeClassStub
                    }
                };
            });
        });

        afterEach(function() {
            delete app.router;
        });

        describe('when there are items in edit mode', function() {
            beforeEach(function() {
                view.editCount = 1;
                sinon.stub(app.alert, 'show').callsFake(function() {});

                view.duplicateClicked();
            });

            it('should call app.alert.show', function() {
                expect(app.alert.show).toHaveBeenCalled();
            });
        });

        describe('when no items are in edit mode', function() {
            var callArgs;

            beforeEach(function() {
                view.editCount = 0;
                sinon.stub(app.alert, 'show').callsFake(function() {});

                view.duplicateClicked();
                callArgs = app.controller.loadView.lastCall.args[0];
            });

            afterEach(function() {
                callArgs = null;
            });

            it('should call app.controller.loadView with correct action', function() {
                expect(callArgs.action).toBe('edit');
            });

            it('should call app.controller.loadView with correct collection', function() {
                expect(callArgs.collection).toBe(contextCollection);
            });

            it('should call app.controller.loadView with correct copy param', function() {
                expect(callArgs.copy).toBeTruthy();
            });

            it('should call app.controller.loadView with correct create param', function() {
                expect(callArgs.create).toBeTruthy();
            });

            it('should call app.controller.loadView with correct layout name', function() {
                expect(callArgs.layout).toBe('create');
            });

            it('should call app.controller.loadView with correct model', function() {
                expect(callArgs.model.get('name')).toBe('Test Quote');
            });

            it('should call app.controller.loadView with correct module', function() {
                expect(callArgs.module).toBe('Quotes');
            });

            it('should close the main dropdown when copy is clicked', function() {
                expect(removeClassStub).toHaveBeenCalledWith('open');
            });

            describe('when copying bundle', function() {
                var bundle;

                beforeEach(function() {
                    bundle = callArgs.relatedRecords[0];
                });

                afterEach(function() {
                    bundle = null;
                });

                it('should remove id', function() {
                    expect(bundle.id).toBeUndefined();
                });

                it('should remove _products-rel_exp_values', function() {
                    expect(bundle['_products-rel_exp_values']).toBeUndefined();
                });
            });

            describe('when copying QLIs', function() {
                var qliModel1;
                var qliModel2;

                beforeEach(function() {
                    qliModel1 = callArgs.relatedRecords[0].product_bundle_items[0];
                    qliModel2 = callArgs.relatedRecords[0].product_bundle_items[1];
                });

                afterEach(function() {
                    qliModel1 = null;
                });

                it('should remove id', function() {
                    expect(qliModel1.id).toBeUndefined();
                });

                it('should remove quote_id', function() {
                    expect(qliModel1.quote_id).toBeUndefined();
                });

                it('should set product_template_name to the QLI name if product_template_name is empty', function() {
                    expect(qliModel1.product_template_name).toBe(qliModel1.name);
                });

                it('should set name to the QLI product_template_name if name is empty', function() {
                    expect(qliModel2.name).toBe(qliModel2.product_template_name);
                });
            });

            it('should call app.router.navigate', function() {
                expect(app.router.navigate).toHaveBeenCalledWith('#Quotes/create', {trigger: false});
            });
        });
    });

    describe('_handleItemToggled', function() {
        it('should have an edit count equal to 0', function() {
            view.editCount = 0;
            view.editIds[1] = true;
            view._handleItemToggled(false, 1);
            expect(view.editCount).toBe(0);
        });

        it('should have an edit count equal to 0', function() {
            view.editCount = 1;
            view.editIds[1] = true;
            view._handleItemToggled(false, 1);
            expect(view.editCount).toBe(0);
        });

        it('should have an edit count equal to 1', function() {
            view.editCount = 0;
            view._handleItemToggled(true, 1);
            expect(view.editCount).toBe(1);
        });

        it('should have an edit count equal to 1', function() {
            view.editCount = 1;
            view.editIds[1] = true;
            view._handleItemToggled(true, 1);
            expect(view.editCount).toBe(1);
        });
    });

    describe('saveClicked', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(app.alert, 'show').callsFake(function() {});
        });

        it('should call _super', function() {
            view.editCount = 0;
            view.saveClicked();
            expect(view._super).toHaveBeenCalled();
        });

        it('should call app.alert.show', function() {
            view.editCount = 1;
            view.saveClicked();
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('cancelClicked', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {});
            sinon.stub(view.context, 'trigger').callsFake(function() {});
        });

        it('should call _super', function() {
            view.cancelClicked();
            expect(view._super).toHaveBeenCalled();
        });

        it('should call context.trigger with list:editrow:fire', function() {
            view.cancelClicked();
            expect(view.context.trigger).toHaveBeenCalledWith('list:editrow:fire');
        });
    });

    describe('_handleEditShippingField()', function() {
        var shipField;
        var event;
        var $targetEl;
        var $targetElParent;

        beforeEach(function() {
            $targetEl = $('<a class="btn"></a>');
            $targetElParent = $('<div class="record-cell" data-name="shipFieldName1"></div>');
            $targetElParent.append($targetEl);
            event = {
                target: $targetEl
            };
            shipField = {
                id: 'shipFieldId1',
                name: 'shipFieldName1'
            };
            view.editableFields = [shipField];
            sinon.stub(view, 'setButtonStates').callsFake(function() {});
            sinon.stub(view, 'toggleField').callsFake(function() {});
            view.inlineEditMode = false;

            view._handleEditShippingField(event);
        });

        afterEach(function() {

        });

        it('should set inlineEditMode true', function() {
            expect(view.inlineEditMode).toBeTruthy();
        });

        it('should call setButtonStates', function() {
            expect(view.setButtonStates).toHaveBeenCalledWith('edit');
        });

        it('should call toggleField', function() {
            expect(view.toggleField).toHaveBeenCalledWith(shipField);
        });

        it('should call setButtonStates', function() {
            expect(view.setButtonStates).toHaveBeenCalledWith('edit');
        });
    });
    describe('hasUnsavedChanges()', function() {
        beforeEach(function() {
            sinon.stub(view, 'hasUnsavedQuoteChanges').callsFake(function() {});
        });

        it('should call hasUnsavedQuoteChanges', function() {
            view.hasUnsavedChanges();

            expect(view.hasUnsavedQuoteChanges).toHaveBeenCalled();
        });
    });

    describe('getCustomSaveOptions', function() {
        var attributes;
        var actual;
        beforeEach(function() {
            attributes = {
                currency_id: 'test_1',
                base_rate: '0.90'
            };
            model.setSyncedAttributes(attributes);
            model.set(attributes);
        });

        it('should return an empty object when currency has not changed', function() {
            actual = view.getCustomSaveOptions();
            expect(actual).toEqual({});
        });

        it('should return an object with success when the currency has changed', function() {
            model.set('currency_id', 'test_2');
            actual = view.getCustomSaveOptions({});
            expect(actual).not.toEqual({});
            expect(actual.success).toBeDefined();
        });
    });

    describe('_createBulkBundlesPayload', function() {
        var tmpRow;
        var expected;
        var actual;
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function(module, action, attributes) {
                return 'rest/v10/' + module + '/' + attributes.id;
            });
            expected = [{
                url: '/v10/ProductBundles/1233',
                method: 'PUT',
                data: {
                    currency_id: 'test_1',
                    base_rate: '0.90'
                }
            }];
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                currency_id: 'test_1',
                base_rate: '0.90',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
                    currency_id: 'test_1',
                    base_rate: '0.90',
                    _module: 'ProductBundles',
                    _link: 'product_bundles',
                    product_bundle_items: [{
                        id: 12345,
                        name: 'item_1',
                        _module: 'Products',
                        _link: 'products'
                    }]
                }]
            };
            model.setSyncedAttributes(tmpRow);
            model.set(tmpRow);
        });

        it('should return the one bundle', function() {
            actual = view._createBulkBundlesPayload();

            expect(actual).toEqual(expected);
        });

        it('should not return new bundles', function() {
            model.get('bundles').add({
                name: 'bundle_new',
                currency_id: 'test_1',
                base_rate: '0.90',
                _module: 'ProductBundles'
            });
            actual = view._createBulkBundlesPayload();

            expect(actual).toEqual(expected);
        });
    });

    describe('_sendBulkBundlesUpdate', function() {
        var payload;
        var bindReturn;
        beforeEach(function() {
            sinon.stub(app.api, 'buildURL').callsFake(function() {
                return 'rest/v10/bulk';
            });
            sinon.stub(app.api, 'call').callsFake(function() {});
        });

        it('should not call the api when bulkSaveRequests is empty', function() {
            view._sendBulkBundlesUpdate([]);
            expect(app.api.buildURL).not.toHaveBeenCalled();
            expect(app.api.call).not.toHaveBeenCalled();
        });

        it('should send the api call', function() {
            payload = [{
                url: '/v10/ProductBundles/1233',
                method: 'PUT',
                data: {
                    currency_id: 'test_1',
                    base_rate: '0.90'
                }
            }];
            bindReturn = $.noop;

            sinon.stub(_, 'bind').callsFake(function() {
                return bindReturn;
            });

            view._sendBulkBundlesUpdate(payload);

            expect(app.api.buildURL).toHaveBeenCalled();
            expect(_.bind).toHaveBeenCalledWith(view._onBulkBundlesUpdateSuccess, view);
            expect(app.api.call).toHaveBeenCalledWith(
                'create',
                'rest/v10/bulk',
                {
                    requests: payload
                },
                {
                    success: bindReturn
                }
            );
        });
    });

    describe('_onBulkBundlesUpdateSuccess', function() {
        var tmpRow;
        var response;
        var bundle;
        beforeEach(function() {
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                currency_id: 'test_1',
                base_rate: '0.90',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
                    currency_id: 'test_1',
                    base_rate: '0.90',
                    _module: 'ProductBundles',
                    _link: 'product_bundles',
                    product_bundle_items: [{
                        id: 12345,
                        name: 'item_1',
                        _module: 'Products',
                        _link: 'products'
                    }]
                }]
            };
            model.setSyncedAttributes(tmpRow);
            model.set(tmpRow);
            bundle = model.get('bundles').at(0);

            sinon.spy(bundle, 'set');
            sinon.spy(bundle, 'setSyncedAttributes');

            response = [{
                contents: {
                    id: '1233'
                }
            }];
        });

        it('should call set and setSyncedAttributes on the bundle', function() {
            view._onBulkBundlesUpdateSuccess(response);

            expect(bundle.set).toHaveBeenCalledWith(response[0].contents);
            expect(bundle.setSyncedAttributes).toHaveBeenCalledWith(response[0].contents);
        });
    });
});
