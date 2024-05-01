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
describe('Base.View.DashletSearchControlsView', function() {
    var view;
    var app;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'dashlet-search-controls');
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        view = SugarTest.createView(
            'base',
            'Opportunities',
            'dashlet-search-controls',
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

    describe('throttledSearch', function() {
        it('should call applyQuickSearch method', function() {
            sinon.stub(view, 'applyQuickSearch');
            view.throttledSearch();

            expect(view.applyQuickSearch).toHaveBeenCalled();
        });
    });

    describe('clearQuickSearch', function() {
        it('should call applyQuickSearch method', function() {
            sinon.spy(view, '$');
            view.$el.html('<input type="text" class="search-name" value="test">');
            sinon.stub(view, 'applyQuickSearch');
            view.clearQuickSearch();

            expect(view.applyQuickSearch).toHaveBeenCalled();
            expect(view.$('input.search-name').val()).toEqual('');
        });
    });

    describe('applyQuickSearch', function() {
        beforeEach(function() {
            sinon.stub(jQuery.fn, 'val').callsFake(function() {
                return 'newSearch';
            });
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should do nothing if the search term has not changed', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.currentSearch = 'newSearch';
            view.applyQuickSearch();

            expect(triggerStub).not.toHaveBeenCalled();
        });

        it('should fire a search if the search term has changed', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.currentSearch = '';
            view.applyQuickSearch();

            expect(triggerStub).toHaveBeenCalledWith('dashlet:controls:search', 'newSearch');
        });
    });

    describe('toggleClearQuickSearchIcon', function() {
        beforeEach(function() {
            sinon.spy(view, '$');
        });

        it('should remove .sicon-close.add-on if addIt is false', function() {
            view.$el.html('<div class="filter-view search">' +
                '<i class="sicon sicon-close add-on"></i>' +
                '</div>');

            view.toggleClearQuickSearchIcon(false);

            expect(view.$('.sicon-close.add-on').css('display')).toEqual('none');
        });

        it('should add .sicon-close.add-on if addIt is true and element is not present', function() {
            view.$el.html('<div class="filter-view search">' +
                '<i class="sicon sicon-close add-on" style="display: none;"></i>' +
                '</div>');
            view.toggleClearQuickSearchIcon(true);

            expect(view.$('.sicon-close.add-on').css('display')).not.toEqual('none');
        });
    });

});
