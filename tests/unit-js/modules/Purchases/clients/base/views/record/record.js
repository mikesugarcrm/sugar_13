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

describe('Purchases.Base.View.Record', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        view = SugarTest.createView('base', 'Purchases',
            'record', {}, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        app = null;
    });

    describe('setupDuplicateFields', function() {
        it('should ignore calculated fields', function() {
            var attributes = {
                start_date: '2020-06-11',
                end_date: '2020-06-12'
            };
            var prefill = app.data.createBean('Purchases', attributes);
            view.setupDuplicateFields(prefill);
            expect(prefill.has('start_date')).toBeFalsy();
            expect(prefill.has('end_date')).toBeFalsy();
        });
    });
});
