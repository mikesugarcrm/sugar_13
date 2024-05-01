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
describe('ForecastWorksheets.Base.Field.Int', function () {
    var app,
        field,
        moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadComponent('base', 'field', 'int');

        var fieldDef = {
            name: 'test_field',
            type: 'int',
            len: 4
        };

        field = SugarTest.createField('base', 'int', 'int', 'record', fieldDef, moduleName, null, null, true);
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['ClickToEdit'];
        sinon.restore();
        field = null;
        app = null;
    });

    it('should have ClickToEdit Plugin', function() {
        expect(field.plugins).toContain('ClickToEdit');
    });

    describe('ClickToEdit _fieldValueChanged', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'get').callsFake(function() {
                return field.value;
            });
            field.value = '50';
        });
        afterEach(function() {
            field.value = undefined;
        });

        it('should return true when adding 1', function() {
            sinon.stub(field.$el, 'find').callsFake(function() {
                return {
                    val: function() {
                        return '+1';
                    }
                };
            });
            expect(field._fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when subtracting 1', function() {
            sinon.stub(field.$el, 'find').callsFake(function() {
                return {
                    val: function() {
                        return '-1';
                    }
                };
            });
            expect(field._fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when adding a percent', function() {
            sinon.stub(field.$el, 'find').callsFake(function() {
                return {
                    val: function() {
                        return '+10%';
                    }
                };
            });
            expect(field._fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when subtracting a percent', function() {
            sinon.stub(field.$el, 'find').callsFake(function() {
                return {
                    val: function() {
                        return '-10%';
                    }
                };
            });
            expect(field._fieldValueChanged(field)).toBeTruthy();
        });

        it('should return false when values are the same', function() {
            sinon.stub(field.$el, 'find').callsFake(function() {
                return {
                    val: function() {
                        return '50';
                    }
                };
            });
            expect(field._fieldValueChanged(field)).toBeFalsy();
        });
    });
});
