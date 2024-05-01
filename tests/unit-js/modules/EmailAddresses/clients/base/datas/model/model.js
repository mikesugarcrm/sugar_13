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

describe('Data.Base.EmailAddressesModel', function() {
    var app;
    var optedOutConfig;

    beforeEach(function() {
        app = SugarTest.app;
        optedOutConfig = app.config.newEmailAddressesOptedOut;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', 'EmailAddresses', true, false);
        app.data.declareModels();
    });

    afterEach(function() {
        app.config.newEmailAddressesOptedOut = optedOutConfig;
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    using('default opt out', [true, false], function(flag) {
        it('should default `opt_out` to config value', function() {
            var model;

            app.config.newEmailAddressesOptedOut = flag;

            model = app.data.createBean('EmailAddresses');

            expect(model.getDefault('opt_out')).toBe(flag);
            expect(model.get('opt_out')).toBe(flag);
        });

        it('should set `opt_out` to passed in value', function() {
            var model;

            app.config.newEmailAddressesOptedOut = flag;

            model = app.data.createBean('EmailAddresses', {opt_out: !flag});

            expect(model.getDefault('opt_out')).toBe(flag);
            expect(model.get('opt_out')).toBe(!flag);
        });
    });
});
