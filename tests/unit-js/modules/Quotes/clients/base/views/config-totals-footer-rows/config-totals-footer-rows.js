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
describe('Quotes.View.ConfigTotalsFooterRows', function() {
    var app;
    var view;
    var options;
    var context;
    var viewCollection;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        var meta = {
            label: 'testLabel',
            panels: [{
                fields: []
            }]
        };
        options = {
            collection: viewCollection,
            context: context,
            meta: meta
        };

        view = SugarTest.createView('base', 'Quotes', 'config-totals-footer-rows', meta, context, true);
        view.options.eventViewName = 'footer_rows';
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        options = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            view.initialize(options);
        });

        it('should reset footerFields', function() {
            expect(view.footerFields).toEqual([]);
        });

        it('should reset footerGrandTotalFields', function() {
            expect(view.footerGrandTotalFields).toEqual([]);
        });

        it('should reset syncedFields', function() {
            expect(view.syncedFields).toEqual([]);
        });
    });

    describe('setFooterRowFields()', function() {
        var footerFields;

        beforeEach(function() {
            footerFields = [{
                name: 'new_sub',
                type: 'currency'
            }, {
                name: 'tax',
                type: 'currency',
                related_fields: ['taxrate_value']
            }, {
                name: 'shipping',
                type: 'quote-footer-currency',
                css_class: 'quote-footer-currency',
                default: '0.00'
            }, {
                name: 'total',
                label: 'LBL_LIST_GRAND_TOTAL',
                type: 'currency',
                css_class: 'grand-total'
            }];
            sinon.stub(view, 'render');

            view.setFooterRowFields(footerFields);
        });

        afterEach(function() {
            footerFields = null;
        });

        it('should set syncedFields', function() {
            expect(view.syncedFields).toEqual(footerFields);
        });

        it('should set footerFields', function() {
            expect(view.footerFields).toEqual(footerFields.splice(0, 3));

        });

        it('should set footerGrandTotalFields', function() {
            expect(view.footerGrandTotalFields).toEqual(footerFields.splice(3, 1));
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('addFooterRowField()', function() {
        beforeEach(function() {
            sinon.stub(view, '_parseFieldsForModel').callsFake(function() {
                return view.footerFields;
            });
            sinon.stub(view, 'render');
            view.footerFields = [{
                name: 'aaa'
            }];

            view.addFooterRowField({
                name: 'bbb'
            });
        });

        it('should set footer_rows on the model', function() {
            expect(view.model.get('footer_rows')).toEqual([{
                name: 'bbb'
            }, {
                name: 'aaa'
            }]);
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('removeFooterRowField()', function() {
        beforeEach(function() {
            view.footerFields = [{
                name: 'bbb'
            }, {
                name: 'aaa'
            }];
            view.footerGrandTotalFields = [{
                name: 'ccc'
            }, {
                name: 'ddd'
            }];
            sinon.stub(view, 'render');
            sinon.stub(view, '_parseFieldsForModel').callsFake(function() {
                return [].concat(view.footerFields, view.footerGrandTotalFields);
            });
        });

        it('should remove field from footerFields', function() {
            view.removeFooterRowField({
                name: 'aaa'
            });

            expect(view.footerFields).toEqual([{
                name: 'bbb'
            }]);
            expect(view.footerGrandTotalFields).toEqual([{
                name: 'ccc'
            }, {
                name: 'ddd'
            }]);
        });

        it('should remove field from footerGrandTotalFields', function() {
            view.removeFooterRowField({
                name: 'ccc'
            });

            expect(view.footerFields).toEqual([{
                name: 'bbb'
            }, {
                name: 'aaa'
            }]);
            expect(view.footerGrandTotalFields).toEqual([{
                name: 'ddd'
            }]);
        });

        it('should set fields back on the model', function() {
            view.removeFooterRowField({
                name: 'ccc'
            });

            expect(view.model.get('footer_rows')).toEqual([{
                name: 'bbb'
            }, {
                name: 'aaa'
            }, {
                name: 'ddd'
            }]);
        });

        it('should call render', function() {
            view.removeFooterRowField({
                name: 'aaa'
            });

            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        var sortableSpy;
        var droppableStub;
        var returnObj;
        beforeEach(function() {
            droppableStub = sinon.stub();
            returnObj = {
                sortable: function() {
                    return {
                        disableSelection: $.noop
                    };
                },
                droppable: droppableStub
            };
            sortableSpy = sinon.spy(returnObj, 'sortable');

            sinon.stub(view, '$').callsFake(function() {
                return returnObj;
            });
            sinon.stub(view, '_super').callsFake($.noop);

            view.render();
        });

        afterEach(function() {
            sortableSpy = null;
            droppableStub = null;
            returnObj = null;
        });

        it('should call this.$(.connected-containers)', function() {
            expect(view.$).toHaveBeenCalledWith('.connected-containers');
        });

        it('should call sortable on .connected-containers', function() {
            expect(sortableSpy).toHaveBeenCalled();
        });

        it('should call droppable on .connected-containers', function() {
            expect(droppableStub).toHaveBeenCalled();
        });
    });

    describe('_onDragStop()', function() {
        var ui;
        var cssSelector;
        var $parent;
        var $item;

        beforeEach(function() {
            sinon.stub(view, '_moveFieldToNewPosition').callsFake($.noop);
            view.footerFields = [{
                name: 'bbb'
            }, {
                name: 'aaa'
            }];
            view.footerGrandTotalFields = [{
                name: 'ccc'
            }, {
                name: 'ddd'
            }];
            sinon.stub(view, 'render');
            sinon.stub(view, '_parseFieldsForModel').callsFake(function() {
                return [].concat(view.footerFields, view.footerGrandTotalFields);
            });
        });

        describe('moving to new group', function() {
            it('should call _moveFieldToNewPosition to move from total to grand total', function() {
                cssSelector = '.' + view.sortableGrandTotalFieldsContainerClass + ' .sortable-item';
                $parent = $('<div data-group-type="grand-total"></div>');
                $item = $('<div data-field-name="test" data-field-type="total"></div>');
                $parent.append($item);
                ui = {
                    item: $item
                };
                view._onDragStop({}, ui);

                expect(view._moveFieldToNewPosition).toHaveBeenCalledWith(
                    'test',
                    view.footerFields,
                    view.footerGrandTotalFields,
                    cssSelector
                );
            });

            it('should call _moveFieldToNewPosition to move from grand total to total', function() {
                cssSelector = '.' + view.sortableFieldsContainerClass + ' .sortable-item';
                $parent = $('<div data-group-type="total"></div>');
                $item = $('<div data-field-name="test" data-field-type="grand-total"></div>');
                $parent.append($item);
                ui = {
                    item: $item
                };
                view._onDragStop({}, ui);

                expect(view._moveFieldToNewPosition).toHaveBeenCalledWith(
                    'test',
                    view.footerGrandTotalFields,
                    view.footerFields,
                    cssSelector
                );
            });
        });

        describe('staying in same group', function() {
            it('should call _moveFieldToNewPosition to change position in footerFields', function() {
                cssSelector = '.' + view.sortableFieldsContainerClass + ' .sortable-item';
                $parent = $('<div data-group-type="total"></div>');
                $item = $('<div data-field-name="test" data-field-type="total"></div>');
                $parent.append($item);
                ui = {
                    item: $item
                };
                view._onDragStop({}, ui);

                expect(view._moveFieldToNewPosition).toHaveBeenCalledWith(
                    'test',
                    view.footerFields,
                    view.footerFields,
                    cssSelector
                );
            });

            it('should call _moveFieldToNewPosition to change position in footerGrandTotalFields', function() {
                cssSelector = '.' + view.sortableGrandTotalFieldsContainerClass + ' .sortable-item';
                $parent = $('<div data-group-type="grand-total"></div>');
                $item = $('<div data-field-name="test" data-field-type="grand-total"></div>');
                $parent.append($item);
                ui = {
                    item: $item
                };
                view._onDragStop({}, ui);

                expect(view._moveFieldToNewPosition).toHaveBeenCalledWith(
                    'test',
                    view.footerGrandTotalFields,
                    view.footerGrandTotalFields,
                    cssSelector
                );
            });
        });

        it('should set fields back on model', function() {
            view._onDragStop({}, ui);

            expect(view.model.get('footer_rows')).toEqual([{
                name: 'bbb'
            }, {
                name: 'aaa'
            }, {
                name: 'ccc'
            }, {
                name: 'ddd'
            }]);
        });

        it('should call render', function() {
            view._onDragStop({}, ui);

            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('_parseFieldsForModel()', function() {
        beforeEach(function() {
            view.footerFields = [{
                name: 'new_sub',
                type: 'currency',
                label: 'LBL_NEW_SUB',
                syncedType: 'testSyncedType1',
                css_class: 'testCssClass1 testCssClass2 testCssClass3',
                syncedCssClass: 'testCssClass1'
            }, {
                name: 'tax',
                type: 'currency',
                syncedType: 'testSyncedType2',
                css_class: 'testCssClass2',
                syncedCssClass: 'testSyncedCssClass2',
                default: '0.00'
            }];
            view.footerGrandTotalFields = [{
                name: 'shipping',
                type: 'currency',
                syncedType: 'quote-footer-currency',
                syncedCssClass: 'grand-total quote-footer-currency',
                css_class: 'quote-footer-currency',
                default: '0.00'
            }, {
                name: 'total',
                label: 'LBL_LIST_GRAND_TOTAL',
                type: 'currency',
                css_class: 'grand-total',
                syncedType: 'currency',
                syncedCssClass: 'grand-total'
            }];
        });

        it('should return fields parsed for the model', function() {
            expect(view._parseFieldsForModel()).toEqual([{
                name: 'new_sub',
                type: 'testSyncedType1',
                label: 'LBL_NEW_SUB',
                css_class: 'testCssClass1 testCssClass2 testCssClass3'
            }, {
                name: 'tax',
                type: 'testSyncedType2',
                css_class: 'testSyncedCssClass2 testCssClass2',
                default: '0.00'
            }, {
                name: 'shipping',
                type: 'quote-footer-currency',
                css_class: 'grand-total quote-footer-currency',
                default: '0.00'
            }, {
                name: 'total',
                type: 'currency',
                label: 'LBL_LIST_GRAND_TOTAL',
                css_class: 'grand-total'
            }]);
        });
    });

    describe('_moveFieldToNewPosition()', function() {
        var oldGroup;
        var newGroup;
        var result;
        var expectedOldGroup;
        var expectedNewGroup;

        afterEach(function() {
            oldGroup = null;
            newGroup = null;
            result = null;
            expectedOldGroup = null;
            expectedNewGroup = null;
        });

        describe('when oldGroup and newGroup are the same', function() {
            beforeEach(function() {
                sinon.stub(view, '$').callsFake(function() {
                    return [
                        '<div data-field-name="test2"></div>',
                        '<div data-field-name="test3"></div>',
                        '<div data-field-name="test1"></div>',
                        '<div data-field-name="test4"></div>'
                    ];
                });
            });

            it('should update group fields to move test1', function() {
                oldGroup = newGroup = [{
                    name: 'test1'
                }, {
                    name: 'test2'
                }, {
                    name: 'test3'
                }, {
                    name: 'test4'
                }];
                expectedOldGroup = expectedNewGroup = [{
                    name: 'test2'
                }, {
                    name: 'test3'
                }, {
                    name: 'test1'
                }, {
                    name: 'test4'
                }];
                result = view._moveFieldToNewPosition(
                    'test1',
                    oldGroup,
                    newGroup,
                    'test'
                );

                expect(oldGroup).toEqual(expectedOldGroup);
                expect(newGroup).toEqual(expectedNewGroup);
            });
        });

        describe('when oldGroup and newGroup are different', function() {
            beforeEach(function() {
                sinon.stub(view, '$').callsFake(function() {
                    return [
                        '<div data-field-name="test1"></div>',
                        '<div data-field-name="test4"></div>'
                    ];
                });
            });

            it('should update old and new groups fields to move test1', function() {
                oldGroup = [{
                    name: 'test1'
                }, {
                    name: 'test2'
                }, {
                    name: 'test3'
                }];
                newGroup = [{
                    name: 'test4'
                }];
                expectedOldGroup = [{
                    name: 'test2'
                }, {
                    name: 'test3'
                }];
                expectedNewGroup = [{
                    name: 'test1'
                }, {
                    name: 'test4'
                }];
                result = view._moveFieldToNewPosition(
                    'test1',
                    oldGroup,
                    newGroup,
                    'test'
                );

                expect(oldGroup).toEqual(expectedOldGroup);
                expect(newGroup).toEqual(expectedNewGroup);
            });
        });
    });
});
