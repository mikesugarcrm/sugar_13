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

describe('Base.Layouts.ContentsearchDropdown', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'layout', 'contentsearch-dropdown');
        layout = SugarTest.createLayout(
            'base',
            null,
            'contentsearch-dropdown',
            null,
            null,
            true,
            null,
            true,
            'base'
        );
        sinon.stub(layout, '$').callsFake(function() {
            return {
                data: function() {
                    return 'some-url';
                },
                hide: $.noop,
                show: $.noop
            };
        });
    });

    afterEach(function() {
        layout.dispose();
        layout = null;
        sinon.restore();
    });

    describe('show', function() {
        it('should set body click handler', function() {
            var onStub = sinon.stub($.fn, 'on');
            layout.show();
            expect(onStub).toHaveBeenCalledWith('click.contentsearch', jasmine.any(Function));
        });
    });

    describe('linkClicked', function() {
        it('should open link in a new tab', function() {
            var evt = {
                currentTarget: 'something',
            };
            var openStub = sinon.stub(window, 'open');
            layout.linkClicked(evt);
            expect(openStub).toHaveBeenCalledWith('some-url', '_blank');
        });
    });
});
