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
describe('Sugar.Tooltip', function() {

    var app, tooltip, $fixture;

    beforeEach(function() {
        app = SugarTest.app;
        tooltip = app.tooltip;

        $fixture = $('<div id="Sugar.Tooltips">').appendTo('body');
    });

    afterEach(function() {
        sinon.restore();
        $fixture.remove();
        // remove any tooltips created in the DOM (added in to the body)
        $('.tooltip').remove();
    });

    it('should allow clear to be called without any existing tooltip', function() {
        expect($('[aria-describedby]').length).toBe(0);
        app.tooltip.clear();
        expect($('[aria-describedby]').length).toBe(0);
    });

    describe('touch devices', function() {

        beforeEach(function() {
            sinon.stub(Modernizr, 'touch').value(true);
            tooltip.init();
        });

        it('should have tooltips disabled', function() {
            var $el = $('<div rel="tooltip" title="tooltip unit test">').appendTo($fixture);
            $el.trigger('mouseenter');
            expect($el.data('bs.tooltip')).not.toBeDefined();
        });
    });

    describe('non-touch devices tooltips', function() {

        var $el;

        beforeEach(function() {
            sinon.stub(Modernizr, 'touch').callsFake(false);
            tooltip.init();

            $el = $('<div rel="tooltip" title="tooltip unit test">').appendTo($fixture);
        });

        it('should remove any tooltips when clicking in the element', function() {
            $el.trigger('mouseenter');
            $el.trigger('click');
            expect($('[aria-describedby]').length).toBe(0);
        });

        it('should remove any tooltips when clicking on any element', function() {
            var $el2 = $('<div>Unrelated Div</div>').appendTo($fixture);
            $el.trigger('mouseenter');
            $el2.trigger('click');
            expect($('[aria-describedby]').length).toBe(0);
        });

        it('should remove any tooltips when calling clear', function() {
            $el.trigger('mouseenter');
            app.tooltip.clear();
            expect($('[aria-describedby]').length).toBe(0);
        });

        it('should not display a tooltip if the `show` event namespace is not `bs.tooltip`', function() {
            $el.trigger($.Event('show'));
            expect($('.tooltip').length).toBe(0);
        });

        it('should not update the current tooltip if the `shown` event namespace is not `bs.tooltip`', function() {
            $el.trigger('mouseenter');
            var expectedCurrentTip = app.tooltip._$currentTip;

            $el.trigger($.Event('shown'));
            expect(app.tooltip._$currentTip).toEqual(expectedCurrentTip);
        });
    });

    describe('non-touch devices ellipsis', function() {
        var $el;

        beforeEach(function() {
            sinon.stub(Modernizr, 'touch').callsFake(false);
            tooltip.init();

            $el = $('<div class="ellipsis_inline" title="tooltip unit test"></div>').appendTo($fixture);
        });
    });
});
