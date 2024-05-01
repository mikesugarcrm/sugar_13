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
describe('Base.Field.ActionMenu', function() {
    var app,
        field,
        Account,
        $checkBox,
        context,
        massCollection;

    beforeEach(function() {
        app = SugarTest.app;
        var def = {};
        context = app.context.getContext();
        var MassCollection = Backbone.Collection.extend();
        massCollection = new MassCollection([{id: 1}, {id: 2}]);
        context.set({
            mass_collection: massCollection
        });
        context.prepare();

        field = SugarTest.createField('base', 'actionmenu', 'actionmenu', 'recordlist', def, null, null, context);
        field.view.layout = undefined;
        $checkBox = '<input type="checkbox" name="check">';
        field.$el.append($checkBox);
        field.$el.append('<a data-bs-toggle="dropdown"></a>');
        field.actionDropDownTag = '[data-bs-toggle=dropdown]';

    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field._loadTemplate = null;
        field = null;
        Account = null;
    });

    describe('initialize', function() {
        it('should force no_default_action = true on the field def', function() {
            expect(field.def.no_default_action).toEqual(true);
        });
    });

    describe('toggleSelect:', function() {
        beforeEach(function() {
            sinon.spy(field.context, 'trigger');
        });

        it('should trigger a "mass_collection:add" event', function() {
            field.toggleSelect(true);

            expect(field.context.trigger).toHaveBeenCalledWith('mass_collection:add');
        });

        it('should trigger a "mass_collection:remove" event', function() {
            field.toggleSelect(false);

            expect(field.context.trigger).toHaveBeenCalledWith('mass_collection:remove');
        });
    });

    describe('toggleAll:', function() {
        beforeEach(function() {
            sinon.spy(field.context, 'trigger');
        });

        it('should trigger a "mass_collection:add:all" event', function() {
            field.toggleAll(true);

            expect(field.context.trigger).toHaveBeenCalledWith('mass_collection:add:all');
        });

        it('should trigger a "mass_collection:remove:all" event', function() {
            field.toggleAll(false);

            expect(field.context.trigger).toHaveBeenCalledWith('mass_collection:remove:all');
        });
    });

    describe('check:', function() {
        using('checkbox state', [true, false], function(state) {
            it('should call toggleSelect', function() {
                var toggleSelectStub = sinon.stub(field, 'toggleSelect');
                field.$(field.fieldTag).prop('checked', state);
                field.check();

                expect(toggleSelectStub).toHaveBeenCalledWith(state);
            });
        });
    });

    describe('setDropdownDisabled:', function() {
        using('dropdown state', [true, false], function(state) {
            it('should toggle disable class on the dropdown element', function() {
                field.setDropdownDisabled(state);

                expect(field.$(field.actionDropDownTag).hasClass('disabled')).toBe(state);
            });
        });
    });

    describe('Adding a model to the mass collection', function() {
        beforeEach(function() {
            field.isCheckAllCheckbox = true;
            sinon.spy(field.context, 'trigger');
            sinon.spy(field, '_onMassCollectionAddAll');
        });

        using('disable_select_all_alert state', [true, false], function(state) {
            it('should enable the dropdown and handle the toggleSelectAll Alert', function() {
                field.def.disable_select_all_alert = state;
                field.$(field.actionDropDownTag).addClass('disabled');
                field.bindDataChange();
                massCollection.add({id: 3});

                expect(field._onMassCollectionAddAll).toHaveBeenCalled();
                expect(field.$(field.actionDropDownTag).hasClass('disabled')).toBe(false);
                if (state) {
                    expect(field.context.trigger).not.toHaveBeenCalled();
                } else {
                    expect(field.context.trigger).toHaveBeenCalledWith('toggleSelectAllAlert');
                }
            });
        });
    });

    describe('Removing a model from the mass collection', function() {
        beforeEach(function() {
            field.isCheckAllCheckbox = true;
            sinon.spy(field.context, 'trigger');
            field.collection.add({id: 1});
            sinon.spy(field, '_onMassCollectionRemoveResetAll');
        });

        it('should disable the dropdown if the mass collection has no models', function() {
            field.$(field.actionDropDownTag).removeClass('disabled');
            field.bindDataChange();
            massCollection.remove([{id: 1}, {id: 2}]);

            expect(field._onMassCollectionRemoveResetAll).toHaveBeenCalled();
            expect(field.$(field.actionDropDownTag).hasClass('disabled')).toBe(true);
        });

        using('disable_select_all_alert state', [true, false], function(state) {
            it('should handle the toggleSelectAll Alert and dropdown buttons disabling', function() {
                sinon.stub(field, 'setButtonsDisabled');
                sinon.spy(field, '_bindAllModelChangeEvents');
                field.def.disable_select_all_alert = state;
                field.bindDataChange();
                massCollection.remove([{id: 2}]);

                expect(field._onMassCollectionRemoveResetAll).toHaveBeenCalled();
                if (state) {
                    expect(field.context.trigger).not.toHaveBeenCalled();
                    expect(field.setButtonsDisabled).not.toHaveBeenCalled();
                } else {
                    expect(field.setButtonsDisabled).toHaveBeenCalled();
                    expect(field.context.trigger).toHaveBeenCalledWith('toggleSelectAllAlert');
                }
            });
        });

        it('should check the `checkall` checkbox and enable dropdown if at least one model of the collection is in the mass collection', function() {
            field.$(field.fieldTag).prop('checked', false);
            field.bindDataChange();
            massCollection.remove({id: 2});

            expect(field.$(field.fieldTag).prop('checked')).toBe(true);
            expect(field.$(field.actionDropDownTag).hasClass('disabled')).toBe(false);
        });
    });
});
