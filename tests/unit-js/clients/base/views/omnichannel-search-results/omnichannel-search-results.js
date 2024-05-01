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
describe('View.Views.Base.OmnichannelSearchResultsView', function() {
    var viewName = 'omnichannel-search-results';
    var view;
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView(
            'base',
            undefined,
            viewName,
            null,
            null,
            null,
            layout
        );
        var app = SUGAR.App;
        view.collection = app.data.createMixedBeanCollection();
        view.collection.models = [0, 1, 2, 3, 4];
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('omnichannel:close', function() {
        var closeStub;
        var removeBackdropStub;
        beforeEach(function() {
            closeStub = sinon.stub(view, 'close');
            removeBackdropStub = sinon.stub(view, 'removeBackdrop');
        });

        it('should call removeBackdrop and close', function() {
            view.layout.trigger('omnichannel:close');
            expect(closeStub).toHaveBeenCalled();
            expect(removeBackdropStub).toHaveBeenCalled();
        });
    });

    describe('omnichannel:results:close', function() {
        var closeStub;
        var removeBackdropStub;
        beforeEach(function() {
            closeStub = sinon.stub(view, 'close');
            removeBackdropStub = sinon.stub(view, 'removeBackdrop');
        });

        it('should call removeBackdrop and close', function() {
            view.layout.trigger('omnichannel:results:close');
            expect(closeStub).toHaveBeenCalled();
            expect(removeBackdropStub).toHaveBeenCalled();
        });
    });
});
