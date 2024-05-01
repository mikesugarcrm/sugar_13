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

describe('Base.Views.ContentsearchResults', function() {
    var app;
    var view;
    var renderStub;

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('base', 'view', 'contentsearch-results');
        view = SugarTest.createView(
            'base',
            null,
            'contentsearch-results',
            null,
            context,
            true,
            null,
            true,
            'base'
        );
        renderStub = sinon.stub(view, 'render');
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sinon.restore();
    });

    describe('showData', function() {
        it('should set data and render', function() {
            var data = {
                records: [
                    {
                        name: 'name',
                        description: 'desc',
                        url: 'url'
                    }
                ]
            };
            view.showData(data);
            expect(view.records).toEqual(data.records);
            expect(view.dataFetched).toBeTruthy();
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('showFetching', function() {
        it('should set no data and render', function() {
            view.showFetching();
            expect(view.records).toEqual([]);
            expect(view.dataFetched).toBeFalsy();
            expect(renderStub).toHaveBeenCalled();
        });
    });
});
