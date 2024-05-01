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

describe('PortalRecordView', function() {
    var app;
    var moduleName = 'Contacts';
    var viewName = 'record';
    var view;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('portal', 'view', viewName);

        app = SugarTest.app;

        view = SugarTest.createView('portal', moduleName, viewName);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
    });

    describe('initialize', function() {
        it('should not have Pii plugin', function() {
            expect(view.plugins).not.toContain('Pii');
        });
    });
});
