
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
describe('View.Fields.Base.Users.AvailableModulesField', function() {
    let field;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('available-modules', 'field', 'base', 'detail', 'Users');
        SugarTest.loadHandlebarsTemplate('available-modules', 'field', 'base', 'edit', 'Users');
        SugarTest.testMetadata.set();

        field = SugarTest.createField({
            client: 'base',
            name: 'user_tabs',
            type: 'available-modules',
            viewName: 'detail',
            module: 'Users',
            loadFromModule: true,
        });

        field.model.set(field.name, {
            display: ['Accounts', 'Contacts', 'Leads', 'Opportunities', 'Calendar'],
            hide: []
        });
        field.model.set('number_pinned_modules', 3);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
    });

    describe('bindDataChange', function() {
        it('should update the divider when the number of pinned modules changes', function() {
            field.render();
            let list = field._getListElementByName('display');
            let items = $(list).find('li');
            _.each(items, function(item, index) {
                expect($(item).hasClass('border-b-2')).toEqual(index + 1 === 3);
            });

            field.model.set('number_pinned_modules', 4);
            _.each(items, function(item, index) {
                expect($(item).hasClass('border-b-2')).toEqual(index + 1 === 4);
            });
        });

        it('should rerender when the model value changes', function() {
            sinon.stub(field, '_render');
            field.model.set(field.name, {
                display: ['fakeModule'],
                hide: ['fakeModule2']
            });
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('unformat', function() {
        it('should unformat the element state into the correct model value', function() {
            field.setMode('edit');
            field.render();

            let oldValue = field.unformat();
            expect(oldValue.display).toEqual(['Accounts', 'Contacts', 'Leads', 'Opportunities', 'Calendar']);
            expect(oldValue.hide).toEqual([]);

            // Move "Contacts" to the hide list
            let displayList = field._getListElementByName('display');
            let hideList = field._getListElementByName('hide');
            let item = $(displayList).find('li:nth-child(2)');
            item.detach().appendTo($(hideList));

            let newValue = field.unformat();
            expect(newValue.display).toEqual(['Accounts', 'Leads', 'Opportunities', 'Calendar']);
            expect(newValue.hide).toEqual(['Contacts']);
        });
    });

    describe('_render', function() {
        describe('in detail mode', function() {
            beforeEach(function() {
                field.setMode('detail');
            });

            it('should not initialize any list sortability', function() {
                field._render();
                expect(field.$('.sortable-list').sortable('instance')).toBeUndefined();
            });
        });

        describe('in edit mode', function() {
            beforeEach(function() {
                field.setMode('edit');
            });

            it('should initialize list sortability', function() {
                field._render();
                expect(field.$('.sortable-list').sortable('instance')).not.toBeUndefined();
            });
        });
    });

    describe('_updateDivider', function() {
        beforeEach(function() {
            field._render();
        });

        it('should set the divider only at the correct position', function() {
            field._updateDivider();

            let list = field._getListElementByName('display');
            let items = $(list).find('li');
            _.each(items, function(item, index) {
                expect($(item).hasClass('border-b-2')).toEqual(index + 1 === 3);
            });
        });
    });

    describe('_removeClicked', function() {
        it('should move the clicked item to the hide list', function() {
            field.setMode('edit');
            field.render();

            let modelValue = field.model.get(field.name);
            expect(modelValue.display).toEqual(['Accounts', 'Contacts', 'Leads', 'Opportunities', 'Calendar']);
            expect(modelValue.hide).toEqual([]);

            let displayList = field._getListElementByName('display');
            $(displayList).find('li:nth-child(2) i').click();

            modelValue = field.model.get(field.name);
            expect(modelValue.display).toEqual(['Accounts', 'Leads', 'Opportunities', 'Calendar']);
            expect(modelValue.hide).toEqual(['Contacts']);
        });
    });
});
