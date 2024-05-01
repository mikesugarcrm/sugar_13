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
describe('Base.Layout.Help', function() {
    var app;
    var sinonSandbox;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'help');
        SugarTest.loadComponent('base', 'view', 'helplet');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        layout = SugarTest.createLayout('base', null, 'help');
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        layout.dispose();
        layout = null;
    });

    describe('toggle', function() {
        var initHelpObjectStub;
        var renderStub;
        var initPopoverStub;
        var bindClickStub;
        var unbindClickStub;
        var triggerStub;

        beforeEach(function() {
            layout.button = {
                popover: sinonSandbox.spy()
            };
            initHelpObjectStub = sinonSandbox.stub(layout, '_initHelpObject');
            renderStub = sinonSandbox.stub(layout, 'render');
            initPopoverStub = sinonSandbox.stub(layout, '_initPopover');
            bindClickStub = sinonSandbox.stub(layout, 'bindOutsideClick');
            unbindClickStub = sinonSandbox.stub(layout, 'unbindOutsideClick');
            triggerStub = sinonSandbox.stub(layout, 'trigger');
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

        using('different show values', [true, false, undefined], function(show) {
            it('should always destroy the popover', function() {
                layout.toggle(show);
                expect(layout.button.popover.withArgs('dispose').calledOnce).toBe(true);
            });
        });

        it('should initHelpObject, render, initPopover, popover on button, and bindOutsideClick, ' +
            'when _isOpen is `true`', function() {
            layout.toggle(true);
            expect(initHelpObjectStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
            expect(initPopoverStub).toHaveBeenCalled();
            expect(layout.button.popover.withArgs('show').calledOnce).toBe(true);
            expect(bindClickStub).toHaveBeenCalled();
            expect(unbindClickStub).not.toHaveBeenCalled();
        });

        it('should unbindOutsideClick when _isOpen is `false`', function() {
            layout.toggle(false);
            expect(unbindClickStub.called).toBe(true);
            expect(bindClickStub.called).toBe(false);
        });

        it('should called trigger with "show" when _isOpen is `true`', function() {
            layout.toggle(true);
            expect(triggerStub.withArgs('show').calledOnce).toBe(true);
        });

        it('should call trigger with "hide" when _isOpen is `false`', function() {
            layout.toggle(false);
            expect(triggerStub.withArgs('hide').calledOnce).toBe(true);
        });
    });

    describe('bindOutsideClick', function() {
        it('should bind the click event on `$(body)`', function() {
            var bindStub = sinonSandbox.stub($.fn, 'bind');
            layout.cid = 'cid';

            layout.bindOutsideClick();
            expect(bindStub.withArgs('click.cid').calledOnce).toBe(true);
        });
    });

    describe('unbindOutsideClick', function() {
        it('should unbind the click event on `$(body)`', function() {
            var unbindStub = sinonSandbox.stub($.fn, 'unbind');
            layout.cid = 'cid';

            layout.unbindOutsideClick();
            expect(unbindStub.withArgs('click.cid').calledOnce).toBe(true);
        });
    });

    describe('close', function() {
        it('should call toggle with `false`', function() {
            var toggleStub = sinonSandbox.stub(layout, 'toggle');
            layout.close();
            expect(toggleStub.withArgs(false).calledOnce).toBe(true);
        });
    });
});
