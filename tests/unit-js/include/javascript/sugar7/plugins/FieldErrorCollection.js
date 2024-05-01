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
describe('Plugins.FieldErrorCollection', function() {
    var view,
        layout,
        field;

    beforeEach(function() {
        SugarTest.loadPlugin('FieldErrorCollection');
        layout = SugarTest.createLayout('base', 'ForecastWorksheets', 'list', {}, null);
        view = SugarTest.createView('base', 'Forecasts', 'list-headerpane', null, null, true, layout, true);
        view.layout.context = {
            on: function() {},
            off: function() {}
        };

        sinon.stub(view, 'on').callsFake(function() {});
        sinon.stub(view, 'getField').callsFake(function() {
            return {
                setDisabled: function() {}
            }
        });

        field = {};
        field.model = new Backbone.Model();
        field.model.set({
            id: 'fieldID'
        });
    });

    afterEach(function() {
        layout.dispose();
        view.dispose();
        sinon.restore();
        layout = null;
        view = null;
        field = null;
    });

    describe('handleErrorEvent', function() {
        beforeEach(function() {
            sinon.spy(view.context, 'trigger');
        });

        it('should add field model on field error', function() {
            view.context.trigger('field:error', field, true);
            expect(view._errorCollection.models[0].get('id')).toBe('fieldID');
        });

        it('should remove field model on field error clear', function() {
            view.context.trigger('field:error', field, true);
            view.context.trigger('field:error', field, false);
            expect(view._errorCollection.models.length).toEqual(0);
        });

        it('should trigger its own event on the context', function() {
            view.context.trigger('field:error', field, true);
            expect(view.context.trigger).toHaveBeenCalledWith('plugin:fieldErrorCollection:hasFieldErrors');
        });
    });

    describe('hasFieldErrors', function() {
        it('should be true if a field still has an error state', function() {
            view.context.trigger('field:error', field, true);
            expect(view.hasFieldErrors()).toBeTruthy();
        });

        it('should be false if all fields have cleared their errors', function() {
            view.context.trigger('field:error', field, true);
            view.context.trigger('field:error', field, false);
            expect(view.hasFieldErrors()).toBeFalsy();
        });
    });
});
