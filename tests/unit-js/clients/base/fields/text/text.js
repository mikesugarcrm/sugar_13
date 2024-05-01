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
describe('Base.Fields.Text', function() {

    var field;

    it('setup field', function() {
        field = SugarTest.createField('base', 'foo', 'text', 'edit');
    });

    using('different data types', [
        { before: 10, after: '10' },
        { before: 0, after: '0' },
        { before: 12.2, after: '12.2' },
        { before: true, after: '1' },
        { before: false, after: '0' },
        { before: ['a', 'b', 'c', 'd'], after: 'a,b,c,d' },
        { before: {'a':'b', 'c':'d'}, after: '' }
    ], function(provider) {
        it('should convert the value into a string', function() {
            expect(field.format(provider.before)).toEqual(provider.after);
        });
    });
});
