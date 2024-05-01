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
describe('View.Views.Base.SearchListView', function() {

    var view, app, model;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('search-list', {
            'panels': {
                1: {
                    name: 'primary',
                    fields: [{name: 'name'}]
                },
                2: {
                    name: 'secondary',
                    fields: [{name: 'description'}]
                }
            }
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'GlobalSearch', 'search-list');
        app = SUGAR.App;
        model = app.data.createBean();

        SugarTest.loadFile('../include/javascript/sugar7', 'utils-search', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return fixtures.search.getModule1_return;
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        model = null;
        sinon.restore();
    });

    describe('parseModels', function() {
        using('different highlighted fields', [
            {
                highlights: [
                    {
                        name: 'description',
                        value: 'This is the description.',
                        highlighted: true
                    }
                ],
                modelFields: {name: 'ExampleName', description: 'ExampleDescription'},
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false
                    }
                ],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        highlighted: true,
                        link: false,
                        value: 'This is the description.'
                    }
                ]
            },
            {
                highlights: [
                    {
                        name: 'description',
                        value: 'This is the description.'
                    },
                    {
                        name: 'name',
                        value: 'James Dean'
                    }
                ],
                modelFields: {name: 'ExampleName', description: 'ExampleDescription'},
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false,
                        value: 'James Dean'
                    }],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        link: false,
                        value: 'This is the description.'
                    }
                ]
            },
            {
                highlights: [
                    {
                        name: 'case_number',
                        value: 12
                    },
                    {
                        name: 'name',
                        value: 'James Dean'
                    }
                ],
                modelFields: {'case_number' : 12, name: 'ExampleName', description: 'ExampleDescription'},
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false,
                        value: 'James Dean'
                    }
                ],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        link: false
                    },
                    {
                        name: 'case_number',
                        value: 12
                    }
                ]
            },
            {
                highlights: [],
                modelFields: {name: 'ExampleName', description: 'ExampleDescription'},
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false
                    }
                ],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        link: false
                    }
                ]
            },
            {
                highlights: [],
                modelFields: null,
                expectedPrimaryFields: [
                    {
                        // We don't ever reject primary fields
                        name: 'name',
                        primary: true,
                        ellipsis: false
                    }
                ],
                expectedSecondaryFields: [
                ]
            }
        ], function(val) {
            it('should create "primaryFields" and "secondaryFields" property on the model',
                function() {
                    model.set('_highlights', val.highlights);
                    model.set(val.modelFields);
                    model.fields = {};
                    view.parseModels([model]);
                    expect(model.primaryFields).toEqual(val.expectedPrimaryFields);
                    expect(model.secondaryFields).toEqual(val.expectedSecondaryFields);
                }
            );
        });
    });
});
