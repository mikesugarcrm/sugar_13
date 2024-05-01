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
describe('Base.View.ReportDashletListPreviewView', function() {
    var app;
    var context;
    var meta;
    var view;
    var viewName = 'report-dashlet-list-preview';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'report-dashlet-list-preview');

        context = new app.Context();
        context.set({
            model: new Backbone.Model({
                label: 'testLabel',
                sortOrder: 'asc',
                reportType: 'detailed_summary',
                displayColumns: ['status', 'priority'],
            }),
            module: 'Home',
        });

        meta = {
            config: false
        };

        view = SugarTest.createView('base', null, viewName, {}, context, true);

        app.drawer = {
            $: function() {
                return {
                    empty: sinon.stub(),
                    append: sinon.stub(),
                };
            },
        };
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();

        app = null;
        view = null;
    });

    describe('_initProperties()', function() {
        it('should properly setup default properties', function() {
            view._initProperties();

            expect(view._dashletTitle).toEqual('testLabel');
            expect(view._reportType).toEqual('summation-with-detail-skeleton-loader');
        });
    });
});
