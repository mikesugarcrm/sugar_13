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
describe("Emails.Views.Compose", function() {
    var app,
        view,
        dataProvider,
        sandbox;

    beforeEach(function() {
        var context,
            viewName = 'compose',
            moduleName = 'Emails';
        app = SugarTest.app;
        app.drawer = { on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('compose-senderoptions', 'view', 'base', 'compose-senderoptions', moduleName);
        SugarTest.loadComponent('base', 'view', 'record');

        // set UserSignatures metadata
        SugarTest.testMetadata.updateModuleMetadata('UserSignatures', {
            fields: {
                name: {
                    name: "name",
                    vname: "LBL_NAME",
                    type: "varchar",
                    len: 255,
                    comment: "Name of this bean"
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: "bc6fc50d9d0d3064f5d522d9e15968fa"
        });

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare(true);

        view = SugarTest.createView('base', moduleName, viewName, null, context, true);

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        app.drawer = undefined;
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Render', function() {
        var setTitleStub, prepopulateStub;

        beforeEach(function() {
            setTitleStub = sandbox.stub(view, 'setTitle');
            prepopulateStub = sandbox.stub(view, 'prepopulate');
        });

        it('No prepopulate on context - title should be set no fields pre-populated', function() {
            sandbox.stub(view, 'notifyConfigurationStatus');
            view._render();
            expect(setTitleStub).toHaveBeenCalled();
            expect(prepopulateStub.callCount).toEqual(0);
        });

        it('prepopulate on context - call is made to populate them', function() {
            var dummyPrepopulate = {subject: 'Foo!'};

            sandbox.stub(view, 'notifyConfigurationStatus');
            view.context.set('prepopulate', dummyPrepopulate);
            view._render();
            expect(prepopulateStub.callCount).toEqual(1);
            expect(prepopulateStub.lastCall.args).toEqual([dummyPrepopulate]);
        });

        it('No email client preference error - should not disable the send button or alert user', function() {
            var alertShowStub = sandbox.stub(app.alert, 'show');

            sandbox.stub(app.user, 'getPreference')
                .withArgs('email_client_preference')
                .returns({type: 'sugar'});

            view._render();

            expect(alertShowStub.callCount).toBe(0);
        });

        it('Email client preference error - should disable the send button and alert user', function() {
            var alertShowStub = sandbox.stub(app.alert, 'show'),
                sendField = {setDisabled: $.noop},
                spyOnField = sandbox.spy(sendField, 'setDisabled');

            sandbox.stub(app.user, 'getPreference')
                .withArgs('email_client_preference')
                .returns({type: 'sugar', error: {code: 101, message: 'LBL_EMAIL_INVALID_USER_CONFIGURATION'}});
            sandbox.stub(view, 'getField')
                .withArgs('send_button')
                .returns(sendField);

            view._render();

            expect(alertShowStub.callCount).toBe(1);
            expect(spyOnField.calledOnce).toBe(true);
        });
    });

    describe('prepopulate', function() {
        var populateRelatedStub, modelSetStub, populateForModulesStub, flag;

        beforeEach(function() {
            flag = false;
            populateRelatedStub = sandbox.stub(view, 'populateRelated').callsFake(function() {
                flag = true;
            });

            populateForModulesStub = sandbox.stub(view, '_populateForModules').callsFake(function() {
                flag = true;
            });

            modelSetStub = sandbox.stub(view.model, 'set').callsFake(function() {
                flag = true;
            });
        });

        it("Should trigger recipient add on context if to_addresses, cc_addresses, or bcc_addresses value is passed in.", function() {
            runs(function() {
                view.prepopulate({
                    to_addresses: [{email: "to@foo.com"}, {email: "too@foo.com"}],
                    cc_addresses: [{email: "cc@foo.com"}],
                    bcc_addresses: [{email: "bcc@foo.com"}]
                });
            });

            waitsFor(function() {
                return flag;
            }, 'model.set() should have been called but timeout expired', 1000);

            runs(function() {
                expect(modelSetStub.callCount).toBe(3); // once for each recipient type passed in
            });
        });

        it("should call populateRelated if related value passed", function () {
            runs(function() {
                view.prepopulate({related: {id: '123'}});
            });

            waitsFor(function() {
                return flag;
            }, 'populateRelated() should have been called but timeout expired', 1000);

            runs(function() {
                expect(populateRelatedStub.callCount).toBe(1);
                expect(populateForModulesStub.callCount).toBe(1);
            });
        });

        it("should set other values if passed", function () {
            runs(function() {
                view.prepopulate({foo: 'bar'});
            });

            waitsFor(function() {
                return flag;
            }, 'model.set() should have been called but timeout expired', 1000);

            runs(function() {
                expect(modelSetStub.calledOnce).toBe(true);
            });
        });
    });

    describe("populateRelated", function () {
        var relatedModel, fetchedModel, parentId, parentValue, inputValues, fetchedValues;

        beforeEach(function () {
            inputValues = {
                id: '123',
                name: 'Input Name'
            };
            fetchedValues = {
                id: inputValues.id,
                name: 'Fetched Name'
            };
            relatedModel = new Backbone.Model(inputValues);
            fetchedModel = new Backbone.Model(fetchedValues);
            relatedModel.module = fetchedModel.module = 'foo';
            sandbox.stub(relatedModel, 'fetch').callsFake(function(params) {
                params.success(fetchedModel);
            });
            sandbox.stub(view, 'getField').callsFake(function() {
                return {
                    isAvailableParentType: function() {
                        return true;
                    },
                    setValue: function(model) {
                        parentId = model.id;
                        parentValue = model.value;
                    }
                };
            });
        });

        afterEach(function () {
            parentId = undefined;
            parentValue = undefined;
        });

        it("should set the parent_name field with id and name on the relatedModel passed in", function () {
            view.populateRelated(relatedModel);
            expect(parentId).toEqual(inputValues.id);
            expect(parentValue).toEqual(inputValues.name);
        });

        it("should set the parent_name field with id and name on the fetched model when no name on the relatedModel passed in", function () {
            relatedModel.unset('name');
            view.populateRelated(relatedModel);
            expect(parentId).toEqual(fetchedValues.id);
            expect(parentValue).toEqual(fetchedValues.name);
        });

        it("should not set the parent_name field at all if no id on related Model", function () {
            relatedModel.unset('id');
            view.populateRelated(relatedModel);
            expect(parentId).toBeUndefined();
            expect(parentValue).toBeUndefined();
        });
    });

    describe("populateForCases", function () {
        var flag,
            relatedModel,
            fetchedModel,
            inputValues,
            sandbox = sinon.createSandbox(),
            configStub,
            caseSubjectMacro = '[CASE:%1]';


        beforeEach(function () {
            flag = false;
            configStub = sandbox.stub(app.metadata, 'getConfig').callsFake(function() {
                return {
                    'inboundEmailCaseSubjectMacro': caseSubjectMacro,
                }
            });

            inputValues = {
                id: '123',
                case_number: '100',
                name: 'My Case'
            };

            relatedModel = app.data.createBean('Cases', inputValues);
            sandbox.stub(relatedModel, 'fetch').callsFake(function(params) {
                params.success(relatedModel);
            });
        });

        afterEach(function() {
            configStub.restore();
        });

        it("should populate only the subject and when cases does not have any related contacts", function () {
            var tmpModel = new Backbone.Model(),
                relatedCollectionStub;

            relatedModel.getRelatedCollection = function () {
                return tmpModel;
            };

            relatedCollectionStub = sandbox.stub(tmpModel, 'fetch').callsFake(function() {
                flag = true;
            });

            runs(function () {
                view._populateForCases(relatedModel);
            });

            waitsFor(function () {
                return flag;
            }, 'fetch() should have been called but timeout expired', 1000);

            runs(function () {
                expect(view.model.get('name')).toEqual('[CASE:100] My Case');
                expect(relatedCollectionStub.callCount).toBe(1);
            });
        });

        it("should populate both the subject and 'to' field when cases has related contacts", function () {
            var contact = app.data.createBean('Contacts'),
                toAddresses = [{bean: contact}];
            toAddresses.trim = function() { return ''; };

            view.model.set('to_addresses', toAddresses);
            view._populateForCases(relatedModel);
            expect(view.model.get('name')).toEqual('[CASE:100] My Case');
            expect(view.model.get('to_addresses')).toEqual(toAddresses);
        });
    });

    describe('Sender Options', function() {
        var toggleFieldVisibilitySpy,
            isSenderOptionButtonActive;

        beforeEach(function () {
            toggleFieldVisibilitySpy = sandbox.spy(view, '_toggleFieldVisibility');
            sandbox.stub(view, '_renderSenderOptions').callsFake(function() {
                var template = app.template.getView("compose-senderoptions", view.module);
                view.$el.append(template({'module' : view.module}));
            });
        });

        isSenderOptionButtonActive = function(fieldName) {
            var selector = '[data-toggle-field="' + fieldName + '"]';
            return view.$(selector).hasClass('active');
        };

        using('CC/BCC values',
            [
                [
                    {cc_addresses: [], bcc_addresses: []},
                    {ccActive: false, bccActive: false}
                ],
                [
                    {cc_addresses: ['foo@bar.com'], bcc_addresses: []},
                    {ccActive: true, bccActive: false}
                ],
                [
                    {cc_addresses: [], bcc_addresses: ['foo@bar.com']},
                    {ccActive: false, bccActive: true}
                ],
                [
                    {cc_addresses: ['foo@bar.com'], bcc_addresses: ['bar@foo.com']},
                    {ccActive: true, bccActive: true}
                ]
            ],
            function(value, result) {
                it('should add sender options on render and initialize cc/bcc fields appropriately', function() {
                    view.model.set(value);
                    view._render();

                    // check buttons
                    expect(isSenderOptionButtonActive('cc_addresses')).toBe(result.ccActive);
                    expect(isSenderOptionButtonActive('bcc_addresses')).toBe(result.bccActive);

                    // check field visibility
                    expect(toggleFieldVisibilitySpy.firstCall.args).toEqual(["cc_addresses", result.ccActive]);
                    expect(toggleFieldVisibilitySpy.secondCall.args).toEqual(["bcc_addresses", result.bccActive]);
                });
            }
        );

        it("should toggle sender option between active/inactive state when active flag not specified", function () {
            var fieldName = 'cc_addresses';
            view._render();
            expect(isSenderOptionButtonActive(fieldName)).toBe(false);
            view.toggleSenderOption(fieldName);
            expect(isSenderOptionButtonActive(fieldName)).toBe(true);
            view.toggleSenderOption(fieldName);
            expect(isSenderOptionButtonActive(fieldName)).toBe(false);
        });

        it("should set sender option to active when active flag is true", function () {
            var fieldName = 'cc_addresses';
            view._render();
            expect(isSenderOptionButtonActive(fieldName)).toBe(false);
            view.toggleSenderOption(fieldName, true);
            expect(isSenderOptionButtonActive(fieldName)).toBe(true);
            view.toggleSenderOption(fieldName, true);
            expect(isSenderOptionButtonActive(fieldName)).toBe(true);
        });

        it("should set sender option to inactive when active flag is false", function () {
            var fieldName = 'cc_addresses';
            view._render();
            expect(isSenderOptionButtonActive(fieldName)).toBe(false);
            view.toggleSenderOption(fieldName, false);
            expect(isSenderOptionButtonActive(fieldName)).toBe(false);
        });

        it("should toggle sender option between active/inactive state when cc/bcc buttons clicked", function () {
            view._render();
            expect(isSenderOptionButtonActive('bcc_addresses')).toBe(false);
            view.$('[data-toggle-field="bcc_addresses"]').click();
            expect(isSenderOptionButtonActive('bcc_addresses')).toBe(true);
            view.$('[data-toggle-field="bcc_addresses"]').click();
            expect(isSenderOptionButtonActive('bcc_addresses')).toBe(false);
        });
    });

    describe('saveModel', function() {
        var apiCallStub, alertShowStub, alertDismissStub;

        beforeEach(function() {
            apiCallStub = sandbox.stub(app.api, 'call').callsFake(function(method, myURL, model, options) {
                options.success(model, null, options);
            });
            alertShowStub = sandbox.stub(app.alert, 'show');
            alertDismissStub = sandbox.stub(app.alert, 'dismiss');
            sandbox.stub(view, 'setMainButtonsDisabled');

            view.model.off('change');
        });

        it('should call mail api with correctly formatted model', function() {
            var actualModel,
                expectedStatus = 'ready',
                to_addresses   = new Backbone.Collection([{id: "1234", email: "foo@bar.com"}]);

            view.model.set('to_addresses', to_addresses);
            view.model.set('foo', 'bar');
            view.saveModel(expectedStatus, 'pending message', 'success message');

            expect(apiCallStub.lastCall.args[0]).toEqual('create');
            expect(apiCallStub.lastCall.args[1]).toMatch(/.*\/Mail/);

            actualModel = apiCallStub.lastCall.args[2];
            expect(actualModel.get('status')).toEqual(expectedStatus); //status set on model
            expect(actualModel.get('to_addresses')).toEqual(to_addresses); //email formatted correctly
            expect(actualModel.get('foo')).toEqual('bar'); //any other model attributes passed to api

            to_addresses = undefined;
        });

        it('should display error alert on save if there are invalid recipient email addresses', function() {
            var expectedStatus = 'ready';
            var toAddresses = new Backbone.Collection([{id: '1234', email: 'foo&bar.com', _invalid: true}]);

            view.model.set('to_addresses', toAddresses);
            view.saveModel(expectedStatus, 'pending message', 'success message');

            expect(apiCallStub).not.toHaveBeenCalled();
            expect(alertShowStub.firstCall.args[0]).toEqual('mail_invalid_recipients');
        });

        it('should show pending message before call, then after call dismiss that message and show success', function() {
            var pending = 'pending message',
                success = 'success message';

            view.saveModel('ready', pending, success);

            expect(alertShowStub.firstCall.args[1].title).toEqual(pending);
            expect(alertDismissStub.firstCall.args[0]).toEqual(alertShowStub.firstCall.args[0]);
            expect(alertShowStub.secondCall.args[1].messages).toEqual(success);
        });
    });

    describe('Send', function() {
        var saveModelStub, alertShowStub;

        beforeEach(function() {
            saveModelStub = sandbox.stub(view, 'saveModel');
            alertShowStub = sandbox.stub(app.alert, 'show');

            view.model.off('change');
        });

        it('should send email when to, subject and html_body fields are populated', function() {
            view.model.set('to_addresses', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when cc, subject and html_body fields are populated', function() {
            view.model.set('cc_addresses', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when bcc, subject and html_body fields are populated', function() {
            view.model.set('bcc_addresses', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should show error alert when address fields are empty', function() {
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(false);
            expect(alertShowStub.called).toBe(true);
        });

        it('should show confirmation alert message when subject field is empty', function() {
            view.model.unset('name');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when html_body field is empty', function() {
            view.model.set('name', 'foo');
            view.model.unset('description_html');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when subject and html_body fields are empty', function() {
            view.model.unset('name');
            view.model.unset('description_html');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });
    });

    describe("insert templates", function() {
        describe("replacing templates", function() {
            var insertTemplateAttachmentsStub,
                createBeanCollectionStub,
                updateEditorWithSignatureStub;

            beforeEach(function() {
                insertTemplateAttachmentsStub = sandbox.stub(view, 'insertTemplateAttachments');
                createBeanCollectionStub      = sandbox.stub(app.data, 'createBeanCollection').callsFake(function() {
                    return {fetch: $.noop};
                });
                updateEditorWithSignatureStub = sandbox.stub(view, "_updateEditorWithSignature");

                view.model.off('change');
            });

            it('should not populate editor if template parameter is not an object', function() {
                view.insertTemplate(null);
                expect(createBeanCollectionStub.callCount).toBe(0);
                expect(insertTemplateAttachmentsStub.callCount).toBe(0);
                expect(updateEditorWithSignatureStub.callCount).toBe(0);
                expect(view.model.get('name')).toBeUndefined();
                expect(view.model.get('description_html')).toBeUndefined();
            });

            it("should not set content of subject when the template doesn't include a subject", function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    templateModel = new Bean({
                        id:        '1234',
                        body_html: bodyHtml
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBeUndefined();
                expect(view.model.get('description_html')).toBe(bodyHtml);
            });

            it('should set content of editor with html version of template', function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    subject       = 'This is my subject',
                    templateModel = new Bean({
                        id:        '1234',
                        subject:   subject,
                        body_html: bodyHtml
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBe(subject);
                expect(view.model.get('description_html')).toBe(bodyHtml);
            });

            it('should set content of editor with text only version of template', function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    bodyText      = 'Test',
                    subject       = 'This is my subject',
                    templateModel = new Bean({
                        id:         '1234',
                        subject:    subject,
                        body_html:  bodyHtml,
                        body:       bodyText,
                        text_only:  1
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBe(subject);
                expect(view.model.get('description_html')).toBe(bodyText);
            });

            it("should call to insert the signature that was marked as the last one selected", function() {
                var bodyHtml      = '<h1>Test</h1>',
                    subject       = 'This is my subject',
                    templateModel = new app.Bean({
                        id:        '1234',
                        subject:   subject,
                        body_html: bodyHtml
                    }),
                    signature     = new app.Bean({id: "abcd"});

                view._lastSelectedSignature = signature;

                view.insertTemplate(templateModel);
                expect(updateEditorWithSignatureStub).toHaveBeenCalledWith(signature);
            });
        });
    });

    describe("Signatures", function() {
        var ajaxSpy;

        beforeEach(function() {
            ajaxSpy = sandbox.spy($, 'ajax');
            view.model.off('change');
        });

        it("should retrieve a signature when the signature ID is present", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id});

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*rest\/v10\/UserSignatures\/.*/,
                [200, { "Content-Type": "application/json"}, JSON.stringify({})]);

            view._updateEditorWithSignature(signature);
            expect(ajaxSpy.getCall(0).args[0].url).toContain("rest/v10/UserSignatures");
        });

        it("should not retrieve a signature when the signature ID is not present", function() {
            var signature = new app.Bean();

            view._updateEditorWithSignature(signature);
            expect(ajaxSpy.callCount).toBe(0);
        });

        it("should change the last selected signature, on success, to the one that is retrieved", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id}),
                results   = {
                    id:             id,
                    name:           "Signature A",
                    signature:      "Regards",
                    signature_html: "&lt;p&gt;Regards&lt;/p&gt;"
                };

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*rest\/v10\/UserSignatures\/" + id + ".*"), [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature.attributes).toEqual(results);
        });

        it("should not change the last selected signature, on success, when no signature is returned", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id}),
                results   = [];

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*rest\/v10\/UserSignatures\/" + id + ".*"), [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();
        });

        it("should not change the last selected signature on error", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id});

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*rest\/v10\/UserSignatures\/" + id + ".*"), [404, {}, ""]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();
        });

        describe("signature helpers", function() {
            dataProvider = [
                {
                    message:   "should format a signature with &lt; and/or &gt; to use < and > respectively",
                    signature: "This &lt;signature&gt; has HTML-style brackets",
                    expected:  "This <signature> has HTML-style brackets"
                },
                {
                    message:   "should leave a signature as is if &lt; and &gt; are not found",
                    signature: "This signature has no HTML-style brackets",
                    expected:  "This signature has no HTML-style brackets"
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    var actual = view._formatSignature(data.signature);
                    expect(actual).toBe(data.expected);
                });
            }, this);

            var tag = "<signature />";

            dataProvider = [
                {
                    message:  "should prepend the signature block at the absolute beginning when <body> is not found",
                    body:     "my message body is rockin'",
                    prepend:  true,
                    expected: tag + "my message body is rockin'"
                },
                {
                    message:  "should prepend the signature block inside <body> when <body> is found",
                    body:     "<html><head></head><body>my message body is rockin'",
                    prepend:  true,
                    expected: "<html><head></head><body>" + tag + "my message body is rockin'"
                },
                {
                    message:  "should append the signature block at the absolute end when </body> is not found",
                    body:     "my message body is rockin'",
                    prepend:  false,
                    expected: "my message body is rockin'" + tag
                },
                {
                    message:  "should append the signature block inside </body> when </body> is found",
                    body:     "<html><head></head><body>my message body is rockin'</body></html>",
                    prepend:  false,
                    expected: "<html><head></head><body>my message body is rockin'" + tag + "</body></html>"
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    var actual = view._insertSignatureTag(data.body, tag, data.prepend);
                    expect(actual).toBe(data.expected);
                });
            }, this);
        });

        describe("insert a signature", function() {
            var htmlBody = "my message body is rockin'",
                signatureTagBegin ='<br class=\"signature-begin\" />',
                signatureTagEnd ='<br class=\"signature-end\" />';

            dataProvider = [
                {
                    message:        "should append a signature when it is an object, the signature_html attribute exists, there is no existing signature, and the user preference says not to prepend the signature",
                    body:           htmlBody,
                    signature:      {signature_html: "<b>Sincerely, John</b>"},
                    expectedReturn: true,
                    expectedBody:   htmlBody + signatureTagBegin + "<b>Sincerely, John</b>" + signatureTagEnd
                },
                {
                    message:        "should insert a signature that runs from open tag until EOF when there is no close tag",
                    body:           htmlBody + signatureTagBegin + "<b>Sincerely, John</b>" + htmlBody,
                    signature:      {signature_html: "<i>Regards, Jim</i>"},
                    expectedReturn: true,
                    expectedBody:   htmlBody + signatureTagBegin + "<i>Regards, Jim</i>" + signatureTagEnd
                },
                {
                    message:        "should insert a signature that runs from BOF until close tag when there is no open tag",
                    body:           htmlBody + "<b>Sincerely, John</b><br class=\"signature-end\" />" + htmlBody,
                    signature:      {signature_html: "<i>Regards, Jim</i>"},
                    expectedReturn: true,
                    expectedBody:   signatureTagBegin + "<i>Regards, Jim</i>" + signatureTagEnd + htmlBody
                },
                {
                    message:        "should insert a signature that contains any whitespace and non-white space characters",
                    body:           htmlBody,
                    signature:      {signature_html: "<b>Sincerely, John</b>\r\n<b>how are you doing</b>\r\n\t\f"},
                    expectedReturn: true,
                    expectedBody:   htmlBody +  signatureTagBegin + "<b>Sincerely, John</b>\r\n<b>how are you doing</b>\r\n\t\f" + signatureTagEnd
                },
                {
                    message:        "should not insert a signature because signature is not an object",
                    body:           htmlBody,
                    signature:      "<b>Sincerely, John</b>",
                    expectedReturn: false,
                    expectedBody:   htmlBody
                },
                {
                    message:        "should not insert a signature because the signature_html attribute does not exist",
                    body:           htmlBody,
                    signature:      {html: "<b>Sincerely, John</b>"},
                    expectedReturn: false,
                    expectedBody:   htmlBody
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    view.model.set('description_html', data.body);
                    var actualReturn = view._insertSignature(new app.Bean(data.signature)),
                        actualBody   = view.model.get('description_html');
                    expect(actualReturn).toBe(data.expectedReturn);
                    expect(actualBody).toBe(data.expectedBody);
                });
            }, this);
        });
    });

    describe('InitializeSendEmailModel', function() {
        beforeEach(function() {
            view.model.off('change');
        });

        it('should populate the send model attachments correctly', function() {
            var sendModel,
                attachment1 = {id:'123',type:'upload'},
                attachment2 = {id:'456',type:'document'},
                attachment3 = {id:'789',type:'template'};

            view.model.set('attachments', [attachment1,attachment2,attachment3]);
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('attachments')).toEqual([attachment1,attachment2,attachment3]);
        });

        it('should populate the send model attachments/documents as empty when attachments not set', function() {
            var sendModel;
            view.model.unset('attachments');
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('attachments')).toEqual([]);
        });

        it("should populate the related field according to how the Mail API expects it", function () {
            var sendModel,
                parentId = '123',
                parentType = 'Foo';
            view.model.set('parent_id', parentId);
            view.model.set('parent_type', parentType);
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('related')).toEqual({id: parentId, type: parentType});
        });
    });

    describe('ResizeEditor', function() {
        var $drawer, $editor;

        beforeEach(function() {
            var mockHtml = '<div><div class="drawer">' +
                '<div class="headerpane"></div>' +
                '<div class="record"><div class="mce-stack-layout"><div class="mce-stack-layout-item"><iframe frameborder="0"></iframe></div></div></div>' +
                '<div class="show-hide-toggle"></div>' +
                '</div></div>',
                drawerHeight = view.MIN_EDITOR_HEIGHT + 300,
                otherHeight = 50,
                editorHeight = drawerHeight - (otherHeight * 2) - view.EDITOR_RESIZE_PADDING -
                    view.ATTACHMENT_FIELD_HEIGHT;

            view.$el = $(mockHtml);
            $drawer = view.$('.drawer');
            $drawer.height(drawerHeight);
            $editor = view.$('.mce-stack-layout .mce-stack-layout-item iframe');
            $editor.height(editorHeight);

            view.$('.headerpane').height(otherHeight);
            view.$('.record').height(editorHeight);
            view.$('.show-hide-toggle').height(otherHeight);

            sandbox.stub(app.drawer, 'getHeight').callsFake(function() {
                return $drawer.height();
            });
        });

        it("should increase the height of the editor when drawer height increases", function() {
            var editorHeightBefore = $editor.height(),
                drawerHeightBefore = $drawer.height();

            //increase drawer height by 100 pixels
            $drawer.height(drawerHeightBefore + 100);

            view.resizeEditor();
            //editor should be increased to fill the space
            expect($editor.height()).toEqual(editorHeightBefore + 100);
        });

        it("should decrease the height of the editor when drawer height decreases", function() {
            var editorHeightBefore = $editor.height(),
                drawerHeightBefore = $drawer.height();

            //decrease drawer height by 100 pixels
            $drawer.height(drawerHeightBefore - 100);

            view.resizeEditor();
            //editor should be decreased to account for decreased drawer height
            expect($editor.height()).toEqual(editorHeightBefore - 100);
        });

        it("should ensure that editor maintains minimum height when drawer shrinks beyond that", function() {
            //decrease drawer height to 50 pixels below min editor height
            $drawer.height(view.MIN_EDITOR_HEIGHT - 50);

            view.resizeEditor();
            //editor should maintain min height
            expect($editor.height()).toEqual(view.MIN_EDITOR_HEIGHT);
        });

        it("should resize editor to fill empty drawer space but with a padding to prevent scrolling", function() {
            var editorHeightBefore = $editor.height(),
                editorHeightPlusPadding = editorHeightBefore + view.EDITOR_RESIZE_PADDING;

            //add the resize padding on
            $editor.height(editorHeightPlusPadding);
            view.$('.record').height(editorHeightPlusPadding);

            //padding should be added back
            view.resizeEditor();
            expect($editor.height()).toEqual(editorHeightBefore);
        });
    });
});
