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
describe('Quotes.Base.Fields.QuoteDataActionmenu', function() {
    var field;
    var fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-data-actionmenu',
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };
        field = SugarTest.createField('base', 'quote-data-actionmenu', 'quote-data-actionmenu',
            'detail', fieldDef, 'Quotes', null, null, true);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('_getChildFieldsMeta()', function() {
        it('should return a copy of the buttons', function() {
            expect(field._getChildFieldsMeta()).toEqual(['button1']);
        });
    });
});
