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

describe('Emails.BaseEmailOutboundEmailField', function() {
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

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'outbound_email_id',
            type: 'outbound-email',
            viewName: 'edit',
            module: 'Emails',
            model: model,
            context: context,
            loadFromModule: true
        });

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

    it('should populate the help attribute if user is an admin', function() {
        sandbox.stub(app.user, 'get');
        app.user.get.withArgs('type').returns('admin');
        field = SugarTest.createField({
            name: 'outbound_email_id',
            type: 'outbound-email',
            viewName: 'edit',
            module: 'Emails',
            model: model,
            context: context,
            loadFromModule: true
        });
        expect(field.def.help).not.toBeEmpty();
    });

    it('should not populate the help attribute if user is not an admin', function() {
        expect(field.def.help).not.toBeDefined();
    });

    it('should show a warning to the user when a not_authorized error is returned', function() {
        var callback = sandbox.spy();
        var onError = sandbox.spy();
        var error = {
            status: 403,
            code: 'not_authorized',
            message: 'You are not authorized to perform this action.'
        };

        sandbox.stub(app.api, 'enumOptions').callsFake(function(module, field, callbacks) {
            callbacks.error(error);
            callbacks.complete();
        });
        sandbox.stub(app.api, 'defaultErrorHandler');
        sandbox.stub(app.lang, 'get').withArgs('LBL_NO_DATA', field.module).returns('No Data');
        sandbox.stub(app.alert, 'show').callsFake(function(key, options) {
            expect(key).toBe('email-client-status');
            expect(options.level).toBe('warning');
            expect(options.autoClose).toBe(false);
        });
        sandbox.stub(field.view, 'trigger').withArgs('email_not_configured', error);

        field.loadEnumOptions(true, callback, onError);

        expect(callback).toHaveBeenCalledOnce();
        expect(onError).toHaveBeenCalledWith(error);
        expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);
        expect(_.size(field.items)).toBe(1);
        expect(field.items['']).toBe('No Data');
        expect(app.alert.show).toHaveBeenCalledOnce();
        expect(field.view.trigger).toHaveBeenCalledOnce();
    });
});
