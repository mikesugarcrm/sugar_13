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
describe('Base.Fields.EnumColorcodedForeBkgdField', function() {
    var app;
    var field;
    var fieldName = 'test_enum';
    var model;
    var module = 'Opportunities';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);

        field = SugarTest.createField(
            'base',
            fieldName,
            'enum-colorcoded-fore-bkgd',
            'list',
            {},
            module,
            model,
            null
        );
    });

    afterEach(function() {
        model = null;
        field.dispose();
        field = null;
    });

    describe('initialize', function() {
        it('should set default colors', function() {
            field.initialize({});
            expect(field._defaultColorCodes.length).toEqual(field.colorCount);
            expect(field._defaultColorCodes[0]).toEqual('enum-color1');
        });
    });
});
