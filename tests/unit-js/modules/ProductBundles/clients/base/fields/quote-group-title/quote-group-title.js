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
describe('ProductBundles.Base.Fields.QuoteGroupTitle', function() {
    var field;
    var fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-group-title',
            label: 'testLbl',
            css_class: 'test-css-class'
        };
        field = SugarTest.createField('base', 'quote-group-title', 'quote-group-title',
            'detail', fieldDef, 'ProductBundles', null, null, true);

        sinon.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('initialize()', function() {
        it('should set css_class if it exists', function() {
            field.initialize({
                def: fieldDef
            });
            expect(field.css_class).toBe('test-css-class');
        });
    });
});
