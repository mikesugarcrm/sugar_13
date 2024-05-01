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
describe('Base.View.HistorySummaryListBottom', function() {
    var app,
        view,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        var context = app.context.getContext();
        view = SugarTest.createView('base', null, 'history-summary-list-bottom', null, context, false, null, true);

        sandbox.stub(app.lang, 'get').callsFake(function() {
            return 'More history...';
        });
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        view = null;
    });

    describe('setShowMoreLabel()', function() {
        it('should populate showMoreLabel with lang string', function() {
            view.setShowMoreLabel();
            expect(view.showMoreLabel).toBe('More history...');
        });
    });
});
