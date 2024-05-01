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
describe('View.Views.Base.ActivityCardDetailView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        view = SugarTest.createView('base', '', 'activity-card-detail');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('setDateDetails', function() {
        it('should set date details', function() {
            view.detailDay = '';
            view.detailDateTime = '';

            sinon.stub(app.date, 'getUserDateFormat').returns('YYYY-MM-DD');
            sinon.stub(app.date, 'getUserTimeFormat').returns('hh:mma');

            view.setDateDetails('2021-04-26 12:00:00');

            expect(view.detailDay).toEqual('Monday');
            expect(view.detailDateTime).toEqual('2021-04-26 12:00pm');
        });
    });
});
