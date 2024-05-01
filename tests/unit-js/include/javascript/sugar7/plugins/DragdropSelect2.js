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
describe('DragdropSelect2 Plugin', function() {
    var app, field, $select2, $items;

    beforeEach(function() {
        app = SugarTest.app;

        field = SugarTest.createField('base', 'foo', 'base');
        field.plugins = ['DragdropSelect2'];
        SugarTest.loadPlugin('DragdropSelect2');
        SugarTest.app.plugins.attach(field, 'field');

        field.$el.append('<select multiple id="e1" class="select2">' +
            '<option value="A" selected>Apple</option>' +
            '<option value="B" selected>Banana</option>' +
            '<option value="C" selected>Carrot</option>' +
            '<option value="D" selected>Donut</option>' +
            '</select>');

        $select2 = field.$('.select2');
        $select2.select2({
            formatSelection: function(item) {
                return '<span data-id="' + item.id + '">' + item.text + '</span>';
            }
        });
        $items = field.$(field.itemSelector);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('setSelectable', function() {
        var $firstItem, $lastItem, getItemById, getItems;

        getItemById = function(id) {
            return field.$('[data-id="' + id + '"]').closest('li');
        };

        getItems = function() {
            var itemIds = ['A', 'B', 'C', 'D'],
                items = {};

            _.each(itemIds, function(itemId) {
                items[itemId] = getItemById(itemId);
            });
            return items;
        };

        beforeEach(function() {
            $firstItem = $items.first();
            $lastItem = $items.last();
            field.setSelectable($select2, $items);
        });

        it('should mark item selected', function() {
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should keep item selected', function() {
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should change selection from first to last item', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(false);

            $lastItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            expect($lastItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should select both first and last item', function() {
            var metaClickEvent = jQuery.Event('click', {keyCode: 91, metaKey: true});

            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(false);

            $lastItem.trigger(metaClickEvent);
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(true);
        });

        using('range selection combinations', [
            {
                expectation: 'should select range forward (A through C)',
                firstItemClick: 'A',
                shiftItemClick: 'C',
                expectedSelectedItems: ['A', 'B', 'C']
            },
            {
                expectation: 'should select range backwards (D through B)',
                firstItemClick: 'D',
                shiftItemClick: 'B',
                expectedSelectedItems: ['B', 'C', 'D']
            },
            {
                expectation: 'should select single item range (B)',
                firstItemClick: 'B',
                shiftItemClick: 'B',
                expectedSelectedItems: ['B']
            }
        ], function(data) {
            it(data.expectation, function() {
                var shiftClickEvent = jQuery.Event('click', {keyCode: 91, shiftKey: true}),
                    items = getItems();

                items[data.firstItemClick].click();
                items[data.shiftItemClick].trigger(shiftClickEvent);
                _.each(items, function(item, id) {
                    var expectedValue = _.contains(data.expectedSelectedItems, id);
                    expect(items[id].hasClass(field.selectedClass)).toBe(expectedValue);
                });
            });
        });

        it('should only select item C if no previously selected item on shift click event', function() {
            var shiftClickEvent = jQuery.Event('click', {keyCode: 91, shiftKey: true}),
                items = getItems();

            items['C'].trigger(shiftClickEvent);
            expect(items['A'].hasClass(field.selectedClass)).toBe(false);
            expect(items['B'].hasClass(field.selectedClass)).toBe(false);
            expect(items['C'].hasClass(field.selectedClass)).toBe(true);
            expect(items['D'].hasClass(field.selectedClass)).toBe(false);
        });

        it('should deselect when clicking on document', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);

            $(document).click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
        });

        it('should deselect when clicking on select2 container whitespace', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);

            $select2.select2('container').click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
        });

        it('should select all items when the select all event is fired with target in this field', function() {
            var event = jQuery.Event('keydown', {target: field.$('.select2-search-field input').get(0)});
            field.trigger('init');
            expect(field.$('.' + field.selectedClass).length).toEqual(0);
            field.context.trigger('dragdropselect2:select:all', event);
            expect(field.$('.' + field.selectedClass).length).toEqual($items.length);
        });

        it('should not select all items when the select all event is fired with target not in this field', function() {
            var event = jQuery.Event('keydown', {target: document});
            field.trigger('init');
            expect(field.$('.' + field.selectedClass).length).toEqual(0);
            field.context.trigger('dragdropselect2:select:all', event);
            expect(field.$('.' + field.selectedClass).length).toEqual(0);
        });
    });

    describe('setDraggable', function() {
        var $firstItem, $lastItem;

        beforeEach(function() {
            $firstItem = $items.first();
            $lastItem = $items.last();
            field.setDraggable($select2, $items);
        });

        it('should allow drag of one item', function() {
            var event, helper, result;

            event = jQuery.Event('click', {
                keyCode: 91,
                target: $firstItem.get(0)
            });
            helper = $firstItem.draggable('option', 'helper');

            result = helper(event);
            expect($(result).find('[data-id]').length).toEqual(1);
        });

        it('should allow drag of two items', function() {
            var event, helper, result;

            $firstItem.addClass(field.selectedClass);
            $lastItem.addClass(field.selectedClass);

            event = jQuery.Event('click', {
                keyCode: 91,
                target: $firstItem.get(0)
            });
            helper = $firstItem.draggable('option', 'helper');

            result = helper(event);
            expect($(result).find('[data-id]').length).toEqual(2);
        });
    });

    describe('setDroppable', function() {
        beforeEach(function() {
            field.setDroppable($select2);
        });

        it('should move items from one field to another', function() {
            var dropHandler,
                $helper,
                sourceCollection = app.data.createBeanCollection(),
                targetCollection = app.data.createBeanCollection(),
                item1 = app.data.createBean('Foo', {id: '123'}),
                item2 = app.data.createBean('Foo', {id: '456'}),
                item3 = app.data.createBean('Foo', {id: '789'}),
                mockEvent = {};

            sourceCollection.reset([item1, item2, item3]);
            field.model.set({
                source_field: sourceCollection,
                target_field: targetCollection
            });
            field.name = 'target_field';

            dropHandler = $select2.select2('container').droppable('option', 'drop');

            //build up mock drag helper with first and third items dragged
            $helper = $('<div data-source-field="source_field"></div>');
            $helper.append('<span data-id="' + item1.id + '"></span>');
            $helper.append('<span data-id="' + item3.id + '"></span>');

            //simulate the drop
            dropHandler(mockEvent, {helper: $helper.get(0)});

            //only item2 should remain in sourceCollection
            expect(sourceCollection.get(item1.id)).toBeUndefined();
            expect(sourceCollection.get(item2.id)).not.toBeUndefined();
            expect(sourceCollection.get(item3.id)).toBeUndefined();

            //item1 and item3 should now be in targetCollection
            expect(targetCollection.get(item1.id)).not.toBeUndefined();
            expect(targetCollection.get(item2.id)).toBeUndefined();
            expect(targetCollection.get(item3.id)).not.toBeUndefined();
        });
    });
});
