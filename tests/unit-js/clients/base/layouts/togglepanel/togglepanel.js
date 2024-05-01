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

describe('Base.Layout.Togglepanel', function() {

    var app, layout, getModuleStub;

    beforeEach(function() {
        app = SugarTest.app;
        getModuleStub = sinon.stub(app.metadata, 'getModule');

        getModuleStub.returns({
            'activityStreamEnabled': true
        });

        SugarTest.loadFile('../modules/Dashboards/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        getModuleStub.restore();
        app.cache.cutAll();
        app.view.reset();
        app.router.stop();
        Handlebars.templates = {};
        layout.dispose();
        layout = null;
    });

    describe('Toggle Panel', function() {
        var oLastState;
        beforeEach(function() {
            var meta = {
            }
            oLastState = app.user.lastState;
            app.user.lastState = {
                key: function() {},
                get: function() {},
                set: function() {},
                register: function() {}
            };
            var stub = sinon.stub(app.user.lastState);
            layout = SugarTest.createLayout('base', 'Accounts', 'togglepanel', meta);
        });
        afterEach(function() {
            app.user.lastState = oLastState;
        });
        it('should initialize', function() {
            var processToggleSpy = sinon.stub(layout, 'processToggles').callsFake(function() {
            });
            var options = {};
            layout.initialize(options);
            expect(layout.componentsList).toEqual({});
            expect(processToggleSpy).toHaveBeenCalled();
        });
        it('should add toggle components to the togglable component lists', function() {
            let mockComponent = app.view.createView({type: 'test1', name: 'test1'});
            layout.availableToggles = [
                {
                    'name': 'test1',
                    'label': 'test1',
                    'icon': 'icon1'
                }
            ];
            layout._placeComponent(mockComponent);

            expect(layout.componentsList[mockComponent.name]).toEqual(mockComponent);

            mockComponent.dispose();
        });

        describe('processToggles', function() {
            let options = {};
            let meta = {
                'availableToggles': [
                    {
                        'name': 'test1',
                        'label': 'testFoo1',
                        'icon': 'icon1'
                    },
                    {
                        'name': 'test2',
                        'label': 'testFoo2',
                        'icon': 'icon2'
                    },
                    {
                        'name': 'test3',
                        'label': 'testFoo3',
                        'icon': 'icon3',
                        'css_class': 'testClass',
                        'disabled': true
                    }
                ],
                'components': {
                    'c1': {
                        'view': 'test1'
                    },
                    'c2': {
                        'layout': 'test2'
                    },
                    'c3': {
                        'layout': {
                            'type': 'test3'
                        }
                    }
                }
            };
            options.context = {
                get: function() {},
                off: function() {}
            };
            options.meta = meta;

            it('should process toggles for layout with subpanels', function() {
                options.meta.components.c4 = {'layout': 'subpanels'};
                layout.initialize(options);
                expect(layout.toggles).toEqual([
                    {
                        class: 'icon1',
                        title: 'testFoo1',
                        toggle: 'test1',
                        disabled: false
                    },
                    {
                        class: 'icon2',
                        title: 'testFoo2',
                        toggle: 'test2',
                        disabled: false
                    }
                ]);
            });
            it('should process toggles for pipeline and list layouts', function() {
                layout.availableToggles = [
                    {
                        'name': 'test1',
                        'label': 'test1',
                        'icon': 'icon1',
                    },
                    {
                        'name': 'test2',
                        'label': 'test2',
                        'icon': 'icon2',
                    },
                    {
                        'name': 'test3',
                        'label': 'test3',
                        'icon': 'icon3',
                        'disabled': true
                    }
                ];
                options.meta.components.c4 = {'layout': 'foo'};
                layout.initialize(options);
                expect(layout.toggles).toEqual([
                    {
                        class: 'icon1',
                        title: 'test1',
                        toggle: 'test1',
                        disabled: false
                    },
                    {
                        class: 'icon2',
                        title: 'test2',
                        toggle: 'test2',
                        disabled: false
                    }
                ]);
            });
        });

        describe('isToggleButtonDisabled', function() {
            it('should return true if the toggle button does not exist', function() {
                layout.toggles = [];
                expect(layout.isToggleButtonDisabled('not_a_real_button')).toEqual(true);
            });
        });

        describe('getNonToggleComponents', function() {
            it('should only return components that cannot be toggled', function() {
                var actual;
                var nonTogglable = app.view.createView({type: 'base'});
                var togglable = app.view.createView({type: 'base'});

                layout._components = [nonTogglable, togglable];
                layout.componentsList = [togglable];

                actual = layout.getNonToggleComponents();

                expect(actual.length).toBe(1);
                expect(actual[0].cid).toBe(nonTogglable.cid);

                nonTogglable.dispose();
                togglable.dispose();
            });
        });

        describe('toggleView', function() {
            var evt = {
                currentTarget: 'testTarget'
            };
            var lastStateSetStub;
            beforeEach(function() {
                sinon.stub(app.router,'navigate').callsFake(function() {});
                sinon.stub(layout,'showComponent').callsFake(function() {});
                sinon.stub(layout,'_toggleAria').callsFake(function() {});
            });
            afterEach(function() {
                sinon.restore();
            });

            describe('when the data.route is not pipeline', function() {
                it('should set last state with last state key and data.route', function() {
                    sinon.stub(layout, '$').callsFake(function() {
                        return {
                            data: function() {
                                return {
                                    route: ''
                                };
                            },
                            hasClass: function() {
                                return false;
                            }
                        };
                    });
                    app.user.lastState.set.restore();
                    sinon.stub(app.user.lastState, 'set');
                    layout.toggleView(evt);
                    expect(app.user.lastState.set).toHaveBeenCalled();
                });
            });
        });

        describe('_placeToggleButtons', function() {
            it('should set toggle buttons position in the case where there are no toggels', function() {
                layout.$el.append('<div id="test-toggels" class="controls-two refresh pipeline-refresh-btn"></div>');
                layout.toggles = [];
                layout._placeToggleButtons();

                expect(layout.$('#test-toggels').hasClass('controls-zero')).toBeTruthy();
                expect(layout.$('#test-toggels').hasClass('refresh-for-zero')).toBeTruthy();
                expect(layout.$('#test-toggels').hasClass('pipeline-refresh-btn')).toBeFalsy();
            });

            it('should set toggle buttons position in the case where there are three toggels', function() {
                layout.$el.append('<div id="test-toggels" class="controls-two refresh pipeline-refresh-btn"></div>');
                layout.toggles = ['foo', 'bar', 'baz'];
                layout._placeToggleButtons();

                expect(layout.$('#test-toggels').hasClass('controls-three')).toBeTruthy();
                expect(layout.$('#test-toggels').hasClass('refresh-for-three')).toBeTruthy();
                expect(layout.$('#test-toggels').hasClass('pipeline-refresh-btn')).toBeFalsy();
            });
        });

        describe('_setDisabled', function() {
            using('various layout actions', [
                {
                    isPipelineEnabled: true,
                    activityStreamEnabled: false,
                    name: 'pipeline',
                    layout: 'pipeline-records',
                    result: false
                },
                {
                    isPipelineEnabled: true,
                    activityStreamEnabled: false,
                    name: 'pipeline',
                    layout: 'record',
                    result: true
                },
                {
                    isPipelineEnabled: true,
                    activityStreamEnabled: false,
                    name: 'pipeline',
                    layout: 'records',
                    result: false
                },
                {
                    isPipelineEnabled: false,
                    activityStreamEnabled: true,
                    name: 'activitystream',
                    layout: 'records',
                    result: false
                },
                {
                    isPipelineEnabled: true,
                    activityStreamEnabled: false,
                    name: 'activitystream',
                    layout: 'pipeline-records',
                    result: true
                },
                {
                    isPipelineEnabled: true,
                    activityStreamEnabled: false,
                    name: 'list',
                    layout: 'pipeline-records',
                    result: false
                }
            ], function(value) {
                it('should return false if pipeline is enabled for a module or activity stream is on', function() {
                    getModuleStub.withArgs('Accounts').returns({
                        'isPipelineEnabled': value.isPipelineEnabled,
                        'activityStreamEnabled': value.activityStreamEnabled
                    });
                    sinon.stub(layout.options.context, 'get')
                        .withArgs('layout').returns(value.layout);
                    let toggle = {
                        name: value.name
                    };
                    expect(layout._setDisabled(toggle)).toBe(value.result);
                });
            });
        });
    });

    describe('_render', function() {
        it('should fall back to the first enabledToggle instead of the lastViewed if the lastViewed is disabled',
            function() {
                app.metadata.getModule.withArgs('Accounts').returns({
                    activityStreamEnabled: false
                });

                layout = SugarTest.createLayout('base', 'Accounts', 'togglepanel', {});
                sinon.stub(app.user.lastState, 'get').returns('activitystream');
                sinon.stub(layout, 'showComponent');
                layout._render();

                expect(layout.showComponent).toHaveBeenCalledWith('list', true);
            });
    });
});
