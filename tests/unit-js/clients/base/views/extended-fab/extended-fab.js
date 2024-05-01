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
describe('Base.View.ExtendedFab', function() {
    var view;
    var app = SUGAR.App;

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate('extended-fab', 'view', 'base');
        view = SugarTest.createView('base', '', 'extended-fab');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('setOptions()', function() {
        it('should set options', function() {
            var options = {
                icon: 'icon',
                label: 'label',
                style: 'style',
                action: 'action'
            };
            view.setOptions(options);
            expect(view.icon).toEqual('icon');
            expect(view.label).toEqual('label');
            expect(view.style).toEqual('style');
            expect(view.action).toEqual('action');
            expect(view.events['click [data-action=action]']).toBeDefined();
            expect(view.$el.hasClass('style')).toBe(true);

            options = {
                icon: 'icon1',
                label: 'label1',
                style: 'style1',
                action: 'action1'
            };
            view.setOptions(options);
            expect(view.icon).toEqual('icon1');
            expect(view.label).toEqual('label1');
            expect(view.style).toEqual('style1');
            expect(view.action).toEqual('action1');
            expect(view.events['click [data-action=action1]']).toBeDefined();
            expect(view.$el.hasClass('style1')).toBe(true);
            expect(view.events['click [data-action=action]']).toBeUndefined();
            expect(view.$el.hasClass('style')).toBe(false);
        });
    });
});
