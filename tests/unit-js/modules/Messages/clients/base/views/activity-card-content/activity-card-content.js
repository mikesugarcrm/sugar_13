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
describe('View.Views.Base.Messages.ActivityCardContentView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'activity-card-content', 'Messages');
        view = SugarTest.createView('base', 'Messages', 'activity-card-content');
        view.activity = app.data.createBean('');
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('getMessageDate', function() {
        using('different end date', [
            {
                date_end: '2020-01-01 00:00:00',
                formatUserStr: '01/01/2020 12:00am',
                expected: '01/01/2020 12:00am',
            },
            {
                date_end: '',
                formatUserStr: '01/01/2020 12:00am',
                expected: '',
            },
        ], function(values) {
            it('should format end_date correctly', function() {
                sinon.stub(app.date.fn, 'formatUser').returns(values.formatUserStr);

                view.activity.set('date_end', values.date_end);
                var result = view.getMessageDate();

                expect(result).toEqual(values.expected);
            });
        });
    });

    describe('getStatusMessage', function() {
        using('different status data', [
            {
                status: 'Completed',
                messageDate: '01/01/2020 12:00am',
                expected: '(LBL_ACTIVITY_STATUS_TEXT LBL_ACTIVITY_FINISHED) 01/01/2020 12:00am'
            },
            {
                status: 'In Progress',
                messageDate: '01/01/2020 12:00am',
                expected: '(LBL_ACTIVITY_STATUS_TEXT LBL_ACTIVITY_IN_PROGRESS) 01/01/2020 12:00am'
            }
        ], function(values) {
            it('should return the status message', function() {
                view.activity.set('status', values.status);
                sinon.stub(view, 'getMessageDate').returns(values.messageDate);

                var actual = view.getStatusMessage();

                expect(actual).toEqual(values.expected);
            });
        });
    });
});
