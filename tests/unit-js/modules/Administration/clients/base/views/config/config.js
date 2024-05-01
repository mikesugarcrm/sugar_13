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
describe('Administration.Views.Config', function() {
    var app;
    var view;
    var viewName = 'config';
    var moduleName = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', viewName, moduleName);

        var meta = {
            panels: [{
                name: 'panel_1'
            }]
        };

        var model = app.data.createBean(moduleName);
        var context = app.context.getContext();
        context.set('model', model);

        view = SugarTest.createView('base', moduleName, viewName, meta, context, true);
    });

    afterEach(function() {
        view.dispose();
        sinon.restore();
    });

    describe('save', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'call');
        });

        it('should save by calling the correct api', function() {
            view.save();

            var url = app.api.buildURL(moduleName, view.settingPrefix);
            expect(app.api.call).toHaveBeenCalledWith(
                'create',
                url,
                view.model.toJSON()
            );
        });
    });

    describe('validationComplete', function() {
        it('should save if all fields are valid', function() {
            var saveStub = sinon.spy(view, 'save');
            view.validationComplete(true);

            expect(saveStub).toHaveBeenCalled();
        });

        it('should not save if some fields are invalid', function() {
            var saveStub = sinon.spy(view, 'save');
            view.validationComplete(false);

            expect(saveStub).not.toHaveBeenCalled();
        });
    });

    describe('generateHelpBlock', function() {
        using('different meta panels', [
            {
                meta: {
                    panels: []
                },
                expected: {}
            },
            {
                meta: {
                    panels: [
                        {
                            name: 'panel_1',
                            helpLabels: [
                                {
                                    name: 'LBL_HELP_LABEL_NAME',
                                    text: 'LBL_HELP_LABEL_TEXT'
                                }
                            ]
                        }
                    ]
                },
                expected: {
                    panel_1: '<div class="help-block"></div>'
                }
            }
        ],
        function(data) {
            it('should return the strings for the help block', function() {
                sinon.stub(app.template, 'getView').returns(function() {
                    return '<div class="help-block"></div>';
                });
                view.meta.panels = data.meta.panels;

                var actual = view.generateHelpBlock();
                expect(actual).toEqual(data.expected);
            });
        });
    });

    describe('getHelpLabels', function() {
        using('different panels', [
            {
                panel: {},
                expected: []
            },
            {
                panel: {
                    helpLabels: [
                        {
                            name: 'LBL_HELP_LABEL_NAME',
                            text: 'LBL_HELP_LABEL_TEXT'
                        }
                    ]
                },
                expected: [
                    {
                        name: 'LBL_HELP_LABEL_NAME:',
                        label: 'LBL_HELP_LABEL_TEXT',
                        text: {
                            string: 'LBL_HELP_LABEL_TEXT'
                        }
                    }
                ]
            }
        ],
        function(data) {
            it('should get the help labels for the panel', function() {
                var actual = view.getHelpLabels(data.panel);
                expect(actual).toEqual(data.expected);
            });
        });
    });

    describe('getLinkLabels', function() {
        using('different panels', [
            {
                panel: {},
                expected: []
            },
            {
                panel: {
                    linkLabels: [
                        {
                            name: 'link-name',
                            link: {
                                text: 'LBL_LINK_TEXT',
                                css_class: 'link-class',
                                href: 'javascript:void(0)'
                            },
                            text: 'LBL_LINK_TEXT'
                        }
                    ]
                },
                expected: [
                    {
                        is_link: true,
                        link_text: {
                            string: 'LBL_LINK_TEXT'
                        },
                        link_css_class: 'link-class',
                        link_href: 'javascript:void(0)',
                        link_target: '_self',
                        name: 'link-name',
                        css_class: '',
                        text: {
                            string: 'LBL_LINK_TEXT'
                        }
                    }
                ]
            }
        ], function(data) {
            it('should get the link labels for the panel', function() {
                var actual = view.getLinkLabels(data.panel);
                expect(actual).toEqual(data.expected);
            });
        });
    });

    describe('_toggleFieldVisibility', function() {
        using('different `show` values', [true, false], function(show) {
            it('should hide/show the field based on args passed in', function() {
                // Create stubs for use in our mock objects
                var toggleStub = sinon.stub();
                // Mock the field and field.$el objects
                var $el = {
                    closest: function() {
                        return {
                            toggle: toggleStub
                        };
                    },
                    toggle: toggleStub

                };
                var epField = {
                    $el: $el,
                };
                view._toggleFieldVisibility(epField, show);
                expect(toggleStub.called).toBe(true);
                expect(toggleStub).toHaveBeenCalledWith(show);
            });
        });
    });
});
