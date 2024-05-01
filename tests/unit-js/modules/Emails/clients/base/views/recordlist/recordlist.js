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

describe('Emails.Views.Recordlist', function() {
    var app;
    var view;
    var sandbox;
    var moduleName = 'Emails';

    beforeEach(function() {
        var context;
        var viewName = 'recordlist';

        app = SugarTest.app;
        app.drawer = {on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop, count: $.noop};
        sandbox = sinon.createSandbox();

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        context = app.context.getContext();
        context.prepare(true);

        sandbox.stub(Backbone.history, 'getFragment').returns('#Emails');
        view = SugarTest.createView('base', moduleName, viewName, null, context, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Delete Confirmation', function() {
        it('should display (no subject) as the record name on delete confirmation', function() {
            var name = view._getNameForMessage(view.model);
            expect(name).toBe('LBL_NO_SUBJECT');
        });

        it('should display the record name when not empty on delete confirmation', function() {
            var recordName = 'Test Record';
            view.model = app.data.createBean('Emails');
            view.model.set('name', recordName);

            var name = view._getNameForMessage(view.model);
            expect(name).toBe(recordName);
        });
    });
});
