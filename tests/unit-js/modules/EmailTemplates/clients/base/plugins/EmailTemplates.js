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
describe('EmailTemplates.Base.Plugins.EmailTemplates', function() {
    var app;
    var plugin;
    var moduleName = 'EmailTemplates';
    var model;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadFile(
            '../modules/EmailTemplates/clients/base/plugins',
            'EmailTemplates',
            'js',
            function(data) {
                app.events.off('app:init');
                eval(data);
                app.events.trigger('app:init');
            }
        );
        plugin = app.plugins.plugins.view.EmailTemplates;
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lorem ipsum dolor sit amet'
        });
        plugin.model = model;
    });

    afterEach(function() {
        sinon.restore();
        plugin = null;
        model = null;
    });

    describe('_insertVariable', function() {
        using('different text_only values', [true, false], function(isTextOnly) {
            it('should call insert variable text if in text only mode', function() {
                sinon.stub(model, 'get').returns(isTextOnly);
                sinon.stub(plugin, '_insertVariableText');
                sinon.stub(plugin, '_insertVariableHtml');

                plugin._insertVariable('sample');
                expect(plugin._insertVariableText.called).toEqual(isTextOnly);
                expect(plugin._insertVariableHtml.called).toEqual(!isTextOnly);
            });
        });
    });
});
