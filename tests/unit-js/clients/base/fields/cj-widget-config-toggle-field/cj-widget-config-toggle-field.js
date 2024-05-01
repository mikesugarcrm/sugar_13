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
describe('Base.Field.CjWidgetConfigToggle', function() {
    var field;
    var app;
    var fieldName = 'cj-widget-config-toggle';
    var stateKey = 'cj-widget-config-toggle:toggleActiveArchived';
    var fieldDef = {
        stateValueMapping: {
            active: true,
            archived: false,
        },
        keyName: 'toggleActiveArchived',
        defaultStateValue: 'active',
    };

    beforeEach(function() {
        SugarTest.loadComponent('base', 'field', 'base');
        app = SugarTest.app;
        field = SugarTest.createField('base', fieldName, 'cj-widget-config-toggle', 'edit',
            fieldDef, null, null, null, true);
        context = app.context.getContext();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    describe('initialize()', function() {
        it('should have the initialized values', function() {
            sinon.stub(app.user.lastState, 'buildKey');
            expect(field.stateValueMapping).toEqual(fieldDef.stateValueMapping);
            expect(field.stateKey).toEqual(stateKey);
        });
    });

    describe('_render()', function() {
        it('should set the field value to the selected value', function() {
            sinon.stub(field, 'getCurrentValue').callsFake(function() {
                return true;
            });
            field._render();
            expect(field.model.get(fieldName)).toBe(true);
        });
    });

    describe('getCurrentValue()', function() {
        it('should call getToggleFieldState function, get current field value', function() {
            sinon.stub(field, 'getToggleFieldState').callsFake(function() {
                return 'active';
            });
            let result = field.getCurrentValue();
            expect(field.getToggleFieldState).toHaveBeenCalled();
            expect(result).toBe(true);
        });
    });

    describe('getToggleFieldState()', function() {
        it('should return user last state value or default state value', function() {
            app.user.lastState.set(stateKey, 'archived');
            let result = field.getToggleFieldState();
            expect(result).toEqual('archived');
        });
    });

    describe('setToggleFieldStateInCache()', function() {
        it('should set the toggle field state in cache and return', function() {
            field.setToggleFieldStateInCache();
            expect(app.user.lastState.get(stateKey)).toEqual('archived');
        });
    });

    describe('_getFallbackTemplate()', function() {
        it('should return the correct fallback template', function() {
            let result = field._getFallbackTemplate();
            expect(result).toEqual('detail');
        });
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app = undefined;
        field = undefined;
        SugarTest.testMetadata.dispose();
    });
});
