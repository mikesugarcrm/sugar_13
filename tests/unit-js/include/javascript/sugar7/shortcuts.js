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
describe('Shortcuts', function() {
    var app,
        view,
        mousetrapBindStub,
        mousetrapUnbindStub;

    beforeEach(function() {
        app = SugarTest.app;

        view = app.view.createView({type: 'base'});
        view2 = app.view.createView({type: 'base'});

        mousetrapBindStub = sinon.stub();
        mousetrapUnbindStub = sinon.stub();

        Mousetrap = {
            bind: mousetrapBindStub,
            unbind: mousetrapUnbindStub
        };
    });

    afterEach(function() {
        app.shortcuts._currentSession = null;
        app.shortcuts._savedSessions = [];
        app.shortcuts._globalShortcuts = {};
        app.shortcuts._enable = false;
        Mousetrap = undefined;

        view.dispose();
        view2.dispose();
    });

    describe('createSession', function() {
        it('should create a new shortcut session', function() {
            app.shortcuts.createSession(['foo'], view);
            expect(app.shortcuts.getCurrentSession()).toBeDefined();
        });

        it('should activate the new session', function() {
            app.shortcuts.createSession(['foo'], view);
            expect(app.shortcuts.getCurrentSession().isActive()).toBe(true);
        });

        it('should deactivate the previous session', function() {
            var deactivateSpy;

            app.shortcuts.createSession(['foo'], view);
            deactivateSpy = sinon.spy(app.shortcuts.getCurrentSession(), 'deactivate');

            app.shortcuts.createSession(['foo'], view);
            expect(deactivateSpy.calledOnce).toBe(true)

            deactivateSpy.restore();
        });
    });

    describe('clearSession', function() {
        it('should deactivate the current session', function() {
            var shortcutSession;

            app.shortcuts.createSession(['foo'], view);
            expect(app.shortcuts.getCurrentSession().isActive()).toBe(true);

            shortcutSession = app.shortcuts.getCurrentSession();
            app.shortcuts.clearSession();
            expect(shortcutSession.isActive()).toBe(false);
        });

        it('should not have any sessions the current session', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.clearSession();
            expect(app.shortcuts.getCurrentSession()).toBeFalsy();
        });
    });

    describe('register', function() {
        //TODO: This test is for backward compatibility and will be removed as part of MAR-3427
        it('should bind shortcut keys and log warning when using deprecated method signature', function() {
            var warnStub = sinon.stub(app.logger, 'warn').callsFake(function() {});

            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register('foo', 'a', $.noop, view);
            expect(mousetrapBindStub.calledOnce).toBe(true);
            expect(warnStub.calledOnce).toBe(true);
            warnStub.restore();
        });

        it('should bind shortcut keys if it is allowed in the session', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });

        it('should bind all shortcut keys when multiple keys are specified', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: ['a','b'],
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.args[0][0]).toEqual(['a','b']);
        });

        it('should not bind shortcut keys if the session is inactive', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.getCurrentSession().deactivate();
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.called).toBe(false);
        });

        it('should register shortcut keys if it is allowed in the session', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeDefined();
        });

        it('should register shortcut keys even if the session is inactive', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.getCurrentSession().deactivate();
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeDefined();
        });

        it('should not register shortcut keys if they are not allowed in the session', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'bar',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts.getCurrentSession()._shortcuts.bar).toBeUndefined();
            expect(mousetrapBindStub.called).toBe(false);
        });

        it('should register the shortcut for the session that is tied to the component layout', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);

            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts._savedSessions[0]._shortcuts.foo.keys).toBeDefined();
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeUndefined();
        });

        it('should not register shortcut keys if the component is a dashlet', function() {
            view.layout = app.view.createView({
                type: 'dashlet'
            });
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'bar',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts.getCurrentSession()._shortcuts.bar).toBeUndefined();
            expect(mousetrapBindStub.called).toBe(false);

            view.layout.dispose();
        });

        it('should register shortcut keys if the same key has already been bound', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledTwice).toBe(true);
        });

        it('should unregister shortcut keys if the same key has already been bound', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapUnbindStub.calledOnce).toBe(true);
        });
    });

    describe('registerGlobal', function() {
        it('should register global shortcut keys', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.registerGlobal({
                id: 'bar',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });

        it('should register global shortcut keys even if the current session is deactivated', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.getCurrentSession().deactivate();
            app.shortcuts.registerGlobal({
                id: 'bar',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });

        it('should not register shortcut keys if the same key has already been bound as a global shortcut key', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.registerGlobal({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledOnce).toBe(true);

            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });
    });

    describe('unregister', function() {
        it('should unregister the shortcut', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeDefined();

            app.shortcuts.unregister('foo', view);
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeUndefined();
        });

        it('should unregister the shortcut for the session that is tied to the component layout', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view2,
                listener: $.noop
            });
            expect(app.shortcuts._savedSessions[0]._shortcuts.foo.keys).toBeDefined();
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeDefined();

            app.shortcuts.unregister('foo', view);
            expect(app.shortcuts._savedSessions[0]._shortcuts.foo.keys).toBeUndefined();
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeDefined();
        });

        it('should unregister but not unbind the shortcut if the session is inactive', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.getCurrentSession().deactivate();
            expect(mousetrapUnbindStub.calledOnce).toBe(true);

            app.shortcuts.unregister('foo', view);
            expect(app.shortcuts.getCurrentSession()._shortcuts.foo.keys).toBeUndefined();
            expect(mousetrapUnbindStub.calledOnce).toBe(true);
        });

        it('should not unregister if it has never been registered', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.unregister('foo', view);
            expect(mousetrapUnbindStub.called).toBe(false);
        });
    });

    describe('saveSession and restoreSession', function() {

        it('should restore all shortcut bindings', function() {
            app.shortcuts.createSession(['a','b'], view);
            app.shortcuts.register({
                id: 'a',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'b',
                keys: 'b',
                component: view,
                listener: $.noop
            });
            expect(mousetrapBindStub.callCount).toBe(2);

            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);
            app.shortcuts.restoreSession();

            expect(mousetrapBindStub.callCount).toBe(4);
        });

        it('should make the last save session active', function() {
            var firstSession;

            app.shortcuts.createSession(['foo'], view);

            firstSession = app.shortcuts.getCurrentSession();
            expect(firstSession.isActive()).toBe(true);

            app.shortcuts.saveSession();
            app.shortcuts.createSession(['bar'], view2);

            expect(firstSession.isActive()).toBe(false);

            app.shortcuts.restoreSession();

            expect(firstSession.isActive()).toBe(true);
            expect(app.shortcuts.getCurrentSession()).toBe(firstSession);
        });

        it('should not restore shortcut session that is tied to a disposed layout', function() {
            var firstSession;
            var view3 = app.view.createView({type: 'base'});

            app.shortcuts.createSession(['foo'], view);

            firstSession = app.shortcuts.getCurrentSession();

            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view3);

            view2.disposed = true;
            app.shortcuts.restoreSession();

            expect(app.shortcuts.getCurrentSession()).toBe(firstSession);
            expect(firstSession.isActive()).toBe(true);

            view3.dispose();
        });

        it('should not restore shortcut session when there are no saved sessions to restore', function() {
            var firstSession;
            app.shortcuts.createSession(['foo'], view);
            firstSession = app.shortcuts.getCurrentSession();
            app.shortcuts.restoreSession();
            expect(app.shortcuts.getCurrentSession()).toBe(firstSession);
        });

        it('should have no shortcut session if the last session had none', function() {
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);

            app.shortcuts.restoreSession();
            app.shortcuts.restoreSession();
            expect(app.shortcuts.getCurrentSession()).toBeNull();
        });
    });

    describe('_getShortcutSessionForComponent', function() {
        it('should get the shortcut session that the component is tied to', function() {
            var result;
            var session;

            view2.layout = view;
            app.shortcuts.createSession(['foo'], view);
            session = app.shortcuts.getCurrentSession();
            result = app.shortcuts._getShortcutSessionForComponent(view2);

            expect(result).toBe(session);
        });

        it('should get the shortcut session even if the session is saved and not active', function() {
            var result;
            var session;
            var view3 = app.view.createView({type: 'base'});

            view2.layout = view;
            app.shortcuts.createSession(['foo'], view);
            session = app.shortcuts.getCurrentSession();
            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view3);

            result = app.shortcuts._getShortcutSessionForComponent(view2);

            expect(result).toBe(session);

            view3.dispose();
        });

        it('should return undefined when it cannot find the shortcut session', function() {
            var result;

            app.shortcuts.createSession(['foo'], view);
            result = app.shortcuts._getShortcutSessionForComponent(view2);

            expect(result).toBeUndefined();
        });
    });

    describe('deleteSavedSession', function() {
        it('should remove the session that the layout is tied to', function() {
            var firstSession, secondSession;
            var view3 = app.view.createView({type: 'base'});

            app.shortcuts.createSession(['foo'], view);
            firstSession = app.shortcuts.getCurrentSession();

            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view2);
            secondSession = app.shortcuts.getCurrentSession();

            app.shortcuts.saveSession();
            app.shortcuts.createSession(['foo'], view3);

            expect(app.shortcuts._savedSessions.length).toBe(2);
            expect(app.shortcuts._savedSessions[0]).toBe(firstSession);
            expect(app.shortcuts._savedSessions[1]).toBe(secondSession);

            app.shortcuts.deleteSavedSession(view);
            expect(app.shortcuts._savedSessions.length).toBe(1);
            expect(app.shortcuts._savedSessions[0]).toBe(secondSession);

            view3.dispose();
        });
    });

    describe('saveCustomShortcutKey', function() {
        var getCurrentSessionStub,
            updatePreferencesStub,
            deactivateStub,
            activateStub;

        beforeEach(function() {
            app.user.set('preferences', {});
            deactivateStub = sinon.stub();
            activateStub = sinon.stub();
            getCurrentSessionStub = sinon.stub(app.shortcuts, 'getCurrentSession').callsFake(function() {
                return {
                    deactivate: deactivateStub,
                    activate: activateStub
                };
            });
            updatePreferencesStub = sinon.stub(app.user, 'updatePreferences').callsFake(function(shortcuts, callback) {
                callback();
            });
        });

        afterEach(function() {
            app.user.set('preferences', {});
            getCurrentSessionStub.restore();
            updatePreferencesStub.restore();
        });

        it('should save the custom shortcut keys in user preference', function() {
            app.shortcuts.saveCustomShortcutKey([{
                id: 'foo',
                keys: ['a']
            }], $.noop);

            expect(updatePreferencesStub.args[0][0].shortcuts).toEqual({foo:['a']});
        });

        it('should deactivate and activate the current session', function() {
            app.shortcuts.saveCustomShortcutKey([{
                id: 'foo',
                keys: ['a']
            }], $.noop);

            expect(activateStub.calledOnce).toBe(true);
            expect(deactivateStub.calledOnce).toBe(true);
        });

        it('should not update the user preference if custom shortcut keys array is empty', function() {
            app.shortcuts.saveCustomShortcutKey([], $.noop);

            expect(updatePreferencesStub.calledOnce).toBe(false);
        });
    });

    describe('removeCustomShortcutKeys', function() {
        var getCurrentSessionStub,
            updatePreferencesStub,
            deactivateStub,
            activateStub;

        beforeEach(function() {
            app.user.set('preferences', {
                shortcuts: {
                    foo: ['a'],
                    bar: ['b']
                }
            });
            deactivateStub = sinon.stub();
            activateStub = sinon.stub();
            getCurrentSessionStub = sinon.stub(app.shortcuts, 'getCurrentSession').callsFake(function() {
                return {
                    deactivate: deactivateStub,
                    activate: activateStub
                };
            });
            updatePreferencesStub = sinon.stub(app.user, 'updatePreferences').callsFake(function(shortcuts, callback) {
                callback();
            });
        });

        afterEach(function() {
            app.user.set('preferences', {});
            getCurrentSessionStub.restore();
            updatePreferencesStub.restore();
        });

        it('should remove the custom key from the user preference', function() {
            app.shortcuts.removeCustomShortcutKeys(['foo'], $.noop);

            expect(app.user.get('preferences').shortcuts).toEqual({bar:['b']});
        });

        it('should save the updated user preference on the server', function() {
            app.shortcuts.removeCustomShortcutKeys(['foo'], $.noop);

            expect(updatePreferencesStub.args[0][0].shortcuts).toEqual({bar:['b']});
        });

        it('should deactivate and activate the current session', function() {
            app.shortcuts.removeCustomShortcutKeys(['foo'], $.noop);

            expect(activateStub.calledOnce).toBe(true);
            expect(deactivateStub.calledOnce).toBe(true);
        });

        it('should not update the user preference if custom shortcut keys array is empty', function() {
            app.shortcuts.removeCustomShortcutKeys([], $.noop);

            expect(updatePreferencesStub.calledOnce).toBe(false);
        });
    });

    describe('getRegisteredGlobalShortcuts', function() {
        it('should return the IDs, the keys, and the description of all available global shortcuts', function() {
            app.shortcuts.createSession([], view);
            app.shortcuts.registerGlobal({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop,
                description: 'Foo'
            });
            app.shortcuts.registerGlobal({
                id: 'bar',
                keys: 'b',
                component: view,
                listener: $.noop
            });

            expect(app.shortcuts.getRegisteredGlobalShortcuts()).toEqual([{
                id: 'foo',
                keys: ['a'],
                description: 'Foo'
            }, {
                id: 'bar',
                keys: ['b']
            }]);
        });
    });

    describe('shouldCallOnFocus', function() {
        it('should return true if the key has been registered to be called on focus', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop,
                callOnFocus: true
            });

            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(true);
        });

        it('should return false if the key has been registered to not call on focus', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });

            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(false);
        });

        it('should return true if a global shortcut key has been registered to be called on focus', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop,
                callOnFocus: true
            });

            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(true);
        });

        it('should return false if the session is not active', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop,
                callOnFocus: true
            });
            app.shortcuts.getCurrentSession().deactivate();

            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(false);
        });

        it('should return false if the key has been not registered', function() {
            app.shortcuts.createSession([], view);
            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(false);
        });

        it('should return false if there are no sessions', function() {
            expect(app.shortcuts.shouldCallOnFocus('a')).toBe(false);
        });
    });

    describe('ShortcutSession.getRegisteredShortcuts', function() {
        var session,
            expected;
        beforeEach(function() {
            expected = [{
                id: 'foo',
                keys: ['a']
            }, {
                id: 'bar',
                keys: ['b']
            }];

            app.shortcuts.createSession(['foo','bar', 'test'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'a',
                component: view,
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'bar',
                keys: 'b',
                component: view,
                listener: $.noop
            });
            session = app.shortcuts.getCurrentSession();
        });

        afterEach(function() {
            session = null;
        });

        it('should return the IDs and the keys of all registered shortcuts for that session', function() {
            expect(session.getRegisteredShortcuts()).toEqual(expected);
        });

        it('should not return shortcuts that do not have keys registered', function() {
            var testObjFound = _.find(session.getRegisteredShortcuts(), function(shortcut) {
                return shortcut.id == 'test';
            });
            expect(testObjFound).toBeUndefined();
        });
    });

    describe('enable/disable', function() {
        it('should enable shortcuts if enable is called', function() {
            app.shortcuts.enable();
            expect(app.shortcuts.isEnabled()).toBe(true);
        });

        it('should disable shortcuts if disable is called', function() {
            app.shortcuts.disable();
            expect(app.shortcuts.isEnabled()).toBe(false);
        });
    });

    describe('custom shortcut keys', function() {
        var getCurrentSessionStub,
            updatePreferencesStub,
            deactivateStub,
            activateStub;

        beforeEach(function() {
            app.user.set('preferences', {
                shortcuts: {
                    foo: ['a'],
                    bar: ['b']
                }
            });
            activateStub = sinon.stub();
            getCurrentSessionStub = sinon.stub(app.shortcuts, 'getCurrentSession').callsFake(function() {
                return {
                    deactivate: deactivateStub,
                    activate: activateStub
                };
            });
            updatePreferencesStub = sinon.stub(app.user, 'updatePreferences').callsFake(function(shortcuts, callback) {
                callback();
            });
        });

        afterEach(function() {
            app.user.set('preferences', {});
            getCurrentSessionStub.restore();
            updatePreferencesStub.restore();
        });

        it('should be used when registering shortcut keys instead of the default keys', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'z',
                component: view,
                listener: $.noop
            });

            expect(mousetrapBindStub.args[0][0]).toEqual(['a']);
        });

        it('should be used when unbinding shortcut keys instead of the default keys', function() {
            app.shortcuts.createSession(['foo'], view);
            app.shortcuts.register({
                id: 'foo',
                keys: 'z',
                component: view,
                listener: $.noop
            });

            expect(mousetrapUnbindStub.args[0][0]).toEqual(['a']);

            app.shortcuts.unregister('foo', view);

            expect(mousetrapUnbindStub.args[1][0]).toEqual(['a']);
        });
    });
});
