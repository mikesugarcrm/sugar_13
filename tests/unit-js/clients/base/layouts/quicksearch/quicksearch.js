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
describe('View.Layouts.Base.QuicksearchLayout', function() {
    var app, layout, viewA, viewB, viewC,
        viewAisFocusable, viewBisFocusable, viewCisFocusable;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        viewA = app.view.createView({name: 'view-a'});
        viewB = app.view.createView({name: 'view-b'});
        viewC = app.view.createView({name: 'view-c'});
        sinon.stub(viewA, 'trigger');
        sinon.stub(viewB, 'trigger');
        sinon.stub(viewC, 'trigger');
        viewA.isFocusable = function() {return viewAisFocusable};
        viewB.isFocusable = function() {return viewBisFocusable};
        viewC.isFocusable = function() {return viewCisFocusable};
        SugarTest.addComponent('base', 'view', 'view-a', viewA);
        SugarTest.addComponent('base', 'view', 'view-b', viewB);
        SugarTest.addComponent('base', 'view', 'view-c', viewC);
        SugarTest.testMetadata.set();
        var defaultMeta = {
            components: [
                {view: 'view-a'},
                {view: 'view-b'},
                {view: 'view-c'}
            ]
        };
        layout = SugarTest.createLayout('base', null, 'quicksearch', defaultMeta);
        app.routing.start();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        viewA.dispose();
        viewB.dispose();
        viewC.dispose();
        layout.dispose();
        sinon.restore();
        app.shortcuts._activeSession = null;
        app.shortcuts._savedSessions = [];
        app.shortcuts._globalShortcuts = {};
        layout = viewA = viewB = viewC = viewAisFocusable = viewBisFocusable = viewCisFocusable = null;
        app.router.stop();
    });

    describe('firing navigation events on components', function() {
        it('should trigger events on the component in the forward case', function() {
            layout.trigger('navigate:next:component');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:receive');
        });

        it('should trigger events on the component in the backward case', function() {
            layout.trigger('navigate:previous:component');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:receive');
        });
    });

    describe('navigate:next:component', function() {
        beforeEach(function() {
            viewAisFocusable = true;
        });

        it('should find the next focusable component', function() {
            viewBisFocusable = true;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(1);
        });

        it('should skip unfocusable components', function() {
            viewBisFocusable = false;
            viewCisFocusable = true;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(2);
        });

        it('should skip nav if there are no focusable components', function() {
            viewBisFocusable = false;
            viewCisFocusable = false;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(0);
        });
    });

    describe('navigate:previous:component', function() {
        beforeEach(function() {
            viewCisFocusable = true;
            layout.compOnFocusIndex = 2;
        });

        it('should find the previous focusable component', function() {
            viewBisFocusable = true;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(1);
        });

        it('should skip unfocusable components', function() {
            viewAisFocusable = true;
            viewBisFocusable = false;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(0);
        });

        it('should skip nav if there are no focusable components', function() {
            viewAisFocusable = false;
            viewBisFocusable = false;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(2);
        });
    });
    describe('navigate:to:component', function() {
        beforeEach(function() {
            viewAisFocusable = true;
            viewBisFocusable = true;
            layout.compOnFocusIndex = 0;
        });
        it('should navigate directly to the specified component', function() {
            layout.trigger('navigate:to:component', 'view-b');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:lost');
            expect(viewB.trigger).toHaveBeenCalledWith('navigate:focus:receive');
            expect(layout.compOnFocusIndex).toEqual(1);
        });
    });
    describe('\'quicksearch:close\' event', function() {
        beforeEach(function() {
            viewAisFocusable = true;
            layout.compOnFocusIndex = 0;
            sinon.spy(layout, 'removeFocus');
            sinon.stub(layout, 'collapse');
            sinon.stub(layout.collection, 'abortFetchRequest');
            layout.expanded = true;
        });
        it('should lose focus and abort the fetch request', function() {
            layout.trigger('quicksearch:close');
            expect(layout.removeFocus).toHaveBeenCalled();
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:lost');
            expect(layout.collection.abortFetchRequest).toHaveBeenCalled();
        });
        using('keep expanded to true or false', [true, false], function(keepExpanded) {
            it('should collapse or not according to the argument', function() {
                layout.trigger('quicksearch:close', keepExpanded);
                if (keepExpanded) {
                    expect(layout.collapse).not.toHaveBeenCalled();
                } else {
                    expect(layout.collapse).toHaveBeenCalled();
                }
            });
        });
    });

    describe('expand', function() {
        beforeEach(function() {
            sinon.stub(layout, 'trigger');
            sinon.stub(layout, 'routerHandler');
            sinon.spy(app.router, 'off');
            sinon.stub(app.router, 'on');
            sinon.stub($.prototype, 'removeClass');
            sinon.stub($.prototype, 'addClass');
            sinon.stub($.prototype, 'animate');
            sinon.stub(layout, 'closestComponent').callsFake(function() {
                return {
                    trigger: sinon.stub(),
                    getModuleListMinWidth: sinon.stub(),
                    setModuleListResize: sinon.stub()
                };
            });
        });
        using('update width to true or false', [true, false], function(update) {
            it('should expand the bar', function() {
                var newWidth = 100;
                sinon.stub($.prototype, 'width');
                sinon.stub(layout, '_calculateExpansion').returns(newWidth);

                layout.expand(update);

                expect(layout.expanded).toBe(true);
                expect(app.router.off).toHaveBeenCalled();
                expect(app.router.on).toHaveBeenCalled();
                expect(layout.trigger).toHaveBeenCalledWith('quicksearch:expanded');
                expect(layout.trigger).toHaveBeenCalledWith('quicksearch:button:toggle');

                if (update) {
                    expect($.prototype.width).toHaveBeenCalledWith(newWidth);
                } else {
                    expect($.prototype.animate).toHaveBeenCalledWith({width: newWidth});
                }
            });
        });

        it('should toggle css classes and send the focus to the bar in responsive mode', function() {
            layout.isResponsiveMode = true;
            layout.expand(true);

            expect($.prototype.addClass).toHaveBeenCalledWith('expanded');
            expect(layout.trigger).toHaveBeenCalledWith('navigate:to:component', 'quicksearch-bar');
            expect($.prototype.animate).not.toHaveBeenCalledWith();
        });
    });

    describe('collapse', function() {
        beforeEach(function() {
            sinon.stub(layout, 'trigger');
            sinon.stub(layout, 'routerHandler');
            sinon.spy(app.router, 'off');
            sinon.stub($.prototype, 'removeClass');
            sinon.stub($.prototype, 'addClass');
        });
        it('should collapse the search bar', function() {
            sinon.stub($.prototype, 'width');
            sinon.stub(layout, 'closestComponent').callsFake(function() {
                return {
                    trigger: sinon.stub(),
                    resize: sinon.stub(),
                    getModuleListMinWidth: sinon.stub(),
                    setModuleListResize: sinon.stub()
                };
            });

            layout.expanded = true;

            layout.collapse();

            expect(layout.expanded).toBe(false);
            expect(layout.trigger).toHaveBeenCalledWith('quicksearch:collapse');
            expect(layout.trigger).toHaveBeenCalledWith('quicksearch:button:toggle', true);
            expect(app.router.off).toHaveBeenCalled();

            expect($.prototype.width).toHaveBeenCalledWith('');
        });

        it('should toggle css classes in responsive mode', function() {
            layout.expanded = true;
            layout.isResponsiveMode = true;

            layout.collapse();

            expect($.prototype.removeClass).toHaveBeenCalledWith('expanded');
        });
    });
});
