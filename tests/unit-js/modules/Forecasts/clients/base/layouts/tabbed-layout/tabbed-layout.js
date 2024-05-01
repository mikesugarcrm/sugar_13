
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

describe('Forecasts.Layout.TabbedLayout', function() {
    let app;
    let layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Forecasts', 'tabbed-layout', {}, null, true);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        app.view.reset();
        app.cache.cutAll();
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(layout, '_resize');
        });

        it('should bind a listener for metric:data:ready to _resize', function() {
            layout.bindDataChange();
            expect(layout._resize).not.toHaveBeenCalled();
            app.events.trigger('metric:data:ready');
            expect(layout._resize).toHaveBeenCalled();
        });

        it('should bind a listener for window.resize to _resize', function() {
            layout.bindDataChange();
            expect(layout._resize).not.toHaveBeenCalled();
            $(window).trigger('resize');
            expect(layout._resize).toHaveBeenCalled();
        });
    });
});
