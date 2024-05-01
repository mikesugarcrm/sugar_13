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
describe('Sugar7 error handler', function() {

    var app, origLayout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();

        origLayout = app.controller.layout;
        app.controller.layout = {error: {
            handleValidationError: function() { }
        }};
    });

    afterEach(function() {
        app.controller.layout = origLayout;
        sinon.restore();
    });

    describe('409 Handle method conflict error', function() {
        it('should log an error', function() {
            var loggerStub = sinon.stub(app.logger, 'error');
            app.error.handleMethodConflictError();
            expect(loggerStub).toHaveBeenCalledWith('Data conflict detected.');
        });
    });

    describe('422 Handle validation error', function() {
        it('should show an alert on error', function() {
            var alertStub = sinon.stub(app.alert, 'show');

            app.error.handleValidationError({});
            expect(alertStub).toHaveBeenCalled();
        });

        it('should call the layout error handler if it exists', function() {
            var layoutStub = sinon.stub(app.controller.layout.error, 'handleValidationError').returns(null);
            var alertStub = sinon.stub(app.alert, 'show');
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should not show an alert if the layout handler returns false', function() {
            var layoutStub = sinon.stub(app.controller.layout.error, 'handleValidationError').returns(false);
            var alertStub = sinon.stub(app.alert, 'show');
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should do nothing when passed a bean', function() {
            var alertStub = sinon.stub(app.alert, 'show');
            var bean = new SugarTest.app.data.beanModel();
            app.error.handleValidationError(bean);
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
        });
    });

    describe('400 invalid request error', function() {
        var errorPageStub;

        beforeEach(function() {
            errorPageStub = sinon.stub(app.controller, 'loadView');
        });

        it('should show an error page on error', function() {
            app.error.handleUnspecified400Error({});
            expect(errorPageStub).toHaveBeenCalledWith({
                layout: 'error',
                errorType: '400',
                module: 'Error',
                create: true
            });
        });
    });

    describe('412 precondition failed error', function() {
        var syncStub;

        beforeEach(function() {
            syncStub = sinon.stub(app, 'sync');
            app.isSynced = true;
        });

        it('should not sync if we have already started syncing', function() {
            app.isSynced = false;
            app.error.handleHeaderPreconditionFailed({});
            expect(syncStub).not.toHaveBeenCalled();
        });

        it('should only sync when metadata is out of date', function() {
            var error = null;

            app.error.handleHeaderPreconditionFailed(error);
            expect(syncStub).not.toHaveBeenCalled();

            error = {
                code: 'throwing 412 error for no reason'
            };

            app.error.handleHeaderPreconditionFailed(error);
            expect(syncStub).not.toHaveBeenCalled();
        });

        describe('infinite loop prevention', function() {

            beforeEach(function() {
                sinon.stub(app.logger, 'fatal');
            });

            using('different error responses', [
                // Cases where sync should not occur (false 412)
                {
                    metaHash: false,
                    userHash: false,
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'oldMeta',
                    sentUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: false,
                    userHash: 'oldUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'oldMeta',
                    sentUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: 'oldMeta',
                    userHash: false,
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'newUser',
                    sentMetaHash: 'oldMeta',
                    sentUserHash: 'newUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: 'oldMeta',
                    userHash: 'oldUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'oldMeta',
                    sentUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },

                // Cases where sync should occur (valid 412 error)
                {
                    metaHash: 'newMeta',
                    userHash: 'newUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'oldMeta',
                    sentUserHash: 'oldUser',
                    loadingAfterSync: false,
                    shouldSync: true
                },
                {
                    metaHash: 'oldMeta',
                    userHash: 'oldUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'newMeta',
                    sentUserHash: 'newUser',
                    loadingAfterSync: false,
                    shouldSync: true
                },
                {
                    metaHash: 'newMeta',
                    userHash: 'newUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    sentMetaHash: 'newMeta',
                    sentUserHash: 'newUser',
                    loadingAfterSync: false,
                    shouldSync: true
                },
            ], function(options) {
                it('should only sync when we have a new metadata hash and a new user hash', function() {
                    sinon.stub(app.metadata, 'getHash').returns(options.oldMetaHash);
                    sinon.stub(app.router, 'refresh');
                    sinon.stub(app.user, 'get').withArgs('_hash').returns(options.oldUserHash);

                    var error = {
                        code: 'metadata_out_of_date',
                        responseText: JSON.stringify({
                            metadata_hash: options.metaHash,
                            user_hash: options.userHash
                        }),
                        request: {
                            state: {
                                loadingAfterSync: options.loadingAfterSync
                            },
                            params: {
                                headers: {
                                    'X-Metadata-Hash': options.sentMetaHash,
                                    'X-Userpref-Hash': options.sentUserHash
                                }
                            }
                        }
                    };
                    app.error.handleHeaderPreconditionFailed(error);
                    expect(syncStub.called).toBe(options.shouldSync);
                });
            });
        });
    });

    describe('500, 502, and 503 handle internal server error', function() {
        using('different error payloads', [
                {
                    error: {
                        code: 'internal_server_error'
                    },
                    expected: {
                        layout: 'error',
                        errorType: '500',
                        module: 'Error',
                        error: {
                            code: 'internal_server_error'
                        },
                        create: true
                    }
                },
                {
                    error: {
                        code: '',
                        status: '502'
                    },
                    expected: {
                        layout: 'error',
                        errorType: '502',
                        module: 'Error',
                        error: {
                            code: '',
                            status: '502'
                        },
                        create: true
                    }
                }
            ], function(data) {
                it('should send regular users to the error page', function() {
                    var loadViewStub = sinon.stub(app.controller, 'loadView');
                    app.error.handleServerError(data.error);
                    expect(loadViewStub).toHaveBeenCalledWith(data.expected);
                });
            }
        );
    });
});
