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
describe('Administration.Views.PortalthemeConfig', function() {
    var app;
    var view;
    var model;
    var moduleName = 'Administration';
    var viewName = 'portaltheme-config';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'config', moduleName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        model = app.data.createBean(moduleName);
        var context = app.context.getContext();
        context.set('model', model);
        view = SugarTest.createView('base', moduleName, viewName, {}, context, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('handleBannerBackgroundStyleChange', function() {
        using('different field values', [
            {
                formattedValue: 'default',
                expectedColorDisplay: false,
                expectedImageDisplay: false
            },
            {
                formattedValue: 'color',
                expectedColorDisplay: true,
                expectedImageDisplay: false
            },
            {
                formattedValue: 'image',
                expectedColorDisplay: false,
                expectedImageDisplay: true
            }
        ], function(data) {
            it('should appropriately toggle field visibility', function() {
                var backgroundStyleField = {
                    name: 'portaltheme_banner_background_style',
                    getFormattedValue: function() {
                        return data.formattedValue;
                    }
                };
                var colorField = {
                    name: 'portaltheme_banner_background_color'
                };
                var imageField = {
                    name: 'portaltheme_banner_background_image'
                };

                sinon.stub(view, 'getField')
                    .withArgs('portaltheme_banner_background_style').returns(backgroundStyleField)
                    .withArgs('portaltheme_banner_background_color').returns(colorField)
                    .withArgs('portaltheme_banner_background_image').returns(imageField);
                sinon.stub(view, '_toggleFieldVisibility');

                view.handleBannerBackgroundStyleChange();

                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(colorField, data.expectedColorDisplay);
                expect(view._toggleFieldVisibility).toHaveBeenCalledWith(imageField, data.expectedImageDisplay);
            });
        });
    });

    describe('handleModelChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'triggerPreview');
        });

        using('different def data', [
            {
                field: {},
                data: {
                    preview_components: []
                },
                expected: false
            },
            {
                field: {},
                data: {
                    preview_components: [
                        {
                            layout: 'layout-name',
                            view: 'view-name'
                        }
                    ]
                },
                expected: true
            }
        ], function(values) {
            it('should call triggerPreview when the definition is properly defined', function() {
                view.model.changed = {
                    field_name: 'New Value'
                };

                sinon.stub(view, 'getField').returns(values.field);
                sinon.stub(view, 'getPreviewContextData').returns(values.data);

                view.handleModelChange();

                if (values.expected) {
                    expect(view.triggerPreview).toHaveBeenCalled();
                } else {
                    expect(view.triggerPreview).not.toHaveBeenCalled();
                }
            });
        }
        );
    });

    describe('triggerPreview', function() {
        beforeEach(function() {
            view.layout = SugarTest.createLayout('base', '', 'default');
            sinon.stub(view.layout.context, 'trigger');
        });

        using('different context data', [
            {
                isConfigLayout: false,
                expected: false
            },
            {
                isConfigLayout: true,
                expected: true
            }
        ], function(values) {
            it('should trigger portal:config:preview when in config layout', function() {
                sinon.stub(view.layout.context, 'get')
                    .withArgs('config-layout')
                    .returns(values.isConfigLayout);

                view.triggerPreview({});

                if (values.expected) {
                    expect(view.layout.context.trigger).toHaveBeenCalledWith('portal:config:preview');
                } else {
                    expect(view.layout.context.trigger).not.toHaveBeenCalled();
                }
            });
        }
        );
    });

    describe('getPreviewComponentsDef', function() {
        beforeEach(function() {
            view.layout = SugarTest.createLayout('base', '', 'default');
            sinon.stub(view.layout, 'trigger');
        });

        using('different context data', [
            {
                field: {},
                expected: []
            },
            {
                field: {
                    def: {
                        preview_components: [
                            'components'
                        ]
                    }
                },
                expected: [
                    'components'
                ]
            }
        ], function(values) {
            it('should return the preview_components definition', function() {
                var actual = view.getPreviewComponentsDef(values.field);
                expect(actual).toEqual(values.expected);
            });
        }
        );
    });

    describe('basic functionality', function() {
        it('should always place labels on top', function() {
            expect(view.getLabelPlacement()).toEqual(true);
        });
    });

    describe('reset settings', function() {
        var fieldFactory = function(type, name, defaultValue) {
            SugarTest.loadComponent('base', 'field', type);
            return SugarTest.createField({
                name: name, type: type, viewName: viewName,
                fieldDef: {
                    name: name, type: type, default: defaultValue
                },
                module: moduleName, model: model, context: null, loadFromModule: false
            });
        };

        it('should iterate through the fields and init default restoration', function() {
            view.fields['1'] = fieldFactory('colorpicker', 'customcolor1', '#0679C8');
            view.fields['2'] = fieldFactory('text', 'customtext1', 'LBL_PORTAL_THEME_NEW_CASE_MESSAGE_DEFAULT');
            view.fields['3'] = fieldFactory('image-url', 'customimgurl1', 'themes/default/images/company_logo.png');

            var restoreStub = sinon.stub(view, 'resetFieldToDefault');
            sinon.stub(app.alert, 'show').callsFake(function() {});

            // check that alert is shown to user
            view.restoreClicked();
            expect(app.alert.show).toHaveBeenCalledWith('restore_default_confirmation');

            // Check that when user confirms the functionality is correct
            view.restoreFields();
            expect(restoreStub.callCount).toEqual(3);
        });

        describe('reset process', function() {
            var fieldSetup = [{
                name: 'test1', type: 'enum', default: null,
                hasDefaultValue: false, expectedDefault: ''
            }, {
                name: 'test2', type: 'text',
                hasDefaultValue: false, expectedDefault: ''
            }, {
                name: 'test3', type: 'bool', default: 0,
                hasDefaultValue: true, expectedDefault: 0
            }, {
                hasDefaultValue: false,
                type: 'text', default: 'LBL_SOMETHING'
            }, {
                name: 'test5', type: 'int',
                hasDefaultValue: false, expectedDefault: ''
            }, {
                hasDefaultValue: true, expectedDefault: '#0679C8',
                name: 'test6', type: 'colorpicker', default: '#0679C8'
            }, {
                hasDefaultValue: true, expectedDefault: 'somevalue',
                name: 'test7', type: 'text', default: 'LBL_SOMEVALUE'
            }, {
                hasDefaultValue: true, expectedDefault: '',
                name: 'test8', type: 'image-url', default: 'themes/someimage.png'
            }, {
                hasDefaultValue: true, expectedDefault: '',
                name: 'test9', type: 'image-url', default: 'https://mydomain.com/themes/someimage.png'
            }];

            using('different fields for the reset', fieldSetup, function(data) {
                var field;

                beforeEach(function() {
                    field = fieldFactory(data.type, data.name, data.default);
                });

                it('should verify the default value', function() {
                    var result = view.hasDefaultValue(field);
                    expect(result).toEqual(data.hasDefaultValue);
                });

                it('should set the appropriate default value', function() {
                    sinon.stub(field, 'render');
                    sinon.stub(app.lang, 'get').withArgs('LBL_SOMEVALUE').returns('somevalue');
                    view.resetFieldToDefault(field);
                    expect(view.model.get(field.name)).toEqual(data.expectedDefault);
                });
            });
        });
    });
});
