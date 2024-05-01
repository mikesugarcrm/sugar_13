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
describe('Plugins.Previewable', function() {
    var app;
    var view;
    var defaultPlugins;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', '', 'record');
        defaultPlugins = view.plugins;
        view.plugins = ['Previewable'];
        SugarTest.loadPlugin('Previewable');
        SugarTest.app.plugins.attach(view, 'view');
        view.trigger('init');
    });

    afterEach(function() {
        sinon.restore();
        view.plugins = defaultPlugins;
        view.dispose();
        view = null;
        app.cache.cutAll();
        app = null;
    });

    describe('handleDataPreview', function() {
        beforeEach(function() {
            sinon.stub(view, 'render');
        });

        using('different change results', [
                {
                    setFieldsPreviewMeta: false,
                    setPropertiesPreviewData: false,
                    expected: false
                },
                {
                    setFieldsPreviewMeta: true,
                    setPropertiesPreviewData: false,
                    expected: true
                }
            ], function(values) {
                it('should render when there are changes', function() {
                    sinon.stub(view, 'setFieldsPreviewMeta').returns(values.setFieldsPreviewMeta);
                    sinon.stub(view, 'setPropertiesPreviewData').returns(values.setPropertiesPreviewData);

                    view.handleDataPreview({});

                    if (values.expected) {
                        expect(view.render).toHaveBeenCalled();
                    } else {
                        expect(view.render).not.toHaveBeenCalled();
                    }
                });
            }
        );
    });

    describe('setFieldsPreviewMeta', function() {
        using('different data', [
                {
                    data: {
                        fields: [
                            'test-field'
                        ]
                    },
                    field: null,
                    expected: false
                },
                {
                    data: {
                        fields: [
                            'test-field'
                        ]
                    },
                    field: {
                        type: 'text'
                    },
                    expected: false
                },
                {
                    data: {
                        fields: [
                            'test-field'
                        ]
                    },
                    field: {
                        type: 'button'
                    },
                    expected: true
                }
            ], function(values) {
                it('should return true when field metadata has changed', function() {
                    sinon.stub(view, 'getField').returns(values.field);
                    sinon.stub(view, 'setButtonPreviewMeta').returns(true);

                    var actual = view.setFieldsPreviewMeta(values.data);
                    expect(actual).toEqual(values.expected);
                });
            }
        );
    });

    describe('setButtonPreviewMeta', function() {
        using('different button metadata', [
                {
                    fieldName: 'test-button',
                    metadata: {},
                    buttons: [
                        {
                            name: 'test-button-1'
                        }
                    ],
                    expectedMetadata: {},
                    expected: false
                },
                {
                    fieldName: 'test-button',
                    metadata: {
                        label: 'New Label'
                    },
                    buttons: [
                        {
                            name: 'test-button'
                        }
                    ],
                    expectedMetadata: {
                        name: 'test-button',
                        label: 'New Label'
                    },
                    expected: true
                }
            ], function(values) {
                it('should return true when button metadata has changed', function() {
                    view.meta = _.extend(view.meta ? view.meta : {}, {
                        buttons: values.buttons
                    });

                    var actual = view.setButtonPreviewMeta(values.fieldName, values.metadata);
                    expect(actual).toEqual(values.expected);

                    if (values.expected) {
                        var buttonIndex = _.findIndex(view.meta.buttons, function(button) {
                            return button.name === values.fieldName;
                        }, this);

                        expect(view.meta.buttons[buttonIndex]).toEqual(values.expectedMetadata);
                    }
                });
            }
        );
    });

    describe('setPropertiesPreviewData', function() {
        beforeEach(function() {
            view.testPropertyOne = null;
        });

        using('different properties metadata', [
                {
                    data: {
                        properties: [
                            'testPropertyTwo'
                        ],
                        preview_data: ''
                    },
                    expected: false
                },
                {
                    data: {
                        properties: [
                            'testPropertyOne'
                        ],
                        preview_data: 'New Value'
                    },
                    expected: true
                }
            ], function(values) {
                it('should return true when properties has changed', function() {
                    var actual = view.setPropertiesPreviewData(values.data);
                    expect(actual).toEqual(values.expected);

                    if (values.expected) {
                        _.each(values.data.properties, function(property) {
                            expect(view[property]).toEqual(values.data.preview_data);
                        });
                    }
                });
            }
        );
    });
});
