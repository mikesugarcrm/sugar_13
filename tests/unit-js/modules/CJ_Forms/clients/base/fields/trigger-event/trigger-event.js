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
describe('Base.Field.CJ_Forms.FieldTriggerFilter', function() {
    let field;
    let fieldType = 'trigger-event';
    let app;
    let model;
    let fieldName = 'trigger_event';
    let module = 'CJ_Forms';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = createField(model);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('_loadTemplate', function() {
        it('should load the Template', function() {
            sinon.stub(field, '_super');
            field._loadTemplate();
            expect(field.type).toBe('enum');
            expect(field._super).toHaveBeenCalledWith('_loadTemplate');
        });
    });

    describe('_render', function() {
        it('should call the render', function() {
            sinon.stub(field, 'prepareItems');
            sinon.stub(field, '_super');
            field._render();
            expect(field._super).toHaveBeenCalledWith('_render');
            expect(field.prepareItems).toHaveBeenCalled();
        });
    });

    describe('prepareItems', function() {
        it('should  prepare the items object and render the field', function() {
            field.model.set('parent_type', 'enum');
            field.itemsMap = {
                enum: {
                    action: 'details',
                },
            };
            field.prepareItems();
            expect(field.items.action).toEqual('details');
        });
    });
});

