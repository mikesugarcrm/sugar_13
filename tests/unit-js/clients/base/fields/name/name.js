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
describe('Base.Field.Name', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'name', 'name', 'detail');
    });

    afterEach(function() {
        field.dispose();
    });

    describe('Render', function() {
        using('different view names and values', [
            ['audit', undefined, false],
            ['preview', undefined, true],
            ['preview', false, false],
            ['preview', true, true],
            ['other', undefined, undefined]
        ], function(view, linkValue, expected) {
            it('should set `def.link` appropriately based on view', function() {
                field.view.name = view;
                field.def.link = linkValue;
                field.render();
                expect(field.def.link).toBe(expected);
            });
        });
    });
});
