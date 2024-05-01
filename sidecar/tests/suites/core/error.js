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

const {HttpError} = require('@sugarcrm/ventana');
const ErrorHandler = require('../../../src/core/error');

describe('Core/Error', function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        SugarTest.seedFakeServer();
        this.sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        this.sandbox.restore();
    });

    it('should inject custom http error handlers and should handle http code errors', function() {
        var bean = app.data.createBean('Cases'),
            handled = false, statusCodes;

        // The reason we don't use a spy in this case is because
        // the status codes are copied instead of passed in by
        // by reference, thus the spied function will never be called.
        statusCodes = {
            404: function() {
                handled = true;
            }
        };

        ErrorHandler.initialize({statusCodes: statusCodes});

        sinon.spy(ErrorHandler, 'handleHttpError');
        SugarTest.server.respondWith([404, {}, '']);
        bean.save();
        SugarTest.server.respond();
        expect(handled).toBeTruthy();
        expect(ErrorHandler.handleHttpError.called).toBeTruthy();

        ErrorHandler.handleHttpError.restore();
    });

    it('should handle validation errors', function() {
        var bean, flag = false, spy;

        // Set the length arbitrarily low to force validation error
        app.data.declareModel('Cases', fixtures.metadata.modules.Cases);
        bean = app.data.createBean('Cases');
        bean.fields.name.len = 1;

        ErrorHandler.initialize();
        sinon.spy(ErrorHandler, 'handleValidationError');

        bean.set({name: 'This is a test'});
        spy = sinon.spy(bean, 'trigger');
        bean.save(null, {fieldsToValidate: ['name']});
        waitsFor(function() {
            return spy.calledWith('validation:complete');
        });
        runs(function() {
            expect(ErrorHandler.handleValidationError.called).toBeTruthy();
            ErrorHandler.handleValidationError.restore();
            spy.restore();
        });
    });

    describe('handleValidationError', function() {
        beforeEach(function() {
            this.alertStub = this.sandbox.stub(app.alert, 'dismissAll');
            this.debugStub = this.sandbox.stub(app.logger, 'debug');
        });

        it('should dismiss alerts', function() {
            ErrorHandler.handleValidationError({ responseText: 'validation problem' });
            expect(this.alertStub).toHaveBeenCalled();
        });

        it('should log a debug message based on the validation errors', function() {
            ErrorHandler.handleValidationError({
                responseText: {
                    field1: 5,
                    field2: {
                        greaterThan: 2,
                        lessThan: 7,
                    },
                },
            });
            expect(this.debugStub.getCall(0).args[0]).toEqual('validation failed for field `field1`:\n5');
            expect(this.debugStub.getCall(1).args[0]).toEqual(
                'validation failed for field `field2`:\n' +
                '(Message) ERROR_IS_GREATER_THAN\n' +
                '(Message) ERROR_IS_LESS_THAN\n'
            );
        });
    });

    it('overloads window.onerror', function() {
        // Remove on error
        window.onerror = false;

        // Initialize error module
        ErrorHandler.overloaded = false;
        ErrorHandler.initialize();

        // Check to see if onerror was overloaded
        expect(_.isFunction(window.onerror)).toBeTruthy();
    });

    it('should get error strings', function() {
        var errorKey = 'ERROR_TEST';
        var context = '10';
        var string = ErrorHandler.getErrorString(errorKey, context);
        expect(string).toEqual('Some error string 10');
    });

    describe('handleHttpError', function() {
        let jsonFunc = () => 'application/json';
        let dataProvider = [
            {
                handler: 'handleInvalidGrantError',
                xhr: {
                    responseText: '{"error": "invalid_grant", "error_description": "some desc"}',
                    status: '400',
                    getResponseHeader: jsonFunc,
                },
            },
            {
                handler: 'handleInvalidClientError',
                xhr: {
                    responseText: '{"error": "invalid_client", "error_description": "some desc"}',
                    status: '400',
                    getResponseHeader: jsonFunc,
                },
            },
            {
                handler: 'handleForbiddenError',
                xhr: {
                    status: '403',
                    responseText: '{"error": "fubar"}',
                    getResponseHeader: jsonFunc,
                },
            },
            {
                handler: 'handleMethodNotAllowedError',
                xhr: {
                    status: '405',
                    responseText: '{"error": "method_not_allowed"}',
                    getResponseHeader: jsonFunc,
                },
            },
            {
                handler: 'handleMethodConflictError',
                xhr: {
                    status: '409',
                    responseText: '{"error": "conflict_error"}',
                    getResponseHeader: jsonFunc,
                },
            },
            {
                handler: 'handleHeaderPreconditionFailed',
                xhr: {
                    status: '412',
                    responseText: '{"error": "precondition_failed"}',
                    getResponseHeader: jsonFunc,
                }
            },
            {
                handler: 'handleServerError',
                xhr: {
                    status: '502',
                    responseText: '{"error": "bad_gateway"}',
                    getResponseHeader: jsonFunc,
                }
            },
            {
                handler: 'handleServerError',
                xhr: {
                    status: '503',
                    responseText: '{"error": "internal_server_error"}',
                    getResponseHeader: jsonFunc,
                }
            },
        ];

        it('should call specific callback if available or resort to fallback', function () {
            _.each(dataProvider, function(data) {
                let handlerName = data.handler;
                let xhr = data.xhr;
                ErrorHandler[handlerName] = _.noop;
                let spyHandler = sinon.spy(ErrorHandler, handlerName);
                let request = {xhr: xhr};
                let error = new HttpError(request);
                ErrorHandler.handleHttpError(error);
                expect(spyHandler.called).toBeTruthy();

                // Now try with it undefined and the fallback should get called
                ErrorHandler[handlerName] = undefined;
                let spyFallbackHandler = sinon.spy(ErrorHandler, 'handleStatusCodesFallback');
                ErrorHandler.handleHttpError(error);
                expect(spyHandler).not.toHaveBeenCalledTwice();
                expect(spyFallbackHandler.called).toBeTruthy();
                spyHandler.restore();
                spyFallbackHandler.restore();
            });
        });
    });

    describe('handleUnhandledError', function() {
        it('should log with the fatal level', function() {
            let unhandledErrorStub = this.sandbox.stub(app.logger, 'fatal');
            ErrorHandler.handleUnhandledError('Big weird problem', 'https://myapp.com/badscript.js', 555);
            let expectedMsg = `Big weird problem at https://myapp.com/badscript.js on line 555`;
            expect(unhandledErrorStub).toHaveBeenCalledWith(expectedMsg);
        });
    });
});
