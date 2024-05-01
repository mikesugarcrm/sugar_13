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

describe('OutboundEmail.BaseNameField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.declareData('base', 'OutboundEmail', true, false);
        SugarTest.loadHandlebarsTemplate('name', 'field', 'base', 'detail', 'OutboundEmail');
        SugarTest.loadComponent('base', 'field', 'name');
        SugarTest.loadComponent('base', 'field', 'name', 'OutboundEmail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'OutboundEmail'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('declaring the help def', function() {
        it('should set the help def if it is a system account', function() {
            field = SugarTest.createField({
                name: 'name',
                type: 'name',
                viewName: 'detail',
                module: 'OutboundEmail',
                model: model,
                context: context,
                loadFromModule: true
            });
            field.model.set('type', 'system');
            field.render();

            expect(field.def.help).not.toBeEmpty();
        });

        using('non-system accounts', ['system-override', 'user'], function(type) {
            it('should not set the help def if it is not a system account', function() {
                field = SugarTest.createField({
                    name: 'name',
                    type: 'name',
                    viewName: 'detail',
                    module: 'OutboundEmail',
                    model: model,
                    context: context,
                    loadFromModule: true
                });
                field.model.set('type', type);
                field.render();

                expect(field.def.help).toBeUndefined();
            });
        });
    });
});
