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
describe('View.Layouts.Base.AdministrationContentGridLayout', function() {
    var app;
    var context;
    var layout;
    var parentLayout;
    var layoutName = 'content-grid';
    var module = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        sinon.stub(app.api, 'call');

        SugarTest.loadComponent('base', 'layout', layoutName, module);
        layout = SugarTest.createLayout('base', module, layoutName, {}, context, true);

        SugarTest.loadComponent('base', 'layout', 'administration', module);
        parentLayout = SugarTest.createLayout('base', module, 'administration', {}, context, true);

        layout.layout = parentLayout;
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        parentLayout.dispose();
        parentLayout = null;
        context = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('getContentContainerComponentDef', function() {
        using('different def', [
            {
                def: {},
                expected: {
                    layout: {
                        name: 'content-container',
                        css_class: 'grid-stack-item-content',
                        label: '',
                        description: '',
                        components: [
                            {
                                view: {
                                    name: 'action-items',
                                    items: []
                                }
                            }
                        ]
                    }
                }
            },
            {
                def: {
                    label: 'LBL_NAME',
                    description: 'LBL_DESC',
                },
                expected: {
                    layout: {
                        name: 'content-container',
                        css_class: 'grid-stack-item-content',
                        label: 'LBL_NAME',
                        description: 'LBL_DESC',
                        components: [
                            {
                                view: {
                                    name: 'action-items',
                                    items: []
                                }
                            }
                        ]
                    }
                }
            },
            {
                def: {
                    label: 'LBL_NAME',
                    description: 'LBL_DESC',
                    options: [
                        {
                            label: 'LBL_OPTION_LABEL',
                            description: 'LBL_OPTION_DESC',
                            icon: 'sicon-cloud',
                            customIcon: '',
                            link: 'link'
                        }
                    ]
                },
                expected: {
                    layout: {
                        name: 'content-container',
                        css_class: 'grid-stack-item-content',
                        label: 'LBL_NAME',
                        description: 'LBL_DESC',
                        components: [
                            {
                                view: {
                                    name: 'action-items',
                                    items: [
                                        {
                                            label: 'LBL_OPTION_LABEL',
                                            tooltip: 'LBL_OPTION_DESC',
                                            icon: 'sicon-cloud',
                                            customIcon: '',
                                            href: 'link'
                                        }
                                    ]
                                }
                            }
                        ]
                    }
                }
            },
            {
                def: {
                    label: 'LBL_NAME',
                    description: 'LBL_DESC',
                    options: [
                        {
                            label: 'LBL_OPTION_LABEL',
                            description: 'LBL_OPTION_DESC',
                            icon: '',
                            customIcon: 'icon.gif',
                            link: 'link'
                        }
                    ]
                },
                expected: {
                    layout: {
                        name: 'content-container',
                        css_class: 'grid-stack-item-content',
                        label: 'LBL_NAME',
                        description: 'LBL_DESC',
                        components: [
                            {
                                view: {
                                    name: 'action-items',
                                    items: [
                                        {
                                            label: 'LBL_OPTION_LABEL',
                                            tooltip: 'LBL_OPTION_DESC',
                                            icon: '',
                                            customIcon: 'icon.gif',
                                            href: 'link'
                                        }
                                    ]
                                }
                            }
                        ]
                    }
                }
            }
        ], function(values) {
            it('should get def for content container', function() {
                let actual = layout.getContentContainerComponentDef(values.def);
                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('getGridstackWidgetHeight', function() {
        using('different def', [
            {
                pixelsPerGridstackRow: 100,
                wrapperHeight: 250,
                expected: 3
            },
            {
                pixelsPerGridstackRow: 200,
                wrapperHeight: 700,
                expected: 4
            }
        ], function(values) {
            it('should calculate the height of the widget', function() {
                layout.pixelsPerGridstackRow = values.pixelsPerGridstackRow;

                let component = {
                    $el: {
                        find: function() {
                            return {
                                outerHeight: function() {
                                    return values.wrapperHeight;
                                }
                            };
                        }
                    }
                };

                let actual = layout.getGridstackWidgetHeight(component);
                expect(actual).toEqual(values.expected);
            });
        });
    });
});
