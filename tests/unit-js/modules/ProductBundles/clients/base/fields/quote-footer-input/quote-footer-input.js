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
describe('ProductBundles.Base.Fields.QuoteFooterInput', function() {
    var app;
    var field;
    var fieldDef;
    beforeEach(function() {
        app = SugarTest.app;

        fieldDef = {
            type: 'quote-footer-input',
            label: 'testLbl'
        };
        field = SugarTest.createField('base', 'quote-footer-input', 'quote-footer-input',
            'detail', fieldDef, 'ProductBundles', null, null, true);

        sinon.stub(app.currency, 'formatAmountLocale').callsFake(function() {
            return '$0.00';
        });
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('format()', function() {
        it('should set value_amount and value_percent', function() {
            field.format();
            expect(field.value_amount).toBe('$0.00');
            expect(field.value_percent).toBe('0%');
        });
    });
});
