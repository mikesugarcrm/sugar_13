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
describe('View.Views.Base.OmnichannelSearchList', function() {
    var app;
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'omnichannel-search-list');
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        layout.collection = app.data.createMixedBeanCollection();
        view = SugarTest.createView(
            'base',
            null,
            'omnichannel-search-list',
            null,
            null,
            false,
            layout
        );
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

    describe('getPaginationOptions', function() {
        beforeEach(function() {
            view.context.set('selectedFacets', 'fakeSelectedFacets');
        });

        it('should return the API options with the currently selected facets', function() {
            var expectedOptions = {
                apiOptions: {
                    data: {
                        agg_filters: 'fakeSelectedFacets'
                    },
                    fetchWithPost: true,
                    useNewApi: true
                }
            };
            expect(view.getPaginationOptions()).toEqual(expectedOptions);
        });
    });
});
