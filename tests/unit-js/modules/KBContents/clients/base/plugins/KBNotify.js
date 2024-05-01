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

describe('modules.KBContents.clients.base.plugins.KBNotify', function() {
    var app;
    var moduleName = 'KBContents';
    var sinonSandbox;
    var components;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBNotify',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create', moduleName);
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'editablelistbutton');
        SugarTest.loadComponent('base', 'field', 'editablelistbutton', moduleName);

        layout = SugarTest.createLayout('base', moduleName, 'list');

        components = [];
        components.push(SugarTest.createView('base', moduleName, 'create', null, null, true, layout));
        components.push(SugarTest.createView('base', moduleName, 'recordlist', null, null, true, layout));
        components.push(SugarTest.createField(
            'base', 'editablelistbutton', 'editablelistbutton', 'recordlist', {}, moduleName, null, null, true
        ));
    });

    afterEach(function() {
        _.each(components, function(component) {
            component.dispose();
        });
        layout.dispose();
        delete app.plugins.plugins.view.KBNotify;
        sinonSandbox.restore();
        app.view.reset();
    });

    it('Notify all should notify all subscribed components', function() {
        var eventName = 'some:custom:event';
        var callbackStubs = [];

        _.each(components, function(component) {
            var callbackStub = sinonSandbox.stub();
            callbackStubs.push(callbackStub);
            component.on(eventName, callbackStub);
        });

        // random component
        var component = components[Math.floor(Math.random() * components.length)];
        component.notifyAll(eventName);

        _.each(callbackStubs, function(callbackStub) {
            expect(callbackStub).toHaveBeenCalled();
        });
    });
});

