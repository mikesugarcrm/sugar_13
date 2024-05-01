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

const User = require('../../../src/core/user');

describe('Core/PluginManager', function() {
    beforeEach(function() {
        this.pluginManager = require('../../../src/core/plugin-manager');
        SugarTest.seedMetadata(true);
        User.set('module_list', fixtures.metadata.module_list);
    });

    afterEach(function() {
        this.pluginManager.plugins = {
            view: {},
            field: {},
            layout: {},
            model: {},
            collection: {},
        };
    });

    it('should register plugins for views', function() {
        let testPlugin = {foo: 'bar'};

        this.pluginManager.register('test', 'view', testPlugin);
        expect(this.pluginManager.plugins.view.test).toEqual(testPlugin);
        expect(this.pluginManager.plugins.field.test).toEqual(null);
        expect(this.pluginManager.plugins.layout.test).toEqual(null);
    });

    it('should register plugins for multiple types', function() {
        let testPlugin = {foo: 'bar'};

        this.pluginManager.register('test', ['view', 'layout'], testPlugin);

        expect(this.pluginManager.plugins.view.test).toEqual(testPlugin);
        expect(this.pluginManager.plugins.layout.test).toEqual(testPlugin);
        expect(this.pluginManager.plugins.field.test).toEqual(null);
    });

    it('should mix into existing objects', function() {
        let testPlugin = {foo: 'bar'};
        let testView = {
            foo: 'notBar',
            prop1: 'not overridden',
            plugins: ['test'],
        };

        this.pluginManager.register('test', ['view', 'layout'], testPlugin);
        this.pluginManager.attach(testView, 'view');

        expect(testView.foo).toEqual('bar');
        expect(testView.prop1).toEqual('not overridden');
    });

    it('should merge the events list', function() {
        let testPlugin = {
            events: {'click div.test': 'pluginClickCallback'},
            pluginClickCallback: function() {},
        };
        let testView = {
            events: {'click div.somethingElse': 'clickCallback'},
            clickCallback: function() {},
            plugins: ['test'],
        };

        this.pluginManager.register('test', ['view', 'layout'], testPlugin);
        this.pluginManager.attach(testView, 'view');

        expect(testView.events['click div.test']).toEqual('pluginClickCallback');
        expect(testView.events['click div.somethingElse']).toEqual('clickCallback');
    });

    it('should call on onAttach callback', function() {
        let testPlugin = {
            plugMix: 'Mix a little of this ',
            onAttach: function() {
                this.out = this.plugMix + this.fromView;
            }
        };
        let testView = {
            fromView: 'with a little of that.',
            plugins: ['test']
        };
        sinon.spy(testPlugin, 'onAttach');
        this.pluginManager.register('test', ['view', 'layout'], testPlugin);
        this.pluginManager.attach(testView, 'view');
        expect(testPlugin.onAttach).toHaveBeenCalled();
        expect(testView.out).toEqual('Mix a little of this with a little of that.');
    });

    it('should call on onDetach callback when a plugin is disposed', function() {
        let testPlugin = {
            plugMix: 'Mix a little of this ',
            onDetach: function() {
                this.out = this.plugMix + this.fromView;
            },
        };
        let testView = {
            fromView: 'with a little of that.',
            plugins: ['test'],
        };
        sinon.spy(testPlugin, 'onDetach');
        this.pluginManager.register('test', ['view', 'layout'], testPlugin);
        this.pluginManager.attach(testView, 'view');
        this.pluginManager.detach(testView, 'view');
        expect(testPlugin.onDetach).toHaveBeenCalledOnce();
        expect(testView.out).toEqual('Mix a little of this with a little of that.');
    });

    it('should call on onAttach when using object instead of array for attach', function() {
        let testPlugin = {
            onAttach: _.noop,
            events: 'test',
        };
        let testView = {
            plugins: [{test: testPlugin, view: testPlugin}],
        };

        sinon.spy(testPlugin, 'onAttach');

        this.pluginManager.register('test', ['view', 'layout'], testPlugin);

        this.pluginManager.attach(testView, 'view');
        expect(testPlugin.onAttach).toHaveBeenCalledOnce();
    });

    it('should not call on onAttach for a non-enabled plugin', function() {
        let testPlugin = {
            onAttach: _.noop,
            events: 'test',
        };
        let testView = {
            plugins: ['test'],
            disabledPlugins: ['test'],
        };

        sinon.spy(testPlugin, 'onAttach');

        this.pluginManager.register('test', ['view', 'layout'], testPlugin);

        this.pluginManager.attach(testView, 'view');
        expect(testPlugin.onAttach).not.toHaveBeenCalled();
    });
});
