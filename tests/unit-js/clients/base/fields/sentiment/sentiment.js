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
describe('Base.fields.sentiment', function() {
    var app;
    var field;
    var beanType = 'Calls';
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(beanType);
        field = SugarTest.createField(
            'base',
            'sentiment',
            'sentiment',
            'detail',
            {},
            beanType,
            model
        );
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        model = null;
        field = null;
    });

    describe('mapSentiment', function() {
        using('valid values',
            [
                [1.4, 'styleguide/assets/img/sentiment/Positive.svg', 'LBL_SENTIMENT_POSITIVE'],
                [-1.4, 'styleguide/assets/img/sentiment/Negative.svg', 'LBL_SENTIMENT_NEGATIVE'],
                [1.3, 'styleguide/assets/img/sentiment/Neutral.svg', 'LBL_SENTIMENT_NEUTRAL'],
                [-1.3, 'styleguide/assets/img/sentiment/Neutral.svg', 'LBL_SENTIMENT_NEUTRAL']
            ],
            function(value, expectedIcon, expectedLabel) {
                it('Should set the correct icon and label.', function() {
                    field.name = 'fieldname';
                    field.model.set('fieldname', value);
                    field.mapSentiment();
                    expect(field.sentimentIcon).toBe(expectedIcon);
                    expect(field.sentimentLabel).toBe(expectedLabel);
                });
            });
    });
});
