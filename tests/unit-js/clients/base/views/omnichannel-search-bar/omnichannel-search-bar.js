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
        SugarTest.loadComponent('base', 'view', 'omnichannel-search-bar');
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        view = SugarTest.createView(
            'base',
            null,
            'omnichannel-search-bar',
            null,
            null,
            false,
            layout
        );
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.dispose();
        layout.dispose();
        view = null;
        layout = null;
    });

    describe('keyUpHandler', function() {
        beforeEach(function() {
            sinon.stub(view, 'fireSearch');
            sinon.stub(view, 'fireQuickSearch');
        });

        it('should fire a full search if the user hits enter', function() {
            view.fireQuickSearch.cancel = sinon.stub();
            var event = {
                keyCode: 13
            };
            view.keyupHandler(event);
            expect(view.fireQuickSearch.cancel).toHaveBeenCalled();
            expect(view.fireSearch).toHaveBeenCalled();
        });

        it('should fire a quick search if the user enters anything other than enter', function() {
            var event = {
                keyCode: 65
            };
            view.keyupHandler(event);
            expect(view.fireQuickSearch).toHaveBeenCalled();
        });
    });

    describe('fireSearch', function() {
        var defaultFetchOptions;

        beforeEach(function() {
            defaultFetchOptions = {
                term: 'test term',
                module_list: ['Accounts', 'Cases'],
                limit: 20
            };

            sinon.stub(view, '_getDefaultFetchOptions').returns(defaultFetchOptions);
            sinon.stub(view.collection, 'fetch');
            sinon.stub(app.events, 'trigger');
        });

        it('should fetch the collection with the default options', function() {
            view.fireSearch();
            expect(view.collection.fetch).toHaveBeenCalledWith(defaultFetchOptions);
        });
    });

    describe('fireQuickSearch', function() {
        var defaultFetchOptions;

        beforeEach(function() {
            defaultFetchOptions = {
                term: 'test term',
                module_list: ['Accounts', 'Cases'],
                limit: 20
            };

            sinon.stub(view, '_getDefaultFetchOptions').returns(defaultFetchOptions);
            sinon.stub(view.layout, 'trigger');
        });

        it('should delegate the quicksearch to another view that uses a separate collection', function() {
            view.fireQuickSearch();
            expect(view.layout.trigger).toHaveBeenCalledWith('omnichannelsearch:quicksearch:fire', defaultFetchOptions);
        });
    });
});
