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
describe('Base.Fields.Piiname', function() {
    var app;
    var context;
    var model;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        context = new app.Context();
        model = app.data.createBean();
        context.set({model: model});
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    it('should not format value if parent context does not exist', function() {
        field = SugarTest.createField({
            name: 'piiname',
            type: 'piiname'
        });
        expect(field.format('fieldName')).toEqual('fieldName');
    });

    it('should not format value if field does not exist in model of parent context', function() {
        model.fields = {};
        field = SugarTest.createField({
            name: 'piiname',
            type: 'piiname',
            context: context.getChildContext()
        });
        expect(field.format('fieldName')).toEqual('fieldName');
    });

    it('should format value if field exists in model of parent context', function() {
        model.fields = {fieldName: {label: 'LBL_EMAIL_ADDRESS_PRIMARY'}};
        field = SugarTest.createField({
            name: 'piiname',
            type: 'piiname',
            context: context.getChildContext()
        });
        sinon.stub(app.lang, 'get').withArgs('LBL_EMAIL_ADDRESS_PRIMARY').returns('Dummy Email');
        expect(field.format('fieldName')).toEqual('Dummy Email');
    });
});
