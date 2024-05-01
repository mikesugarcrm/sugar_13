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

const Alert = require('../../../src/view/alert');
const AlertView = require('../../../src/view/alert-view');

describe('View/Alert', function() {
    var app, alert;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        Alert.dismissAll();
    });

    it('should render alert view', function() {
        sinon.stub(AlertView.prototype, 'render');
        var dismissStub = sinon.stub(Alert, 'dismiss').callsFake(function() {
            SugarTest.setWaitFlag();
        });

        var alert = Alert.show('fubar', {level: 'info', title: 'foo', messages: 'message', autoClose: true});

        SugarTest.wait();

        runs(function() {
            expect(Alert.getAll()).toBeDefined();
            expect(Alert.get('fubar')).toBeDefined();
            expect(Alert.get('fubar').key).toEqual('fubar');
            expect(alert.options).toEqual({level: 'info', title: 'foo', messages: ['message'], autoClose: true});
            expect(AlertView.prototype.render).toHaveBeenCalled();
            expect(dismissStub).toHaveBeenCalled();
        });
    });

    it('should execute callback on autoclose', function() {
        var autoCloseSpy = sinon.spy(),
            dismissStub = sinon.stub(Alert, 'dismiss').callsFake(function() {
                SugarTest.setWaitFlag();
            });

        Alert.show('fubar', {
            level: 'info',
            title: 'foo',
            messages: 'message',
            autoClose: true,
            onAutoClose: autoCloseSpy
        });

        SugarTest.wait();

        runs(function() {
            expect(autoCloseSpy).toHaveBeenCalled();
            expect(autoCloseSpy).toHaveBeenCalledWith(Alert.get('fubar').key);
            expect(dismissStub).toHaveBeenCalled();
        });
    });

    it('should dismiss alerts', function() {
        var alert,
            spy1,
            spy2,
            clearSpy = sinon.spy(window, 'clearTimeout'),
            setTimeoutSpy = sinon.spy(window, 'setTimeout'),
            autoCloseDelayOverride = 2000;
        app.config.alertAutoCloseDelay = 10000;
        Alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        Alert.show('mykey2', {
            level: 'info',
            title: 'foo',
            messages: 'message',
            autoClose: true,
            autoCloseDelay: autoCloseDelayOverride
        });
        Alert.show('mykey3', {level: 'info', title: 'foo', messages: 'message', autoClose: false});

        alert = Alert.get('mykey');
        spy1 = sinon.spy(alert, 'dispose');

        alert = Alert.get('mykey2');
        spy2 = sinon.spy(alert, 'dispose');

        Alert.dismiss('mykey');
        Alert.dismiss('mykey');
        Alert.dismiss('mykey2');

        expect(spy1).toHaveBeenCalledOnce();
        expect(spy2).toHaveBeenCalledOnce();
        expect(Alert.get('fubar')).toBeUndefined();
        expect(clearSpy).toHaveBeenCalledTwice();
        expect(setTimeoutSpy.firstCall.args[1]).toEqual(app.config.alertAutoCloseDelay);
        expect(setTimeoutSpy.lastCall.args[1]).toEqual(autoCloseDelayOverride);
    });

    it('should clear timeout if it already exists', function() {
        var clearSpy = sinon.spy(window, 'clearTimeout');

        Alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        expect(clearSpy).not.toHaveBeenCalled();
        Alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        expect(clearSpy).toHaveBeenCalled();
    });

    it('should dismiss all with the given level', function() {
        var alert, s1, s2, s3;

        Alert.show('mykey2', {level: 'error', title: 'bar', message: 'message2', autoClose: false});
        Alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
        Alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});

        alert = Alert.get('mykey1');
        s1 = sinon.spy(alert, 'dispose');

        alert = Alert.get('mykey2');
        s2 = sinon.spy(alert, 'dispose');

        alert = Alert.get('mykey3');
        s3 = sinon.spy(alert, 'dispose');

        Alert.dismissAll('error');

        expect(s1).not.toHaveBeenCalled();
        expect(s2).toHaveBeenCalled();
        expect(s3).toHaveBeenCalled();

        expect(Alert.get('mykey1')).toBeDefined();
        expect(Alert.get('mykey2')).toBeUndefined();
        expect(Alert.get('mykey3')).toBeUndefined();
    });

    it('should dismiss all', function() {
        Alert.show('mykey2', {level:'error', title:'bar', message:'message2', autoClose: false});
        Alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
        Alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});

        Alert.dismissAll();

        expect(Alert.get('mykey1')).toBeUndefined();
        expect(Alert.get('mykey2')).toBeUndefined();
        expect(Alert.get('mykey3')).toBeUndefined();

    });

    describe('displaying multiple alerts', function() {
        var renderStub;
        beforeEach(function() {
            renderStub = sinon.stub(AlertView.prototype, 'render');
        });

        it('should allow to display multiple alerts', function() {
            Alert.show('mykey2', {level: 'error', title: 'bar', message: 'message2', autoClose: false});
            expect(renderStub).toHaveBeenCalledOnce();
            expect(Alert.preventAnyAlert).toBeFalsy();
            Alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
            expect(renderStub).toHaveBeenCalledTwice();
            expect(Alert.preventAnyAlert).toBeFalsy();
            Alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});
            expect(renderStub).toHaveBeenCalledThrice();
            expect(Alert.preventAnyAlert).toBeFalsy();
        });

        it('should prevent other alerts while confirmation is shown', function() {
            //Should show confirmation alert
            Alert.show('fubar', {level: 'confirmation', title: 'foo', messages: 'message', autoClose: true});
            expect(Alert.get('fubar')).toBeDefined();
            expect(renderStub).toHaveBeenCalledOnce();
            expect(Alert.preventAnyAlert).toBeTruthy();

            //Should prevent this alert to be shown
            Alert.show('test', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
            expect(Alert.get('test')).toBeUndefined();
            expect(renderStub).not.toHaveBeenCalledTwice();
            expect(Alert.preventAnyAlert).toBeTruthy();

            // Test dismiss resets flag
            Alert.dismissAll();
            expect(Alert.preventAnyAlert).toBeFalsy();
        });
    });

    describe('test initialization', function() {
        var _alertsEl = SUGAR.App.config.alertsEl;

        afterEach(function() {
            SUGAR.App.config.alertsEl = _alertsEl;
        });

        it('should return null when there are no alerts', function() {
            SUGAR.App.config.alertsEl = '';
            Alert.init();
            expect(Alert.show()).toBeNull();
        });

        it('should create alert if it does not exist', function() {
            SUGAR.App.config.alertsEl = '<html><body><div><span class="alert-wrapper">Test</span></div></body></html>';

            expect(_.keys(Alert.getAll()).length).toEqual(0);
            Alert.init();
            expect(_.keys(Alert.getAll()).length).toEqual(1);
        });
    });

});
