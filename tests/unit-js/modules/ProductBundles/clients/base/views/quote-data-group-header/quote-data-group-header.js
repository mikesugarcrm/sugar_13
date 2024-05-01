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
describe('ProductBundles.Base.Views.QuoteDataGroupHeader', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var viewContext;
    var viewParentContext;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };

        viewParentContext = app.context.getContext();
        viewParentContext.set({
            module: 'Quotes',
            create: false
        });
        viewParentContext.prepare();

        viewContext = app.context.getContext();
        viewContext.set({
            module: 'ProductBundles'
        });
        viewContext.parent = viewParentContext;
        viewContext.prepare();

        layout = SugarTest.createLayout('base', 'ProductBundles', 'default', layoutDefs);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-header',
            viewMeta, viewContext, true, layout);
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
        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should have the correct saveIconCssClass', function() {
            expect(view.saveIconCssClass).toBe('.group-loading-icon');
        });

        it('should set listColSpan to be the layout listColSpan', function() {
            expect(view.listColSpan).toBe(layout.listColSpan);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        describe('when calling initialize', function() {
            var initOptions;
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
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    name: 'quote-data-group-header',
                    model: new Backbone.Model(),
                    layout: {
                        listColSpan: 2,
                        layout: layoutLayout
                    }
                };

                sinon.stub(view, 'addMultiSelectionAction').callsFake(function() {});
                sinon.stub(view.layout, 'on').callsFake(function() {});
            });

            afterEach(function() {
                initOptions = null;
                layoutLayoutMassCollection = null;
                layoutLayout = null;
            });

            it('should set mass_collection to the layout.layout mass collection', function() {
                view.initialize(initOptions);

                expect(view.context.get('mass_collection')).toEqual(layoutLayoutMassCollection);
            });

            it('should set isCreateView to true for create view', function() {
                viewParentContext.set('create', true);
                view.initialize(initOptions);

                expect(view.isFirstRender).toBeTruthy();
            });

            it('should set isCreateView to false when not create view', function() {
                viewParentContext.set('create', false);
                view.initialize(initOptions);

                expect(view.isFirstRender).toBeTruthy();
            });

            it('should set isFirstRender to true', function() {
                view.initialize(initOptions);

                expect(view.isFirstRender).toBeTruthy();
            });

            it('should set viewName and action to list', function() {
                view.initialize(initOptions);

                expect(view.viewName).toBe('list');
                expect(view.action).toBe('list');
            });

            it('should reset vars to empty', function() {
                view.initialize(initOptions);

                expect(view.toggledModels).toEqual({});
                expect(view.leftColumns).toEqual([]);
                expect(view.leftSaveCancelColumn).toEqual([]);
            });

            it('should call addMultiSelectionAction()', function() {
                view.initialize(initOptions);

                expect(view.addMultiSelectionAction).toHaveBeenCalled();
            });

            it('should call setElement', function() {
                view.initialize(initOptions);

                expect(view.setElement).toHaveBeenCalled();
            });

            it('should set groupSaveCt = 0', function() {
                view.initialize(initOptions);

                expect(view.groupSaveCt).toBe(0);
            });

            it('should call layout.on with quotes:group:save:start', function() {
                view.initialize(initOptions);

                expect(view.layout.on.args[0][0]).toBe('quotes:group:save:start');
            });

            it('should call layout.on with quotes:group:save:stop', function() {
                view.initialize(initOptions);

                expect(view.layout.on.args[1][0]).toBe('quotes:group:save:stop');
            });

            it('should call layout.on with editablelist:<viewName>:save', function() {
                view.initialize(initOptions);

                expect(view.layout.on.args[2][0]).toBe('editablelist:quote-data-group-header:save');
            });

            it('should call layout.on with editablelist:<viewName>:saving', function() {
                view.initialize(initOptions);

                expect(view.layout.on.args[3][0]).toBe('editablelist:quote-data-group-header:saving');
            });

            it('should call layout.on with editablelist:<viewName>:create:cancel', function() {
                view.initialize(initOptions);

                expect(view.layout.on.args[4][0]).toBe('editablelist:quote-data-group-header:create:cancel');
            });
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(view, '_setRowFields').callsFake(function() {});
            sinon.stub(view, 'toggleRow').callsFake(function() {});
        });

        it('should call _setRowFields', function() {
            view._render();

            expect(view._setRowFields).toHaveBeenCalled();
        });

        it('should call toggleRow if there are items in toggledModels', function() {
            view.toggledModels = [{
                module: 'testModule'
            }];
            view._render();

            expect(view.toggleRow).toHaveBeenCalledWith('testModule', 0, true);
        });

        it('should call toggleRow if this group header was just saved', function() {
            view.isFirstRender = true;
            view.model.set('_justSaved', true);
            view.model.module = 'Quotes';
            view.model.cid = 'quotesCid';

            view._render();

            expect(view.toggleRow).toHaveBeenCalledWith('Quotes', 'quotesCid', true);
            expect(view.isFirstRender).toBeFalsy();
            expect(view.model.has('_justSaved')).toBeFalsy();
        });
    });

    describe('_onGroupSaveStart()', function() {
        var showStub;
        beforeEach(function() {
            showStub = sinon.stub();

            sinon.stub(view, '$').callsFake(function() {
                return {
                    show: showStub
                };
            });

            view._onGroupSaveStart();
        });

        it('should increment the groupSaveCt counter', function() {
            expect(view.groupSaveCt).toBe(1);
        });

        it('should call this.$(this.saveIconCssClass)', function() {
            expect(view.$.args[0][0]).toBe(view.saveIconCssClass);
        });

        it('should call show on the saveIconCssClass element', function() {
            expect(showStub).toHaveBeenCalled();
        });
    });

    describe('_onGroupSaveStop()', function() {
        var hideStub;
        beforeEach(function() {
            hideStub = sinon.stub();

            sinon.stub(view, '$').callsFake(function() {
                return {
                    hide: hideStub
                };
            });
        });

        it('should decrement the groupSaveCt counter', function() {
            view.groupSaveCt = 3;
            view._onGroupSaveStop();

            expect(view.groupSaveCt).toBe(2);
        });

        describe('when groupSaveCt = 0', function() {
            beforeEach(function() {
                view.groupSaveCt = 1;
                view._onGroupSaveStop();
            });

            it('should call this.$(this.saveIconCssClass)', function() {
                expect(view.$.args[0][0]).toBe(view.saveIconCssClass);
            });

            it('should call show on the saveIconCssClass element', function() {
                expect(hideStub).toHaveBeenCalled();
            });
        });

        describe('when groupSaveCt goes below 0 in some freak async accident', function() {
            beforeEach(function() {
                view.groupSaveCt = -10;
                view._onGroupSaveStop();
            });

            it('should reset groupSaveCt to 0', function() {
                expect(view.groupSaveCt).toBe(0);
            });
        });
    });

    describe('_onCreateQLIBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.spy(view.layout, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateQLIBtnClicked();

            expect(view.layout.trigger).toHaveBeenCalledWith('quotes:group:create:qli');
        });
    });

    describe('_onCreateCommentBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.spy(view.layout, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateCommentBtnClicked();

            expect(view.layout.trigger).toHaveBeenCalledWith('quotes:group:create:note');
        });
    });

    describe('_onDeleteBundleBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            view.context.parent = SugarTest.app.context.getContext();
            sinon.spy(view.context.parent, 'trigger');
            view._onDeleteBundleBtnClicked();

            expect(view.context.parent.trigger).toHaveBeenCalledWith('quotes:group:delete');
        });
    });

    describe('toggleRow()', function() {
        var addClassSpy;
        var removeClassStub;
        var toggleClassStub;
        var rowFields;
        var modelId;

        beforeEach(function() {
            view.model.modelView = 'testModelView';

            modelId = 'testModel1';
            view.model.set('id', modelId);
            view.model.id = modelId;

            rowFields = ['fieldA', 'fieldB'];
            view.rowFields[modelId] = rowFields;

            removeClassStub = sinon.stub();
            toggleClassStub = sinon.stub();

            addClassSpy = sinon.spy(function() {
                return {
                    removeClass: removeClassStub
                };
            });

            sinon.stub(view, '$').callsFake(function() {
                return {
                    addClass: addClassSpy,
                    toggleClass: toggleClassStub
                };
            });
            sinon.stub(view, 'toggleFields').callsFake(function() {});
            sinon.stub(view.context, 'trigger').callsFake(function() {});

        });

        afterEach(function() {
            modelId = null;
            rowFields = null;
            addClassSpy = null;
            removeClassStub = null;
            toggleClassStub = null;
        });

        describe('isEdit = true', function() {
            beforeEach(function() {
                view.toggleRow('Quotes', modelId, true);
            });

            it('should set model.modelView to be edit', function() {
                expect(view.model.modelView).toBe('edit');
            });

            it('should add model to toggledModels', function() {
                expect(view.toggledModels[modelId]).toBe(view.model);
            });

            it('should call toggleClass with tr-inline-edit, true', function() {
                expect(toggleClassStub).toHaveBeenCalledWith('tr-inline-edit', true);
            });

            it('should call toggleFields with models rowFields and true', function() {
                expect(view.toggleFields).toHaveBeenCalledWith(rowFields, true);
            });

            it('should call row.addClass with not-sortable', function() {
                expect(addClassSpy).toHaveBeenCalledWith('not-sortable');
            });

            it('should call row.removeClass with sortable ui-sortable', function() {
                expect(removeClassStub).toHaveBeenCalledWith('sortable ui-sortable');
            });

            it('should call context.trigger with list:editgroup:fire', function() {
                expect(view.context.trigger).toHaveBeenCalledWith('list:editgroup:fire');
            });
        });

        describe('isEdit = false', function() {
            beforeEach(function() {
                view.toggledModels[modelId] = view.model;
                view.toggleRow('Quotes', modelId, false);
            });

            it('should set model.modelView to be detail', function() {
                expect(view.model.modelView).toBe('list');
            });

            it('should remove model from toggledModels', function() {
                expect(view.toggledModels[modelId]).toBeUndefined();
            });

            it('should call toggleClass with tr-inline-edit, false', function() {
                expect(toggleClassStub).toHaveBeenCalledWith('tr-inline-edit', false);
            });

            it('should call toggleFields with models rowFields and true', function() {
                expect(view.toggleFields).toHaveBeenCalledWith(rowFields, false);
            });
        });

    });
});
