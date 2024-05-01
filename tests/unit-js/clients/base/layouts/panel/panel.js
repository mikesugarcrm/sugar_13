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

describe('Base.Layout.Panel', function() {
    var app, layout;
    var module = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;
        var context = app.context.getContext();
        context.set({
            module: module,
            layout: 'panel'
        });
        context.prepare();
        context.parent = app.context.getContext();
        layout = SugarTest.createLayout('base', module, 'panel', null, context);
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

    describe('initialize', function() {
        using('different configurations', [
            {
                collapsedSubpanels: true,
                lastState: 'show',
                expected: true
            },
            {
                collapsedSubpanels: true,
                lastState: 'hide',
                expected: true
            },
            {
                collapsedSubpanels: false,
                lastState: 'show',
                expected: false
            },
            {
                collapsedSubpanels: false,
                lastState: 'hide',
                expected: true
            },
            {
                collapsedSubpanels: undefined,
                lastState: undefined,
                expected: true
            }
        ], function(options) {
            it('should set the collapsed state on the context', function() {
                sinon.stub(app.user.lastState, 'get').returns(options.lastState);
                app.config.collapseSubpanels = options.collapsedSubpanels;

                var context = app.context.getContext();
                context.set({
                    module: module,
                    layout: 'panel'
                });
                context.prepare();
                context.parent = app.context.getContext();
                var testLayout = SugarTest.createLayout('base', module, 'panel', null, context);
                expect(testLayout.context.get('collapsed')).toEqual(options.expected);

                testLayout.dispose();
            });
        });
    });

    describe('toggle', function() {
        beforeEach(function() {
            sinon.stub(layout.context, 'loadData');
            sinon.stub(app.user.lastState, 'set');
            sinon.spy(layout.context, 'set');
        });

        using('different values', [
            {
                isCreate: true,
                show: true,
                setCalled: false
            },
            {
                isCreate: true,
                show: false,
                setCalled: false
            },
            {
                isCreate: false,
                show: true,
                setCalled: true
            },
            {
                isCreate: false,
                show: false,
                setCalled: true
            }
        ], function(options) {
            it('should never toggle a create subpanel', function() {
                layout.context.set('isCreateSubpanel', options.isCreate);
                layout.toggle(options.show);

                expect(layout.context.set.calledWith('collapsed', !options.show)).toEqual(options.setCalled);
            });
        });
    });

    describe('_stopComponentToggle', function() {
        using('components with different classes', [
            {
                $el: $('<div></div>').addClass('subpanel-header'),
                expected: true
            },
            {
                $el: $('<div></div>').addClass('test-class'),
                expected: false
            }
        ], function(component) {
            it('should stop toggle with certain criteria', function() {
                var result = layout._stopComponentToggle(component);
                expect(result).toEqual(component.expected);
            });
        });
    });
});
