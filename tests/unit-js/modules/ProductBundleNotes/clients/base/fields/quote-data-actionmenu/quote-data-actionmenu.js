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
describe('ProductBundleNotes.Base.Fields.QuoteDataActionmenu', function() {
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
            'detail', fieldDef, 'ProductBundleNotes', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('_getChildFieldsMeta()', function() {
        it('should return a copy of the buttons', function() {
            expect(field._getChildFieldsMeta()).toEqual(['button1']);
        });
    });

    describe('toggleSelect()', function() {
        var parentTriggerStub;
        beforeEach(function() {
            parentTriggerStub = sinon.stub();
            field.context.parent = {
                trigger: parentTriggerStub
            };
        });

        afterEach(function() {
            parentTriggerStub = null;
        });

        it('should call trigger on the context.parent', function() {
            field.toggleSelect(true);

            expect(parentTriggerStub).toHaveBeenCalled();
        });

        it('should trigger event mass_collection:add when checked is true', function() {
            field.toggleSelect(true);

            expect(parentTriggerStub).toHaveBeenCalledWith('mass_collection:add', field.model);
        });

        it('should trigger event mass_collection:remove when checked is false', function() {
            field.toggleSelect(false);

            expect(parentTriggerStub).toHaveBeenCalledWith('mass_collection:remove', field.model);
        });
    });
});
