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
describe("Base.View.ListBottom", function () {
    var view, app;
    var bottomView;
    var layout;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list-bottom');
        view = SugarTest.createView("base", "Opportunities", "list", null, null);
        app = SUGAR.App;
        layout = app.view.createLayout({type: 'base'});
        bottomView = SugarTest.createView('base', 'Opportunities', 'list-bottom', null, null, false, layout);
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        bottomView.dispose();
        layout.dispose();
        bottomView = null;
        layout = null;
    });

    it('should module names start with lowercase letters', function() {
        var lowerCaseModuleName = 'opportunities';
        var showMoreLabel = app.lang.get(view.options.meta.showMoreLabel, 'Opportunities', {
            module: app.lang.getModuleName(lowerCaseModuleName, {plural: true})
        });
        expect(view.showMoreLabel).toEqual(showMoreLabel);

    });

    it('should not set show more label if collection is not available', function() {
        bottomView.showMoreLabel = 'oldlabel';
        bottomView.collection = null;
        bottomView.setShowMoreLabel();
        expect(bottomView.showMoreLabel).toEqual('oldlabel');
    });

    it('should set collection offset before Pagination fetch', function() {
        bottomView.paginationComponent = {
            getNextPagination: function() {}
        };
        var getNextPaginationStub = sinon.stub(bottomView.paginationComponent, 'getNextPagination');
        bottomView.collection.length = 5;
        bottomView.showMoreRecords();
        expect(getNextPaginationStub).toHaveBeenCalled();
        expect(bottomView.collection.offset).toEqual(bottomView.collection.length);
    });
});
