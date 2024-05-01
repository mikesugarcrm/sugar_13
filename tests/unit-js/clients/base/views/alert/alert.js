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
describe('Alert View', function() {
    var moduleName = 'Cases',
        app,
        sinonSandbox, view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'alert');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'process');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'confirmation');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'error');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', moduleName, 'alert');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
        sinon.restore();
    });

    describe('getTranslatedLabels()', function() {
        it("Should return a translated string when a string is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings').callsFake(function() {
                return {
                    FOO: 'bar'
                }
            });

            expect(view.getTranslatedLabels('FOO').string).toBe('bar');
        });

        it("Should return a translated array of strings when an array is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings').callsFake(function() {
                return {
                    FOO: 'bar'
                }
            });
            var result = view.getTranslatedLabels(['FOO','FOO','FOO']);

            expect(_.isArray(result)).toBe(true);
            _.each(result , function(text) {
                expect(text.string).toBe('bar');
            });
        });
    });

    describe('_getAlertTemplate()', function() {
        using('different alert options', [
            {
                // process with custom title
                options: {
                    level: 'process',
                    title: 'FOO',
                },
                selectors: ['.alert-process'],
                expected: {
                    title: 'FOO',
                },
            },
            {
                options: {
                    level: 'process',
                },
                selectors: ['.alert-process'],
                expected: {
                    title: 'LBL_ALERT_TITLE_LOADING',
                },
            },
            {
                options: {
                    level: 'success',
                },
                selectors: ['.alert-success', '.sicon-check-circle-line-lg'],
                expected: {
                    title: 'LBL_ALERT_TITLE_SUCCESS',
                },
            },
            {
                options: {
                    level: 'warning',
                },
                selectors: ['.alert-warning', '.sicon-warning-lg'],
                expected: {
                    title: 'LBL_ALERT_TITLE_WARNING',
                },
            },
            {
                options: {
                    level: 'info',
                },
                selectors: ['.alert-info', '.sicon-info-lg'],
                expected: {
                    title: 'LBL_ALERT_TITLE_NOTICE',
                },
            },
            {
                options: {
                    level: 'error',
                },
                selectors: ['.alert-danger', '.sicon-error-lg'],
                expected: {
                    title: 'LBL_ALERT_TITLE_ERROR',
                },
            },
            {
                options: {
                    level: 'confirmation',
                },
                selectors: ['.alert-warning', '.sicon-warning-lg'],
                expected: {
                    title: 'LBL_ALERT_TITLE_WARNING',
                },
            },
        ], function(provider) {
            it('should have the appropriate alert class, title, and icons', function() {
                var result = view._getAlertTemplate(provider.options);
                var $result = $('<div/>').append(result);

                _.each(provider.selectors, function(selector) {
                    expect($result.find(selector).length).toBeTruthy();
                });

                expect(result.indexOf(provider.expected.title)).not.toBe(-1);
            });
        });

        it('Should return an empty string when no options are passed', function() {
            var result = view._getAlertTemplate();
            expect(result).toBe('');
        });

        it("Should return the default title if title is not given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings').callsFake(function() {
                return {
                    LBL_ALERT_TITLE_SUCCESS: 'foo bar'
                }
            });

            var result = view._getAlertTemplate({level: view.LEVEL.SUCCESS, messages: 'BAR'});
            expect(result.indexOf('foo bar')).not.toBe(-1);
        });

        it('should clear double ellipsis on processing labels', function() {
            var result;
            result = view._getAlertTemplate({level: view.LEVEL.PROCESS, title: 'Loading...'});
            expect($(result).text().replace(/^\s+|\s+$/g, '')).toBe('Loading...');
            result = view._getAlertTemplate({level: view.LEVEL.PROCESS, title: 'Deleting...'});
            expect($(result).text().replace(/^\s+|\s+$/g, '')).toBe('Deleting...');
        });
    });

    describe('closeClicked', function() {
        it('should call onClose if it exists and then dismiss the alert', function() {
            var dismissStub = sinon.stub(app.alert, 'dismiss');
            view.onClose = sinon.stub();
            var myEvent = {simulated: 'event'};
            view.closeClicked(myEvent);
            expect(view.onClose).toHaveBeenCalledWith(myEvent);
            expect(dismissStub).toHaveBeenCalled();
        });
    });

    describe('confirmation alerts', function() {
        it('should cancel alert before calling onCancel and onConfirm', function() {
            var calledLast,
                cancelStub;
            view.onCancel = function() {
                calledLast = 'onCancel';
            };
            view.onConfirm = function() {
                calledLast = 'onConfirm';
            };
            cancelStub = sinon.stub(view, 'cancel').callsFake(function() {
                calledLast = 'cancel';
            });

            //Test onCancel
            view.cancelClicked();
            expect(cancelStub).toHaveBeenCalledOnce();
            expect(calledLast).toEqual('onCancel');
            //Test onConfirm
            view.confirmClicked();
            expect(cancelStub).toHaveBeenCalledTwice();
            expect(calledLast).toEqual('onConfirm');

        });

        var alertClass;

        describe("when button objects aren't use for the confirmation buttons", function() {
            beforeEach(function() {
                alertClass = app.view.views['BaseAlertView'];
            });

            it('should set onConfirm from options.onConfirm', function() {
                var alert = new alertClass({level: 'confirmation', onConfirm: 'confirm'});
                expect(alert.onConfirm).toEqual('confirm');
            });

            it('should set confirmLabel to the default label', function() {
                var alert = new alertClass({level: 'confirmation'});
                expect(alert.confirmLabel).toEqual('LBL_CONFIRM_BUTTON_LABEL');
            });

            it('should set onCancel from options.onCancel', function() {
                var alert = new alertClass({level: 'confirmation', onCancel: 'cancel'});
                expect(alert.onCancel).toEqual('cancel');
            });

            it('should set cancelLabel to the default label', function() {
                var alert = new alertClass({level: 'confirmation'});
                expect(alert.cancelLabel).toEqual('LBL_CANCEL_BUTTON_LABEL');
            });

            it('should prioritize options.onConfirm over options.confirm.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    onConfirm: 'foo',
                    confirm: {
                        callback: 'bar'
                    }
                });
                expect(alert.onConfirm).toEqual('foo');
            });

            it('should prioritize options.onCancel over options.cancel.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    onCancel: 'foo',
                    cancel: {
                        callback: 'bar'
                    }
                });
                expect(alert.onCancel).toEqual('foo');
            });
        });

        describe('when button objects are use for the confirmation buttons', function() {
            beforeEach(function() {
                alertClass = app.view.views['BaseAlertView'];
            });

            it('should set onConfirm from options.confirm.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    confirm: {
                        callback: 'confirm'
                    }
                });
                expect(alert.onConfirm).toEqual('confirm');
            });

            it('should set confirmLabel to the custom label', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    confirm: {
                        label: 'LBL_CONFIRM'
                    }
                });
                expect(alert.confirmLabel).toEqual('LBL_CONFIRM');
            });

            it('should set onCancel from options.cancel.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    cancel: {
                        callback: 'cancel'
                    }
                });
                expect(alert.onCancel).toEqual('cancel');
            });

            it('should set cancelLabel to the custom label', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    cancel: {
                        label: 'LBL_CANCEL'
                    }
                });
                expect(alert.cancelLabel).toEqual('LBL_CANCEL');
            });
        });
    });

    describe('Key bindings', function() {
        var oldShortcuts;
        var keybindingSandbox = sinon.createSandbox();

        beforeEach(function() {
            oldShortcuts = app.shortcuts;
            var callCount = 0;
            app.shortcuts = {
                saveSession: keybindingSandbox.stub(),
                createSession: keybindingSandbox.stub(),
                register: keybindingSandbox.stub(),
                restoreSession: keybindingSandbox.stub(),
                getCurrentSession: function() {
                    if (callCount) {
                        return {layout: 'not this'};
                    }

                    callCount++;
                    return {layout: view};
                }
            };
        });

        afterEach(function() {
            keybindingSandbox.restore();
            app.shortcuts = oldShortcuts;
        });

        it('Should create a new shortcut session and register new keys for confirmation alerts', function() {
            view.options = {level: 'confirmation'};
            view.render();

            expect(app.shortcuts.createSession.calledOnce).toBe(true);
            expect(app.shortcuts.register.called).toBe(true);
        });

        it('Should not create a new shortcut session for other alerts', function() {
            view.options = {level: 'warning'};

            view.render();

            expect(app.shortcuts.createSession.called).toBe(false);
        });

        it('Should restore previous shortcut session when confirmation alert is closed', function() {
            view.options = {level: 'confirmation'};
            view.level = 'confirmation';

            view.render();
            view.close();

            expect(app.shortcuts.restoreSession.calledOnce).toBe(true);
        });

        it('Should not restore previous shortcut session when other alerts are closed', function() {
            view.options = {level: 'warning'};
            view.render();
            view.close();

            expect(app.shortcuts.restoreSession.called).toBe(false);
        });
    });

    it('should apply styles when rendering', function() {
        view.options = {closeable: true, level: 'info'};
        view.render();

        expect(view.$('.alert').hasClass('alert-info')).toBeTruthy();
        expect(view.$('.alert').hasClass('closeable')).toBeTruthy();

        view.options = {closeable: false, level: 'error'};

        view.render();

        expect(view.$('.alert').hasClass('closeable')).toBeFalsy();
        expect(view.$('.alert').hasClass('alert-danger')).toBeTruthy();
    });
});
