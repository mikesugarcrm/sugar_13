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
describe('View.Views.Base.AuditHeaderpaneView', function() {
    var view;
    var app;
    var title;

    beforeEach(function() {
        app = SUGAR.App;
        var context = new app.Context({
            module: 'Contacts',
            model: app.data.createBean('Contacts')
        });
        view = SugarTest.createView('base', null, 'audit-headerpane', null, context);
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
            var model = view.context.get('model');
            var recordName = 'Dummy_Name';
            var formattedTitle = 'Audit Log for ' + recordName;
            sinon.stub(app.utils, 'getRecordName').withArgs(model).returns(recordName);
            sinon.stub(app.lang, 'get')
                .withArgs('TPL_AUDIT_LOG_TITLE', model.module, {name: recordName})
                .returns(formattedTitle);
            title = view._formatTitle();
            expect(title).toEqual(formattedTitle);
        });

        it('should return default title if default value exists but record name is empty', function() {
            var model = view.context.get('model');
            var defaultTitle = 'Audit Log';
            var defaultValue = 'LBL_AUDIT_TITLE';
            sinon.stub(app.utils, 'getRecordName')
                .withArgs(model).returns('');
            sinon.stub(app.lang, 'get')
                .withArgs(defaultValue, 'Contacts').returns(defaultTitle);
            title = view._formatTitle(defaultValue);
            expect(title).toEqual(defaultTitle);
        });
    });
});
