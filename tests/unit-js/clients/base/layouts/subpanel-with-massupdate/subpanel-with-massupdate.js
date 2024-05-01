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

describe('Base.Layout.SubpanelWithMassupdate', function() {
    var app,
        layout,
        module = 'Opportunities';

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', module, 'subpanel-with-massupdate', null, null);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

   describe('_stopComponentToggle', function() {
        using('components with different classes', [
            {
                name: 'panel-top',
                expected: true
            },
            {
                name: 'massupdate',
                expected: true
            },
            {
                name: 'test-name',
                expected: false
            },
            {
                $el: $('<div></div>').addClass('subpanel-header'),
                expected: true
            },
            {
                $el: $('<div></div>').addClass('test-class'),
                expected: false
            },
        ], function(component) {
            it('should stop toggle with certain criteria', function() {
                var result = layout._stopComponentToggle(component);
                expect(result).toEqual(component.expected);
            });
        });
    });
});
