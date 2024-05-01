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
describe('View.Views.Base.OmnichannelSearchListBottomView', function() {
    var app;
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'omnichannel-search-list-bottom');
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        layout.collection = app.data.createMixedBeanCollection();
        view = SugarTest.createView(
            'base',
            null,
            'omnichannel-search-list-bottom',
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

    describe('setShowMoreLabel', function() {
        it('should set show more label if multiple modules are processed', function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_SHOW_MORE_RESULTS').returns('More search results');
            var showMoreLabel = 'More search results';
            view.collection.dataFetched = true;
            view.collection.models = [{id: 12345}, {id: 67890}];
            view.collection.module_list = ['Accounts', 'Contacts'];
            view.setShowMoreLabel(view.collection);
            expect(view.showMoreLabel).toEqual(showMoreLabel);
        });

        it('should not set show more label if collection is empty', function() {
            view.showMoreLabel = 'oldlabel';
            view.collection.reset();
            view.setShowMoreLabel();
            expect(view.showMoreLabel).toEqual('oldlabel');
        });
    });

    describe('showMoreRecords', function() {
        it('should set collection offset before Pagination fetch', function() {
            view.paginationComponent = {
                getNextPagination: function() {}
            };
            var getNextPaginationStub = sinon.stub(view.paginationComponent, 'getNextPagination');
            view.collection.next_offset = 20;
            view.showMoreRecords();
            expect(getNextPaginationStub).toHaveBeenCalled();
            expect(view.collection.offset).toEqual(view.collection.next_offset);
        });
    });
});
