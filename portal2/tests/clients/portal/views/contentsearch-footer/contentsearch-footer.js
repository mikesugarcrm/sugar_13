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

describe('Portal.Layouts.ContentsearchFooter', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('portal', 'view', 'contentsearch-footer');
        view = SugarTest.createView(
            'portal',
            null,
            'contentsearch-footer',
            null,
            context,
            true,
            null,
            true,
            'portal'
        );
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
    });

    describe('createCase', function() {
        it('should pre-populate name', function() {
            app.drawer = {
                open: sinon.stub()
            };
            view.data = {
                options: {
                    q: 'term'
                }
            };
            view.createCase();
            expect(app.drawer.open).toHaveBeenCalled();
            expect(app.drawer.open.lastCall.args[0].context.model.get('name')).toEqual('term');
        });
    });
});
