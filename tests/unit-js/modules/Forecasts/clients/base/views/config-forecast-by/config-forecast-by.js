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
describe('Forecasts.View.ConfigForecastBy', function() {
    var app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Forecasts', 'config-forecast-by', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('_updateTitleValues()', function() {
        it('should set this.titleSelectedValues', function() {
            view.model.set('forecast_by', 'testValue');
            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('testValue');
        });
    });
});
