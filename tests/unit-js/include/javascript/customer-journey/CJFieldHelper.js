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

describe('SUGAR.App.CJFieldHelper', function() {
    let app;
    let field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'date', 'date', 'detail');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();

        field.dispose();
        field = null;
    });

    describe('_hideField', function() {
        it('should call field _hide and CJBaseHelper _hideFieldLabel function', function() {
            sinon.stub(app.CJFieldHelper, '_hideFieldLabel');
            sinon.stub(field, '_hide');
            app.CJFieldHelper._hideField(field, function() {});

            expect(field._hide).toHaveBeenCalled();
            expect(app.CJFieldHelper._hideFieldLabel).toHaveBeenCalled();
        });
    });

    describe('_hideFieldLabel', function() {
        beforeEach(function() {
            sinon.stub(field.$el, 'parents').callsFake(function() {
                return {
                    eq: function() {
                        return field.$el;
                    },
                };
            });
            sinon.stub(field.$el, 'hide');
        });

        it('should not call field $el.parents and hide functions', function() {
            app.CJFieldHelper._hideFieldLabel();

            expect(field.$el.parents).not.toHaveBeenCalled();
            expect(field.$el.hide).not.toHaveBeenCalled();
        });

        it('should call field $el.parents and hide functions', function() {
            app.CJFieldHelper._hideField(field);

            expect(field.$el.parents).toHaveBeenCalled();
            expect(field.$el.hide).toHaveBeenCalled();
        });
    });

    describe('_showFieldLabel', function() {
        beforeEach(function() {
            sinon.stub(field.$el, 'parents').returns(field.$el);
            sinon.stub(field.$el, 'eq').returns(field.$el);
            sinon.stub(field.$el, 'find').returns(field.$el);
            sinon.stub(field.$el, 'removeClass').returns(field.$el);
            sinon.stub(field.$el, 'addClass').returns(field.$el);
            sinon.stub(field.$el, 'show').returns(field.$el);
        });

        it('should not call field $el.parents and show functions', function() {
            app.CJFieldHelper._showFieldLabel();

            expect(field.$el.parents).not.toHaveBeenCalled();
            expect(field.$el.show).not.toHaveBeenCalled();
        });

        it('should call field $el.parents and show functions', function() {
            app.CJFieldHelper._showFieldLabel(field);

            expect(field.$el.parents).toHaveBeenCalled();
            expect(field.$el.show).toHaveBeenCalled();
        });
    });

    describe('_showField', function() {
        beforeEach(function() {
            sinon.stub(field, '_show');
            sinon.stub(app.CJFieldHelper, '_showFieldLabel');
            sinon.stub(field, 'setMode').callsFake(function(action) {
                field.action = action;
            });
        });

        it('field action should be detail and should call _show, setMode and _showFieldLabel functions', function() {
            field.view.createMode = false;
            app.CJFieldHelper._showField(field);

            expect(field.action).toBe('detail');
            expect(field._show).toHaveBeenCalled();
            expect(field.setMode).toHaveBeenCalled();
            expect(app.CJFieldHelper._showFieldLabel).toHaveBeenCalled();
        });

        it('field action should be edit and should call _show, setMode and _showFieldLabel functions', function() {
            field.view.createMode = false;
            field.view.currentState = 'edit';
            app.CJFieldHelper._showField(field);

            expect(field.action).toBe('edit');
            expect(field._show).toHaveBeenCalled();
            expect(field.setMode).toHaveBeenCalled();
            expect(app.CJFieldHelper._showFieldLabel).toHaveBeenCalled();
        });
    });

    describe('_enableOrDisableField', function() {
        beforeEach(function() {
            sinon.stub(field, 'setDisabled');
        });

        it('field readonly should be undefined and should not call field setDisabled', function() {
            app.CJFieldHelper._enableOrDisableField();

            expect(field.readonly).toBe(undefined);
            expect(field.setDisabled).not.toHaveBeenCalled();
        });

        it('should not call field setDisabled and field readonly should be true', function() {
            app.CJFieldHelper._enableOrDisableField(field, true);

            expect(field.readonly).toBe(true);
            expect(field.setDisabled).toHaveBeenCalled();
        });
    });
});
