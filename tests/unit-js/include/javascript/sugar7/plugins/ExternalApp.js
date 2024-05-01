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
describe('Plugins.ExternalApp', function() {
    var app;
    var plugin;
    var view;
    var getMetadata = function() {
        return {
            hasExternalFields: true,
            panels: [
                {
                    fields: [
                        {
                            name: 'test',
                            type: 'external-app-field',
                            loadField: ['test'],
                        }
                    ]
                }
            ]
        };
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('ExternalApp');
        plugin = app.plugins.plugins.view.ExternalApp;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('record', getMetadata(), 'Accounts');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'view', 'record');
        view = SugarTest.createView('base', 'Accounts', 'record');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app = null;
    });

    describe('addExternalAppFieldsToContext', function() {
        it('should add fields to context if hasExternalFields is set', function() {
            view.addExternalAppFieldsToContext();
            expect(view.context.get('fields')).toEqual(['test']);
        });
    });
});
