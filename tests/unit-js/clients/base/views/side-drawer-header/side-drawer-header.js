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

describe('Base.View.SideDrawerHeaderView', function() {
    var view;
    var app;
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'dashboard-headerpane-main');

        view = SugarTest.createView('base', 'Cases', 'side-drawer-header');
        view.toggleFields = sinon.stub();
        view._setButtons = sinon.stub();
        view.setEditableFields = sinon.stub();
        view.setButtonStates = sinon.stub();
        view.clearValidationErrors = sinon.stub();
    });

    afterEach(function() {
        sandbox.restore();
        view = null;
    });

    describe('initialize', function() {
        it('should create a RHS dashboard and enter edit mode', function() {
            var context = new app.Context({
                model: app.data.createBean('Dashboards'),
                create: true
            });
            context.parent = new app.Context({
                module: 'Accounts'
            });
            sandbox.stub(app.metadata, 'getView')
                .withArgs('Accounts', 'dashboard-headerpane', 'Dashboards')
                .returns('dashboard metadata');
            sandbox.stub(app.template, 'getView').withArgs('dashboard-headerpane')
                .returns($.noop);

            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane', null, context, true);

            expect(view.changed).toBeTruthy();
            expect(view.action).toEqual('edit');
            expect(view.inlineEditMode).toBeTruthy();
        });
    });

    describe('event handling', function() {
        it('should trigger the enable actions event on edit save', function() {
            var eventSpy = sandbox.spy(app.events, 'trigger');
            view.setRecordState('view');
            expect(view.display).toEqual(false);
            expect(eventSpy).toHaveBeenCalledWith('drawer:enable:actions');
        });

        it('should trigger the enable actions event on cancel', function() {
            var eventSpy = sandbox.spy(app.events, 'trigger');
            view.cancelClicked();
            expect(view.display).toEqual(false);
            expect(eventSpy).toHaveBeenCalledWith('drawer:enable:actions');
        });

        it('should show the header in edit mode', function() {
            var eventSpy = sandbox.spy(view.model, 'trigger');
            app.events.trigger('drawer:edit');
            expect(view.display).toEqual(true);
            expect(eventSpy).toHaveBeenCalledWith('setMode', 'edit');
        });
    });
});
