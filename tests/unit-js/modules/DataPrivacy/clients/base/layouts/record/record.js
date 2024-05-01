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
describe('DataPrivacy.Layouts.Record', function() {
    var app;
    var context;
    var layout;
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        context = new app.Context();
        model = app.data.createBean();

        SugarTest.loadComponent('base', 'layout', 'record');
        SugarTest.loadComponent('base', 'layout', 'record', 'DataPrivacy');

        layout = SugarTest.createLayout('base', 'DataPrivacy', 'record', {}, context);

        app.drawer = {
            open: sinon.stub()
        };
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
        delete app.drawer;
        app = null;
    });

    describe('showMarkForEraseDrawer', function() {
        it('should open a mark for erase drawer', function() {
            var childContext = new app.Context();
            sinon.stub(layout.context, 'getChildContext').returns(childContext);

            layout.showMarkForEraseDrawer(model);

            expect(app.drawer.open).toHaveBeenCalledWith({
                layout: 'mark-for-erasure',
                context: childContext
            });
        });
    });
});
