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
describe('View.Views.Base.ActivityCardContentView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        view = SugarTest.createView('base', '', 'activity-card-content');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('formatContent', function() {
        using('different text data', [
            {
                text: 'string1\nstring2\n',
                formated: 'string1<br />string2<br />',
            },
            {
                text: 'string1 string2',
                formated: 'string1 string2',
            },
            {
                text: '',
                formated: '',
            },
        ], function(values) {
            it('should format content text', function() {
                var result = view.formatContent(values.text);

                expect(result).toEqual(values.formated);
                result = null;
            });
        });
    });

    describe('initDateDetails', () => {
        using('different date data', [
            {
                dateEntered: '2021-08-25T02:02:33-04:00',
                dateEnteredFormat: '2021/08/25 02:02',
                dateModified: '2021-08-25T02:01:51-04:00',
                dateModifiedFormat: '2021/08/25 01:51',
                diff: true,
            },
            {
                dateEntered: '2021-08-25T02:01:51-04:00',
                dateEnteredFormat: '2021/08/25 02:01',
                dateModified: '2021-08-25T02:01:51-04:00',
                dateModifiedFormat: '2021/08/25 02:01',
                diff: false,
            },
        ], values => {
            it('should set the modified date if date entered and date modified are different', () => {
                let model = app.data.createBean('');
                model.set('date_entered', values.dateEntered);
                model.set('date_modified', values.dateModified);

                view.activity = model;
                sinon.stub(view.activity, 'get')
                    .withArgs('date_entered').returns(values.dateEntered)
                    .withArgs('date_modified').returns(values.dateModified);

                const date = sinon.stub(app, 'date')
                    .withArgs(values.dateEntered).returns({
                        formatUser: () => {
                            return values.dateEnteredFormat;
                        },
                        isValid: () => {
                            return true;
                        }
                    })
                    .withArgs(values.dateModified).returns({
                        formatUser: () => {
                            return values.dateModifiedFormat;
                        },
                        isValid: () => {
                            return true;
                        }
                    });

                view.initDateDetails();

                values.diff ? expect(view.dateModified).toEqual(values.dateModifiedFormat) :
                    expect(view.dateModified).toBeUndefined();
            });
        });
    });
});
