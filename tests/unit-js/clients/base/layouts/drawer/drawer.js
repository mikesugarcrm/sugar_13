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
describe("Drawer Layout", function() {
    var moduleName = 'Contacts',
        layoutName = 'drawer',
        sinonSandbox,
        $drawers,
        drawer,
        components,
        app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'businesscard');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns":2,
                    "labels":false,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        "phone_work",
                        "email1",
                        "phone_office",
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();

        sinonSandbox = sinon.createSandbox();

        $drawers = $('<div id="drawers"></div>');
        SugarTest.createLayout('base', moduleName, layoutName, {}, undefined, false, {
            el: $drawers
        });

        app = SugarTest.app;
        drawer = app.drawer;
        app.routing.start();
        app.data.declareModels();

        sinonSandbox.stub(app, 'triggerBefore').callsFake(function() {
            return true;
        });
        sinonSandbox.stub(app.shortcuts, 'saveSession');
        sinonSandbox.stub(app.shortcuts, 'restoreSession');
    });

    afterEach(function() {
        app.router.stop();
        SugarTest.testMetadata.dispose();
        sinonSandbox.restore();
        // delete SugarTest.app.drawer;
        app = null;
        drawer = null;
    });

    describe('Initialize', function() {
        it('Should not have any components and the close callback should be empty', function() {
            expect(drawer._components.length).toBe(0);
            expect(drawer.onCloseCallback.length).toBe(0);
        });

        it('should start in the idle state', function() {
            expect(drawer.isIdle()).toBe(true);
        });
    });

    describe('Open', function() {
        it('Should add drawers every time it is called', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(1);
            expect(drawer._components[drawer._components.length - 1].name).toBe('foo');

            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(2);
            expect(drawer._components[drawer._components.length - 1].name).toBe('bar');
        });

        it('should trigger an app:view:change event', function(){
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function(callback) {
                callback();
            });
            let triggerStub = sinonSandbox.stub(app, 'trigger').callsFake($.noop());
            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            expect(triggerStub.calledOnce).toBe(true);
            expect(triggerStub.firstCall.args[0]).toEqual("app:view:change");
            expect(triggerStub.firstCall.args[1]).toEqual("foo");
        });

        it('Should save scroll positions for each drawer opens', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer.scrollTopPositions.length).toBe(1);

            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer.scrollTopPositions.length).toBe(2);
        });

        it('should go into the opening state and then back to the idle state', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function(cb) {
                expect(drawer.isOpening()).toBe(true);
                cb();
            });
            sinonSandbox.spy(drawer, '_enterState');

            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            expect(drawer._enterState.calledTwice).toBe(true);
            expect(drawer._enterState.getCall(0).args[0]).toEqual('opening');
            expect(drawer._enterState.getCall(1).args[0]).toEqual('idle');
            expect(drawer.isIdle()).toBe(true);
        });

        it('should store the backbone fragment when a drawer is open by the router.', function() {
            var expectedPrevFragment = 'prevFragment';
            var expectedCurrentFragment = 'curFragment';
            sinonSandbox.stub(app.router, 'getPreviousFragment').returns(expectedPrevFragment);
            sinonSandbox.stub(app.router, 'getFragment').returns(expectedCurrentFragment);
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    name: 'foo',
                    'components': [{view: 'record'}]
                },
                context: {
                    create: true,
                    fromRouter: true
                }
            });

            expect(drawer._fragments).toEqual([expectedPrevFragment, expectedCurrentFragment]);

            drawer.open({
                layout: {
                    name: 'bar',
                    components: [{view: 'record'}]
                },
                context: {
                    create: true,
                    fromRouter: true
                }
            });

            expect(drawer._fragments).toEqual([expectedPrevFragment, expectedCurrentFragment, expectedCurrentFragment]);
        });

        describe('omniConsole', function() {
            let closeStub;
            let oldOmniConsole;

            beforeEach(function() {
                closeStub = sinonSandbox.stub();
                oldOmniConsole = app.omniConsole;
                sinonSandbox.stub(drawer, '_initializeComponentsFromDefinition').callsFake(function() {
                    drawer._components = [{
                        loadData: $.noop,
                        render: $.noop,
                        dispose: $.noop
                    }];
                });
                sinonSandbox.stub(drawer, '_updateFragments').callsFake($.noop());
                sinonSandbox.stub(drawer, '_scrollToTop').callsFake($.noop());
                sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
            });

            afterEach(function() {
                closeStub = null;
                app.omniConsole = oldOmniConsole;
            });

            it('should close the omniConsole if it is open in full mode', function() {
                app.omniConsole = {
                    isOpen: function() {
                        return true;
                    },
                    getMode: function() {
                        return 'full';
                    },
                    close: closeStub
                };

                drawer.open({
                    layout: {
                        name: 'bar',
                        components: [{
                            view: 'record'
                        }]
                    },
                    context: {
                        create: true
                    }
                });

                expect(closeStub).toHaveBeenCalled();
            });

            it('should not close the omniConsole if it is open in compact mode', function() {
                app.omniConsole = {
                    isOpen: function() {
                        return true;
                    },
                    getMode: function() {
                        return 'compact';
                    },
                    close: closeStub
                };

                drawer.open({
                    layout: {
                        name: 'bar',
                        components: [{
                            view: 'record'
                        }]
                    },
                    context: {
                        create: true
                    }
                });

                expect(closeStub).not.toHaveBeenCalled();
            });
        });
    });

    describe('Close', function() {
        it('Should remove drawers every time it is called', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function(callback) {
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(2);
            expect(drawer._components[drawer._components.length - 1].name).toBe('bar');

            drawer.close();

            expect(drawer._components.length).toBe(1);
            expect(drawer._components[drawer._components.length - 1].name).toBe('foo');

            drawer.close();

            expect(drawer._components.length).toBe(0);
        });

        it('Should call the onClose callback function', function() {
            var spy = sinonSandbox.spy();
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function(callback) {
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, spy);

            expect(drawer.onCloseCallback.length).toBe(1);

            drawer.close('foo');

            expect(spy.calledWith('foo')).toBe(true);
            expect(drawer.onCloseCallback.length).toBe(0);
        });

        it('Should call closeImmediately if browser does not support css transitions', function() {
            var stub = sinonSandbox.stub(drawer, 'closeImmediately');
            var cssTransitions = Modernizr.csstransitions;
            var animateCloseStub = sinonSandbox.stub(drawer, '_animateCloseDrawer');
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    'name': 'foo',
                    'components': [{'view': 'record'}]
                },
                context: {create: true}
            });

            Modernizr.csstransitions = false;
            drawer.close('foo');
            expect(stub.calledWith('foo')).toBe(true);
            expect(animateCloseStub.called).toBe(false);
            Modernizr.csstransitions = cssTransitions;
            stub.restore();
            animateCloseStub.restore();
        });

        it('should navigate back to the last fragment when closing a drawer opened by the router.', function() {
            var expectedPrevFragment = 'prevFragment';
            var expectedCurrentFragment = 'curFragment';
            sinonSandbox.stub(app.router, 'getPreviousFragment').returns(expectedPrevFragment);
            sinonSandbox.stub(app.router, 'getFragment').returns(expectedCurrentFragment);
            sinonSandbox.stub(app.router, 'navigate');

            sinonSandbox.stub(drawer, '_animateOpenDrawer');
            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {
                    create: true,
                    fromRouter: true
                }
            });
            drawer.close();

            expect(app.router.navigate).toHaveBeenCalledWith(expectedPrevFragment);
            expect(drawer._fragments).toEqual([]);
        });

        it('should trigger an app:view:change event', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function(callback) {
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, $.noop());

            let triggerStub = sinonSandbox.stub(app, 'trigger').callsFake($.noop());

            drawer.close('foo');
            expect(triggerStub.calledOnce).toBe(true);
            expect(triggerStub.firstCall.args[0]).toEqual("app:view:change");
            triggerStub.restore();
        });

        it('Should remove scroll positions that has been closed, and call jQuery scrollTop as expected', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function() {
                drawer._scrollBackToOriginal($());
            });
            sinonSandbox.spy($.fn, 'scrollTop');

            drawer.open({
                layout: {
                    'name': 'foo',
                    'components': [{'view': 'record'}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    'name': 'bar',
                    'components': [{'view': 'record'}]
                },
                context: {create: true}
            });

            expect(drawer.scrollTopPositions.length).toBe(2);
            // called 8 times per drawer opening, 4 times to get initial
            // position, 4 times to scroll elements to top
            expect($.fn.scrollTop.callCount).toBe(16);

            drawer.close();
            expect(drawer.scrollTopPositions.length).toBe(1);
            // called 4 times per drawer closing
            expect($.fn.scrollTop.callCount).toBe(20);

            drawer.close();
            expect(drawer.scrollTopPositions.length).toBe(0);
            expect($.fn.scrollTop.callCount).toBe(24);

            // closing with no scrollTopPositions should not call scrollTop
            drawer.close();
            expect(drawer.scrollTopPositions.length).toBe(0);
            expect($.fn.scrollTop.callCount).toBe(24);
        });

        it('should go into the closing state and then back to the idle state', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function(cb) {
                expect(drawer.isClosing()).toBe(true);
                cb();
            });
            sinonSandbox.spy(drawer, '_enterState');

            drawer.close();

            expect(drawer._enterState.calledTwice).toBe(true);
            expect(drawer._enterState.getCall(0).args[0]).toEqual('closing');
            expect(drawer._enterState.getCall(1).args[0]).toEqual('idle');
            expect(drawer.isIdle()).toBe(true);
        });
    });

    describe('_afterCloseActions', function() {
        describe('omniConsole', function() {
            let openStub;
            let oldOmniConsole;

            beforeEach(function() {
                openStub = sinonSandbox.stub();
                oldOmniConsole = app.omniConsole;
            });

            afterEach(function() {
                openStub = null;
                app.omniConsole = oldOmniConsole;
            });

            it('should open the omniConsole if it can be automatically reopened', function() {
                app.omniConsole = {
                    isOpen: function() {
                        return false;
                    },
                    canBeAutomaticallyReopened: () => true,
                    open: openStub
                };

                drawer._afterCloseActions();

                expect(openStub).toHaveBeenCalled();
            });

            it('should not open the omniConsole if it cannot be automatically reopened', function() {
                app.omniConsole = {
                    isOpen: function() {
                        return false;
                    },
                    canBeAutomaticallyReopened: () => false,
                    open: openStub
                };

                drawer._afterCloseActions();

                expect(openStub).not.toHaveBeenCalled();
            });
        });
    });

    describe('Close immediately', function() {
        it('Should remove drawers every time it is called', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
            sinonSandbox.stub(drawer, '_removeBackdrop').callsFake(function() {});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(2);
            expect(drawer._components[drawer._components.length - 1].name).toBe('bar');

            drawer.closeImmediately();

            expect(drawer._components.length).toBe(1);
            expect(drawer._components[drawer._components.length - 1].name).toBe('foo');

            drawer.closeImmediately();

            expect(drawer._components.length).toBe(0);
        });

        it('Should call the onClose callback function', function() {
            var spy = sinonSandbox.spy();
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
            sinonSandbox.stub(drawer, '_removeBackdrop').callsFake(function() {});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, spy);

            expect(drawer.onCloseCallback.length).toBe(1);

            drawer.closeImmediately('foo');

            expect(spy.calledWith('foo')).toBe(true);
            expect(drawer.onCloseCallback.length).toBe(0);
        });

        it('Should remove scroll positions that has been closed', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
            sinonSandbox.stub(drawer, '_removeBackdrop').callsFake(function() {});
            sinonSandbox.spy($.fn, 'scrollTop');

            drawer.open({
                layout: {
                    'name': 'foo',
                    'components': [{'view': 'record'}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    'name': 'bar',
                    'components': [{'view': 'record'}]
                },
                context: {create: true}
            });

            expect(drawer.scrollTopPositions.length).toBe(2);
            // called 8 times per drawer opening, 4 times to get initial
            // position, 4 times to scroll elements to top
            expect($.fn.scrollTop.callCount).toBe(16);

            drawer.closeImmediately();
            expect(drawer.scrollTopPositions.length).toBe(1);
            // called 4 times per drawer closing
            expect($.fn.scrollTop.callCount).toBe(20);

            drawer.closeImmediately();
            expect(drawer.scrollTopPositions.length).toBe(0);
            expect($.fn.scrollTop.callCount).toBe(24);

            // closing with no scrollTopPositions should not call scrollTop
            drawer.closeImmediately();
            expect(drawer.scrollTopPositions.length).toBe(0);
            expect($.fn.scrollTop.callCount).toBe(24);
        });

        it('should go into the closing state and then back to the idle state', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            sinonSandbox.stub(drawer, '_animateCloseDrawer').callsFake(function(cb) {
                expect(drawer.isClosing()).toBe(true);
                cb();
            });
            sinonSandbox.stub(drawer, '_removeBackdrop');
            sinonSandbox.stub(drawer, '_cleanUpAfterClose');
            sinonSandbox.spy(drawer, '_enterState');

            drawer.closeImmediately();

            expect(drawer._enterState.calledTwice).toBe(true);
            expect(drawer._enterState.getCall(0).args[0]).toEqual('closing');
            expect(drawer._enterState.getCall(1).args[0]).toEqual('idle');
            expect(drawer.isIdle()).toBe(true);
        });
    });

    describe('Load', function() {
        it('Should replace the top-most drawer', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
            sinonSandbox.stub(drawer, '_createBackdrop').callsFake(function() {});
            sinonSandbox.stub(drawer, '_removeBackdrop').callsFake(function() {});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(1);
            expect(drawer._components[drawer._components.length - 1].name).toBe('foo');

            drawer.load({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(drawer._components.length).toBe(1);
            expect(drawer._components[drawer._components.length - 1].name).toBe('bar');
        });

        it('should go into the opening state and then back to the idle state', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            sinonSandbox.stub(drawer, '_createBackdrop');
            sinonSandbox.stub(drawer, '_removeBackdrop');
            sinonSandbox.spy(drawer, '_enterState');

            drawer.load({
                layout: {
                    name: 'bar',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            expect(drawer._enterState.calledTwice).toBe(true);
            expect(drawer._enterState.getCall(0).args[0]).toEqual('opening');
            expect(drawer._enterState.getCall(1).args[0]).toEqual('idle');
            expect(drawer.isIdle()).toBe(true);
        });
    });

    describe('Reset', function() {
        it('Should remove all drawers', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._components.length).toBe(2);
            expect(drawer.onCloseCallback.length).toBe(2);

            drawer.reset();

            expect(drawer._components.length).toBe(0);
            expect(drawer.onCloseCallback.length).toBe(0);
        });
        it('should allow caller to bypass triggerBefore', function() {
            var triggerBeforeStub = sinonSandbox.stub(drawer, 'triggerBefore');
            drawer.reset(false);
            expect(triggerBeforeStub).not.toHaveBeenCalled();
        });

        it('should go into the closing state and then back to the idle state', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer');

            drawer.open({
                layout: {
                    name: 'foo',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            sinonSandbox.stub(drawer, '_removeBackdrop');
            sinonSandbox.spy(drawer, '_enterState');

            drawer.reset({
                layout: {
                    name: 'bar',
                    components: [{view: 'record'}]
                },
                context: {create: true}
            });

            expect(drawer._enterState.calledTwice).toBe(true);
            expect(drawer._enterState.getCall(0).args[0]).toEqual('closing');
            expect(drawer._enterState.getCall(1).args[0]).toEqual('idle');
            expect(drawer.isIdle()).toBe(true);
        });
    });

    describe('_getDrawers(true)', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return no drawers when there are none opened', function() {
            var result = drawer._getDrawers(true);

            expect(result.$next).not.toBeDefined();
            expect(result.$top).not.toBeDefined();
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there is one open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$top.is($mainDiv)).toBe(true);
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there are two open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$top.is(drawer._components[drawer._components.length - 2].$el)).toBe(true);
            expect(result.$bottom.is($mainDiv)).toBe(true);
        });

        it('Should return the correct drawers when there are three open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$top.is(drawer._components[drawer._components.length - 2].$el)).toBe(true);
            expect(result.$bottom.is(drawer._components[drawer._components.length - 3].$el)).toBe(true);
        });
    });

    describe('_getDrawers(false)', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return no drawers when there are none opened', function() {
            var result = drawer._getDrawers(false);

            expect(result.$next).not.toBeDefined();
            expect(result.$top).not.toBeDefined();
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there is one open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next).not.toBeDefined();
            expect(result.$top.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$bottom.is($mainDiv)).toBe(true);
        });

        it('Should return the correct drawers when there are two open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next.is($mainDiv)).toBe(true);
            expect(result.$top.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$bottom.is(drawer._components[drawer._components.length - 2].$el)).toBe(true);
        });

        it('Should return the correct drawers when there are three open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next.is(drawer._components[drawer._components.length - 3].$el)).toBe(true);
            expect(result.$top.is(drawer._components[drawer._components.length - 1].$el)).toBe(true);
            expect(result.$bottom.is(drawer._components[drawer._components.length - 2].$el)).toBe(true);
        });
    });

    describe('isActive()', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div id="target"></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });
        it('should return true for elements when no drawer is open', function() {
            expect(drawer.isActive($("<div></div>"))).toBe(true);
        });
        it('should return true for elements on active drawer', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.$el.children().first().addClass('drawer active');
            expect(drawer.isActive(drawer._getDrawers(false).$top.find(".record"))).toBe(true);
        });
        it('should return false for elements not on active drawer', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.$el.children().last().addClass('drawer active');
            expect(drawer.isActive($("<div></div>"))).toBe(false);
            expect(drawer.isActive(drawer._getDrawers(false).$bottom.find(".record"))).toBe(false);
            expect(drawer.isActive(drawer._getDrawers(false).$top.find(".record"))).toBe(true);
        });
    });

    describe('getHeight()', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div id="target"></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake($.noop());
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('should return 0 when no drawer is open', function() {
            expect(drawer.getHeight()).toEqual(0);
        });

        it('should return true for elements on active drawer', function() {
            var mockHeight = 42;
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });
            drawer._components[0].$el.height(mockHeight); //mock height of component
            expect(drawer.getHeight()).toEqual(mockHeight);
        });
    });

    describe('_isMainAppContent()', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinonSandbox.stub(drawer, '_animateOpenDrawer').callsFake(function() {});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return false for a drawer', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._isMainAppContent(drawer._components[drawer._components.length - 1].$el)).toBe(false);
        });

        it('Should return true for the main application content area', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._isMainAppContent($mainDiv)).toBe(true);
        });
    });

    describe('getActiveDrawerLayout()', function() {
        beforeEach(function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer');
        });

        it('Should return the currently opened drawer layout', function() {
            drawer.open({
                layout: {
                    name: 'foo',
                    components:[{view: 'record'}]
                },
                context: {create: true}
            });

            drawer.open({
                layout: {
                    name: 'bar',
                    components:[{view: 'record'}]
                },
                context: {create: true}
            });

            expect(drawer.getActiveDrawerLayout().name).toBe('bar');
        });

        it('Should return the main controller layout when no drawers are open', function() {
            var result,
                oldController = app.controller;

            app.controller = {
                layout: {
                    name: 'controllerLayout'
                }
            };

            result = drawer.getActiveDrawerLayout();
            expect(result.name).toBe('controllerLayout');

            app.controller = oldController;
        });
    });

    describe('resize drawer', function() {
        beforeEach(function() {
            sinonSandbox.stub(drawer, '_getDrawers').returns({'$top': 'foo'});
            sinonSandbox.stub(drawer, '_expandDrawer');
        });

        it('should resize', function() {
            sinonSandbox.stub(drawer, 'isOpening').returns(false);
            sinonSandbox.stub(drawer, 'isClosing').returns(false);

            drawer._resizeDrawer();

            expect(drawer._expandDrawer).toHaveBeenCalled();
        });

        it('should not resize when opening', function() {
            sinonSandbox.stub(drawer, 'isOpening').returns(true);
            sinonSandbox.stub(drawer, 'isClosing').returns(false);

            drawer._resizeDrawer();

            expect(drawer._expandDrawer).not.toHaveBeenCalled();
        });

        it('should not resize when closing', function() {
            sinonSandbox.stub(drawer, 'isOpening').returns(false);
            sinonSandbox.stub(drawer, 'isClosing').returns(true);

            drawer._resizeDrawer();

            expect(drawer._expandDrawer).not.toHaveBeenCalled();
        });
    });
});
