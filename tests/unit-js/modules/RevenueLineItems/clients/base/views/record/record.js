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
if (!(fixtures)) {
    var fixtures = {};
}
// Make this play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if (!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
fixtures.metadata.currencies = {
    "-99": {
        id: '-99',
        symbol: "$",
        conversion_rate: "1.0",
        iso4217: "USD"
    },
    //Because obviously everyone loves 1970's Jackson5 hits
    "abc123": {
        id: 'abc123',
        symbol: "€",
        conversion_rate: "0.9",
        iso4217: "EUR"
    }
};

describe("RevenueLineItems.Base.View.Record", function() {
    var app,
        layout,
        view,
        options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: "commit_stage"
                            }
                        ]
                    }
                ]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.loadPlugin('CommittedDeleteWarning');
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');

        layout = SugarTest.createLayout('base', 'RevenueLineItems', 'record', {});
        view = SugarTest.createView('base', 'RevenueLineItems', 'record', options.meta, null, true, layout);
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(app.utils, 'hideForecastCommitStageField').callsFake(function() {});
        });

        it('should add HistoricalSummary plugin', function() {
            expect(view.plugins).toContain('HistoricalSummary');
        });

        it('should add CommittedDeleteWarning plugin', function() {
            expect(view.plugins).toContain('CommittedDeleteWarning');
        });

        it('should add MassQuote plugin', function() {
            expect(view.plugins).toContain('MassQuote');
        });

        it('should call app.utils.hideForecastCommitStageField', function() {
            view.initialize(options);

            expect(app.utils.hideForecastCommitStageField).toHaveBeenCalled();
        });
    });

    describe('_handleDuplicateBefore', function() {
        var new_model;
        beforeEach(function() {
            new_model = new Backbone.Model();
        });

        afterEach(function() {
            new_model = undefined;
        });

        it('should unset quote_id and quote_name', function() {
            new_model.set({quote_id: '123', quote_name: 'name'});

            view._handleDuplicateBefore(new_model);

            expect(new_model.attributes.quote_id).toBeUndefined();
            expect(new_model.attributes.quote_name).toBeUndefined();
        });
    });
})
