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

describe('modules.KBContents.clients.base.fields.EditablelistbuttonField', function() {
    var app;
    var moduleName = 'KBContents';
    var fieldName = 'editablelistbutton';
    var sinonSandbox;

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

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'editablelistbutton');
        SugarTest.loadComponent('base', 'field', fieldName, moduleName);

    });

    afterEach(function() {
        delete app.plugins.plugins.view.KBNotify;
        sinonSandbox.restore();
        app.view.reset();
    });

    it('Success updating callback should trigger kb:collection:updated', function() {
        var field = SugarTest.createField('base', fieldName, fieldName, 'list', {}, moduleName, null, null, true);
        var callbackStub = sinonSandbox.stub();
        var eventCallbackStub = sinonSandbox.stub();
        field.on('kb:collection:updated', eventCallbackStub);

        var options = {'success': callbackStub};
        var customSaveOptions = field.getCustomSaveOptions(options);
        customSaveOptions.success();

        expect(callbackStub).toHaveBeenCalled();
        expect(eventCallbackStub).toHaveBeenCalled();
    });
});
