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
describe('Base.View.History', function() {
    var view;
    var app;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    it('should open the archive email drawer', function() {
        var context = app.context.getContext();

        context.set({module: 'Contacts'});
        context.prepare();
        view = SugarTest.createView('base', 'Contacts', 'history', null, context);

        view.model.set('_acl', {});
        view.model.set('id', _.uniqueId());
        view.model.set('name', 'Randall Brothers');

        sandbox.stub(app.utils, 'openEmailCreateDrawer');

        view.archiveEmail();

        expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('create');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].related).toBe(view.model);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.module).toBe('EmailParticipants');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('_link')).toBe('to');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent')).toEqual({
            _acl: {},
            type: view.model.module,
            id: view.model.get('id'),
            name: view.model.get('name')
        });
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_type')).toBe(view.model.module);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_id')).toBe(view.model.get('id'));
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_name')).toBe(view.model.get('name'));
    });

    it('should not add a recipient', function() {
        var context = app.context.getContext();

        context.set({module: 'Cases'});
        context.prepare();
        view = SugarTest.createView('base', 'Cases', 'history', null, context);

        view.model.set('_acl', {});
        view.model.set('id', _.uniqueId());
        view.model.set('name', 'Need help!');

        sandbox.stub(app.utils, 'openEmailCreateDrawer');

        view.archiveEmail();

        expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('create');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].related).toBe(view.model);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to).toBeUndefined();
    });

    using('modules', ['Contacts', 'Cases'], function(module) {
        it('should trigger panel-top:refresh events on the parent context', function() {
            var context = app.context.getContext();
            var model = app.data.createBean('Emails');
            var links = app.utils.getLinksBetweenModules(module, 'Emails');
            var parentContext;

            context.set({module: module});
            context.prepare();
            view = SugarTest.createView('base', module, 'history', null, context);

            parentContext = app.context.getContext({module: module});
            parentContext.prepare(true);
            sandbox.spy(parentContext, 'trigger');
            view.context.parent = parentContext;
            view.layout = {
                reloadDashlet: sandbox.stub(),
                off: sandbox.stub()
            };
            sandbox.stub(app.utils, 'openEmailCreateDrawer').callsArgWith(2, model);

            view.archiveEmail();

            expect(view.context.parent.trigger.callCount).toBe(links.length);
        });
    });
});
