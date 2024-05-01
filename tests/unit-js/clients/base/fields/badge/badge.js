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
describe('Base.Field.Badge', function() {
    var field;

    beforeEach(function() {
        field = SugarTest.createField('base', 'testField', 'badge', 'record');
        field.model = new Backbone.Model({id: '123'});
        sinon.stub(field, '_super');
    });

    afterEach(function() {
        field = null;
        sinon.restore();
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.stub(field, 'hide');
            sinon.stub(field, 'show');
        });

        it('should call super _render method', function() {
            field._render();
            expect(field._super).toHaveBeenCalledWith('_render');
        });

        it('should call isHidden method ', function() {
            sinon.stub(field, 'isHidden').returns(true);
            field._render();
            expect(field.isHidden).toHaveBeenCalled();
        });

        it('should call hide method if field is hidden', function() {
            sinon.stub(field, 'isHidden').returns(true);
            field._render();
            expect(field._super).toHaveBeenCalledWith('hide');
        });

        it('should not call hide method if field is hidden', function() {
            sinon.stub(field, 'isHidden').returns(false);
            field._render();
            expect(field._super).toHaveBeenCalledWith('show');
        });
    });

    describe('isHidden', function() {
        using('differnt field values', [
            {
                value: undefined,
                result: true
            },
            {
                value: false,
                result: true
            },
            {
                value: true,
                result: false
            }
        ], function(option) {
            it('should return correct result based on field value', function() {
                sinon.stub(field.model, 'get').returns(option.value);

                expect(field.isHidden()).toBe(option.result);
            });
        });
    });

    describe('hide', function() {
        it('should call super hide method', function() {
            sinon.stub(field.$el, 'closest');
            field.hide();
            expect(field._super).toHaveBeenCalledWith('hide');
        });

        it('should call closest method', function() {
            sinon.stub(field.$el, 'closest');
            field.hide();
            expect(field.$el.closest).toHaveBeenCalledWith('[data-type="badge"]');
        });

        it('should call parent hide/show method if parent element is found', function() {
            sinon.stub(field, 'getParentElem')
                .returns({
                    hide: sinon.stub(),
                    show: sinon.stub(),
                    prop: sinon.stub(),
                    closest: function() {
                        return {
                            hide: sinon.stub(),
                            show: sinon.stub()
                        };
                    }
                });

            var parentElem = field.getParentElem();
            field.hide();
            expect(parentElem.hide).toHaveBeenCalled();
            field.show();
            expect(parentElem.show).toHaveBeenCalled();
        });

        it('should call toggle cell method for the badge cell', function() {
            sinon.stub(field, 'toggleCell');
            field.hide();
            expect(field.toggleCell).toHaveBeenCalled();
        });
    });
});
