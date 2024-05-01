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
describe('View.Views.Base.Meetings.ResolveConflictsListView', function() {
    var app, module, sandbox, view;

    module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'resolve-conflicts-list', module);
        view = SugarTest.createView('base', module, 'resolve-conflicts-list');

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should remove the invitees field', function() {
        var clientModel, databaseModel, stub;

        clientModel = app.data.createBean(module, {id: '1', name: 'foo', invitees: ['a', 'b', 'c']});
        databaseModel = app.data.createBean(module, {id: '1', name: 'bar'});

        stub = sandbox.stub(view, '_super');

        view._buildFieldDefinitions(clientModel, databaseModel);

        expect(stub.args[0][1][1].has('invitees')).toBe(false);
    });
});
