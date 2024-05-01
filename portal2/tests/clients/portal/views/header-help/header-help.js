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

describe('PortalHeaderHelpView', function() {
    var app;
    var view;
    var drawer;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'header-help');
        SugarTest.loadComponent('portal', 'view', 'header-help');
        app = SugarTest.app;
        view = SugarTest.createView('portal', '', 'header-help', {});
        drawer = app.drawer;
        app.drawer = {
            open: sinon.stub()
        };
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
        app.drawer = drawer;
    });

    describe('_createCase', function() {
        it('should open drawer to create a new case', function() {
            view._createNewCase();
            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.drawer.open.lastCall.args[0].layout).toEqual('create');
            expect(app.drawer.open.lastCall.args[0].context.create).toBeTruthy();
            expect(app.drawer.open.lastCall.args[0].context.module).toEqual('Cases');
        });
    });
});
