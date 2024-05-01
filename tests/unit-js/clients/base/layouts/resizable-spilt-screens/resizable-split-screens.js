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
describe('Base.Layouts.ResizableSplitScreens', function() {
    var app;
    var layout;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        layout = SugarTest.createLayout('base', '', 'resizable-split-screens', {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();

        app = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    describe('initialize()', function() {
        it('should call init methods', function() {
            var _beforeInit = sinon.stub(layout, '_beforeInit');
            var _initProperties = sinon.stub(layout, '_initProperties');
            var _registerEvents = sinon.stub(layout, '_registerEvents');

            layout.initialize({
                context: context,
            });

            expect(_beforeInit).toHaveBeenCalled();
            expect(_initProperties).toHaveBeenCalled();
            expect(_registerEvents).toHaveBeenCalled();
        });
    });

    describe('_beforeInit()', function() {
        it('should set _isLoading flag', function() {
            layout._beforeInit({
                meta: {
                    isLoading: true,
                }
            });

            expect(layout._isLoading).toEqual(true);
        });
    });

    describe('_initProperties()', function() {
        it('should call setResizeConfig()', function() {
            var spy = sinon.stub(layout, 'setResizeConfig');

            layout._initProperties();

            expect(spy).toHaveBeenCalled();
        });
    });

    describe('_registerEvents()', function() {
        it('should call listenTo()', function() {
            var spy = sinon.stub(layout, 'listenTo');

            layout._registerEvents();

            expect(spy).toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        it('should call _arrangeElements()', function() {
            var spy = sinon.stub(layout, '_arrangeElements');

            layout._render();

            expect(spy).toHaveBeenCalled();
        });
    });

    describe('_arrangeElements()', function() {
        it('should properly arrange elements', function() {
            var spy = sinon.stub(layout, '_updateUIElements');

            layout._arrangeElements();

            expect(spy).not.toHaveBeenCalled();
            expect(layout._elementsMoved).toEqual(true);
        });
    });

    describe('_updateUIElements()', function() {
        it('should update screens and resizer', function() {
            layout._loadingScreen = {
                show: function() {},
            };

            layout._mainContainer = {
                hide: function() {},
            };

            layout._isLoading = true;

            var show = sinon.stub(layout._loadingScreen, 'show');
            var hide = sinon.stub(layout._mainContainer, 'hide');

            layout._updateUIElements();

            expect(show).toHaveBeenCalled();
            expect(hide).toHaveBeenCalled();
        });
    });

    describe('startResizing()', function() {
        it('should handle mouse down event', function() {
            layout._firstScreen = {
                width: function() {
                    return 100;
                },
                height: function() {
                    return 200;
                },
            };

            layout.startResizing({
                clientX: 10,
                clientY: 20,
            });

            expect(layout._isDragging).toEqual(true);
            expect(layout._xPos).toEqual(10);
            expect(layout._yPos).toEqual(20);
            expect(layout._firstScreenInitialWidth).toEqual(100);
            expect(layout._firstScreenInitialHeight).toEqual(200);
        });
    });

    describe('resizingHandler()', function() {
        it('should not resize screens', function() {
            layout._isDragging = false;
            layout._direction = 'horizontal';
            layout._mainContainer = {
                height: function() {},
            };

            var spy = sinon.stub(layout._mainContainer, 'height');

            layout.resizingHandler();

            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('stopResizing()', function() {
        it('should not call trigger()', function() {
            layout._isDragging = false;

            var spy = sinon.stub(layout.context, 'trigger');

            layout.stopResizing();

            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('setResizeConfig()', function() {
        it('should call _updateUIElements()', function() {
            var spy = sinon.stub(layout, '_updateUIElements');

            layout.setResizeConfig({}, true);

            expect(spy).toHaveBeenCalled();
        });

        it('should properly set _direction and _cursor', function() {
            var resizeConfig = {
                firstScreenRatio: '40%',
                direction: 'horizontal'
            };

            var spy = sinon.stub(layout, '_updateUIElements');

            layout.setResizeConfig(resizeConfig);

            expect(layout._direction).toEqual('horizontal');
            expect(layout._cursor).toEqual('row-resize');
            expect(spy).not.toHaveBeenCalled();
        });
    });
});
