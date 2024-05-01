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
describe('Base.Field.CJ_Forms.ActionType', function() {
    let field;
    let fieldType = 'action-type';
    let app;
    let model;
    let fieldName = 'action_type';
    let module = 'CJ_Forms';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
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

    describe('initialize', function() {
        it('should initialize the enum', function() {
            expect(field.type).toBe('enum');
        });
    });

    describe('modelSyncHandler', function() {
        it('should call reload the enum options and hide/show populate fields panel', function() {
            sinon.stub(field, 'reloadEnumOptions');
            sinon.stub(field, 'hideOrShowPopulateFieldPanel');
            field.modelSyncHandler();
            expect(field.reloadEnumOptions).toHaveBeenCalled();
            expect(field.hideOrShowPopulateFieldPanel).toHaveBeenCalled();
        });
    });

    describe('reloadEnumOptions', function() {
        it('should load the enum options and hide/show email related field', function() {
            sinon.stub(field, 'loadEnumOptions');
            field.reloadEnumOptions();
            expect(field.loadEnumOptions).toHaveBeenCalled();
        });
    });

    describe('loadEnumOptions', function() {
        it('should hide/show email related field', function() {
            sinon.stub(field, 'processAfterEnumLoad');
            field.items = [];
            field.loadEnumOptions();
            expect(field.processAfterEnumLoad).toHaveBeenCalled();
        });
    });

    describe('processAfterEnumLoad', function() {
        it('should update populate fields items and hide/show email related field', function() {
            sinon.stub(field, 'updateItems');
            sinon.stub(field, 'hideOrShowEmailRelatedField');
            sinon.stub(field, 'checkLastRelationship').callsFake(function(module) {
                return true;
            });
            field.processAfterEnumLoad();
            expect(field.updateItems).toHaveBeenCalledWith('Emails');
            expect(field.hideOrShowEmailRelatedField).toHaveBeenCalled();
        });
    });

    describe('updateItems', function() {
        it('should update the items array and set fields to empty', function() {
            field.items = {
                'update_record': '',
            };
            field.model.set(fieldName, 'update_record');
            field.model.set('action_trigger_type', 'create_record');
            field.updateItems('Emails');
            expect(field.items).toEqual([]);
            expect(field.model.get(fieldName)).toEqual('');
            expect(field.model.get('action_trigger_type')).toEqual('');
        });
    });

    describe('getLastRelationship', function() {
        it('should return the last entry in relationship if model is set', function() {
            field.model.set('relationship', [
                'Accounts',
                'Contacts',
                'DRI_Workflow_Templates',
            ]);
            expect(field.getLastRelationship()).toEqual('DRI_Workflow_Templates');
        });
    });

    describe('checkLastRelationship', function() {
        it('should return true if last selected relationship is valid', function() {
            sinon.stub(field, 'getLastRelationship').callsFake(function(module) {
                return {
                    'module': 'DRI_Workflow_Templates',
                    'relationship': 'self',
                };
            });
            expect(field.checkLastRelationship('DRI_Workflow_Templates')).toBe(true);
        });
    });

    describe('hideOrShowPopulateFieldPanel', function() {
        it('should hide or show the populate field panel on the base of action_type', function() {
            field.model.set('main_trigger_type', 'smart_guide_to_sugar_action');
            field.model.set('action_type', 'update_record');
            field.model.set('parent_id', '123');
            field.action = 'edit';
            let object = {
                render: sinon.stub()
            };
            field.view = {
                trigger: sinon.stub(),
                getField: sinon.stub().returns(object),
            };
            field.hideOrShowPopulateFieldPanel();
            expect(field.view.trigger).toHaveBeenCalled();
            expect(field.view.getField).toHaveBeenCalled();
        });
    });

    describe('hideOrShowEmailRelatedField', function() {
        it('should hide or show the Email related fields according to Relationship', function() {
            field.name = 'action_type';
            field.model.set('main_trigger_type', 'smart_guide_to_sugar_action');
            field.model.set('action_type', 'update_record');
            let object = {
                render: sinon.stub()
            };
            field.view = {
                trigger: sinon.stub(),
                getField: sinon.stub().returns(object)
            };
            let hideOrShow = true;
            sinon.stub(app.CJFieldHelper, '_showField');
            field.hideOrShowEmailRelatedField(hideOrShow);
            expect(field.view.trigger).toHaveBeenCalled();
            expect(field.view.getField).toHaveBeenCalled();
            expect(app.CJFieldHelper._showField).toHaveBeenCalled();
        });
    });
});

