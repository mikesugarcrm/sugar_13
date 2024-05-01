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
describe('Filter Quick Search View', function() {

    var view, app, parentLayout, filtersBeanPrototype;

    beforeEach(function() {
        app = SUGAR.App;
        parentLayout = app.view.createLayout({type: 'base'});
        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'Filters');
        view = SugarTest.createView('base', 'Accounts', 'filter-quicksearch', {}, false, false, parentLayout);
        view.layout = parentLayout;
        filtersBeanPrototype = app.data.getBeanClass('Filters').prototype;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.dispose();
        parentLayout.dispose();
        view = null;
        parentLayout = null;
    });

    it('should call clear input on filter:clear:quicksearch', function() {
        var stub = sinon.stub(view, 'clearInput');
        view.initialize(view.options);
        parentLayout.trigger('filter:clear:quicksearch');
        expect(stub).toHaveBeenCalled();
        stub.restore();
    });

    it('should trigger quick search change on applyQuickSearch', function() {
        var spy = sinon.spy();
        parentLayout.on('filter:apply', spy);
        view.applyQuickSearch(true);
        expect(spy).toHaveBeenCalled();
    });

    it('should call applyQuickSearch on throttle search', function() {
        var stub = sinon.stub(view, 'applyQuickSearch');
        view.throttledSearch();
        expect(stub).toHaveBeenCalled();
    });

    it('should trigger filter:apply on clearInput', function() {
        var spy = sinon.spy();
        parentLayout.on('filter:apply', spy);
        view.clearInput();
        expect(spy).toHaveBeenCalled();
    });

    it('should update placeholder with field labels on filter:change:module', function() {
        var updatePlaceholderSpy = sinon.spy(view, 'updatePlaceholder');
        view.initialize(view.options);

        let metadataStub = sinon.stub(app.metadata, 'getModule').returns({
            fields: {
                first_name: {
                    vname: 'LBL_FIRST_NAME'
                },
                last_name: {
                    vname: 'LBL_LAST_NAME'
                }
            }
        });
        let getModuleQuickSearchMetaStub = sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta').returns({
            fieldNames: ['first_name', 'last_name']
        });

        parentLayout.trigger('filter:change:module', 'Contacts', 'contacts');
        expect(updatePlaceholderSpy).toHaveBeenCalled();
        expect(view.$el.attr('placeholder')).toEqual('LBL_SEARCH_BY lbl_first_name, lbl_last_name...');
        updatePlaceholderSpy.restore();
        metadataStub.restore();
        getModuleQuickSearchMetaStub.restore();
    });
});
