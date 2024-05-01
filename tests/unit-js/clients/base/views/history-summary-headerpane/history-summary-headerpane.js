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
describe('View.Views.Base.HistorySummaryHeaderpaneView', function() {
    var view;
    var app;

    beforeEach(function() {
        app = SUGAR.App;
        var context = new app.Context({
            module: 'Contacts',
            model: app.data.createBean('Contacts')
        });
        var childContext = context.getChildContext();
        view = SugarTest.createView('base', null, 'history-summary-headerpane', null, childContext);
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('_formatTitle', function() {
        it('should return EMPTY string when neither record name nor default value is available', function() {
            title = view._formatTitle();
            expect(title).toEqual('');
        });

        it('should return title with record name if it is available', function() {
            var model = view.context.parent.get('model');
            var recordName = 'Dummy_Name';
            var formattedTitle = 'Historical Summary for ' + recordName;
            sinon.stub(app.utils, 'getRecordName').withArgs(model).returns(recordName);
            sinon.stub(app.lang, 'get')
                .withArgs('TPL_HISTORICAL_SUMMARY', model.module, {name: recordName})
                .returns(formattedTitle);
            title = view._formatTitle();
            expect(title).toEqual(formattedTitle);
        });

        it('should return default title if default value exists but record name is empty', function() {
            var model = view.context.parent.get('model');
            var defaultTitle = 'Historical Summary';
            var defaultValue = 'LBL_HISTORICAL_SUMMARY';
            sinon.stub(app.utils, 'getRecordName')
                .withArgs(model).returns('');
            sinon.stub(app.lang, 'get')
                .withArgs(defaultValue, 'Contacts').returns(defaultTitle);
            title = view._formatTitle(defaultValue);
            expect(title).toEqual(defaultTitle);
        });
    });
});
