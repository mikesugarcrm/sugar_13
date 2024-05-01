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
describe('View.Views.Base.OmnichannelSearchBar', function() {
    var app;
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'omnichannel-search-filter');
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        layout.collection = app.data.createMixedBeanCollection();
        view = SugarTest.createView(
            'base',
            null,
            'omnichannel-search-filter',
            {
                facets: [
                    {
                        facet_id: 'assigned_user_id'
                    },
                    {
                        facet_id: 'favorite_link'
                    },
                    {
                        facet_id: 'created_by'
                    },
                    {
                        facet_id: 'modified_user_id'
                    }
                ]
            },
            null,
            false,
            layout
        );

        view.collection.xmod_aggs = {
            modules: {
                results: {
                    Contacts: 5,
                    Cases: 1
                }
            },
            assigned_user_id: {
                results: {
                    count: 3
                }
            },
            favorite_link: {
                results: {
                    count: 1
                }
            },
            created_by: {
                results: {
                    count: 2
                }
            },
            modified_user_id: {
                results: {
                    count: 4
                }
            }
        };
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.dispose();
        layout.dispose();
        view = null;
        layout = null;
    });

    describe('_initializeSelectedFacets', function() {
        it('should initialize all the available facets to unselected', function() {
            var expectedFacets = {
                modules: [],
                assigned_user_id: false,
                favorite_link: false,
                created_by: false,
                modified_user_id: false
            };

            view._initializeSelectedFacets();
            expect(view.selectedFacets).toEqual(expectedFacets);
            expect(view.context.get('selectedFacets')).toEqual(expectedFacets);
        });
    });

    describe('_initModuleFacets', function() {
        it('should build the module facets objects correctly', function() {
            view._initModuleFacets();
            expect(view.moduleFacets.length).toEqual(2);
            expect(view.moduleFacets[0].count).toEqual(5);
            expect(view.moduleFacets[1].count).toEqual(1);
        });
    });

    describe('_initPropertyFacets', function() {
        it('should build the property facets objects correctly', function() {
            view._initPropertyFacets();
            expect(view.propertyFacets.length).toEqual(4);
            expect(view.propertyFacets[0].count).toEqual(3);
            expect(view.propertyFacets[1].count).toEqual(1);
            expect(view.propertyFacets[2].count).toEqual(2);
            expect(view.propertyFacets[3].count).toEqual(4);
        });
    });

    describe('_updateSelectedFacets', function() {
        beforeEach(function() {
            view._initializeSelectedFacets();
        });

        it('should correctly update module facets', function() {
            view._updateSelectedFacets('modules', 'Contacts', false);
            view._updateSelectedFacets('modules', 'Leads', false);

            expect(view.selectedFacets).toEqual({
                modules: ['Leads', 'Contacts'],
                assigned_user_id: false,
                favorite_link: false,
                created_by: false,
                modified_user_id: false
            });
        });

        it('should correctly update property facets', function() {
            view._updateSelectedFacets('assigned_user_id', 'assigned_user_id', true);
            view._updateSelectedFacets('favorite_link', 'favorite_link', true);
            expect(view.selectedFacets).toEqual({
                modules: [],
                assigned_user_id: true,
                favorite_link: true,
                created_by: false,
                modified_user_id: false
            });
        });
    });

    describe('facetClicked', function() {
        var event;

        beforeEach(function() {
            event = {
                currentTarget: '<div></div>'
            };

            sinon.stub(view, '$').returns($('<div></div>'));
            sinon.stub(view, '_updateSelectedFacets');
            sinon.stub(view.collection, 'fetch');
        });

        it('should update the selected facets', function() {
            view.facetClicked(event);
            expect(view._updateSelectedFacets).toHaveBeenCalled();
        });

        it('should trigger a refetch of the collection with the selected facets', function() {
            view.facetClicked(event);

            expect(view.collection.fetch).toHaveBeenCalledWith({
                apiOptions: {
                    data: {
                        agg_filters: view.selectedFacets
                    },
                    fetchWithPost: true,
                    useNewApi: true
                }
            });
        });
    });
});
