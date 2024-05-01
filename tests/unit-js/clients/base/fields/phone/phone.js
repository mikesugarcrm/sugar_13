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
describe("Base.Fields.Phone", function() {
    var app;
    var field;

    beforeEach(function(){
        app = SugarTest.app;
        field = SugarTest.createField("base", "phone", "phone", "detail", {});
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.metadata.reset();
        field = null;
    });
    it("should figure out if skype is enabled", function() {
        let metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo').returns({
            system_skypeout_on: true
        });
        field.initialize(field.options);

        expect(field.dialoutEnabled).toBeTruthy();
        metamock.restore();
        metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo').returns({
            system_skypeout_on: false
        });
        field.initialize(field.options);

        expect(field.dialoutEnabled).toBeFalsy();
        metamock.restore();
    });


    describe('initialize (ccp)', function() {
        using('different values for the CCP being enabled',
              [false, true],
              function(ccpEnabled) {
            it('should set the ccpEnabled field property appropriately', function() {
                window.connect = {
                    core: {
                        initialized: ccpEnabled
                    }
                };
                field.initialize(field.options);
                expect(field.ccpEnabled).toEqual(ccpEnabled);
                window.connect = null;
            });
        });
    });
});
