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
describe('Base.Field.CJ_Forms.Relationship', function() {
    let field;
    let fieldType = 'relationship';
    let app;
    let model;
    let fieldName = 'relationship';
    let module = 'CJ_Forms';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {},
        module, model, null, true);
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

    describe('_changeParentId', function() {
        it('should set the model activityModule field', function() {
            field.stopListening(field.model);
            sinon.stub(field, 'changeActivityModule').callsFake(function() {
                field.model.set('activity_module', 'DRI_Workflows');
            });
            field.model.set('parent_type','DRI_Workflow_Templates');
            field.model.set('parent_id',' ');
            field._changeParentId();
            expect(field.model.get('activity_module')).toBe('DRI_Workflows');
        });
    });

    describe('getRelationOptionLabel', function() {
        it('should return the relationship option label', function() {
            sinon.stub(app.lang, 'get').callsFake(function() {
                return 'Relationship';
            });
            expect(field.getRelationOptionLabel({
                vname: 'relationship',
                name: 'relationship_field'
            }, 'CJ_Forms')).toEqual('Relationship (relationship_field)');
        });
    });

    describe('getRelationshipsForModule', function() {
        it('should return the relationships for the module', function() {
            sinon.stub(app.lang, 'get').callsFake(function() {
                return 'DRI_Workflows';
            });
            expect(field.getRelationshipsForModule('DRI_Workflows')).
                toEqual({self: 'self (DRI_Workflows)'});
        });
    });

    describe('_getTreeNodeID', function() {
        it('should return the tree node id', function() {
            field.level = 1;
            expect(field._getTreeNodeID('CJ_WebHooks')).
                toEqual('cj_rel_jstree_node_CJ_WebHooks_1');
        });
    });

    describe('getSortedArrayFromRelationshipObject', function() {
        it('get the dropdown option list in object form', function() {
            expect(field.getSortedArrayFromRelationshipObject({
                'self': 5,
                'DRI_Workflows': 25,
                'CJ_Forms': 20,
                'CJ_WebHooks': 15,
            })).toEqual([
                {
                    key: 'self',
                    val: 5,
                },
                {
                    key: 'CJ_WebHooks',
                    val: 15,
                },
                {
                    key: 'CJ_Forms',
                    val: 20,
                },
                {
                    key: 'DRI_Workflows',
                    val: 25,
                },
            ]);
        });
    });
});
