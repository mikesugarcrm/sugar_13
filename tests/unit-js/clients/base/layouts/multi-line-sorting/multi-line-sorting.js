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
describe('Base.Layout.MultiLineSorting', function() {
    var app;
    var sinonSandbox;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'multi-line-sorting');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        layout = SugarTest.createLayout('base', null, 'multi-line-sorting');
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        layout.dispose();
        layout = null;
    });

    describe('setSortingDropdownData', function() {
        using('different orderBy values', [
            {
                default: {
                    order_by_primary: 'name',
                    order_by_primary_direction: 'asc',
                    order_by_secondary: 'number',
                    order_by_secondary_direction: 'asc',
                },
                cached: {
                    order_by_primary: 'number',
                    order_by_primary_direction: 'desc',
                    order_by_secondary: 'name',
                    order_by_secondary_direction: 'desc',
                },
                expected: {
                    order_by_primary: 'number',
                    order_by_primary_direction: 'desc',
                    order_by_secondary: 'name',
                    order_by_secondary_direction: 'desc',
                }
            },
            {
                default: {
                    order_by_primary: 'name',
                    order_by_primary_direction: 'asc',
                    order_by_secondary: 'number',
                    order_by_secondary_direction: 'desc',
                },
                cached: null,
                expected: {
                    order_by_primary: 'name',
                    order_by_primary_direction: 'asc',
                    order_by_secondary: 'number',
                    order_by_secondary_direction: 'desc',
                }
            }
        ], function(data) {
            it('should set orderBy values', function() {
                var primarySetDropdownFieldsStub = sinonSandbox.stub();
                var primarySetDefaultStub = sinonSandbox.stub();
                var primarySetStateStub = sinonSandbox.stub();
                var secondarySetDropdownFieldsStub = sinonSandbox.stub();
                var secondarySetDefaultStub = sinonSandbox.stub();
                var secondarySetStateStub = sinonSandbox.stub();
                var renderStub = sinonSandbox.stub(layout, 'render');
                sinonSandbox.stub(layout, '_getPrimarySortComponent').returns({
                    setDropdownFields: primarySetDropdownFieldsStub,
                    setDefaultField: primarySetDefaultStub,
                    setState: primarySetStateStub
                });
                sinonSandbox.stub(layout, '_getSecondarySortComponent').returns({
                    setDropdownFields: secondarySetDropdownFieldsStub,
                    setDefaultField: secondarySetDefaultStub,
                    setState: secondarySetStateStub
                });
                sinonSandbox.stub(layout, '_getMultiLineList').returns({
                    metric: data.default,
                    module: 'Cases',
                    metaFields: {}
                });
                sinonSandbox.stub(layout, 'getCachedOrderBy').returns(data.cached);
                sinonSandbox.stub(layout, '_getSortFields').returns('sortFields');
                layout.setSortingDropdownData();
                expect(primarySetDropdownFieldsStub).toHaveBeenCalledWith('sortFields');
                expect(primarySetDefaultStub).toHaveBeenCalledWith(data.default.order_by_primary);
                expect(primarySetStateStub).toHaveBeenCalledWith(data.expected.order_by_primary,
                    data.expected.order_by_primary_direction);
                expect(secondarySetDropdownFieldsStub).toHaveBeenCalledWith('sortFields');
                expect(secondarySetDefaultStub).toHaveBeenCalledWith(data.default.order_by_secondary);
                expect(secondarySetStateStub).toHaveBeenCalledWith(data.expected.order_by_secondary,
                    data.expected.order_by_secondary_direction);
                expect(renderStub).toHaveBeenCalled();
            });
        });
    });

    describe('_sortSortableDataComponent', function() {
        using('different orderBy values', [
            {
                primary: {
                    currentField: 'name',
                    currentDirection: 'asc',
                    defaultField: 'number'
                },
                secondary: {
                    currentField: 'number',
                    currentDirection: 'desc',
                    defaultField: ''
                },
                expected: {
                    text: 'name:asc,number:desc',
                    cache: {
                        order_by_primary: 'name',
                        order_by_primary_direction: 'asc',
                        order_by_secondary: 'number',
                        order_by_secondary_direction: 'desc',
                    }
                }
            },
            {
                primary: {
                    currentField: 'name',
                    currentDirection: 'asc',
                    defaultField: 'number'
                },
                secondary: {
                    currentField: '',
                    currentDirection: 'desc',
                    defaultField: 'number'
                },
                expected: {
                    text: 'name:asc',
                    cache: {
                        order_by_primary: 'name',
                        order_by_primary_direction: 'asc',
                        order_by_secondary: '',
                        order_by_secondary_direction: 'desc',
                    }
                }
            }
        ], function(data) {
            it('should cache and apply new orderBy values', function() {
                var setOptionStub = sinonSandbox.stub();
                var cacheStub = sinonSandbox.stub(layout, 'cacheOrderBy');
                sinonSandbox.stub(layout.context, 'get').withArgs('collection').returns({
                    getOption: function() {return {};},
                    setOption: setOptionStub,
                    resetLoadFlag: $.noop,
                    set: $.noop,
                    loadData: $.noop
                });
                sinonSandbox.stub(layout, '_getPrimarySortComponent').returns(data.primary);
                sinonSandbox.stub(layout, '_getSecondarySortComponent').returns(data.secondary);
                sinonSandbox.stub(layout, '_getMultiLineList').returns({metric: {id: 'id'}});
                layout._sortSortableDataComponent();
                expect(setOptionStub).toHaveBeenCalledWith('params', {order_by: data.expected.text});
                expect(cacheStub).toHaveBeenCalledWith({id: 'id'}, data.expected.cache);
            });
        });
    });

    describe('_getMultiLineFields', function() {
        using('different meta fields', [
            {
                fields: [
                    {
                        name: 'name',
                        subfields: [
                            {
                                name: 'name',
                                label: 'LBL_LIST_ACCOUNT_NAME'
                            },
                            {
                                name: 'industry',
                                label: 'LBL_INDUSTRY',
                                type: 'enum'
                            },
                        ],
                    },
                ],
                expected: [
                    {
                        name: 'name',
                        label: 'LBL_LIST_ACCOUNT_NAME',
                        type: 'name'
                    },
                    {
                        name: 'industry',
                        label: 'LBL_INDUSTRY',
                        type: 'enum'
                    },
                ]
            },
            {
                fields: [
                    {
                        name: 'name',
                        subfields: [
                            {
                                name: 'name',
                                label: 'LBL_LIST_ACCOUNT_NAME',
                                related_fields: [
                                    'id'
                                ]
                            },
                            {
                                name: 'widget',
                                widget_name: 'widget'
                            }
                        ],
                    },
                ],
                expected: [
                    {
                        name: 'name',
                        label: 'LBL_LIST_ACCOUNT_NAME',
                        related_fields: [
                            'id'
                        ],
                        type: 'name'
                    },
                    {
                        name: 'id',
                        label: 'LBL_ID',
                        type: 'id'
                    },
                ]
            },
        ], function(data) {
            it('should return multi-line fields', function() {
                sinonSandbox.stub(app.metadata, 'getModule').withArgs('Cases', 'fields').returns({
                    id: {
                        name: 'id',
                        type: 'id',
                        label: 'LBL_ID',
                    },
                    name: {
                        name: 'name',
                        type: 'name',
                        label: 'LBL_NAME'
                    },
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        label: 'LBL_INDUSTRY'
                    },
                });
                var fields = layout._getMultiLineFields('Cases', data.fields);
                expect(fields).toEqual(data.expected);
            });
        });
    });

    describe('_getSortFields', function() {
        using('different meta fields', [
            {
                fields: [
                    {
                        name: 'name',
                        subfields: [
                            {
                                name: 'name',
                                label: 'LBL_LIST_ACCOUNT_NAME'
                            },
                            {
                                name: 'industry',
                                label: 'LBL_INDUSTRY',
                                type: 'enum'
                            },
                        ],
                    },
                ],
                expected: [
                    {
                        name: 'name',
                        label: 'label',
                    },
                    {
                        name: 'industry',
                        label: 'label',
                    },
                ]
            },
            {
                fields: [
                    {
                        name: 'name',
                        subfields: [
                            {
                                name: 'name',
                                label: 'LBL_LIST_ACCOUNT_NAME',
                                related_fields: [
                                    'id'
                                ]
                            },
                            {
                                name: 'widget',
                                widget_name: 'widget'
                            }
                        ],
                    },
                ],
                expected: [
                    {
                        name: 'name',
                        label: 'label',
                    }
                ]
            },
        ], function(data) {
            it('should return sortable fields', function() {
                sinonSandbox.stub(app.metadata, 'getModule').withArgs('Cases', 'fields').returns({
                    id: {
                        name: 'id',
                        type: 'id',
                        label: 'LBL_ID',
                    },
                    name: {
                        name: 'name',
                        type: 'LBL_NAME',
                    },
                    industry: {
                        name: 'industry',
                        type: 'varchar',
                        label: 'LBL_INDUSTRY'
                    },
                });
                sinonSandbox.stub(app.acl, 'hasAccess').returns(true);
                sinonSandbox.stub(app.lang, 'get').returns('label');
                var fields = layout._getSortFields('Cases', data.fields);
                expect(fields).toEqual(data.expected);
            });
        });
    });
});
