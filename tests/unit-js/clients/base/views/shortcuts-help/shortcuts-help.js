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
describe('Base.View.ShortcutsHelp', function() {
    var app, view, origMousetrap;
    var firstLayout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'shortcuts-help');
        SugarTest.loadHandlebarsTemplate('shortcuts-help', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('shortcuts-help', 'view', 'base', 'shortcuts-help-table');
        SugarTest.testMetadata.set();

        origMousetrap = Mousetrap;
        Mousetrap = {
            bind: sinon.stub(),
            unbind: sinon.stub()
        };

        view = SugarTest.createView('base', 'Contacts', 'shortcuts-help', undefined, undefined, undefined, undefined, false);
        firstLayout = app.view.createLayout({type: 'base'});
    });

    afterEach(function() {
        sinon.restore();
        app.shortcuts._activeSession = null;
        app.shortcuts._savedSessions = [];
        app.shortcuts._globalShortcuts = {};
        Mousetrap = origMousetrap;

        view.dispose();
        firstLayout.dispose();

        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it('Should display global shortcut help table', function() {
        var expectedResult = '<tr><td>a</td><td>Foo</td></tr><tr><td>b, c</td><td>Bar</td></tr>';

        app.shortcuts.registerGlobal({
            id: 'foo',
            keys: 'a',
            component: firstLayout,
            description: 'Foo',
            listener: $.noop
        });
        app.shortcuts.registerGlobal({
            id: 'bar',
            keys: ['b','c'],
            component: firstLayout,
            description: 'Bar',
            listener: $.noop
        });

        view.render();

        expect(view.$('[data-render=global]').html()).toBe(expectedResult);
    });

    it('Should display contextual shortcut help table', function() {
        var expectedResult = '<tr><td>a</td><td>Foo</td></tr><tr><td>b, c</td><td>Bar</td></tr>';

        app.shortcuts.createSession(['foo','bar'], firstLayout);

        app.shortcuts.register({
            id: 'foo',
            keys: 'a',
            component: firstLayout,
            description: 'Foo',
            listener: $.noop
        });
        app.shortcuts.register({
            id: 'bar',
            keys: ['b','c'],
            component: firstLayout,
            description: 'Bar',
            listener: $.noop
        });
        app.shortcuts.saveSession();

        view.render();

        expect(view.$('[data-render=contextual]').html()).toBe(expectedResult);
    });

    describe('hasCommandKey', function() {
        var getCurrentPlatformStub;
        var testView;

        beforeEach(function() {
            testView = SugarTest.createView('base','Contacts', 'shortcuts-help',
                undefined, undefined, undefined, undefined, false);
            getCurrentPlatformStub = sinon.stub(testView, 'getCurrentPlatform');
        });

        afterEach(function() {
            testView.dispose();
        });

        it('Should be enabled for Macs', function() {
            getCurrentPlatformStub.returns('MacIntel');
            testView.initialize({});

            expect(testView.hasCommandKey).toBe(true);
        });

        it('Should be disabled for Windows', function() {
            getCurrentPlatformStub.returns('Win32');
            testView.initialize({});

            expect(testView.hasCommandKey).toBe(false);
        });
    });

    describe('"mod" shortcut key', function() {
        it('Should display "command" when command key exists', function() {
            var expectedResult = '<tr><td>shift+command+a</td><td>Foo</td></tr><tr><td>command+shift+b, ' +
                'command+c</td><td>Bar</td></tr>';

            view.hasCommandKey = true;

            app.shortcuts.createSession(['foo','bar'], firstLayout);

            app.shortcuts.register({
                id: 'foo',
                keys: 'shift+mod+a',
                component: firstLayout,
                description: 'Foo',
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'bar',
                keys: ['mod+shift+b','mod+c'],
                component: firstLayout,
                description: 'Bar',
                listener: $.noop
            });

            app.shortcuts.saveSession();

            view.render();

            expect(view.$('[data-render=contextual]').html()).toBe(expectedResult);
        });

        it('Should display "ctrl" when command key does not exist', function() {
            var expectedResult = '<tr><td>shift+ctrl+a</td><td>Foo</td></tr><tr><td>ctrl+shift+b, ' +
                'ctrl+c</td><td>Bar</td></tr>';

            view.hasCommandKey = false;

            app.shortcuts.createSession(['foo','bar'], firstLayout);

            app.shortcuts.register({
                id: 'foo',
                keys: 'shift+mod+a',
                component: firstLayout,
                description: 'Foo',
                listener: $.noop
            });
            app.shortcuts.register({
                id: 'bar',
                keys: ['mod+shift+b','mod+c'],
                component: firstLayout,
                description: 'Bar',
                listener: $.noop
            });

            app.shortcuts.saveSession();

            view.render();

            expect(view.$('[data-render=contextual]').html()).toBe(expectedResult);
        });
    });
});
