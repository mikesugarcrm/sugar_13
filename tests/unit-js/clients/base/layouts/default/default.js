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
describe('Base.Layout.Default', function() {
    var layout;
    var app;
    var moduleName = 'Accounts';
    var def = {
        'components': [
            {'layout': {'span': 4}},
            {'layout': {'span': 8}},
        ],
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', moduleName, 'default', def, null);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('listeners', function() {
        var toggleSidePaneStub;
        var testLayout;

        beforeEach(function() {
            testLayout = app.view.createLayout({
                name: 'default',
                module: moduleName,
            });
            toggleSidePaneStub = sinon.stub(testLayout, 'toggleSidePane');
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('should toggle side pane when "sidebar:toggle" is triggered', function() {
            testLayout.initialize({});
            testLayout.trigger('sidebar:toggle');
            expect(toggleSidePaneStub).toHaveBeenCalled();
        });
    });

    describe('isSidePaneVisible', function() {
        var lastStateStub, lastState;

        beforeEach(function() {
            lastStateStub = sinon.stub(app.user.lastState, 'get').callsFake(function() {
                return lastState;
            });
        });

        using('different states and calling isSidePaneVisible', [
            {
                'lastState': '0',
                'expected': true
            },
            {
                'lastState': '1',
                'expected': false
            },
            {
                'lastState': undefined,
                'expected': true
            }
        ], function (option) {
            it('should return the proper value', function() {
                lastState = option.lastState;
                expect(layout.isSidePaneVisible()).toBe(option.expected);
            });
        });

        describe('when the default hide is set to "1"', function() {
            var testLayout;
            var testDef = _.extend({}, def, {default_hide: '1'});

            beforeEach(function (){
                testLayout = app.view.createLayout({
                    name: 'default',
                    module: moduleName,
                    meta: testDef,
                });
            });

            afterEach(function() {
                testLayout.dispose();
            });

            it('should default false', function() {
                lastState = undefined;
                expect(testLayout.isSidePaneVisible()).toBeFalsy();
            });
        });

        describe('when the default focus hide is set to "1"', function() {
            var testLayout;
            var testDef = _.extend({}, def, {default_focus_hide: '1'});

            beforeEach(function() {
                testLayout = app.view.createLayout({
                    name: 'default',
                    module: moduleName,
                    meta: testDef,
                });
            });

            afterEach(function() {
                testLayout.dispose();
            });

            it('should default true', function() {
                lastState = undefined;
                expect(testLayout.isSidePaneVisible()).toBeTruthy();
            });
        });
    });

    describe('toggleSidePane', function() {
        var isSidePaneVisibleStub, isSidePaneVisible,
            lastStateSetStub,
            _toggleVisibilityStub,
            validHideLastStateKey;

        beforeEach(function() {
            isSidePaneVisibleStub = sinon.stub(layout, 'isSidePaneVisible').callsFake(function() {
                return isSidePaneVisible;
            });
            lastStateSetStub = sinon.stub(app.user.lastState, 'set');
            _toggleVisibilityStub = sinon.stub(layout, '_toggleVisibility');
            validHideLastStateKey = moduleName + ':default:hide';
        });

        describe('when "true" is passed', function() {
            it('should set key to 0 and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already visible', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when "false" is passed', function() {
            it('should set key to 1 and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '1');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already hidden', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when nothing is passed', function() {
            it('should set key to 1 and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '1');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should set key to 0 and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });
        });

        describe('when the last state key is manually defined', function(){
            var testLayout;
            var _toggleStub;
            var testDef = _.extend({}, def, {hide_key: 'hide-test'});

            beforeEach(function () {
                validHideLastStateKey = moduleName + ':default:hide-test';
                testLayout = app.view.createLayout({
                    name: 'default',
                    module: moduleName,
                    meta: testDef,
                });

                sinon.stub(testLayout, 'isSidePaneVisible').callsFake(function() {
                    return isSidePaneVisible;
                });
                _toggleStub = sinon.stub(testLayout, '_toggleVisibility');
            });

            afterEach(function() {
                testLayout.dispose();
            });

            it('should use the defined last state key', function () {
                isSidePaneVisible = undefined;
                testLayout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleStub).toHaveBeenCalled();
            });
        })
    });


    describe('_toggleVisibility', function() {
        var resizeStub, triggerStub;

        beforeEach(function() {
            resizeStub = sinon.stub($.fn, 'trigger');
            triggerStub = sinon.stub(layout, 'trigger');
        });

        it('should call window "resize"', function() {
            layout._toggleVisibility(true);
            expect(resizeStub).toHaveBeenCalledWith('resize');
            expect(triggerStub).toHaveBeenCalledWith('sidebar:state:changed');
        });
    });

});
