
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

describe("forecastworksheets_view_filter", function () {

    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'ForecastWorksheets', 'filter', null, null, true);
    });

    afterEach(function() {
        view = null;
        app = null;
    });

    describe("when rendering", function() {
        beforeEach(function() {
            sinon.stub(app.view.View.prototype, "_render");
            sinon.stub(view, "_getRangeFilters");
            sinon.stub(view, "_setUpFilters");
        });

        afterEach(function() {
            view._setUpFilters.restore();
            view._getRangeFilters.restore();
            app.view.View.prototype._render.restore();
        });

        it("should set up the filters", function() {
            view._render();
            expect(view._setUpFilters).toHaveBeenCalled();
        });
    });
});
