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
describe('Reports.Base.Layouts.Record', function() {
    var app;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', 'Reports', 'record', {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();
        app = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    it('Should get the saved reports meta', function() {
        var callStub = sinon.stub(app.api, 'call');

        var params = {
            track: true,
            trackAction: 'detailview',
        };
        var url = app.api.buildURL('Reports/activeSavedReport', 'test-id', {}, params);

        layout.model.set('id', 'test-id');

        layout.initialize({
            context: context
        });

        expect(callStub.getCall(0).args[0]).toEqual('read');
        expect(callStub.getCall(0).args[1]).toEqual(url);

        callStub.restore();
    });

    describe('_manageReportDefChanged()', function() {
        it('should calls app.alert.show()', function() {
            var reportData = {
                lastChangeInfo: {
                    lastReportSeenDate: '10/18/2022 10:10:00',
                    lastReportModifiedDate: '10/18/2022 10:30:00',
                    currentUserId: '1',
                    modifiedUserID: 'will',
                },
            };

            var spy = sinon.stub(app.alert, 'show');

            layout._manageReportDefChanged(reportData);

            expect(spy).toHaveBeenCalled();
        });
    });
});
