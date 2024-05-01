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
describe('SugarLive.Base.Layout.ConfigDrawer', function() {
    var app;
    var layout;
    var context;
    var moduleName = 'SugarLive';

    beforeEach(function() {
        app = SUGAR.App;

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        SugarTest.loadComponent('base', 'layout', 'config-drawer', moduleName);
        context = app.context.getContext();
        layout = SugarTest.createLayout('base', moduleName, 'config-drawer', {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
    });

    describe('loadData', function() {
        it('should not proceed without access', function() {
            sinon.stub(layout, 'checkAccess').returns(false);
            var setStub = sinon.stub(layout.context, 'set');
            var blockStub = sinon.stub(layout, 'blockModule');
            layout.loadData();
            expect(blockStub).toHaveBeenCalled();
            expect(setStub).not.toHaveBeenCalled();
        });

        it('should set details on the context', function() {
            sinon.stub(layout, 'checkAccess').returns(true);
            var setStub = sinon.stub(layout.context, 'set');
            layout.loadData();
            expect(setStub).toHaveBeenCalled();
        });
    });
});
