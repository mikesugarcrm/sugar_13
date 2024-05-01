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
describe('Base.Layout.MergeWidget', function() {
    var app;
    var sinonSandbox;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'layout', 'help');
        SugarTest.loadComponent('base', 'layout', 'merge-widget', 'DocumentMerges');

        SugarTest.loadComponent('base', 'view', 'merge-widget-header', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'merge-widget-list', 'DocumentMerges');
        SugarTest.loadComponent('base', 'view', 'helplet');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();
        layout = SugarTest.createLayout('base', 'DocumentMerges', 'merge-widget', null, null, true);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        layout.dispose();
        layout = null;
    });

    describe('toggle', function() {
        var renderStub;
        var initPopoverStub;

        beforeEach(function() {
            layout.button = {
                popover: sinonSandbox.spy()
            };
            renderStub = sinonSandbox.stub(layout, 'render');
            initPopoverStub = sinonSandbox.stub(layout, '_initPopover');
        });

        using('different show value', [true, false], function(show) {
            it('should set _isOpen to the value of the argument', function() {
                layout.toggle(show);
                expect(layout._isOpen).toBe(show);
            });
        });

        it('should invert _isOpen if called with `undefined`', function() {
            layout._isOpen = true;
            layout.toggle();
            expect(layout._isOpen).toBe(false);

            layout._isOpen = false;
            layout.toggle();
            expect(layout._isOpen).toBe(true);
        });

        it('should render, initPopover, popover on button, when _isOpen is `true`', function() {
            layout.toggle(true);
            expect(renderStub).toHaveBeenCalled();
            expect(initPopoverStub).toHaveBeenCalled();
            expect(layout.button.popover.withArgs('show').calledOnce).toBe(true);
        });
    });
});
