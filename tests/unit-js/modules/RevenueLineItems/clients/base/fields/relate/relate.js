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
describe('RevenueLineItems.Base.Field.Relate', function() {
    var field;
    var fieldDef;
    var fieldModel;
    var app;

    beforeEach(function() {
        app = SugarTest.app;

        fieldDef = {
            name: 'opportunity_name',
            type: 'relate',
        };

        fieldModel = new Backbone.Model({
            id: 'test',
        });

        field = SugarTest.createField('base', 'opportunity_name', 'relate',
            'detail', fieldDef, 'RevenueLineItems', fieldModel, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('bindDataChange()', function() {
        it('checking filter_relate when account_id is present to save RLI', function() {

            fieldDef.def = {
                filter_relate: {
                    'account_id': 'account_id',
                },
            };

            fieldModel.set('account_id', 'test');

            fieldDef.model = fieldModel;

            field.initialize(fieldDef);

            expect(field.def.filter_relate).toEqual(fieldDef.def.filter_relate);

        });

        it('test to create a basic RLI when account_id is not passed', function() {

            fieldDef.def = {
                test: 'test',
                filter_relate: {
                    'account_id': 'account_id',
                },
            };

            fieldDef.model = fieldModel;

            field.initialize(fieldDef);
            expect(field.def.filter_relate).toBeUndefined(fieldDef.def.filter_relate);
            expect(field.def.test).toBe('test');

        });
    });
});
