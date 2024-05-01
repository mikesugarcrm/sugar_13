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
describe("Planned Activities", function () {
    var moduleName = 'Home',
        app, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'tabbed-dashlet');
        SugarTest.loadComponent('base', 'view', 'history');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('planned-activities', {
            'tabs': [
                {
                    'module': 'Meetings',
                    'invitation_actions' : {
                        'name' : 'accept_status_users',
                        'type' : 'invitation-actions'
                    }
                }
            ]
        });

        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinon.restore();
    });

    it('should instantiate an invitation collection if invitation_actions is set', function() {
        var meta = _.extend(app.metadata.getView(moduleName, 'planned-activities'), {
            'last_state': 'ignore'
        });

        view = SugarTest.createView('base', moduleName, 'planned-activities', meta);

        // stub out our test method
        view._createInvitationsCollection = sinon.stub();

        // mock out the parent call on _initTabs
        view.dashletConfig = {
            tabs: view.meta.tabs
        };

        view._initTabs();
        expect(view._createInvitationsCollection.called).toBeTruthy();
        view.dispose();
    });

    it('should not instantiate an invitation collection if invitation_actions is not set', function() {
        var meta = {
            'tabs' : [
                {
                    'module': 'Meetings'
                }
            ],
            'last_state': 'ignore'
        };
        view = SugarTest.createView('base', moduleName, 'planned-activities', meta);

        //stub out our test method
        view._createInvitationsCollection = sinon.stub();

        // mock out the parent call on _initTabs
        view.dashletConfig = {
            tabs: view.meta.tabs
        };

        view._initTabs();
        expect(view._createInvitationsCollection.called).toBeFalsy();
        view.dispose();
    });
});

