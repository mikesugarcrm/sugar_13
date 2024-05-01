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
describe('View.Fields.LinkFromReportButton', function() {
    var app, context, model, field, parentModel, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'sticky-rowaction');
        SugarTest.loadComponent('base', 'field', 'linkfromreportbutton');
        SugarTest.testMetadata.set();

        context = app.context.getContext({
            module: moduleName
        });
        context.prepare();
        model = context.get('model');

        parentModel = app.data.createBean(moduleName);
        parentModel.set({'module': moduleName, id: '91fff938-b83d-b140-4cb2-52dea87e65bd'});

        context.set({
            'link': 'cases',
            'parentModel' : parentModel
        });

        field = SugarTest.createField('base', 'linkfromreportbutton', 'linkfromreportbutton', 'edit', {
            'type': 'rowaction',
            'tooltip': 'Link'
        }, moduleName, model, context);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
        parentModel = null;
        context = null;
    });

    it('should disable action if the user does not have access', function() {
        var hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function() {
            return false;
        });

        field.render();
        expect(field.def.css_class).toEqual('disabled');
        hasAccessStub.restore();
    });

    describe('selectDrawerCallback', function() {
        var alertShowStub, alertDismissStub, openDrawerStub, hasAccessStub;

        beforeEach(function() {
            hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function() {
                return true;
            });

            alertShowStub = sinon.stub(app.alert, 'show');
            alertDismissStub = sinon.stub(app.alert, 'dismiss');

            SugarTest.app.drawer = {
                open: function() {
                },
                close: function() {
                }
            };
        });

        afterEach(function() {
            openDrawerStub.restore();
            hasAccessStub.restore();
            alertShowStub.restore();
            alertDismissStub.restore();
        });

        it('should not call record_list endpoint when the model is not passed in', function() {
            openDrawerStub = sinon.stub(SugarTest.app.drawer, 'open').callsFake(function(opts, closeCallback) {
                if (closeCallback) {
                    field.selectDrawerCallback(null);
                }
            });

            var apiCallStub = sinon.stub(app.api, 'call').callsFake(function() {});

            field.render();
            field.openSelectDrawer();

            expect(openDrawerStub.called).toBe(true);
            expect(apiCallStub.called).toBe(false);
            apiCallStub.restore();
        });

        it('should not make api call to link records when record_list endpoint returns error', function() {
            var reportModel = new Backbone.Model({id: '91eee938-b83d-b140-4cb2-52dea87e74af'}),
                linkRecordListStub = sinon.stub(field, 'linkRecordList');

            reportModel.module = moduleName;

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*rest\/v10\/Reports\/91eee938-b83d-b140-4cb2-52dea87e74af\/record_list.*/,
                [500, {'Content-Type': 'application/json'},
                    '{"error": "invalid_grant", "error_description": "some desc"}']);

            openDrawerStub = sinon.stub(SugarTest.app.drawer, 'open').callsFake(function(opts, closeCallback) {
                if (closeCallback) {
                    field.selectDrawerCallback(reportModel);
                }
            });

            field.render();
            field.openSelectDrawer();
            SugarTest.server.respond();

            expect(openDrawerStub.called).toBe(true);
            expect(alertDismissStub.lastCall.args[0]).toEqual('listfromreport_loading');
            expect(alertShowStub.lastCall.args[0]).toEqual('server-error');
            expect(linkRecordListStub).not.toHaveBeenCalled();
            linkRecordListStub.restore();
        });

        it('should show warning message if user selects report where module does not match linked module', function() {
            var reportModel = new Backbone.Model({
                    id: '91eee938-b83d-b140-4cb2-52dea87e74af',
                    module: moduleName
                }),
                linkRecordListStub = sinon.stub(field, 'linkRecordList');

            openDrawerStub = sinon.stub(SugarTest.app.drawer, 'open').callsFake(function(opts, closeCallback) {
                if (closeCallback) {
                    field.selectDrawerCallback(reportModel);
                }
            });

            field.render();
            field.openSelectDrawer();
            SugarTest.server.respond();

            expect(openDrawerStub.called).toBe(true);
            expect(alertShowStub.lastCall.args[0]).toEqual('listfromreport-warning');
            expect(linkRecordListStub).not.toHaveBeenCalled();
            linkRecordListStub.restore();
        });

        it('list should be refreshed when records are linked', function() {
            let stubOnLoad = sinon.stub(field.context, 'loadData');
            let reportModel =  new Backbone.Model({id: '91eee938-b83d-b140-4cb2-52dea87e74af'});
            let recordlistResults = {
                id: '990d9be4-0931-9db5-3b02-52e2710cd8f0',
                module_name: 'Reports',
                records: ['137e6b6d-2647-7e57-df30-52dea83c622d']
            };
            let relatedRecordsResults = {
                related_records: {
                    success: ['137e6b6d-2647-7e57-df30-52dea83c622d52dea8bd0e05'],
                    error: []
                },
                record: {}
            };

            reportModel.module = moduleName;
            SugarTest.seedFakeServer();
            SugarTest.server.respondImmediately = true;
            SugarTest.server.respondWith('POST', /.*rest\/v10\/Reports\/91eee938-b83d-b140-4cb2-52dea87e74af\/record_list.*/,
                [200, {'Content-Type': 'application/json'},
                JSON.stringify(recordlistResults)]);
            SugarTest.server.respondWith('POST', /.*rest\/v10\/Contacts\/91fff938-b83d-b140-4cb2-52dea87e65bd\/link\/cases\/add_record_list\/990d9be4-0931-9db5-3b02-52e2710cd8f0.*/,
                [200, {'Content-Type': 'application/json'},
                    JSON.stringify(relatedRecordsResults)]);


            openDrawerStub = sinon.stub(SugarTest.app.drawer, 'open').callsFake(function(opts, closeCallback) {
                if (closeCallback) {
                    field.selectDrawerCallback(reportModel);
                }
            });

            field.render();
            field.openSelectDrawer();

            expect(stubOnLoad).toHaveBeenCalled();
            expect(openDrawerStub.called).toBe(true);
            expect(alertDismissStub.lastCall.args[0]).toEqual('listfromreport_loading');
            expect(alertShowStub.lastCall.args[0]).toEqual('server-success');
            stubOnLoad.restore();
        });
    });

});
