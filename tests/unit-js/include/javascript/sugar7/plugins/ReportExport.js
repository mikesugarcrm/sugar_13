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
describe('ReportExport plugin:', function() {
    var app;
    var view;
    var apiCallStub;
    var alertShowStub;
    var alertDismissStub;
    var context;
    var moduleName = 'Reports';
    var viewName = 'report-export-modal';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('report-export-modal', 'view', 'base', 'report-export-modal', moduleName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);

        context = SugarTest.app.context.getContext();
        context.set({
            data: {
                orderBy: 'name',
            },
            reportHasChart: false,
            model: new Backbone.Model({
                id: 'testId',
            }),
        });
        context.prepare();

        apiCallStub = sinon.stub(app.api, 'call');
        alertShowStub = sinon.stub(app.alert, 'show');
        alertDismissStub = sinon.stub(app.alert, 'dismiss');

        view = SugarTest.createView('base', moduleName, viewName, null, context, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinon.restore();
        view = null;
        data = null;
    });

    describe('Initialize:', function() {
        it('the plugin should be added', function() {
            expect(_.contains(view.plugins, 'ReportExport')).toBeTruthy();
        });

        it('it should add the plugin functions', function() {
            expect(_.isFunction(view.exportToPdf)).toBeTruthy();
            expect(_.isFunction(view.exportToCsv)).toBeTruthy();
            expect(_.isFunction(view.downloadFileLocally)).toBeTruthy();
            expect(_.isFunction(view.formatDateToString)).toBeTruthy();
            expect(_.isFunction(view.exportErrorCallback)).toBeTruthy();
            expect(_.isFunction(view.exportCompleteCallback)).toBeTruthy();
            expect(_.isFunction(view._buildReportName)).toBeTruthy();
        });
    });

    describe('exportToPdf function', function() {
        it('should show the alert', function() {
            view.exportToPdf({});
            expect(alertShowStub.calledWith('export-to-pdf')).toBeTruthy();
        });

        it('should call the api to generate the pdf', function() {
            view.exportToPdf({});

            var lastApiCallArgs = apiCallStub.lastCall.args;

            expect(lastApiCallArgs[0]).toBe('read');
            expect(lastApiCallArgs[1]).toBe('../../../rest/v10/Reports/testId/base64?orderBy=name');

        });
    });
    describe('exportToCsv function', function() {
        it('should show the alert', function() {
            view.exportToCsv({});
            expect(alertShowStub.calledWith('export-to-csv')).toBeTruthy();
        });

        it('should call the api to generate the csv', function() {
            view.exportToCsv({});

            var lastApiCallArgs = apiCallStub.lastCall.args;

            expect(lastApiCallArgs[0]).toBe('read');
            expect(lastApiCallArgs[1]).toBe('../../../rest/v10/Reports/testId/csv?orderBy=name');

        });
    });
    describe('downloadFileLocally function', function() {
        var sinonSandbox = sinon.createSandbox();
        var stubCreateElement;
        var stubDocumentApendChild;
        var stubDocumentRemoveChild;

        beforeEach(function() {
            stubCreateElement = sinonSandbox.stub(document, 'createElement').callsFake(function(type) {
                return {
                    style: {
                        display: 'display',
                    },

                    click: sinonSandbox.stub(),

                    setAttribute: sinonSandbox.stub(),
                };
            });

            stubDocumentApendChild = sinonSandbox.stub(document.body, 'appendChild').callsFake(function(element) {});
            stubDocumentRemoveChild = sinonSandbox.stub(document.body, 'removeChild').callsFake(function(element) {});
        });

        afterEach(function() {
            stubCreateElement = null;
            stubDocumentApendChild = null;
            stubDocumentRemoveChild = null;
            sinonSandbox.restore();
        });

        it('should show call the appendChild', function() {
            view.downloadFileLocally('testfile.csv', 'aa', 'pdf');
            expect(stubDocumentApendChild.called).toBeTruthy();
        });

        it('should show call the removeChild', function() {
            view.downloadFileLocally('testfile.csv', 'aa', 'pdf');
            expect(stubDocumentRemoveChild.called).toBeTruthy();
        });
        it('should call the click function', function() {
            view.downloadFileLocally('testfile.csv', 'aa', 'pdf');

            expect(stubCreateElement.click).toHaveBeenCalled;

        });
    });
    describe('exportErrorCallback function', function() {
        var sinonSandbox = sinon.createSandbox();
        var stubCloseModal;
        var error = {
            errorThrown: 'timeout',
        };

        beforeEach(function() {
            stubCloseModal = sinonSandbox.stub(view, 'closeModal').callsFake(sinonSandbox.stub());
        });

        afterEach(function() {
            stubCloseModal = null;
            sinonSandbox.restore();
        });

        it('should show the alert', function() {
            view.exportErrorCallback('csv', error);

            expect(alertShowStub.calledWith('export-to-csv-failed')).toBeTruthy();
        });

        it('should dismiss the alert', function() {
            view.exportErrorCallback('csv', error);

            expect(alertDismissStub.calledWith('export-to-csv-failed')).toBeTruthy();
        });

        it('should close the modal', function() {
            view.exportErrorCallback('csv', error);

            expect(stubCloseModal.called).toBeTruthy();
        });
    });
});
